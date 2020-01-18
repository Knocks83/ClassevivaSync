<?php
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */

function getClient()
{
    include __DIR__ . '/config.php';

    $client = new Google_Client();
    $client->setApplicationName('Classeviva Sync');
    $client->setScopes(Google_Service_Calendar::CALENDAR);
    $client->setAuthConfig($secretName);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    $tokenPath = dirname(__FILE__) . '/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Gets the Calendar events
 * @return evt_sum the Calendar events
 */
function getEvents($calendarId, $timeMin)
{
    // Get the API client and construct the service object.
    $client = getClient();
    $service = new Google_Service_Calendar($client);

    // Get the Calendar events
    $optParams = array(
        'singleEvents' => false,   // Returns once the recurring events
        'timeMin' => $timeMin,   // Doesn't get the events before today
    );
    $results = $service->events->listEvents($calendarId, $optParams);
    $events = $results->getItems();

    if (empty($events)) {
        return null;
    } else {
        $evt_sum = [];
        foreach ($events as $event) {
            array_push($evt_sum, $event->getSummary());
        }
        return ($evt_sum);
    }
}

/**
 * Adds a event in the calendar
 */
function addEvent($calendarId, $summary, $start_date, $end_date)
{
    $client = getClient();
    $service = new Google_Service_Calendar($client);

    $event = new Google_Service_Calendar_Event(array(
        'summary' => $summary,
        'location' => '',
        'description' => '',
        'start' => array(
            'dateTime' => $start_date,
        ),
        'end' => array(
            'dateTime' => $end_date,
        ),
        'reminders' => array(
            'useDefault' => FALSE,
            'overrides' => array(
                array('method' => 'popup', 'minutes' => 24 * 60),
            ),
        ),
    ));

    $event = $service->events->insert($calendarId, $event);
}
