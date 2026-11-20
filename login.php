<?php
require_once __DIR__ . '/classes/Security.php';
Security::startSession();
if (isset($_SESSION['user_id'])) { header('Location: /gestion_evenements/'); exit; }
require_once __DIR__ . '/classes/User.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::verifyCsrf()) { $error = 'Votre session a expiré. Veuillez réessayer.'; }
    else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($email === '' || $password === '') { $error = 'Veuillez remplir tous les champs'; }
        else {
            $user = (new User())->login($email, $password);
            if ($user) {
                Security::regenerateSession();
                $_SESSION['user_id'] = $user['id']; $_SESSION['nom'] = $user['nom']; $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email']; $_SESSION['role'] = $user['role'];
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
<?php if ($error): ?><div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="POST"><?= Security::csrfInput() ?><div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" required autocomplete="email"></div><div class="mb-3"><label for="password" class="form-label">Mot de passe</label><input type="password" class="form-control" id="password" name="password" required autocomplete="current-password"></div><button type="submit" class="btn btn-primary w-100 mb-3"><i class="bi bi-box-arrow-in-right"></i> Se connecter</button><div class="text-center"><p class="mb-0">Pas encore de compte ? <a href="register.php">S'inscrire</a></p></div></form>
</div></div></div></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
