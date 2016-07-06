<?php

define('JIRA_URL', 'http://jira-url.com');
define('LOGIN', '');
define('PASS', '');

$task_id = $argv[1];
$time_spent = $argv[2];

$task_id = str_replace('[#', '', $task_id);
$task_id = str_replace(']', '', $task_id);

$opts_time_spent = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n".
                    "Accept: application/json\r\n".
                    "Authorization: Basic " .  base64_encode(LOGIN . ':' . PASS) . "\r\n",
        "content" => json_encode([
            'timeSpent' => $time_spent
        ])
    ]
];

$context = stream_context_create($opts_time_spent);

$opts_get_worklog = [
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n".
                    "Accept: application/json\r\n".
                    "Authorization: Basic " .  base64_encode(LOGIN . ':' . PASS) . "\r\n",
    ]
];

$worklog_context = stream_context_create($opts_get_worklog);

$worklog_response = json_decode(file_get_contents(JIRA_URL . '/rest/api/2/issue/'.$task_id.'/worklog', false, $worklog_context));

/**
 * remove existing logs
 */
foreach ($worklog_response->worklogs as $worklog) {
    remove_worklog($worklog->self);
}


$edit_response = json_decode(file_get_contents(JIRA_URL . '/rest/api/2/issue/'.$task_id.'/worklog', false, $context));

//var_dump($edit_response);


function remove_worklog($self) {
    $opts_get_worklog = [
        'http' => [
            'method' => 'DELETE',
            'header' => "Content-Type: application/json\r\n".
            "Accept: application/json\r\n".
            "Authorization: Basic " .  base64_encode(LOGIN . ':' . PASS) . "\r\n",
        ]
    ];

    $worklog_context = stream_context_create($opts_get_worklog);

    file_get_contents($self, false, $worklog_context);
}