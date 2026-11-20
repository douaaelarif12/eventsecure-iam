<?php
require_once __DIR__ . '/../classes/Security.php'; Security::startSession();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') { header('Location: /gestion_evenements/login.php'); exit; }
Security::requirePostCsrf(); require_once __DIR__ . '/../classes/Evenement.php';
$id=(int)($_POST['id']??0); if($id>0 && (new Evenement())->supprimerEvenement($id)) $_SESSION['success']='Événement supprimé avec succès.'; else $_SESSION['error']='Suppression impossible.';
header('Location: evenements.php'); exit;
