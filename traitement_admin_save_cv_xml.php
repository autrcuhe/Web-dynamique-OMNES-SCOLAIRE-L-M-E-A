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
     // Rediriger vers la liste ou la page de gestion CV si ID présent
    header('Location: admin_liste_personnel.php'); 
    exit();
}

// Récupérer l'ID du professeur et le contenu XML
$professeur_id = $_POST['professeur_id'] ?? null;
$cv_xml_content = $_POST['cv_xml_content'] ?? '';

// Vérifier si l'ID du professeur est présent et valide
if (!$professeur_id || !filter_var($professeur_id, FILTER_VALIDATE_INT)) {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'ID du professeur manquant ou invalide.'
    ];
    header('Location: admin_liste_personnel.php');
    exit();
}

// Valider le contenu XML
// Utiliser LIBXML_NOERROR et LIBXML_NOWARNING pour éviter d'interrompre l'exécution pour des erreurs mineures
libxml_use_internal_errors(true);
$xml = simplexml_load_string($cv_xml_content);
$errors = libxml_get_errors();
libxml_use_internal_errors(false); // Restaurer le mode par défaut

if ($xml === false || !empty($errors)) {
    $error_messages = ['Le contenu XML est invalide.'];
    foreach ($errors as $error) {
        $error_messages[] = "Ligne " . $error->line . ": " . htmlspecialchars($error->message);
    }
     $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => implode("<br>", $error_messages)
    ];
    header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
    exit();
}

// Définir le répertoire d'upload
$upload_dir = __DIR__ . '/uploads/cvs/';

// Récupérer l'ancien nom de fichier CV si il existe
$old_cv_filename = null;
try {
    $stmt_old_cv = $pdo->prepare("SELECT cv FROM professeurs WHERE id = ?");
    $stmt_old_cv->execute([$professeur_id]);
    $old_cv_filename = $stmt_old_cv->fetchColumn();
} catch (PDOException $e) {
    // Loguer l'erreur mais ne pas interrompre l'exécution pour cela
    // error_log("Erreur lors de la récupération de l'ancien nom de CV: " . $e->getMessage());
}

$new_filename = $old_cv_filename; // Par défaut, réutiliser l'ancien nom

// Si il n'y avait pas d'ancien fichier CV, générer un nouveau nom basé sur l'ID du professeur
if (!$new_filename) {
    $new_filename = 'cv_' . $professeur_id . '.xml';
}

$target_path = $upload_dir . $new_filename;

// Écrire le contenu XML dans le fichier
if (file_put_contents($target_path, $cv_xml_content) === false) {
     $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Erreur lors de l\'écriture du fichier CV.'
    ];
    header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
    exit();
}

// Mettre à jour le nom du fichier CV dans la base de données si un nouveau nom a été généré
if ($new_filename !== $old_cv_filename) {
    try {
        $stmt = $pdo->prepare("UPDATE professeurs SET cv = ? WHERE id = ?");
        $stmt->execute([$new_filename, $professeur_id]);

        // Optionnel: Supprimer l'ancien fichier physique si le nom a changé et qu'il existait
        if ($old_cv_filename && file_exists($upload_dir . $old_cv_filename)) {
             // Attention: S'assurer que $old_cv_filename est sécurisé pour éviter des suppressions arbitraires
            // Une vérification supplémentaire pourrait être nécessaire ici
            // unlink($upload_dir . $old_cv_filename);
        }

    } catch (PDOException $e) {
         $_SESSION['message_statut'] = [
            'type' => 'danger',
            'texte' => 'Erreur lors de la mise à jour de la base de données avec le nouveau nom de fichier : ' . $e->getMessage()
        ];
         // Optionnel: supprimer le fichier nouvellement écrit si la mise à jour BD échoue
        // if (file_exists($target_path)) { unlink($target_path); }

        header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
        exit();
    }
}

// Succès
$_SESSION['message_statut'] = [
    'type' => 'success',
    'texte' => 'Le contenu du CV XML a été sauvegardé avec succès.'
];
header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
exit();

// Si on arrive ici (ce qui ne devrait pas arriver en cas de succès ou erreur gérée)
header('Location: admin_gerer_cv.php?professeur_id=' . $professeur_id);
exit();
?> 