<?php
$canonicalUrl = 'https://arcticaantarctica.com/' . $pagePath;
if ($pagePath === 'event') $canonicalUrl .= '?a=' . rawurlencode($event['date']);
$socialImageUrl = 'https://arcticaantarctica.com/img/social_preview.png?v=' . filemtime(__DIR__ . '/img/social_preview.png');
$pageTitle = escapeHtml($siteContent['name'] . ($pageName !== '' ? ' - ' . $pageName : ''));
$description = escapeHtml($siteContent['description']);
?>
<title><?php echo $pageTitle; ?></title>
<meta charset="utf-8">
<script src="/resize_table.js?v=<?php echo filemtime(__DIR__ . '/resize_table.js'); ?>" defer></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?php echo $description; ?>">
<link rel="icon" href="/favicon.svg?v=<?php echo filemtime(__DIR__ . '/favicon.svg'); ?>" type="image/svg+xml">
<link rel="stylesheet" href="/style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
<?php if ($pagePath === 'updates'): ?><link rel="stylesheet" href="/updates.css?v=<?php echo filemtime(__DIR__ . '/updates.css'); ?>"><?php endif; ?>
<script src="/index.js?v=<?php echo filemtime(__DIR__ . '/index.js'); ?>" defer></script>
<script src="/swipe.js?v=<?php echo filemtime(__DIR__ . '/swipe.js'); ?>" defer></script>
<meta property="og:title" content="<?php echo $pageTitle; ?>">
<meta property="og:description" content="<?php echo $description; ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Arctica / Antarctica">
<meta property="og:url" content="<?php echo escapeHtml($canonicalUrl); ?>">
<meta property="og:image" content="<?php echo escapeHtml($socialImageUrl); ?>">
<meta property="og:image:secure_url" content="<?php echo escapeHtml($socialImageUrl); ?>">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $pageTitle; ?>">
<meta name="twitter:description" content="<?php echo $description; ?>">
<meta name="twitter:image" content="<?php echo escapeHtml($socialImageUrl); ?>">
<link rel="canonical" href="<?php echo escapeHtml($canonicalUrl); ?>">
<?php require __DIR__ . '/site_ui.php'; ?>
