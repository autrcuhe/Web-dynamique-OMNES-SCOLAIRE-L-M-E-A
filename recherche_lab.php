<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - La Recherche</title>
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
                <a href="deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>La Recherche</h2>
        <p>Dans la catégorie « La Recherche », vous pouvez trouver d'abord les laboratoires de recherche de l'Omnes Scolaire. Dans cette option, on va trouver ces laboratoires bien spécialisés :</p>

        <div class="laboratoires-list">
            <a href="lab_enseignants_chercheurs.php?nom=Systèmes intelligents communicants" class="departement-btn">Systèmes intelligents communicants</a>
            <a href="lab_enseignants_chercheurs.php?nom=mathematiques pour ingenieur" class="departement-btn">mathematiques pour ingenieur</a>
            <a href="lab_enseignants_chercheurs.php?nom=Nanoscience et nanotechnologie" class="departement-btn">Nanoscience et nanotechnologie</a>
            <a href="lab_enseignants_chercheurs.php?nom=Intelligence artificielle responsable" class="departement-btn">Intelligence artificielle responsable</a>
        </div>

        <p>Pour chaque laboratoire, on peut trouver au moins deux enseignants-chercheurs dans cette catégorie. Un enseignant-chercheur est un personnel de l'Omnes Scolaire qui enseigne des cours (informatique ou mathématiques ou physique) et qui travaille également dans la recherche scientifique. Alors, dans son CV, il y a des espaces pour ses publications scientifiques.</p>
        
        <p>Si, par exemple, on clique sur « Systèmes intelligents communicants », on va trouver un enseignant-chercheur, son nom, sa photo, son bureau, sa disponibilité durant la semaine, et son CV. Si cet enseignant-chercheur est actuellement disponible (par quelques indications sur le site), vous pouvez l'envoyer un message texto ou vocale ou même communiquer avec lui par visioconférence.</p>

        <p>En général, un enseignant travaille à cent pourcent dans l'enseignement tandis qu'un enseignant-chercheur travaille en moitié dans l'enseignement et en moitié dans la recherche.</p>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 