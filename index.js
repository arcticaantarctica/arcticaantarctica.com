'use strict';

const sidebarLayout = window.matchMedia('(min-width: 800px)');

function syncSidebarState() {
  const menuOpen = document.getElementById('toc').classList.contains('toc-open');
  const settings = document.getElementById('settings-panel');
  const settingsOpen = settings.classList.contains('settings-panel-open');
  settings.hidden = !settingsOpen;
  document.getElementById('toggle-menu-button').setAttribute('aria-expanded', String(menuOpen));
  document.getElementById('toggle-settings-panel-button').setAttribute('aria-expanded', String(settingsOpen));
  document.body.classList.toggle('toc-sidebar-open', menuOpen);
  document.body.classList.toggle('settings-sidebar-open', settingsOpen);
  document.body.classList.toggle('toc-lock', menuOpen && !sidebarLayout.matches);
  document.body.classList.toggle('settings-lock', settingsOpen && !sidebarLayout.matches);
  if (sidebarLayout.matches) {
    localStorage.setItem('global.toc_sidebar_open', String(menuOpen));
    localStorage.setItem('global.settings_sidebar_open', String(settingsOpen));
  }
}

function restoreSidebarState() {
  const savedMenu = localStorage.getItem('global.toc_sidebar_open');
  const savedSettings = localStorage.getItem('global.settings_sidebar_open');
  document.getElementById('toc').classList.toggle('toc-open', sidebarLayout.matches && savedMenu !== 'false');
  document.getElementById('settings-panel').classList.toggle('settings-panel-open', sidebarLayout.matches && savedSettings !== 'false');
  syncSidebarState();
}

function closeMobileSidebars() {
  if (sidebarLayout.matches) return;
  document.getElementById('toc').classList.remove('toc-open');
  document.getElementById('settings-panel').classList.remove('settings-panel-open');
  syncSidebarState();
}

function toggleMenu() {
  if (!sidebarLayout.matches) document.getElementById('settings-panel').classList.remove('settings-panel-open');
  document.getElementById('toc').classList.toggle('toc-open');
  syncSidebarState();
}

function toggleSettingsPanel() {
  if (!sidebarLayout.matches) document.getElementById('toc').classList.remove('toc-open');
  document.getElementById('settings-panel').classList.toggle('settings-panel-open');
  syncSidebarState();
}

function updateModeButtonState() {
  const active = !document.documentElement.classList.contains('invert-colors');
  const button = document.getElementById('toggle-mode-button');
  button.classList.toggle('button-on', active);
  button.setAttribute('aria-pressed', String(active));
}

function toggleMode() {
  const lightMode = document.documentElement.classList.toggle('invert-colors');
  localStorage.setItem('mode', lightMode ? 'dark' : 'light');
  updateModeButtonState();
}

function updateFullscreenButtonState() {
  const active = Boolean(document.fullscreenElement);
  const button = document.getElementById('toggle-fullscreen-button');
  button.classList.toggle('button-on', active);
  button.setAttribute('aria-pressed', String(active));
}

function showSettingsFeedback(message) {
  const feedback = document.getElementById('settings-feedback');
  feedback.textContent = message;
  feedback.hidden = false;
}

async function toggleFullscreen() {
  try {
    if (document.fullscreenElement) await document.exitFullscreen();
    else await document.documentElement.requestFullscreen();
  } catch (error) {
    console.error('Unable to change fullscreen mode', error);
    showSettingsFeedback('Unable to change fullscreen mode.');
  }
}

async function shareCurrentPage() {
  const url = window.location.href;
  if (navigator.share) {
    try {
      await navigator.share({ title: document.title, url });
      return;
    } catch (error) {
      if (error.name === 'AbortError') return;
      console.warn('Unable to share page', error);
    }
  }
  if (navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(url);
      showSettingsFeedback('Link copied.');
      return;
    } catch (error) {
      console.warn('Unable to copy share URL', error);
    }
  }
  window.prompt('Copy this link:', url);
}

function resetSettings() {
  localStorage.clear();
  const url = new URL(window.location.href);
  if (url.pathname === '/releases') {
    url.searchParams.delete('view');
    url.searchParams.delete('artist');
  }
  window.location.replace(url.href);
}

function setupSitePlay() {
  const button = document.getElementById('play-site-button');
  const links = Array.from(document.querySelectorAll('#toc a[href]'));
  const current = links.findIndex(link => link.hasAttribute('aria-current'));
  const key = 'global.site_play_active';
  let timer;
  const isActive = () => localStorage.getItem(key) === 'true';
  const updateButton = () => {
    button.classList.toggle('button-on', isActive());
    button.setAttribute('aria-pressed', String(isActive()));
  };
  const stop = () => {
    clearTimeout(timer);
    localStorage.removeItem(key);
    updateButton();
  };
  const navigate = direction => {
    window.location.href = links[(current + direction + links.length) % links.length].href;
  };
  const schedule = () => {
    clearTimeout(timer);
    if (isActive() && !document.hidden) timer = setTimeout(() => navigate(1), 2500);
  };
  if (current < 0 || links.length < 2) {
    for (const id of ['prev-site-button', 'play-site-button', 'next-site-button']) {
      document.getElementById(id).disabled = true;
    }
    showSettingsFeedback('Page playback is unavailable.');
    return;
  }
  button.addEventListener('click', () => {
    if (isActive()) stop();
    else {
      localStorage.setItem(key, 'true');
      updateButton();
      schedule();
    }
  });
  document.getElementById('prev-site-button').addEventListener('click', () => navigate(-1));
  document.getElementById('next-site-button').addEventListener('click', () => navigate(1));
  document.addEventListener('click', event => {
    if (isActive() && event.target.closest('a[href], button[data-href], #prev-site-button, #next-site-button')) stop();
  }, true);
  document.addEventListener('submit', () => { if (isActive()) stop(); }, true);
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && isActive()) stop();
  });
  document.addEventListener('visibilitychange', schedule);
  window.addEventListener('storage', event => {
    if (event.key === key || event.key === null) {
      updateButton();
      schedule();
    }
  });
  window.addEventListener('pagehide', () => clearTimeout(timer));
  window.addEventListener('pageshow', () => { updateButton(); schedule(); });
  updateButton();
  schedule();
}

document.addEventListener('DOMContentLoaded', () => {
  const actions = {
    'toggle-menu-button': toggleMenu,
    'toggle-settings-panel-button': toggleSettingsPanel,
    'toggle-menu-close-button': () => {
      document.getElementById('toc').classList.remove('toc-open');
      syncSidebarState();
      document.getElementById('toggle-menu-button').focus();
    },
    'toggle-settings-panel-close-button': () => {
      document.getElementById('settings-panel').classList.remove('settings-panel-open');
      syncSidebarState();
      document.getElementById('toggle-settings-panel-button').focus();
    },
    'toggle-mode-button': toggleMode,
    'toggle-fullscreen-button': toggleFullscreen,
    'share-settings-button': shareCurrentPage,
    'reset-settings-button': resetSettings
  };
  for (const [id, action] of Object.entries(actions)) {
    document.getElementById(id).addEventListener('click', action);
  }
  document.documentElement.classList.toggle('invert-colors', localStorage.getItem('mode') === 'dark');
  document.getElementById('toggle-fullscreen-button').disabled = !document.documentElement.requestFullscreen || document.fullscreenEnabled === false;
  updateModeButtonState();
  updateFullscreenButtonState();
  restoreSidebarState();
  setupSitePlay();
});

sidebarLayout.addEventListener('change', restoreSidebarState);
window.addEventListener('pagehide', closeMobileSidebars);
window.addEventListener('pageshow', closeMobileSidebars);
document.addEventListener('fullscreenchange', updateFullscreenButtonState);
document.addEventListener('click', event => {
  if (sidebarLayout.matches) return;
  if (event.target.closest('#toc a')) {
    closeMobileSidebars();
  } else if (!event.target.closest('#burger-container, #settings-menu-container')) {
    closeMobileSidebars();
  }
});
document.addEventListener('keydown', event => {
  if (event.key !== 'Escape' || sidebarLayout.matches) return;
  const settingsOpen = !document.getElementById('settings-panel').hidden;
  const menuOpen = document.getElementById('toc').classList.contains('toc-open');
  closeMobileSidebars();
  if (settingsOpen) document.getElementById('toggle-settings-panel-button').focus();
  else if (menuOpen) document.getElementById('toggle-menu-button').focus();
});
