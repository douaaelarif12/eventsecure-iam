<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../../classes/Security.php'; Security::startSession();
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'not_logged_in']); exit; }
if (!Security::verifyCsrf()) { http_response_code(419); echo json_encode(['ok'=>false,'error'=>'csrf_failed']); exit; }
require_once __DIR__ . '/../../classes/Database.php'; require_once __DIR__ . '/../../classes/Notification.php';
try { $notif=new Notification((new Database())->getConnection()); $userId=(int)$_SESSION['user_id']; $action=(string)($_POST['action']??''); if($action==='mark_all_read'){ $notif->markAllAsRead($userId); echo json_encode(['ok'=>true,'unread'=>0]); exit; } if($action==='mark_read'){ $id=(int)($_POST['id']??0); if($id>0)$notif->markAsRead($id,$userId); echo json_encode(['ok'=>true,'unread'=>$notif->countUnread($userId)]); exit; } http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_action']); } catch (Throwable $e) { error_log('EventSecure notification action: '.$e->getMessage()); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'server_error']); }
