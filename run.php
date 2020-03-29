<?php
include 'Classeviva.php';
include 'calendar.php';
require 'config.php';

if ($classevivaIdentity == '') {
    $classevivaIdentity = null;
}

// Create a Classeviva object and login
$session = new Classeviva($classevivaUsername, $classevivaPassword, $classevivaIdentity);

// Gets today's day of the month
$today = strval(date('d') + 1);
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

$agenda = $session->agenda($startDate, $endDate);
$events = $session->convertClassevivaAgenda($agenda);
unset($agenda, $startDate, $endDate);

try {
    $googleCalendar = getEvents($calendarId, date('c'));
} catch (\InvalidArgumentException $th) {
    die('You forgot the client secret file!' . PHP_EOL);
}

// Google events summary array to check the classevivaEvents.
$gEvents = array();
foreach ($googleCalendar as $event) {
    $gEvents[] = $event->getSummary();
}

print($calendarId . PHP_EOL);
print_r($gEvents);
print_r($events);


if (!empty($events)) {
    // If there are elements in the Classeviva Agenda check whether to add them.
    foreach ($events as $event) {
        $name = $event->authorName . ': ' . $event->notes;

        if (!in_array($name, $gEvents)) {
            addEvent($calendarId, $name, $event->evtDatetimeBegin, $event->evtDatetimeEnd);
        }
    }

    if ($strict) {
        // If the strict mode is enabled, proceed to check whether the events in the
        // Google Calendar are really Classeviva Events
        $cEvents = array();
        foreach ($events as $event) {
            $cEvents[] = $event->authorName . ': ' . $event->notes;
        }

        foreach ($gEvents as $i => $event) {
            if (!in_array($event, $cEvents)) {
                delEvent($calendarId, $googleCalendar[$i]->getId());
            }
        }
    }
} else {
    if ($strict) {
        // If there aren't elements in the Classeviva Agenda and the Strict Mode is enabled,
        // delete the elements that are in Google Calendar.
        if (!empty($gEvents)) {
            foreach ($gEvents as $i => $event) {
                delEvent($calendarId, $googleCalendar[$i]->getId());
            }
        }
    }
}
