<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}
l
// Récupérer l'ID du professeur depuis l'URL
$professeur_id = $_GET['professeur_id'] ?? null;

// Vérifier si l'ID du professeur est présent
if (!$professeur_id) {
    $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'ID du professeur manquant.'
    ];
    header('Location: admin_liste_personnel.php');
    exit();
}

// Récupérer les informations du professeur
try {
    $stmt = $pdo->prepare("SELECT id, nom, prenom, cv FROM professeurs WHERE id = ?");
    $stmt->execute([$professeur_id]);
    $professeur = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si le professeur existe
    if (!$professeur) {
        $_SESSION['message_statut'] = [
            'type' => 'danger',
            'texte' => 'Professeur introuvable.'
        ];
        header('Location: admin_liste_personnel.php');
        exit();
    }

} catch (PDOException $e) {
     $_SESSION['message_statut'] = [
        'type' => 'danger',
        'texte' => 'Erreur lors de la récupération des informations du professeur : ' . $e->getMessage()
    ];
    header('Location: admin_liste_personnel.php');
    exit();
}

$cv_filename = $professeur['cv']; // Nom du fichier CV stocké en base
$cv_content = null;

// Si un nom de fichier CV existe, tenter de lire le contenu XML
if ($cv_filename && file_exists("uploads/cvs/" . $cv_filename)) {
    // Lire le fichier XML (nous ajouterons le parsing XML plus tard)
    $cv_content = file_get_contents("uploads/cvs/" . $cv_filename);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer CV - <?php echo htmlspecialchars($professeur['nom'] . ' ' . $professeur['prenom']); ?> - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
         .cv-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            color: #f4f4f4;
        }
        .cv-container h2 {
             color: #ffffff;
             text-align: center;
             margin-bottom: 20px;
        }
        .upload-form label {
             display: block;
             margin-bottom: 8px;
             color: #cccccc;
             font-weight: bold;
        }
         .upload-form input[type="file"] {
            display: block;
            margin-bottom: 15px;
            color: #f4f4f4;
        }
         .upload-btn {
            background: #16a085;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .upload-btn:hover {
            background: #1abc9c;
        }
         .cv-content-display {
             margin-top: 20px;
             padding: 15px;
             background-color: rgba(0, 0, 0, 0.2);
             border-radius: 5px;
             white-space: pre-wrap; /* Conserver les retours à la ligne et espaces */
             word-wrap: break-word; /* Couper les mots longs si nécessaire */
             max-height: 400px; /* Limiter la hauteur pour ne pas surcharger */
             overflow-y: auto; /* Ajouter une barre de défilement si le contenu dépasse */
             border: 1px solid #555;
         }
         .cv-content-display h3 {
             color: #ffffff;
             margin-top: 0;
             margin-bottom: 10px;
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
        <div class="cv-container">
            <h2>Gérer le CV de <?php echo htmlspecialchars($professeur['nom'] . ' ' . $professeur['prenom']); ?></h2>

            <form action="traitement_admin_upload_cv.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="professeur_id" value="<?php echo htmlspecialchars($professeur['id']); ?>">
                <div class="form-group">
                    <label for="cv_file">Sélectionner un fichier XML de CV :</label>
                    <input type="file" id="cv_file" name="cv_file" accept=".xml" required>
                </div>
                <button type="submit" class="upload-btn">Uploader le CV</button>
            </form>

            <?php if ($cv_content !== null): ?>
                <div class="cv-content-display">
                    <h3>Contenu du CV (XML) :</h3>
                    <pre><?php echo htmlspecialchars($cv_content); ?></pre>
                </div>
            <?php elseif ($cv_filename): ?>
                 <p>Le fichier CV (<?php echo htmlspecialchars($cv_filename); ?>) est introuvable sur le serveur.</p>
            <?php else: ?>
                 <p>Aucun CV n'est associé à ce professeur pour le moment.</p>
            <?php endif; ?>

        </div>

        <div class="cv-container" style="margin-top: 20px;">
            <h3>Éditer ou Créer le Contenu XML du CV :</h3>
            <form action="traitement_admin_save_cv_xml.php" method="POST">
                <input type="hidden" name="professeur_id" value="<?php echo htmlspecialchars($professeur['id']); ?>">
                <div class="form-group">
                    <label for="cv_xml_content">Contenu XML :</label>
                    <textarea id="cv_xml_content" name="cv_xml_content" rows="20" style="width: 100%; background-color: rgba(0, 0, 0, 0.2); color: #f4f4f4; border: 1px solid #555; border-radius: 5px; padding: 10px;"><?php echo htmlspecialchars($cv_content ?? ''); ?></textarea>
                </div>
                <button type="submit" class="upload-btn">Sauvegarder le CV XML</button>
            </form>
        </div>

    </main>
    <footer>
        <p>Contact : contact@omnes-scolaire.fr | Adresse : 10 rue Exemple, Paris</p>
    </footer>
</body>
</html> 
