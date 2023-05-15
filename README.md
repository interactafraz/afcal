# AFcal

AFcal is a tool for setting up monthly and yearly reminders that can be accessed via a single RSS feed. It works great with services that allow RSS feeds as triggers to execute recurring actions like [n8n](https://github.com/n8n-io/n8n), [IFTTT](https://ifttt.com/) and [Zapier](https://zapier.com/).

## Possible use Cases

* Use as trigger for creating a card on a kanban board for manual NAS backups
* Low-priority tasks at home (like cleaning your washing machine filter every January 1st and June 1st)
* Reminders for manually backing up family photos to an external drive
* Quarterly meetings with your accountant to keep your business running smoothly

## Features

* Self host-able
* Available in German (default language) and English
* Manage reminders via responsive backend interface
* Enable/disable reminders via GET parameters
* Add reminders to groups for better organization
* Saves data to JSON file

## Prerequisites

* Common web server with PHP support

## Installation

1. Set your dashboard language in inc/globals.php
2. Copy files to web server

## Usage

Access the script via `index.php` to get the RSS feed. To add, remove, enable or disable reminders, use the dashboard.

### Dashboard (dashboard.php)

The dashboard lists all existing reminders and allows you to enable/disable them. Reminders only appear in the RSS feed when they are enabled. To enable/disable reminders via GET parameters, use `dashboard.php?start=reminder-id` or `dashboard.php?stop=reminder-id`.

To add/remove reminders, use the `Edit list` button, which opens a form with the following fields:

**ID**: A unique ID for your reminder, used for the timestamp filename and as the item description in the RSS feed. Only regular english letters, numbers and hyphens recommended.

**Title**: The RSS item title.

**Interval**: Two types of reminder intervals are supported.

* Monthly: Use the syntax `Monthly` + `[day number of month]`. Always use a two-digit number (like `Monthly04` for the 4th day of every month).
* Yearly: Use the syntax `[month number]-[day number]`. Always use two-digit numbers (like `02-06` for February 6th or `11-25` for November 25th).

**Group**: An advanced feature for users with many reminders. Use it to handle reminders with different priorities efficiently. Two groups are available.

* All: For reminders with both low and high priority.
* Essential: For reminders with high priority, such as health routines or important daily tasks.

Groups can be useful for the control system that gets triggered by the RSS feed. You could, for example, set up a *Global Timeout Status* variable that helps to exclude RSS items based on their reminder group when you're on vacation.

> Note: Group values do not affect the visibility of RSS feed items.

## Roadmap

- [ ] UI improvement: Redesign overall look and feel
- [ ] UI improvement: Add interval picker to edit form
- [ ] UI improvement: Validate ID syntax while typing into edit form
- [ ] Expose group for each item in RSS feed
- [ ] Allow users to add custom groups
- [ ] Allow users to hide advanced features if not required to declutter UI

## Notes

* If you plan to handle sensitive data with this tool on a publicly accessible server, consider hardening your system using at least htaccess restrictions.
* AFcal comes from *AFRAZ* and *calendar* since it behaves like a calendar for reminders.

## License

[MIT](https://github.com/interactafraz/afcal/blob/main/LICENSE.txt)