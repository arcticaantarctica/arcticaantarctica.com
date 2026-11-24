<?php
require_once __DIR__ . '/functions.php';
$siteContent = $siteContent ?? loadSiteContent();
$pageIcons = ['releases' => 'lp', 'events' => 'calendar', 'event' => 'calendar', 'about' => 'about', 'contact' => 'person', 'links' => 'world', 'updates' => 'github'];
$updates = [];
$updatesUnavailable = false;
if ($pagePath === 'updates') {
    try {
        $updates = loadUpdates();
    } catch (RuntimeException | JsonException $error) {
        error_log('Updates: ' . $error->getMessage());
        http_response_code(503);
        $updatesUnavailable = true;
    }

    $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
    if ($requestedPage === false) {
        http_response_code(400);
        $requestedPage = 1;
        $updatesUnavailable = true;
    }
    $updatesPerPage = 25;
    $totalUpdates = count($updates);
    $updatesPageCount = max(1, (int) ceil($totalUpdates / $updatesPerPage));
    $updatesPage = min($updatesPageCount, max(1, $requestedPage));
    $pageUpdates = array_slice($updates, ($updatesPage - 1) * $updatesPerPage, $updatesPerPage);
}
?>
<!doctype html>
<html lang="en">
<head>
  <?php require __DIR__ . '/head.php'; ?>
</head>
<body>
  <?php require __DIR__ . '/toc.php'; ?>
  <main id="main">
    <?php if ($pagePath === ''): ?>
      <section class="home-feature">
        <img class="brand-mark" src="<?php echo escapeHtml($siteContent['logo']); ?>" alt="" width="768" height="768">
        <h1>Arctica / Antarctica</h1>
        <p><?php echo escapeHtml($siteContent['description']); ?></p>
        <p><a href="<?php echo escapeHtml($siteContent['soundcloud']); ?>">Listen on SoundCloud <svg class="external-link-icon" aria-hidden="true" focusable="false"><use href="/icons.svg#external"></use></svg></a></p>
      </section>
    <?php else: ?>
      <div class="top-line">
        <svg class="icons" aria-hidden="true"><use href="/icons.svg#<?php echo $pageIcons[$pagePath]; ?>"></use></svg>
        <h1><?php echo escapeHtml($pageName); ?></h1>
        <svg class="icons" aria-hidden="true"><use href="/icons.svg#<?php echo $pageIcons[$pagePath]; ?>"></use></svg>
      </div>
      <?php if ($pagePath === 'releases'): ?>
        <section class="content-panel">
          <h2><a href="<?php echo escapeHtml($siteContent['soundcloud']); ?>">Arctica / Antarctica on SoundCloud <svg class="external-link-icon" aria-hidden="true" focusable="false"><use href="/icons.svg#external"></use></svg></a></h2>
          <p>Our first concert, recorded and released on SoundCloud.</p>
          <iframe class="soundcloud-player" title="Arctica / Antarctica on SoundCloud" width="100%" height="450" scrolling="no" allow="autoplay; encrypted-media" src="<?php echo escapeHtml($siteContent['soundcloud_embed']); ?>"></iframe>
        </section>
      <?php elseif ($pagePath === 'events'): ?>
        <section aria-labelledby="previous-heading">
          <h2 id="previous-heading" class="section-heading">Previous</h2>
          <div class="event-grid">
            <?php foreach ($siteContent['events'] as $event): ?>
              <article class="content-panel event-card">
                <div class="event-details">
                  <h3><a href="/event?a=<?php echo rawurlencode($event['date']); ?>"><?php echo escapeHtml($event['name']); ?></a></h3>
                  <p><time datetime="<?php echo escapeHtml($event['date']); ?>"><?php echo escapeHtml((new DateTimeImmutable($event['date']))->format('d-m-Y')); ?></time></p>
                  <p><?php if (isset($event['venue_url'])): ?><a href="<?php echo escapeHtml($event['venue_url']); ?>"><?php endif; ?><?php echo escapeHtml($event['venue']); ?><?php if (isset($event['venue_url'])): ?> <svg class="external-link-icon" aria-hidden="true" focusable="false"><use href="/icons.svg#external"></use></svg></a><?php endif; ?><br><?php echo escapeHtml($event['location']); ?></p>
                </div>
                <?php if (isset($event['photo'])): ?>
                  <a class="event-photo-link" href="/event?a=<?php echo rawurlencode($event['date']); ?>"><img class="event-photo" src="<?php echo escapeHtml($event['photo']['src']); ?>" alt="<?php echo escapeHtml($event['photo']['alt']); ?>" loading="lazy"></a>
                <?php else: ?>
                  <div class="event-placeholder" role="img" aria-label="Event photo not available"><svg class="icons" aria-hidden="true"><use href="/icons.svg#calendar"></use></svg></div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php elseif ($pagePath === 'event'): ?>
        <nav class="event-navigation" aria-label="Event navigation">
          <a href="/event?a=<?php echo rawurlencode($previousEvent['date']); ?>" aria-label="Previous event" title="Show previous event"><svg class="icons" aria-hidden="true"><use href="/icons.svg#chevron_left"></use></svg></a>
          <a href="/event?a=<?php echo rawurlencode($nextEvent['date']); ?>" aria-label="Next event" title="Show next event"><svg class="icons" aria-hidden="true"><use href="/icons.svg#chevron_right"></use></svg></a>
        </nav>
        <section class="content-panel">
          <dl class="event-metadata">
            <div>
              <dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#calendar"></use></svg>Date</dt>
              <dd><time datetime="<?php echo escapeHtml($event['date']); ?>"><?php echo escapeHtml((new DateTimeImmutable($event['date']))->format('d-m-Y')); ?></time></dd>
            </div>
            <div>
              <dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#home"></use></svg>Venue</dt>
              <dd><a href="<?php echo escapeHtml($event['venue_url']); ?>"><?php echo escapeHtml($event['venue']); ?> <svg class="external-link-icon" aria-hidden="true" focusable="false"><use href="/icons.svg#external"></use></svg></a></dd>
            </div>
            <div>
              <dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#world"></use></svg>Location</dt>
              <dd><?php echo escapeHtml($event['location']); ?></dd>
            </div>
            <div>
              <dt><svg class="entry-metadata-icon" aria-hidden="true"><use href="/icons.svg#facebook"></use></svg>Event</dt>
              <dd><a href="<?php echo escapeHtml($event['url']); ?>">Facebook event <svg class="external-link-icon" aria-hidden="true" focusable="false"><use href="/icons.svg#external"></use></svg></a></dd>
            </div>
          </dl>
        </section>
        <?php if (isset($event['photo'])): ?>
          <img class="event-photo event-detail-photo" src="<?php echo escapeHtml($event['photo']['src']); ?>" alt="<?php echo escapeHtml($event['photo']['alt']); ?>">
        <?php endif; ?>
        <?php if (isset($event['poster'])): ?>
          <img class="event-poster" src="<?php echo escapeHtml($event['poster']['src']); ?>" alt="<?php echo escapeHtml($event['poster']['alt']); ?>">
        <?php else: ?>
          <div class="event-placeholder" role="img" aria-label="Event poster not available"><svg class="icons" aria-hidden="true"><use href="/icons.svg#calendar"></use></svg></div>
        <?php endif; ?>
      <?php elseif ($pagePath === 'about'): ?>
        <section class="content-panel">
          <p><?php echo escapeHtml($siteContent['description']); ?></p>
          <h2>Lineup:</h2>
          <ul class="plain-list"><?php foreach ($siteContent['lineup'] as $member): ?><li><?php echo escapeHtml($member); ?></li><?php endforeach; ?></ul>
        </section>
      <?php elseif ($pagePath === 'contact'): ?>
        <section class="content-panel contact-panel">
          <?php if ($messageSent): ?><p class="contact-notice" role="status">Your message has been sent. Thank you.</p><?php endif; ?>
          <?php if ($errors): ?>
            <div id="contact-errors" class="contact-notice" role="alert">
              <p>Your message was not sent.</p>
              <ul><?php foreach ($errors as $error): ?><li><?php echo escapeHtml($error); ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>
          <form id="contact-form" action="/contact" method="post">
            <input type="hidden" name="token" value="<?php echo escapeHtml($_SESSION['contact_token'] ?? ''); ?>">
            <div class="contact-honeypot" aria-hidden="true">
              <label for="contact-website">Website:</label>
              <input type="text" id="contact-website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="contact-fields">
              <div class="contact-field">
                <label for="contact-name">Name:</label>
                <input type="text" id="contact-name" name="name" required maxlength="100" autocomplete="name" value="<?php echo escapeHtml($values['name']); ?>"<?php if (isset($errors['name'])): ?> aria-invalid="true" aria-describedby="contact-errors"<?php endif; ?>>
              </div>
              <div class="contact-field">
                <label for="contact-email">Email:</label>
                <input type="email" id="contact-email" name="email" required maxlength="254" autocomplete="email" value="<?php echo escapeHtml($values['email']); ?>"<?php if (isset($errors['email'])): ?> aria-invalid="true" aria-describedby="contact-errors"<?php endif; ?>>
              </div>
              <div class="contact-field">
                <label for="contact-category">Category:</label>
                <select id="contact-category" name="category"<?php if (isset($errors['category'])): ?> aria-invalid="true" aria-describedby="contact-errors"<?php endif; ?>>
                  <?php foreach ($categories as $value => $label): ?><option value="<?php echo escapeHtml($value); ?>"<?php if ($values['category'] === $value): ?> selected<?php endif; ?>><?php echo escapeHtml($label); ?></option><?php endforeach; ?>
                </select>
              </div>
            </div>
            <textarea id="contact-message" name="message" aria-label="Message" required maxlength="10000" rows="10"<?php if (isset($errors['message'])): ?> aria-invalid="true" aria-describedby="contact-errors"<?php endif; ?>><?php echo escapeHtml($values['message']); ?></textarea>
            <div class="contact-actions">
              <button type="submit" title="Send message"<?php if (!$sessionStarted): ?> disabled<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#arrow_right"></use></svg><span>Send</span></button>
              <button type="submit" name="action" value="reset" formnovalidate title="Reset to default"><svg class="icons" aria-hidden="true"><use href="/icons.svg#reset"></use></svg><span>Reset</span></button>
            </div>
          </form>
        </section>
      <?php elseif ($pagePath === 'links'): ?>
        <section class="content-panel">
          <h2>Polar resources</h2>
          <ul class="plain-list"><?php foreach ($siteContent['links'] as $link): ?><li><a href="<?php echo escapeHtml($link['url']); ?>"><?php echo escapeHtml($link['name']); ?> <svg class="external-link-icon" aria-hidden="true" focusable="false"><use href="/icons.svg#external"></use></svg></a></li><?php endforeach; ?></ul>
        </section>
      <?php elseif ($pagePath === 'updates'): ?>
        <section class="content-panel updates">
          <nav class="updates-pagination" aria-label="Updates pagination">
            <a href="/updates?page=<?php echo $updatesPage === 1 ? $updatesPageCount : $updatesPage - 1; ?>" aria-label="Previous updates" title="Show previous updates"><svg class="icons" aria-hidden="true"><use href="/icons.svg#arrow_left"></use></svg></a>
            <span>Page <?php echo $updatesPage; ?> of <?php echo $updatesPageCount; ?></span>
            <a href="/updates?page=<?php echo $updatesPage === $updatesPageCount ? 1 : $updatesPage + 1; ?>" aria-label="Next updates" title="Show next updates"><svg class="icons" aria-hidden="true"><use href="/icons.svg#arrow_right"></use></svg></a>
          </nav>
          <div class="updates-scroll">
            <table>
              <thead><tr><th scope="col">Title</th><th scope="col">Date</th><th scope="col">Number</th><th scope="col">Commit</th></tr></thead>
              <tbody>
              <?php if ($updatesUnavailable): ?>
                <tr><td colspan="4">Updates unavailable</td></tr>
              <?php elseif ($pageUpdates === []): ?>
                <tr><td colspan="4">No updates available</td></tr>
              <?php else: ?>
                <?php foreach ($pageUpdates as $index => $update): ?>
                  <tr>
                    <td><a href="<?php echo escapeHtml($siteContent['github'] . '/commit/' . $update['head_sha']); ?>" target="_blank" rel="noopener noreferrer"><?php echo escapeHtml($update['display_title']); ?></a></td>
                    <td><time datetime="<?php echo escapeHtml($update['created_at']); ?>"><?php echo escapeHtml(gmdate('d-m-Y H:i', strtotime($update['created_at']))); ?> UTC</time></td>
                    <td><?php echo $totalUpdates - ($updatesPage - 1) * $updatesPerPage - $index; ?></td>
                    <td><?php echo escapeHtml(substr($update['head_sha'], 0, 7)); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>
    <?php endif; ?>
  </main>
  <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
