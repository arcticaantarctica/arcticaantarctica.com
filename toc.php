<header class="top-heading colored">
  <div id="burger-container" class="menu-container">
    <button id="toggle-menu-button" class="settings-toggle" type="button" title="Menu" aria-label="Menu" aria-controls="toc" aria-expanded="false">
      <svg class="icons" aria-hidden="true"><use href="/icons.svg#burger"></use></svg>
    </button>
    <nav id="toc" class="toc-container" aria-label="Main navigation">
      <div class="toc-controls">
        <a class="settings-toggle" href="/" title="Home" aria-label="Home"<?php if ($pagePath === ''): ?> aria-current="page"<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#home"></use></svg></a>
        <div class="site-play-controls" role="group" aria-label="Page playback">
          <button id="prev-site-button" class="settings-toggle" type="button" title="Show previous page" aria-label="Previous page"><svg class="icons" aria-hidden="true"><use href="/icons.svg#skip_prev"></use></svg></button>
          <button id="play-site-button" class="settings-toggle" type="button" title="Play all pages" aria-label="Play all pages" aria-pressed="false"><svg class="icons" aria-hidden="true"><use href="/icons.svg#play"></use></svg></button>
          <button id="next-site-button" class="settings-toggle" type="button" title="Show next page" aria-label="Next page"><svg class="icons" aria-hidden="true"><use href="/icons.svg#skip_next"></use></svg></button>
        </div>
      </div>
      <ul>
        <?php foreach ([
          ['releases', 'Releases', 'lp'],
          ['events', 'Events', 'calendar'],
          ['about', 'About', 'about'],
          ['contact', 'Contact', 'person'],
          ['links', 'Links', 'world'],
          ['updates', 'Updates', 'github'],
        ] as [$path, $label, $icon]): ?>
          <li<?php if ($path === 'releases'): ?> class="toc-section-start"<?php endif; ?>><a href="/<?php echo $path; ?>"<?php if ($pagePath === $path || ($pagePath === 'event' && $path === 'events')): ?> aria-current="page"<?php endif; ?>><svg class="icons" aria-hidden="true"><use href="/icons.svg#<?php echo $icon; ?>"></use></svg><?php echo $label; ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="toc-close-wrap"><button id="toggle-menu-close-button" class="settings-toggle" type="button" title="Close" aria-label="Close menu"><svg class="icons" aria-hidden="true"><use href="/icons.svg#close"></use></svg></button></div>
    </nav>
  </div>
  <div class="heading"><a href="/" aria-label="Arctica / Antarctica home"><img src="<?php echo escapeHtml($siteContent['logo']); ?>" alt=""></a></div>
  <div id="settings-menu-container">
    <button id="toggle-settings-panel-button" class="settings-toggle" type="button" title="Settings" aria-label="Settings" aria-controls="settings-panel" aria-expanded="false"><svg class="icons" aria-hidden="true"><use href="/icons.svg#settings"></use></svg></button>
    <div id="settings-panel" class="settings-panel" role="region" aria-label="Settings" hidden>
      <div class="settings-panel-scroll">
        <button id="toggle-mode-button" class="toc-button" type="button" title="Toggle dark mode" aria-pressed="true"><svg class="icons" aria-hidden="true"><use href="/icons.svg#moon"></use></svg><span>Dark mode</span></button>
        <button id="toggle-fullscreen-button" class="toc-button" type="button" title="Toggle fullscreen" aria-pressed="false"><svg class="icons" aria-hidden="true"><use href="/icons.svg#full_screen"></use></svg><span>Fullscreen</span></button>
        <button id="share-settings-button" class="toc-button" type="button" title="Share"><svg class="icons" aria-hidden="true"><use href="/icons.svg#share"></use></svg><span>Share</span></button>
        <button id="reset-settings-button" class="toc-button" type="button" title="Reset to default"><svg class="icons" aria-hidden="true"><use href="/icons.svg#reset"></use></svg><span>Reset</span></button>
        <p id="settings-feedback" role="status" hidden></p>
      </div>
      <div class="toc-close-wrap"><button id="toggle-settings-panel-close-button" class="settings-toggle" type="button" title="Close" aria-label="Close settings"><svg class="icons" aria-hidden="true"><use href="/icons.svg#close"></use></svg></button></div>
    </div>
  </div>
</header>
