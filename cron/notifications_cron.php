<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Notification.php';

$pdo = (new Database())->getConnection();
$notif = new Notification($pdo);

$sql = "SELECT DISTINCT i.user_id, e.id, e.titre
        FROM inscriptions i
        JOIN evenements e ON e.id = i.evenement_id
        WHERE i.statut = 'confirme'
          AND DATE(e.date_evenement) = DATE_ADD(CURDATE(), INTERVAL 2 DAY)";
foreach ($pdo->query($sql) as $row) {
    $notif->create(
        (int) $row['user_id'],
        "⏰ L’événement « {$row['titre']} » aura lieu dans 2 jours.",
        "/gestion_evenements/user/details_evenement.php?id=" . (int) $row['id'],
        'event_reminder'
    );
}

$sql = "SELECT DISTINCT i.user_id, e.id, e.titre
        FROM inscriptions i
        JOIN evenements e ON e.id = i.evenement_id
        WHERE i.statut = 'confirme' AND e.statut = 'annule'";
foreach ($pdo->query($sql) as $row) {
    $notif->create(
        (int) $row['user_id'],
        "L’événement « {$row['titre']} » a été annulé.",
        "/gestion_evenements/user/details_evenement.php?id=" . (int) $row['id'],
        'warning'
    );
}
