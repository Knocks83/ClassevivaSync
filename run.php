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
$today = strval(date('d')+1);
if(strlen($today) < 2) {    // If it's just one number (eg. 3) it adds a 0 (so it's 03)
    $today = '0'.$today;
}
$startDate = date('Ym'.$today);
unset($today);

if  ((int)date('m') > 8 ){
    $endDate = date((date('Y')+1)."0831");
}else{
    $endDate = date ('Y0831');
}

$agenda = $session->agenda($startDate, $endDate);
$events = $session->convertClassevivaAgenda($agenda);
unset($agenda, $startDate, $endDate);

try {
    $googleCalendar = getEvents($calendarId, date('c'));
} catch (\InvalidArgumentException $th) {
    die('You forgot the client_secret.json file!');
}

print($calendarId.PHP_EOL);
print_r($googleCalendar);

if (!empty($googleCalendar)) {
    foreach ($events as $event) {
        $name = $event->authorName.': '.$event->notes;
        if (!in_array($name, $googleCalendar)) {
            addEvent($calendarId, $name, $event->evtDatetimeBegin, $event->evtDatetimeEnd);
        }

    }
} else {
    foreach ($events as $event) {
        $name = $event->authorName.': '.$event->notes;
        addEvent($calendarId, $name, $event->evtDatetimeBegin, $event->evtDatetimeEnd);
    }

}
