# AFcal
AFcal is a tool for setting up monthly and yearly reminders that can be accessed via a single RSS feed.

It works great with services which allow RSS feeds as triggers to execute recurring actions (like creating a card on a kanban board for manual NAS backups).

### Possible use cases
* Low priority tasks at home (like cleaning your washing machine filter every January 1st and June 1st)
* Reminders for manually backing up your family photos to an external drive
* Quarterly meetings with your accountant to keep your business running smoothly

## Features
* Available in German (default language) and English
* Manage reminders via backend interface
* Enable/disable reminders via GET parameters
* Add reminders to groups like "Essential" or "All". Useful if you temporarily need a break from certain reminders or when you're on vacation.
* Saves data to JSON file

## Prerequisites
* Common web server with PHP support

## Installation
1. Set your dashboard language in inc/globals.php
2. Copy files to web server

## Usage
Accessing the script via `index.php` will display the RSS feed only. For adding, removing, enabling and disabling reminders use the dashboard.

### Dashboard (dashboard.php)
The dashboard lists all existing reminders and allows to enable/disable them. If you want to enable/disable reminders via GET parameters, just use `dashboard.php?start=reminder-id` or `dashboard.php?stop=reminder-id`

To add/remove reminders, use the `Edit list` button. This opens a form with the following fields:

**ID**

A unique ID for your reminder. This will be used for the timestamp filename. So you should only use regular english letters, numbers and hyphens. In the RSS feed this ID will also be the item description.

**Title**

This will be the RSS item title.

**Interval**

There are two types of reminder intervals:
* Monthly: The syntax is `Monthly` + `[day number of month]`. This always requires a number with two digits (numbers below 10 must have a leading zero). So if you want to be reminded on the 4th day of every month you must enter `Monthly04`, not `Monthly4`.
* Yearly: The syntax is `[month number]-[day number]`. This always requires numbers with two digits (numbers below 10 must have a leading zero). So February 6th would be `02-06`, November 25th would be `11-25`

**Group**

This is an advanced feature for users with lots of reminders.

If you tend to be overwhelmed from time to time due to huge amounts of reminders within a short period this might be useful for you. AFcal contains a feature to add reminders to groups. You can use this for the master system (that gets triggered by the RSS feed) to handle reminders with different priorities more efficiently. You could, for example, set up a *Global Timeout Status* variable in your master system that allows to filter RSS items based on their reminder group when you're on vacation.

> Please note that this will only affect the JSON file which contains all reminders, not the RSS feed.

There are two groups:
* All: For all reminders with low and high priority
* Essential: For reminders with high priority (like health routines or important daily tasks)

## Notes

* If you're planning to handle sensitive data with this tool on a publicly accessible server you should harden your system using at least htaccess restrictions.
* AFcal comes from *AFRAZ* and *calendar*

## License
tba