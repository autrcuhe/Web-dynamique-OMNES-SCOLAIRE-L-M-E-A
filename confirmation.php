<?php
$type = isset($_GET['type']) ? $_GET['type'] : '';
$message = '';
$titre = '';

switch($type) {
    case 'rdv':
        $titre = 'Confirmation de Rendez-vous';
        $message = 'Votre demande de rendez-vous a bien été enregistrée. Vous recevrez une confirmation par email.';
        break;
    case 'message':
        $titre = 'Confirmation de Message';
        $message = 'Votre message a bien été envoyé. Vous recevrez une réponse dans les plus brefs délais.';
        break;
    default:
        header('Location: enseignement.php');
        exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - <?php echo $titre; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Omnes Scolaire</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="parcourir.php">Tout Parcourir</a>
            <a href="recherche.php">Recherche</a>
            <a href="rendezvous.php">Rendez-vous</a>
            <?php if (isset($_SESSION['utilisateur'])): ?>
                <a href="messages.php">Messages</a>
                <a href="compte.php">Votre compte</a>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2><?php echo $titre; ?></h2>
        <p><?php echo $message; ?></p>
        <a href="enseignement.php" class="action-btn">Retour à la liste des enseignants</a>
    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 