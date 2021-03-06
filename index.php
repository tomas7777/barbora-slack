<?php

require_once 'vendor/autoload.php';

$barboraCookie = '';
$slackToken = '';
$slackChannel = '';

$barboraClient = new \GuzzleHttp\Client([
    'base_uri' => 'https://barbora.lt',
]);

$response = $barboraClient->request(
    'GET',
    '/api/eshop/v1/cart/deliveries',
    [
        'headers' => [
            'Authorization' => 'Basic YXBpa2V5OlNlY3JldEtleQ==',
            'Cookie' => $barboraCookie,
        ],
    ]
);

$validTimes = [];

$data = json_decode($response->getBody()->getContents(), true);

foreach ($data['deliveries'][0]['params']['matrix'] as $day) {
    foreach ($day['hours'] as $hour) {
        if ($hour['available'] === true) {
            $validTimes[] = [
                'day' => $day['id'],
                'hour' => $hour['hour'],
            ];
        }
    }
}

$slackClient = new \GuzzleHttp\Client([
  'base_uri' => 'https://slack.com'
]);

foreach ($validTimes as $validTime) {
    $slackClient->request(
        'POST',
        '/api/chat.postMessage',
        [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $slackToken),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'channel' => $slackChannel,
                'text' => sprintf('Valid time: %s - %s', $validTime['day'], $validTime['hour']),
            ]),
        ]
    );
}

