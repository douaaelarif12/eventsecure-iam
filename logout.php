<?php
require_once __DIR__ . '/classes/Security.php'; Security::startSession(); Security::requirePostCsrf(); Security::destroySession(); header('Location: /gestion_evenements/'); exit;
