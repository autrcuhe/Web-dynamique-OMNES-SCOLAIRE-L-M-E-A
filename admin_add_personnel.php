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
    <title>Ajouter Personnel/Professeur - Admin Panel - Omnes Scolaire</title>
    <link rel="stylesheet" href="style.css">
     <style>
        .add-personnel-form {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.08); /* Fond légèrement transparent */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            color: #f4f4f4; /* Texte clair */
        }
        .add-personnel-form h2 {
            text-align: center;
            color: #ffffff;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cccccc;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #555;
            border-radius: 5px;
            font-size: 1em;
            background-color: rgba(0, 0, 0, 0.3);
            color: #ffffff;
        }
        .submit-btn {
            background: #16a085;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            width: 100%;
            transition: background 0.2s;
            margin-top: 15px;
        }
        .submit-btn:hover {
            background: #1abc9c;
        }
        /* Style pour les messages de statut (succès/erreur) */
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .message-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
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
            echo '<div class="message-' . htmlspecialchars($message_statut['type']) . '">' . htmlspecialchars($message_statut['texte']) . '</div>';
            unset($_SESSION['message_statut']); // Supprimer le message après l'affichage
        }
        ?>
        <form action="traitement_admin_add_personnel.php" method="POST" class="add-personnel-form" enctype="multipart/form-data">
            <h2>Ajouter un membre du personnel ou un professeur</h2>
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required>
            </div>
             <div class="form-group">
                <label for="password">Mot de passe initial :</label>
                <input type="password" id="password" name="password" required>
            </div>
             <div class="form-group">
                <label for="departement_id">Département ID : (1: informatique, 2: mathématiques, 3: physique)</label>
                <input type="text" id="departement_id" name="departement_id">
            </div>
             <div class="form-group">
                <label for="bureau">Bureau :</label>
                <input type="text" id="bureau" name="bureau">
            </div>
             <div class="form-group">
                <label for="telephone">Téléphone :</label>
                <input type="text" id="telephone" name="telephone">
            </div>
            <div class="form-group">
                <label for="acces">Accès :</label>
                <input type="text" id="acces" name="acces">
            </div>
             <div class="form-group">
                <label for="photo">Photo :</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>
             <!-- Pour simplifier, on ajoutera les autres champs (photo, video, cv, dispo) plus tard -->

            <button type="submit" class="submit-btn">Ajouter le membre</button>
        </form>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 