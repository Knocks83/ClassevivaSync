# ClassevivaSync
Sync Classeviva agenda with Google Calendar

## Requirements

* PHP 7 or newer
* Composer
* A Classeviva account
* A Google Calendar-enabled API secret

### How to create the Google Calendar-enabled API

* Go to the [Google Developers Console](https://console.developers.google.com/)
* Create a project (call it however you want)
* Go to credentials and create a new OAuth client (type: other)
* Save the JSON in the ClassevivaSync folder

Now that you have the APIs credentials you must grant access to the APIs to Google Calendar.
To do this you have to go [here](https://console.developers.google.com/apis/library/calendar-json.googleapis.com) and enable it

## Installation

Clone the repository in your server via

```bash
git clone https://github.com/Knocks83/ClassevivaSync.git
```

and install the Google APIs via

```bash
composer install
```

## Configuration

### Set config file (`config.php`)

* (Optional) Set working mode (whether to delete events that aren't in the Classeviva Calendar)
* (Optional) Change log position/Set debug log position
* Set classeviva Username/email and password
* (Optional) If you login via email and you have more than one accounts linked to the same email you must set a identity 
(the username of the account you want to use)
* Set the name of the Google APIs secret (the one you created before)
* (Optional) Set the calendar ID to your calendar (if you don't know what it is just leave it as "primary")

## First Start

The first start must be made via CLI because it must give you a link to access your Google account.
Start ClassevivaSync by doing 

```bash
php run.php
```

## Additional infos

I remind you that the Classeviva APIs I used in this project are available [here](https://github.com/Knocks83/Classeviva-PHP-Api)

---

That's all Folks!
For help just [ask me on Telegram](https://t.me/MakeNekosNotNukes)!

This Source Code Form is subject to the terms of the GNU Affero General Public License v3.0. 
If a copy of the AGPL-3.0 was not distributed with this file, You can obtain one at <https://www.gnu.org/licenses/gpl-3.0.en.html>.
