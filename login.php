<?php
require_once __DIR__ . '/classes/Security.php';
require_once __DIR__ . '/config/IamConfig.php';
Security::startSession();
if (isset($_SESSION['user_id'])) { header('Location: /gestion_evenements/'); exit; }
require_once __DIR__ . '/classes/User.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && IamConfig::localLoginEnabled()) {
    if (!Security::verifyCsrf()) { $error = 'Votre session a expiré. Veuillez réessayer.'; }
    else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($email === '' || $password === '') { $error = 'Veuillez remplir tous les champs'; }
        else {
            $user = (new User())->login($email, $password);
            if ($user) {
                Security::establishIdentity($user, 'local');
                header('Location: /gestion_evenements/' . ($user['role'] === 'admin' ? 'admin/' : 'user/')); exit;
            }
            $error = 'Email ou mot de passe incorrect';
        }
    }
}
$pageTitle = 'Connexion'; include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
?>
<div class="container my-5"><div class="row justify-content-center"><div class="col-md-6 col-lg-5"><div class="card shadow"><div class="card-body p-5">
<h2 class="text-center mb-4"><i class="bi bi-box-arrow-in-right"></i> Connexion</h2>
<?php if (isset($_GET['iam_error'])): ?><div class="alert alert-danger" role="alert"><i class="bi bi-shield-exclamation"></i> La connexion IAM a échoué. Vérifiez le compte Keycloak ou réessayez.</div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if (IamConfig::enabled()): ?>
<a class="btn btn-dark w-100 mb-3" href="/gestion_evenements/auth/keycloak.php"><i class="bi bi-shield-lock"></i> Continuer avec Keycloak</a>
<?php if (IamConfig::localLoginEnabled()): ?><div class="d-flex align-items-center gap-2 mb-3"><hr class="flex-grow-1"><span class="text-muted small">ou compte local</span><hr class="flex-grow-1"></div><?php endif; ?>
<?php endif; ?>
<?php if (IamConfig::localLoginEnabled()): ?>
<form method="POST"><?= Security::csrfInput() ?><div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" required autocomplete="email"></div><div class="mb-3"><label for="password" class="form-label">Mot de passe</label><input type="password" class="form-control" id="password" name="password" required autocomplete="current-password"></div><button type="submit" class="btn btn-primary w-100 mb-3"><i class="bi bi-box-arrow-in-right"></i> Se connecter</button><div class="text-center"><p class="mb-0">Pas encore de compte ? <a href="register.php">S'inscrire</a></p></div></form>
<?php else: ?><p class="text-muted text-center mb-0">Les comptes sont gérés par le fournisseur d’identité.</p><?php endif; ?>
</div></div></div></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
