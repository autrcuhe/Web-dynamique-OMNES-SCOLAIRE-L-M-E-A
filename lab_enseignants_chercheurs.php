<?php
session_start();
require_once 'config.php';

// Récupérer le nom du laboratoire depuis l'URL
$nom_laboratoire = isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : '';



$enseignants_chercheurs = [];

// Récupérer les enseignants-chercheurs de ce laboratoire depuis la base de données
if (!empty($nom_laboratoire)) {
    $sql = "SELECT p.*, d.nom as departement_nom 
            FROM professeurs p
            LEFT JOIN departements d ON p.departement_id = d.id
            WHERE p.laboratoire_recherche = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom_laboratoire]);
    $enseignants_chercheurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - Enseignants-Chercheurs <?php echo $nom_laboratoire ? '-' . $nom_laboratoire : ''; ?></title>
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
        <h2>Enseignants-Chercheurs <?php echo $nom_laboratoire ? 'du laboratoire ' . $nom_laboratoire : ''; ?></h2>
        
        <?php if (count($enseignants_chercheurs) > 0): ?>
            <div class="enseignants-list">
                <?php foreach($enseignants_chercheurs as $ec): ?>
                    <div class="prof-card">
                        <!-- TODO: Afficher les informations de l'enseignant-chercheur ici -->
                        <!-- Similaire à la structure dans enseignement.php -->
                        <div class='prof-header'>
                            <img src='<?php echo htmlspecialchars($ec['photo'] ?? ''); ?>' alt='<?php echo htmlspecialchars($ec['prenom'] . ' ' . $ec['nom']); ?>' class='prof-photo'>
                            <div class='prof-info'>
                                <h2><?php echo htmlspecialchars($ec['prenom'] . ' ' . $ec['nom']); ?></h2>
                                <p><b>Département :</b> <?php echo htmlspecialchars($ec['departement_nom']); ?></p>
                                <p><b>Laboratoire de recherche :</b> <?php echo htmlspecialchars($ec['laboratoire_recherche']); ?></p>
                                <p><b>Salle :</b> <?php echo htmlspecialchars($ec['bureau']); ?></p>
                                <p><b>Téléphone :</b> <?php echo htmlspecialchars($ec['telephone']); ?></p>
                                <p><b>Email :</b> <?php echo htmlspecialchars($ec['email'] ?? ''); ?></p>
                                <?php if (!empty($ec['acces'])): ?>
                                    <p><b>Accès :</b> <?php echo htmlspecialchars($ec['acces'] ?? ''); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($ec['documents_demandes'])): ?>
                                    <p><b>Documents demandés :</b> <?php echo htmlspecialchars($ec['documents_demandes']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class='prof-actions'>
                            <a href='formulaire.php?professeur_id=<?php echo $ec['id']; ?>&type=rdv' class='action-btn'>Prendre un RDV</a>
                            <a href='formulaire.php?professeur_id=<?php echo $ec['id']; ?>&type=message' class='action-btn'>Communiquer avec le professeur</a>
                            <?php if (!empty($ec['cv'])): ?>
                                <a href='view_cv.php?id=<?php echo $ec['id']; ?>' class='cv-btn'>Voir son CV</a>
                            <?php endif; ?>
                            <!-- TODO: Ajouter ici les informations sur les publications scientifiques si applicable -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aucun enseignant-chercheur trouvé pour ce laboratoire pour le moment.</p>
        <?php endif; ?>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 