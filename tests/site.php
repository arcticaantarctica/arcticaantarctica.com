<?php
require __DIR__ . '/../functions.php';

$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

$siteContent = loadSiteContent();
check($siteContent['name'] === 'Arctica / Antarctica', 'Use the verified artist name');
check($siteContent['soundcloud'] === 'https://soundcloud.com/mc-peko/sets/arctica-antarctica', 'Use the existing SoundCloud set');
check($siteContent['soundcloud_embed'] === 'https://w.soundcloud.com/player/?visual=true&url=https%3A%2F%2Fapi.soundcloud.com%2Fplaylists%2F47853174&show_artwork=true', 'Use the official player URL for the SoundCloud set');
check(is_file(__DIR__ . '/..' . $siteContent['logo']), 'Serve the artist logo from this site');
check(escapeHtml('"&') === '&quot;&amp;', 'Escape HTML attribute content');
$favicon = file_get_contents(__DIR__ . '/../favicon.svg');
check($favicon !== false && str_contains($favicon, 'fill="var(--color1)"'), 'Use the site accent color for the favicon');
check(!str_contains($favicon, '<text'), 'Keep text out of the favicon');
$socialPreview = getimagesize(__DIR__ . '/../img/social_preview.png');
check($socialPreview !== false && $socialPreview[0] === 1200 && $socialPreview[1] === 630, 'Use a 1200 by 630 social preview image');

if (($argv[1] ?? '') === '--http') {
    $base = rtrim($argv[2] ?? '', '/');
    if ($base === '') {
        throw new InvalidArgumentException('Provide the local preview URL after --http.');
    }

    function response(string $path, string $method = 'GET', array $headers = [], string $content = ''): array
    {
        global $base;
        $context = stream_context_create(['http' => [
            'ignore_errors' => true,
            'follow_location' => false,
            'timeout' => 15,
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $content,
        ]]);
        $body = file_get_contents($base . $path, false, $context);
        check($body !== false, "Request failed: $path");
        $headers = http_get_last_response_headers();
        preg_match('~HTTP/\S+ (\d+)~', $headers[0], $status);
        return [(int) $status[1], $body, $headers];
    }

    [$status, $html] = response('/');
    check($status === 200, 'Front page returns HTTP 200');
    check(!preg_match('~(?:Warning|Fatal error|Deprecated):~', $html), 'Front page has no PHP diagnostics');
    check(str_contains($html, '<h1>Arctica / Antarctica</h1>'), 'Render the artist name');
    check(str_contains($html, '<p>Arctica / Antarctica is a Norwegian polar ambient band.</p>'), 'Describe the band on the front page');
    check(str_contains($html, 'name="description" content="Arctica / Antarctica is a Norwegian polar ambient band."'), 'Use the band description in metadata');
    check(str_contains($html, 'property="og:description" content="Arctica / Antarctica is a Norwegian polar ambient band."'), 'Use the band description in social previews');
    check((bool) preg_match('~Produced at <a href="https://focusstudios\.no">Focus Studios <svg class="external-link-icon".*?</a> by\s*<a href="https://peko\.net">Ole Peko Sørensen <svg class="external-link-icon".*?</a>\.~s', $html), 'Credit Focus Studios and Ole Peko Sørensen with external-link icons');
    check(str_contains($html, '<img src="/img/focus_studios.svg" alt="">'), 'Show the Focus Studios logo');
    check(str_contains($html, 'href="https://soundcloud.com/mc-peko/sets/arctica-antarctica">Listen on SoundCloud <svg class="external-link-icon"'), 'Link to the existing SoundCloud set with an external-link icon');
    check(str_contains($html, '<link rel="canonical" href="https://arcticaantarctica.com/">'), 'Use the production canonical URL');
    check(str_contains($html, 'property="og:image" content="https://arcticaantarctica.com/img/social_preview.png?v='), 'Expose the social preview image');
    check(str_contains($html, 'property="og:image:secure_url" content="https://arcticaantarctica.com/img/social_preview.png?v='), 'Expose the secure social preview image');
    check(str_contains($html, 'property="og:image:width" content="1200"'), 'Declare the social preview width');
    check(str_contains($html, 'property="og:image:height" content="630"'), 'Declare the social preview height');
    check(str_contains($html, 'property="og:site_name" content="Arctica / Antarctica"'), 'Declare the social card site name');
    check(str_contains($html, 'name="twitter:card" content="summary_large_image"'), 'Use a large social preview card');
    $homeControlPosition = strpos($html, '<a class="settings-toggle" href="/" title="Home" aria-label="Home" aria-current="page">');
    $previousControlPosition = strpos($html, 'id="prev-site-button"');
    $playControlPosition = strpos($html, '<button id="play-site-button" class="settings-toggle" type="button" title="Play all pages" aria-label="Play all pages" aria-pressed="false"><svg class="icons" aria-hidden="true"><use href="/icons.svg#play"></use></svg></button>');
    $nextControlPosition = strpos($html, 'id="next-site-button"');
    $releasesPosition = strpos($html, '<li><a href="/releases"');
    check($homeControlPosition !== false && $previousControlPosition !== false && $playControlPosition !== false && $nextControlPosition !== false && $releasesPosition !== false
        && $homeControlPosition < $previousControlPosition && $previousControlPosition < $playControlPosition && $playControlPosition < $nextControlPosition && $nextControlPosition < $releasesPosition, 'Place Home and page playback controls before the Releases link without extra section spacing');
    $settingsPanelPosition = strpos($html, '<div id="settings-panel"');
    check($settingsPanelPosition !== false && strpos($html, 'id="play-site-button"', $settingsPanelPosition) === false, 'Keep page playback controls out of Settings');

    foreach (['releases' => 'Releases', 'events' => 'Events', 'about' => 'About', 'contact' => 'Contact', 'links' => 'Links', 'updates' => 'Updates'] as $slug => $title) {
        [$status, $page] = response('/' . $slug);
        check($status === 200, "$title returns HTTP 200");
        check(!preg_match('~(?:Warning|Fatal error|Deprecated):~', $page), "$title has no PHP diagnostics");
        check(str_contains($page, '<h1>' . $title . '</h1>'), "$title renders its heading");
        check(str_contains($page, 'href="/' . $slug . '" aria-current="page"'), "$title is selected in navigation");
        check(str_contains($page, '<link rel="canonical" href="https://arcticaantarctica.com/' . $slug . '">'), "$title has a clean canonical URL");
        [$status, , $headers] = response('/' . $slug . '.php?test=1');
        check($status === 301 && in_array('Location: /' . $slug . '?test=1', $headers, true), "$title redirects its implementation URL with its query");
        [$status, , $headers] = response('/' . $slug . '/?test=1');
        check($status === 301 && in_array('Location: /' . $slug . '?test=1', $headers, true), "$title redirects its trailing slash with its query");
    }

    [, $page] = response('/releases');
    $playerUrl = $siteContent['soundcloud_embed'];
    check(substr_count($page, '<h2>') === 1
        && str_contains($page, 'href="' . escapeHtml($siteContent['soundcloud']) . '">Arctica / Antarctica on SoundCloud <svg class="external-link-icon"')
        && str_contains($page, '<iframe class="soundcloud-player" title="Arctica / Antarctica on SoundCloud" width="100%" height="450" scrolling="no" allow="autoplay; encrypted-media" src="' . escapeHtml($playerUrl) . '"></iframe>'), 'Show and embed the SoundCloud release');
    [, $page] = response('/events');
    check(substr_count($page, '<article class="content-panel event-card">') === 4, 'Show the four archived concerts');
    foreach ($siteContent['events'] as $index => $event) {
        $eventUrl = '/event?a=' . rawurlencode($event['date']);
        check(str_contains($page, 'href="' . $eventUrl . '"') && str_contains($page, escapeHtml($event['venue_url'])), 'Link each event page and venue');
        check(str_contains($page, 'href="' . escapeHtml($event['venue_url']) . '">' . escapeHtml($event['venue']) . ' <svg class="external-link-icon"'), 'Mark external venue links');
        if (isset($event['photo'])) {
            $photo = $event['photo'];
            check(str_contains($page, 'src="' . escapeHtml($photo['src']) . '" alt="' . escapeHtml($photo['alt']) . '"'), 'Show the event photo on the overview');
            check(response($photo['src'])[0] === 200, 'Serve the event photo');
        } else {
            check(str_contains($page, 'class="event-placeholder" role="img" aria-label="Event photo not available"'), 'Show a placeholder when the event photo is missing');
        }
        if (isset($event['poster'])) check(!str_contains($page, $event['poster']['src']), 'Keep posters off the event overview');

        [$status, $detail] = response($eventUrl);
        check($status === 200 && str_contains($detail, '<h1>' . escapeHtml($event['name']) . '</h1>'), 'Show the event detail page');
        check(str_contains($detail, '<link rel="canonical" href="https://arcticaantarctica.com' . $eventUrl . '">'), 'Use the event canonical URL');
        check(str_contains($detail, 'href="/events" aria-current="page"') && str_contains($detail, escapeHtml($event['url'])), 'Select Events and retain the external event link');
        $previous = $siteContent['events'][($index + 1) % count($siteContent['events'])]['date'];
        $next = $siteContent['events'][($index - 1 + count($siteContent['events'])) % count($siteContent['events'])]['date'];
        check(str_contains($detail, 'href="/event?a=' . $previous . '" aria-label="Previous event"') && str_contains($detail, 'href="/event?a=' . $next . '" aria-label="Next event"'), 'Link adjacent event pages');
        $detailPhotoPosition = false;
        if (isset($event['photo'])) {
            $photo = $event['photo'];
            $detailPhotoPosition = strpos($detail, 'src="' . escapeHtml($photo['src']) . '"');
            check($detailPhotoPosition !== false && str_contains($detail, 'alt="' . escapeHtml($photo['alt']) . '"'), 'Show the event photo on its detail page');
        }
        if (isset($event['poster'])) {
            $poster = $event['poster'];
            $posterPosition = strpos($detail, 'src="' . escapeHtml($poster['src']) . '"');
            check($posterPosition !== false && str_contains($detail, 'alt="' . escapeHtml($poster['alt']) . '"'), 'Show the poster on its event page');
            if ($detailPhotoPosition !== false) check($detailPhotoPosition < $posterPosition, 'Show the event photo above its poster');
            check(response($poster['src'])[0] === 200, 'Serve the event poster');
        } else {
            check(str_contains($detail, 'class="event-placeholder" role="img" aria-label="Event poster not available"'), 'Show a placeholder when the event poster is missing');
        }
        check(str_contains($detail, '<dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#calendar"></use></svg>Date</dt>') && str_contains($detail, '<time datetime="' . escapeHtml($event['date']) . '">'), 'Show the event date with its icon');
        check(str_contains($detail, '<dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#home"></use></svg>Venue</dt>') && str_contains($detail, 'href="' . escapeHtml($event['venue_url']) . '">' . escapeHtml($event['venue']) . ' <svg class="external-link-icon"'), 'Show the venue with its icon and external link');
        check(str_contains($detail, '<dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#world"></use></svg>Location</dt>') && str_contains($detail, '<dd>' . escapeHtml($event['location']) . '</dd>'), 'Show the location with its icon');
        check(str_contains($detail, '<dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#facebook"></use></svg>Event</dt>') && str_contains($detail, 'href="' . escapeHtml($event['url']) . '">Facebook event <svg class="external-link-icon"'), 'Show the Facebook event link with its icon');
        foreach ($siteContent['events'] as $otherEvent) {
            if ($otherEvent['date'] !== $event['date'] && isset($otherEvent['photo'])) check(!str_contains($detail, $otherEvent['photo']['src']), 'Keep other event photos off this event page');
            if ($otherEvent['date'] !== $event['date'] && isset($otherEvent['poster'])) check(!str_contains($detail, $otherEvent['poster']['src']), 'Keep other event posters off the event page');
        }
    }
    foreach (['/event', '/event?a=missing', '/event?a%5B%5D=2017-03-04'] as $path) check(response($path)[0] === 404, 'Reject invalid event identifiers');
    [$status, , $headers] = response('/event.php?a=2017-03-04');
    check($status === 301 && in_array('Location: /event?a=2017-03-04', $headers, true), 'Redirect the event implementation URL with its query');
    [$status, , $headers] = response('/event/?a=2017-03-04');
    check($status === 301 && in_array('Location: /event?a=2017-03-04', $headers, true), 'Redirect the event trailing slash with its query');
    [, $page] = response('/about');
    check(str_contains($page, '<p>Arctica / Antarctica is a Norwegian polar ambient band.</p>'), 'Describe the band on About');
    foreach ($siteContent['events'] as $event) {
        if (isset($event['photo'])) check(!str_contains($page, $event['photo']['src']), 'Keep event photos on the Events overview');
    }
    check(str_contains($page, '<h2>Lineup:</h2>'), 'Label the lineup');
    check($siteContent['lineup'] === ['Ole Peko Sørensen', 'Jonas Qvale', 'Jon Martens Holm'], 'Use the requested lineup in order');
    foreach ($siteContent['lineup'] as $member) {
        check(str_contains($page, escapeHtml($member)), 'Show each lineup member');
    }
    [$status, $page, $headers] = response('/contact');
    check($status === 200 && str_contains($page, '<form id="contact-form" action="/contact" method="post">'), 'Show the contact form');
    check((bool) preg_match('/name="token" value="([a-f0-9]{64})"/', $page, $tokenMatch), 'Provide a contact token');
    $cookieHeader = '';
    foreach ($headers as $header) {
        if (str_starts_with($header, 'Set-Cookie: contact_session=')) $cookieHeader = $header;
    }
    check((bool) preg_match('/^Set-Cookie: (contact_session=[^;]+)/', $cookieHeader, $cookieMatch) && stripos($cookieHeader, 'httponly') !== false && stripos($cookieHeader, 'samesite=lax') !== false, 'Protect the contact session cookie');
    $contactHeaders = ['Content-Type: application/x-www-form-urlencoded', 'Cookie: ' . $cookieMatch[1]];
    $fields = ['token' => $tokenMatch[1], 'name' => 'Test', 'email' => 'invalid', 'category' => 'general', 'message' => '<test message>', 'website' => ''];
    [$status, $page] = response('/contact', 'POST', $contactHeaders, http_build_query($fields));
    check($status === 422 && str_contains($page, 'Enter a valid email address.') && str_contains($page, '&lt;test message&gt;'), 'Reject invalid email and preserve escaped message');
    $fields['token'] = 'invalid';
    [$status, $page] = response('/contact', 'POST', $contactHeaders, http_build_query($fields));
    check($status === 403 && str_contains($page, 'The form expired or could not be verified.'), 'Reject an invalid contact token');
    $fields['token'] = $tokenMatch[1];
    $fields['website'] = 'spam';
    [$status, $page] = response('/contact', 'POST', $contactHeaders, http_build_query($fields));
    check($status === 422 && str_contains($page, 'The submission could not be verified.'), 'Reject a filled honeypot');
    [$status, , $headers] = response('/contact', 'POST', $contactHeaders, 'action=reset');
    check($status === 303 && in_array('Location: /contact', $headers, true), 'Reset the contact form');
    check(response('/contact', 'PUT')[0] === 405, 'Reject unsupported contact methods');
    [, $page] = response('/links');
    foreach ($siteContent['links'] as $link) {
        check(str_contains($page, 'href="' . escapeHtml($link['url']) . '">' . escapeHtml($link['name']) . ' <svg class="external-link-icon"'), 'Mark each external polar resource link');
    }
    [$status, $page] = response('/updates');
    check($status === 200 && str_contains($page, 'aria-label="Updates pagination"') && str_contains($page, 'Page 1 of 1')
        && str_contains($page, '<th scope="col">Title</th><th scope="col">Date</th><th scope="col">Number</th><th scope="col">Commit</th>')
        && !str_contains($page, 'data-label=') && str_contains($page, 'No updates available'),
        'Render the paginated empty updates table');
    $updatesCss = file_get_contents(__DIR__ . '/../updates.css');
    check($updatesCss !== false && !str_contains($updatesCss, '@container') && !str_contains($updatesCss, 'display: grid'),
        'Keep the Updates table native on narrow screens');
    [$status, $page] = response('/updates?page=invalid');
    check($status === 400 && str_contains($page, 'Updates unavailable'), 'Reject invalid update page values');

    foreach (['/favicon.svg', '/style.css', '/updates.css', '/index.js', '/swipe.js', '/img/arctica_antarctica.svg', '/img/focus_studios.svg', '/img/band.png', '/img/social_preview.png', '/icons.svg'] as $path) {
        check(response($path)[0] === 200, "Serve asset $path");
    }
    foreach (['/data/site.json', '/functions.php', '/head.php', '/toc.php', '/footer.php', '/content.php', '/tests/site.php', '/missing-page'] as $path) {
        check(response($path)[0] === 404, "Keep private or nonexistent path inaccessible: $path");
    }
}

echo "PASS: $checks Arctica / Antarctica checks\n";
