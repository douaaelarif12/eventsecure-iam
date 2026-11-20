<?php
require_once __DIR__ . '/classes/Inscription.php';
$token=isset($_GET['token'])?trim((string)$_GET['token']):'';
$ticket=(preg_match('/^[a-f0-9]{32}$/', $token)===1)?(new Inscription())->getTicketDataByToken($token):null;
$pageTitle='Vérification Ticket'; include __DIR__.'/includes/header.php'; include __DIR__.'/includes/navbar.php';
?>
<div class="container my-5"><div class="card shadow"><div class="card-header bg-primary text-white"><h4 class="mb-0"><i class="bi bi-shield-check"></i> Vérification du billet</h4></div><div class="card-body">
<?php if(!$ticket): ?><div class="alert alert-danger mb-0"><i class="bi bi-x-circle"></i> Billet invalide ou introuvable.</div><?php else: ?><div class="alert alert-success"><i class="bi bi-check-circle"></i> Billet valide.</div><h5 class="mb-3">Événement</h5><p class="mb-1"><strong>Titre :</strong> <?=htmlspecialchars($ticket['titre'],ENT_QUOTES,'UTF-8')?></p><p class="mb-1"><strong>Date :</strong> <?=date('d/m/Y',strtotime($ticket['date_evenement']))?></p><p class="mb-1"><strong>Horaire :</strong> <?=htmlspecialchars(substr((string)$ticket['heure_debut'],0,5),ENT_QUOTES,'UTF-8')?> - <?=htmlspecialchars(substr((string)$ticket['heure_fin'],0,5),ENT_QUOTES,'UTF-8')?></p><p class="mb-0"><strong>Statut :</strong> <?=htmlspecialchars($ticket['statut']??'—',ENT_QUOTES,'UTF-8')?></p><?php endif; ?>
</div></div></div><?php include __DIR__.'/includes/footer.php'; ?>
