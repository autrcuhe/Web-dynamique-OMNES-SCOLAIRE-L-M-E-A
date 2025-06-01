<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    // Rediriger si pas connecté ou pas admin
    header('Location: index.php'); // Ou une page d'erreur/accès refusé
    exit();
}

$u = $_SESSION['utilisateur'];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panneau d'administration - Omnes Scolaire</title>
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
                 <?php if ($_SESSION['utilisateur']['type'] === 'admin'): ?>
                    <a href="admin_panel.php">Admin Panel</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?php
        // Afficher les messages de statut (succès ou erreur)
        if (isset($_SESSION['message_statut'])) {
            $message_statut = $_SESSION['message_statut'];
            echo '<div class="message message-' . htmlspecialchars($message_statut['type']) . '">' . htmlspecialchars($message_statut['texte']) . '</div>';
            unset($_SESSION['message_statut']); // Supprimer le message après l'affichage
        }
        ?>
        <h2>Panneau d'administration</h2>
        <p>Bienvenue sur le panneau d'administration. Sélectionnez une action ci-dessous :</p>

        <div class="admin-option">
            <h3>Gestion du Personnel</h3>
            <a href="admin_add_personnel.php" class="admin-btn">Ajouter un membre du personnel</a>
            <a href="admin_liste_personnel.php" class="admin-btn">Gérer le personnel</a>
            <a href="admin_gerer_dispos.php" class="admin-btn">Gérer les disponibilités des professeurs</a>
        </div>

        <ul>
            <!-- Ajouter d'autres liens pour la gestion future : liste personnel, gestion CV, etc. -->
        </ul>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 