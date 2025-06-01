<?php
session_start();
require_once 'config.php';

// Récupérer l'ID du professeur depuis l'URL
$professeur_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Vérifier si l'ID du professeur est valide
if (!$professeur_id) {
    die("ID du professeur manquant ou invalide.");
}

// Récupérer les informations de base du professeur
$stmt_professeur = $pdo->prepare("SELECT p.*, d.nom as departement_nom 
                                 FROM professeurs p
                                 LEFT JOIN departements d ON p.departement_id = d.id
                                 WHERE p.id = ?");
$stmt_professeur->execute([$professeur_id]);
$professeur = $stmt_professeur->fetch(PDO::FETCH_ASSOC);

// Vérifier si le professeur existe
if (!$professeur) {
    die("Professeur introuvable.");
}

// Chemin vers le fichier XML
$xml_file = "uploads/cvs/cv_" . $professeur_id . ".xml";

// Vérifier si le fichier XML existe
if (!file_exists($xml_file)) {
    die("Le CV n'est pas disponible.");
}

// Lire et parser le fichier XML
$xml = simplexml_load_file($xml_file);
if ($xml === false) {
    die("Erreur lors de la lecture du CV.");
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CV de <?php echo htmlspecialchars($professeur['prenom'] . ' ' . $professeur['nom']); ?> - Omnes Scolaire</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques pour la page CV si nécessaire */
        .cv-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        /* Style pour le titre principal du CV */
        .cv-container h2 {
            color: #000;
            text-align: center;
            margin-bottom: 20px;
        }
        .cv-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .cv-section h3 {
            color: #333;
            margin-top: 0;
            border-bottom: 2px solid #3498db;
            display: inline-block;
            padding-bottom: 5px;
        }
        .cv-item {
            margin-bottom: 15px;
        }
        .cv-item h4 {
            margin: 0 0 5px 0;
            color: #555;
        }
        .cv-item p {
            margin: 0 0 5px 0;
            color: #666;
        }
        .cv-item ul {
            padding-left: 20px;
        }
        /* Style spécifique pour les informations générales */
        .cv-section.general-info p {
            color: #000;
        }
    </style>
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
        <div class="cv-container">
            <h2>CV de <?php echo htmlspecialchars($professeur['prenom'] . ' ' . $professeur['nom']); ?></h2>
            
            <div class="cv-section general-info">
                <h3>Informations générales</h3>
                <p><b>Département :</b> <?php echo htmlspecialchars($professeur['departement_nom']); ?></p>
                <?php if (!empty($professeur['laboratoire_recherche'])): ?>
                    <p><b>Laboratoire de recherche :</b> <?php echo htmlspecialchars($professeur['laboratoire_recherche']); ?></p>
                <?php endif; ?>
                <p><b>Email :</b> <?php echo htmlspecialchars($professeur['email']); ?></p>
                <?php if (!empty($professeur['telephone'])): ?>
                    <p><b>Téléphone :</b> <?php echo htmlspecialchars($professeur['telephone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($professeur['bureau'])): ?>
                    <p><b>Salle/Bureau :</b> <?php echo htmlspecialchars($professeur['bureau']); ?></p>
                <?php endif; ?>
                 <?php if (!empty($professeur['acces'])): ?>
                    <p><b>Accès :</b> <?php echo htmlspecialchars($professeur['acces']); ?></p>
                <?php endif; ?>
            </div>

            <?php if (isset($xml->formations)): ?>
                <div class="cv-section">
                    <h3>Formations</h3>
                    <?php foreach($xml->formations->formation as $formation): ?>
                        <div class="cv-item">
                            <h4><?php echo htmlspecialchars($formation->diplome); ?></h4>
                            <p><b>Établissement :</b> <?php echo htmlspecialchars($formation->etablissement); ?></p>
                            <?php if (!empty($formation->annee)): ?>
                                <p><b>Année d'obtention :</b> <?php echo htmlspecialchars($formation->annee); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($formation->description)): ?>
                                <p><?php echo nl2br(htmlspecialchars($formation->description)); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($xml->experiences_professionnelles)): ?>
                <div class="cv-section">
                    <h3>Expériences professionnelles</h3>
                    <?php foreach($xml->experiences_professionnelles->experience as $experience): ?>
                        <div class="cv-item">
                            <h4><?php echo htmlspecialchars($experience->titre_poste); ?></h4>
                            <p><b>Entreprise :</b> <?php echo htmlspecialchars($experience->entreprise); ?></p>
                            <p><b>Période :</b> <?php echo htmlspecialchars($experience->periode); ?></p>
                            <?php if (!empty($experience->description)): ?>
                                <p><?php echo nl2br(htmlspecialchars($experience->description)); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($xml->publications)): ?>
                <div class="cv-section">
                    <h3>Publications scientifiques</h3>
                    <ul>
                        <?php foreach($xml->publications->publication as $publication): ?>
                            <li class="cv-item">
                                <h4><?php echo htmlspecialchars($publication->titre); ?></h4>
                                <?php if (!empty($publication->journal)): ?>
                                    <p>Dans : <?php echo htmlspecialchars($publication->journal); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($publication->annee)): ?>
                                    <p>Année : <?php echo htmlspecialchars($publication->annee); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($publication->lien)): ?>
                                    <p><a href="<?php echo htmlspecialchars($publication->lien); ?>" target="_blank">Voir la publication</a></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

        </div>
    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 