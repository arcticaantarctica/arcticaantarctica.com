<?php
require_once __DIR__ . '/functions.php';
$siteContent = loadSiteContent();
$date = $_GET['a'] ?? '';
$eventIndex = is_string($date) ? array_search($date, array_column($siteContent['events'], 'date'), true) : false;
if ($eventIndex === false) {
    http_response_code(404);
    exit('Event not found.');
}

$event = $siteContent['events'][$eventIndex];
$eventCount = count($siteContent['events']);
$previousEvent = $siteContent['events'][($eventIndex + 1) % $eventCount];
$nextEvent = $siteContent['events'][($eventIndex - 1 + $eventCount) % $eventCount];
$pagePath = 'event';
$pageName = $event['name'];
require __DIR__ . '/content.php';
