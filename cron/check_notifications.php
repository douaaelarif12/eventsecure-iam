<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Notification.php';

$pdo = (new Database())->getConnection();
$notification = new Notification($pdo);

$sql = "SELECT DISTINCT i.user_id, e.id AS event_id, e.titre
        FROM inscriptions i
        JOIN evenements e ON e.id = i.evenement_id
        WHERE i.statut = 'confirme'
          AND DATE(e.date_evenement) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";

foreach ($pdo->query($sql)->fetchAll() as $row) {
    $notification->create(
        (int) $row['user_id'],
        "⏰ L'événement « {$row['titre']} » aura lieu demain.",
        "/gestion_evenements/user/details_evenement.php?id=" . (int) $row['event_id'],
        'event_reminder'
    );
}
