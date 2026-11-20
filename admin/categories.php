<?php
require_once __DIR__ . '/../classes/Security.php';
Security::startSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /gestion_evenements/login.php');
    exit;
}

require_once '../classes/Evenement.php';

$evenementObj = new Evenement();

$message = '';
$messageType = '';

// Ajouter une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    Security::requirePostCsrf();
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($nom)) {
        $message = 'Le nom de la catégorie est obligatoire';
        $messageType = 'danger';
    } else {
        if ($evenementObj->ajouterCategorie($nom, $description)) {
            $message = 'Catégorie ajoutée avec succès';
            $messageType = 'success';
        } else {
            $message = 'Erreur lors de l\'ajout de la catégorie';
            $messageType = 'danger';
        }
    }
}

// Supprimer une catégorie
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    Security::requirePostCsrf();
    $categorieId = (int) ($_POST['id'] ?? 0);
    if ($evenementObj->supprimerCategorie($categorieId)) {
        $message = 'Catégorie supprimée avec succès';
        $messageType = 'success';
    } else {
        $message = 'Impossible de supprimer cette catégorie (elle est peut-être utilisée par des événements)';
        $messageType = 'danger';
    }
}

$categories = $evenementObj->getCategories();

$pageTitle = 'Gestion des Catégories';
$cssPath = '/gestion_evenements/assets/css/style.css';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Formulaire d'ajout -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Ajouter une Catégorie</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add"><?= Security::csrfInput() ?>
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Liste des catégories -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-tags"></i> Liste des Catégories</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($categories)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Aucune catégorie disponible
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Description</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $categorie): ?>
                                        <tr>
                                            <td><?php echo $categorie['id']; ?></td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($categorie['nom']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($categorie['description']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($categorie['date_creation'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Security::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$categorie['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$jsPath = '/gestion_evenements/assets/js/script.js';
include '../includes/footer.php'; 
?>
