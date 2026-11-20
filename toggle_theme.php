<?php
require_once __DIR__ . '/classes/Security.php'; Security::startSession(); Security::requirePostCsrf();
$_SESSION['theme'] = ($_SESSION['theme'] ?? 'light') === 'light' ? 'dark' : 'light'; header('Location: ' . Security::safeReferer()); exit;
