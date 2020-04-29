<?php
include 'Classeviva.php';
include 'calendar.php';
require 'config.php';

if ($classevivaIdentity == '') {
    $classevivaIdentity = null;
}

file_put_contents($logPath, date('c') . PHP_EOL, FILE_APPEND);

// Create a Classeviva object and login
try {
    $session = new Classeviva($classevivaUsername, $classevivaPassword, $classevivaIdentity);
} catch (Exception $e) {
    file_put_contents($logPath, 'There was an error with Classeviva!' . PHP_EOL, FILE_APPEND);
    die($e->getMessage());
}

// Gets today's day of the month
$today = strval(date('d'));
if (strlen($today) < 2) {    // If it's just one number (eg. 3) it adds a 0 (so it's 03)
    $today = '0' . $today;
}
$startDate = date('Ym' . $today);
unset($today);

if ((int) date('m') > 8) {
    $endDate = date((date('Y') + 1) . "0831");
} else {
    $endDate = date('Y0831');
}

// Get Classeviva Agenda and transform it in an event array
$agenda = $session->agenda($startDate, $endDate);
$agenda = json_decode($agenda);
if (property_exists($agenda, 'error')) {
    throw new Exception($agenda->error, $agenda->statusCode);
}
$events = $session->convertClassevivaAgenda($agenda);
unset($agenda, $startDate, $endDate);

// Get Google Calendar events
try {
    $googleCalendar = getEvents($calendarId, date('Y-m-d\TH:i:sP', strtotime('today midnight')));
} catch (\InvalidArgumentException $th) {
    die('You forgot the client secret file!' . PHP_EOL);
}

// Google events summary array to compare with classevivaEvents.
$gEvents = array();
foreach ($googleCalendar as $event) {
    $gEvents[] = $event->getSummary();
}

// Generate debug infos
if ($debugPath != null) {
    $toWrite = date('c') . "$calendarId\n
    classeviva\n
    " . print_r($events, true) . "
    Google\n
    " . print_r($gEvents, true);
    $debugFile = fopen($debugPath, 'w+');
    fwrite($debugFile, $toWrite);
    fclose($debugFile);
    unset($toWrite);
}


if (!empty($events)) {

    if ($strict) {
        // If the strict mode is enabled, proceed to check whether the events in the
        // Google Calendar are really Classeviva Events
        $cEvents = array();
        foreach ($events as $event) {
            $cEvents[] = $event->authorName . ': ' . $event->notes;
        }

        foreach ($gEvents as $i => $event) {
            if (!in_array($event, $cEvents)) {
                $name = $event->authorName . ': ' . $event->notes;

                file_put_contents($logPath, '-' . $name . PHP_EOL, FILE_APPEND);
                delEvent($calendarId, $googleCalendar[$i]->getId());
            } elseif ($strict && $events[$i]->evtDatetimeBegin != $googleCalendar[$i]->getStart()->dateTime && $events[$i]->evtDatetimeEnd != $googleCalendar[$i]->getEnd()->dateTime) {
                delEvent($calendarId, $googleCalendar[$i]->getId());
            }
        }
    }

    // If there are elements in the Classeviva Agenda check whether to add them.
    foreach ($events as $i => $event) {
        $name = $event->authorName . ': ' . $event->notes;

        if (!in_array($name, $gEvents)) {
            file_put_contents($logPath, '+' . $name . PHP_EOL, FILE_APPEND);
            addEvent($calendarId, $name, $event->evtDatetimeBegin, $event->evtDatetimeEnd);
        }
    }

} else {
    if ($strict && $deleteOnEmptyResponse) {
        // If there aren't elements in the Classeviva Agenda and the Strict Mode is enabled,
        // delete the elements that are in Google Calendar.
        if (!empty($gEvents)) {
            foreach ($gEvents as $i => $event) {
                file_put_contents($logPath, '----' . $name . PHP_EOL, FILE_APPEND);
                delEvent($calendarId, $googleCalendar[$i]->getId());
            }
        }
    }
}
