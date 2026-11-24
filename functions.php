<?php

function loadSiteContent(): array
{
    $path = __DIR__ . '/data/site.json';
    $json = file_get_contents($path);
    if ($json === false) {
        throw new RuntimeException('Unable to read site content.');
    }

    $content = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($content)) {
        throw new RuntimeException('Site content must be a JSON object.');
    }

    foreach (['name', 'description', 'logo', 'soundcloud', 'soundcloud_embed', 'facebook', 'github'] as $field) {
        if (!isset($content[$field]) || !is_string($content[$field]) || $content[$field] === '') {
            throw new RuntimeException("Site content is missing $field.");
        }
    }

    return $content;
}

function loadUpdates(): array
{
    $path = __DIR__ . '/data/updates.json';
    $json = file_get_contents($path);
    if ($json === false) {
        throw new RuntimeException('Unable to read updates.');
    }
    $feed = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($feed) || !isset($feed['workflow_runs']) || !is_array($feed['workflow_runs'])) {
        throw new RuntimeException('Invalid updates feed.');
    }
    foreach ($feed['workflow_runs'] as $entry) {
        if (!is_array($entry) || !isset($entry['head_sha'], $entry['created_at'], $entry['display_title'])
            || !is_string($entry['head_sha']) || !preg_match('/\A[0-9a-f]{40}\z/', $entry['head_sha'])
            || !is_string($entry['created_at']) || strtotime($entry['created_at']) === false
            || !is_string($entry['display_title'])) {
            throw new RuntimeException('Invalid updates entry.');
        }
    }
    return $feed['workflow_runs'];
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
