<?php
// Page Tout Parcourir
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - Tout Parcourir</title>
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
        <h2>Tout Parcourir</h2>
        <div class="categories">
            <a class="categorie-card" href="enseignement.php">
                <h3>L'Enseignement</h3>
                <p>Voir les départements et enseignants</p>
            </a>
            <a class="categorie-card" href="recherche_lab.php">
                <h3>La Recherche</h3>
                <p>Voir les laboratoires et enseignants-chercheurs</p>
            </a>
            <a class="categorie-card" href="relation.php">
                <h3>Relation Internationale</h3>
                <p>Voir les services et partenaires internationaux</p>
            </a>
        </div>
    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 
