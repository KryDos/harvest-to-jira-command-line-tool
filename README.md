Harvest -> Jira worklog update
==============================

What is it
----------
Script goes to your harvest account, parse tasks and updates JIRA tasks with spent time.

How to use
----------

* each task in harvest should be annotated with `[#TASK-ID]` (e.g. `[#WELL-100]`)
* it does not matter if annotation is at beginning or middle or end of the description

Configuration
-------------

* go to `harvest-pull.php`
* change `LOGIN` and `PASS` constants
* go to `jira-push.php`
* change `LOGIN` and `PASS` constants

How to Run
----------
You need to run only `harvest-pull.php` script. This script will automatically invoke `jira-push.php` in case tasks are found.

`php harvest-pull.php today`

**available options for the script**:

* today - fetch only today's tasks and update JIRA
* week - fetch only tasks for current week and update JIRA
* month - fetch only tasks for current month and update JIRA