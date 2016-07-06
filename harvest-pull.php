<?php

define('HARVEST_URL', 'https://example.harvestapp.com');
define('LOGIN', '');
define('PASS', '');

$time = $argv[1];

$from_to = get_from_to_for_command_line_option($time);

$from = $from_to['from'];
$to = $from_to['to'];

$opts = [
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n".
                    "Accept: application/json\r\n".
                    "Authorization: Basic " .  base64_encode(LOGIN . ':' . PASS) . "\r\n"
    ]
];

$context = stream_context_create($opts);

$who_am_i = json_decode(file_get_contents(HARVEST_URL . '/account/who_am_i', false, $context));
$user_id = $who_am_i->user->id;

$timereport = json_decode(file_get_contents(HARVEST_URL . '/people/' . $user_id . '/entries?from='.$from.'&to=' . $to, false, $context));

$tasks_and_time = [];
foreach ($timereport as $entity) {
    if (preg_match('/\[#.+?\]/', $entity->day_entry->notes, $matches)) {
        $task_id = $matches[0];
        if (empty($tasks_and_time[$task_id])) {
            $tasks_and_time[$task_id] = $entity->day_entry->hours;
        } else {
            $tasks_and_time[$task_id] = $tasks_and_time[$task_id] + $entity->day_entry->hours;
        }
    }
}

print_tasks_and_time($tasks_and_time);


function print_tasks_and_time($tasks_and_time) {
    foreach ($tasks_and_time as $task_id => $time) {
        $time_parts = fetch_hours_and_minutes_from_formatted_time_string(convertTime($time));
        echo 'update ' . $task_id . "\t" .' with ' . "\t" .  $time_parts['hours'] . 'h ' . $time_parts['minutes'] . 'm' . "\n";

        update_in_jira($task_id, $time_parts['hours'] . 'h' . ' ' . $time_parts['minutes'] . 'm');
    }
}

function update_in_jira($task_id, $time) {
    exec('php jira-push.php ' . $task_id . ' "' . $time . '"');
}

function convertTime($dec) {
    // start by converting to seconds
    $seconds = ($dec * 3600);
    // we're given hours, so let's get those the easy way
    $hours = floor($dec);
    // since we've "calculated" hours, let's remove them from the seconds variable
    $seconds -= $hours * 3600;
    // calculate minutes left
    $minutes = floor($seconds / 60);
    // remove those from seconds as well
    $seconds -= $minutes * 60;
    // return the time formatted HH:MM:SS
    return $hours.":".$minutes.":".$seconds;
}


function fetch_hours_and_minutes_from_formatted_time_string($time_string) {
    $string_parts = explode(':', $time_string);
    return [
        'hours' => $string_parts[0],
        'minutes' => $string_parts[1]
    ];
}

function get_from_to_for_command_line_option($time_word) {
    switch ($time_word) {
        case 'week':
            $datetime_from = new DateTime();
            $from = $datetime_from->modify('last monday');
            $from_string = $from->format('Ymd');
            $to = $datetime_from->modify('+6 days');
            $to_string = $to->format('Ymd');
            return [
                'from' => $from_string,
                'to' => $to_string
            ];
            break;

        case 'month':
            $datetime_from = new DateTime();
            $from = $datetime_from->modify('first day of this month');
            $from_string = $from->format('Ymd');
            $to = $datetime_from->modify('last day of this month');
            $to_string = $to->format('Ymd');
            return [
                'from' => $from_string,
                'to' => $to_string
            ];
            break;
        case 'today':
            $datetime_from = new DateTime();
            $date_string = $datetime_from->format('Ymd');
            return [
                'from' => $date_string,
                'to' => $date_string
            ];
            break;

        default:
            // default is week
            $datetime_from = new DateTime();
            $from = $datetime_from->modify('last monday');
            $from_string = $from->format('Ymd');
            $to = $datetime_from->modify('+6 days');
            $to_string = $to->format('Ymd');
            return [
                'from' => $from_string,
                'to' => $to_string
            ];
            break;
    }
}