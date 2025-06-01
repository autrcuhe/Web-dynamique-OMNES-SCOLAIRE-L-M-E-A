<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Accès non autorisé.'
    ];
    header('Location: index.php');
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Méthode de requête invalide.'
    ];
    header('Location: admin_liste_personnel.php'); // Rediriger vers la liste ou la page de gestion CV si ID présent
    exit();
}

// Récupérer l'ID du professeur et le fichier uploadé
$professeur_id = $_POST['professeur_id'] ?? null;
$cv_file = $_FILES['cv_file'] ?? null;

// Vérifier si l'ID du professeur est présent et valide
if (!$professeur_id || !filter_var($professeur_id, FILTER_VALIDATE_INT)) {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'ID du professeur manquant ou invalide.'
    ];
    header('Location: admin_liste_personnel.php');
    exit();
}

// Vérifier si un fichier a été uploadé sans erreur
if ($cv_file === null || $cv_file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Erreur lors de l\'upload du fichier : ' . ($cv_file['error'] ?? 'Code d\'erreur inconnu')
    ];
    header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
    exit();
}

// Valider le type de fichier (vérifier l'extension et potentiellement le type MIME)
$allowed_extensions = ['xml'];
$file_info = pathinfo($cv_file['name']);
$file_extension = strtolower($file_info['extension'] ?? '');

if (!in_array($file_extension, $allowed_extensions)) {
     $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Type de fichier non autorisé. Seuls les fichiers XML sont acceptés.'
    ];
    header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
    exit();
}

// Potentiellement vérifier le type MIME réel (plus sûr mais peut nécessiter des extensions PHP)
// if ($cv_file['type'] !== 'text/xml') {
//     // Peut être plus strict ici si nécessaire
// }

// Définir le répertoire d'upload et créer si nécessaire
$upload_dir = __DIR__ . '/uploads/cvs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Générer le nom de fichier basé sur l'ID du professeur
$new_filename = 'cv_' . $professeur_id . '.' . $file_extension;
$target_path = $upload_dir . $new_filename;

// Déplacer le fichier uploadé vers le répertoire cible
if (!move_uploaded_file($cv_file['tmp_name'], $target_path)) {
     $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Erreur lors du déplacement du fichier.'
    ];
    header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
    exit();
}

// Mettre à jour le nom du fichier CV dans la base de données pour le professeur
try {
    // Optionnel: Supprimer l'ancien fichier CV si il existe
    // Récupérer l'ancien nom de fichier avant de mettre à jour
    // $stmt_old_cv = $pdo->prepare("SELECT cv FROM professeurs WHERE id = ?");
    // $stmt_old_cv->execute([$professeur_id]);
    // $old_cv_filename = $stmt_old_cv->fetchColumn();
    // if ($old_cv_filename && file_exists($upload_dir . $old_cv_filename)) {
    //     unlink($upload_dir . $old_cv_filename);
    // }

    $stmt = $pdo->prepare("UPDATE professeurs SET cv = ? WHERE id = ?");
    $stmt->execute([$new_filename, $professeur_id]);

    $_SESSION['message_statut'] = [
        'type' => 'success',
        'texte' => 'Le CV a été uploadé et associé au professeur avec succès.'
    ];
    header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
    exit();

} catch (PDOException $e) {
     $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Erreur lors de la mise à jour de la base de données : ' . $e->getMessage()
    ];
     // Optionnel: supprimer le fichier uploadé si la mise à jour BD échoue
    // if (file_exists($target_path)) { unlink($target_path); }

    header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
    exit();
}

// Si on arrive ici (ce qui ne devrait pas arriver en cas de succès ou erreur gérée)
header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
exit();

?> 