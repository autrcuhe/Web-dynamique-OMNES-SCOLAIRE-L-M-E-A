<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    // Rediriger si pas connecté ou pas admin
    header('Location: index.php'); // Ou une page d'erreur/accès refusé
    exit();
}

// Vérifier si le formulaire a été soumis en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Méthode de requête invalide.'
    ];
    header('Location: admin_add_personnel.php');
    exit();
}

// Récupération et nettoyage des données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? ''; // Mot de passe initial, sera haché
$departement_id = trim($_POST['departement_id'] ?? '');
$bureau = trim($_POST['bureau'] ?? '');
$telephone_pro = trim($_POST['telephone'] ?? ''); // Utiliser un nom différent pour le téléphone pro
$acces = trim($_POST['acces'] ?? '');

// Validation des données minimales
$erreurs = [];

if (empty($nom)) {
    $erreurs[] = "Le nom est requis.";
}
if (empty($prenom)) {
    $erreurs[] = "Le prénom est requis.";
}
if (empty($email)) {
    $erreurs[] = "L'email (pour la connexion) est requis.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Format d'email (pour la connexion) invalide.";
} else {
    // Vérifier si l'email existe déjà dans la table utilisateurs
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $erreurs[] = "Cet email est déjà utilisé pour un autre compte.";
    }
}

if (empty($password)) {
    $erreurs[] = "Le mot de passe initial est requis.";
} elseif (strlen($password) < 8) {
    $erreurs[] = "Le mot de passe initial doit contenir au moins 8 caractères.";
}

// Si des erreurs de validation sont présentes, rediriger avec les messages
if (!empty($erreurs)) {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => implode("<br>", $erreurs)
    ];
    header('Location: admin_add_personnel.php');
    exit();
}

// Début de la transaction pour assurer l'atomicité
$pdo->beginTransaction();

try {
    // Insérer l'utilisateur dans la table utilisateurs avec le type 'personnel'
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt_user = $pdo->prepare("
        INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, type)
        VALUES (?, ?, ?, ?, 'personnel')
    ");
    $stmt_user->execute([$nom, $prenom, $email, $password_hash]);

    // Récupérer l'ID du nouvel utilisateur inséré
    $new_user_id = $pdo->lastInsertId();

    $photo_path = NULL; // Par défaut, pas de photo
    // 2. Gérer l'upload de la photo si présente
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/photos/';
        // Assurez-vous que le répertoire d'upload existe
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_info = pathinfo($_FILES['photo']['name']);
        $file_extension = strtolower($file_info['extension']);
        // Générer un nom de fichier unique, par exemple basé sur l'ID utilisateur et l'extension
        $new_file_name = 'professors_' . $new_user_id . '.' . $file_extension;
        $target_file = $upload_dir . $new_file_name;

        // Déplacer le fichier uploadé
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = $target_file; // Enregistrer le chemin relatif dans la base de données
        } else {
            
            error_log("Erreur lors du déplacement du fichier uploadé pour l'utilisateur " . $new_user_id);
        }
    }

    // 3. Insérer les informations spécifiques dans la table professeurs
    $stmt_prof = $pdo->prepare("
        INSERT INTO professeurs (
            utilisateur_id, nom, prenom, departement_id, bureau, telephone, email, acces, photo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt_prof->execute([
        $new_user_id, $nom, $prenom, $departement_id, $bureau, $telephone_pro, $email, $acces, $photo_path
    ]);

    // Commiter la transaction si tout s'est bien passé
    $pdo->commit();

    // Message de succès et redirection
    $_SESSION['message_statut'] = [
        'type' => 'success',
        'texte' => 'Le membre du personnel/professeur a été ajouté avec succès.'
    ];
    header('Location: admin_panel.php');
    exit();

} catch (PDOException $e) {
    // En cas d'erreur, annuler la transaction et afficher une erreur
    $pdo->rollBack();
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Une erreur est survenue lors de l\'ajout du membre : ' . $e->getMessage()
    ];
     // Afficher l'erreur détaillée pour le débogage
   
    header('Location: admin_add_personnel.php');
    exit();
}

// Si pas POST, rediriger vers la page d'ajout
header('Location: admin_add_personnel.php');
exit();
?> 