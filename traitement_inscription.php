<?php
session_start();
require_once 'config.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Méthode de requête invalide.'
    ];
    header('Location: inscription.php');
    exit();
}

// Récupération et nettoyage des données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$adresse1 = trim($_POST['adresse1'] ?? '');
$adresse2 = trim($_POST['adresse2'] ?? '');
$ville = trim($_POST['ville'] ?? '');
$code_postal = trim($_POST['code_postal'] ?? '');
$pays = trim($_POST['pays'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$carte_etudiante = trim($_POST['carte_etudiante'] ?? '');

// Validation des données
$erreurs = [];

// Validation du nom et prénom
if (empty($nom)) {
    $erreurs[] = "Le nom est requis.";
}
if (empty($prenom)) {
    $erreurs[] = "Le prénom est requis.";
}

// Validation de l'email
if (empty($email)) {
    $erreurs[] = "L'email est requis.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Format d'email invalide.";
} else {
    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $erreurs[] = "Cet email est déjà utilisé.";
    }
}

// Validation du mot de passe
if (empty($password)) {
    $erreurs[] = "Le mot de passe est requis.";
} elseif (strlen($password) < 8) {
    $erreurs[] = "Le mot de passe doit contenir au moins 8 caractères.";
}

// Validation des champs d'adresse
if (empty($adresse1)) {
    $erreurs[] = "L'adresse est requise.";
}
if (empty($ville)) {
    $erreurs[] = "La ville est requise.";
}
if (empty($code_postal)) {
    $erreurs[] = "Le code postal est requis.";
}
if (empty($pays)) {
    $erreurs[] = "Le pays est requis.";
}

// Validation du téléphone
if (empty($telephone)) {
    $erreurs[] = "Le numéro de téléphone est requis.";
}

// Validation de la carte étudiante
if (empty($carte_etudiante)) {
    $erreurs[] = "Le numéro de carte étudiante est requis.";
}

// Si des erreurs sont présentes, rediriger vers le formulaire
if (!empty($erreurs)) {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => implode("<br>", $erreurs)
    ];
    header('Location: inscription.php');
    exit();
}

try {
    // Hachage du mot de passe
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Préparation de la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (
            nom, prenom, email, mot_de_passe, adresse_ligne1, adresse_ligne2,
            ville, code_postal, pays, telephone, carte_etudiante, type
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'client'
        )
    ");

    // Exécution de la requête avec les valeurs
    $stmt->execute([
        $nom, $prenom, $email, $password_hash, $adresse1, $adresse2,
        $ville, $code_postal, $pays, $telephone, $carte_etudiante
    ]);

    // Message de succès et redirection vers la page de connexion
    $_SESSION['message_statut'] = [
        'type' => 'success',
        'texte' => 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.'
    ];
    header('Location: connexion.php');
    exit();

} catch (PDOException $e) {
    // En cas d'erreur lors de l'insertion, afficher l'erreur détaillée pour le débogage
    // $_SESSION['message_statut'] = [
    //     'type' => 'danger',
    //     'texte' => 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.'
    // ];
    // header('Location: inscription.php');
    // exit();
    echo "Erreur de base de données : " . $e->getMessage();
    // Optionnel : Loguer l'erreur dans un fichier au lieu de l'afficher directement en production
    // file_put_contents('pdo_error.log', $e->getMessage() . "\n", FILE_APPEND);
} 