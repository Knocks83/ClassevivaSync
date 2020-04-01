<?php
// Working mode
$strict = false;                                // Whether to delete the events not in Classeviva Agenda
$logPath = dirname(__FILE__) . '/log.txt';      // Path for the log file (default: dirname(__FILE__) . '/log.txt')
$debugPath = null;                              /* If set to a path different from null, enables debugging to that file
                                                    suggested: dirname(__FILE__) . '/debug.txt', default: null*/

// Classeviva
$classevivaUsername = '';               // Classeviva login name (can be a email or a username)
$classevivaPassword = '';               // Classeviva login password
$classevivaIdentity = '';               /* If you login via email and you have more than one account
                                            connected to the email, you must set the username of the 
                                            account you want to check*/

// Google Calendar
$secretName = dirname(__FILE__) . '/client_secret.json';     // The client secret file name
$calendarId = 'primary';                // The calendar ID (NOT THE NAME!) (default: primary)
