<?php
require_once __DIR__ . '/classes/Security.php'; Security::startSession(); Security::requirePostCsrf();
$lang = (string)($_POST['lang'] ?? ''); if (in_array($lang, ['fr','en'], true)) $_SESSION['lang']=$lang; else $_SESSION['lang']=(($_SESSION['lang']??'fr')==='fr'?'en':'fr'); header('Location: ' . Security::safeReferer()); exit;
