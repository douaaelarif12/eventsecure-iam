<?php
require_once __DIR__ . '/classes/Security.php';
require_once __DIR__ . '/config/IamConfig.php';
Security::startSession();
if (isset($_SESSION['user_id'])) { header('Location: /gestion_evenements/'); exit; }
if (!IamConfig::localLoginEnabled()) { header('Location: /gestion_evenements/login.php'); exit; }
require_once __DIR__ . '/classes/User.php'; $error=''; $success='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 if (!Security::verifyCsrf()) { $error='Votre session a expiré. Veuillez réessayer.'; }
 else { $nom=trim((string)($_POST['nom']??'')); $prenom=trim((string)($_POST['prenom']??'')); $email=trim((string)($_POST['email']??'')); $password=(string)($_POST['password']??''); $confirm=(string)($_POST['confirm_password']??'');
  if ($nom===''||$prenom===''||$email===''||$password==='') $error='Veuillez remplir tous les champs';
  elseif ($password!==$confirm) $error='Les mots de passe ne correspondent pas';
  elseif (strlen($password)<10) $error='Le mot de passe doit contenir au moins 10 caractères';
  elseif ((new User())->register($nom,$prenom,$email,$password)) $success='Inscription réussie ! Vous pouvez maintenant vous connecter.';
  else $error='Cette adresse email est déjà utilisée'; }
}
$pageTitle='Inscription'; include __DIR__.'/includes/header.php'; include __DIR__.'/includes/navbar.php';
?>
<div class="container my-5"><div class="row justify-content-center"><div class="col-md-6 col-lg-5"><div class="card shadow"><div class="card-body p-5"><h2 class="text-center mb-4"><i class="bi bi-person-plus"></i> Inscription</h2>
<?php if($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif; ?><?php if($success): ?><div class="alert alert-success"><?=htmlspecialchars($success,ENT_QUOTES,'UTF-8')?></div><?php endif; ?>
<form method="POST"><?=Security::csrfInput()?><div class="mb-3"><label class="form-label">Nom</label><input type="text" class="form-control" name="nom" value="<?=htmlspecialchars($_POST['nom']??'',ENT_QUOTES,'UTF-8')?>" required autocomplete="family-name"></div><div class="mb-3"><label class="form-label">Prénom</label><input type="text" class="form-control" name="prenom" value="<?=htmlspecialchars($_POST['prenom']??'',ENT_QUOTES,'UTF-8')?>" required autocomplete="given-name"></div><div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?=htmlspecialchars($_POST['email']??'',ENT_QUOTES,'UTF-8')?>" required autocomplete="email"></div><div class="mb-3"><label class="form-label">Mot de passe</label><input type="password" class="form-control" name="password" required autocomplete="new-password"><small class="form-text text-muted">Au moins 10 caractères</small></div><div class="mb-3"><label class="form-label">Confirmer le mot de passe</label><input type="password" class="form-control" name="confirm_password" required autocomplete="new-password"></div><button class="btn btn-primary w-100 mb-3">S'inscrire</button><div class="text-center"><a href="login.php">Se connecter</a></div></form>
</div></div></div></div></div><?php include __DIR__.'/includes/footer.php'; ?>
