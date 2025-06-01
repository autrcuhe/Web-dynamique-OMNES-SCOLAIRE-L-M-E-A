<?php
session_start();
require_once 'config.php';

// Rediriger si l'utilisateur est déjà connecté
if (isset($_SESSION['utilisateur'])) {
    header('Location: compte.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Omnes Scolaire</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques pour le formulaire d'inscription */
        .inscription-form {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.08); /* Fond légèrement transparent */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            color: #f4f4f4; /* Texte clair */
        }
        .inscription-form h2 {
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
         .form-group input[type="tel"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #555;
            border-radius: 5px;
            font-size: 1em;
            background-color: rgba(0, 0, 0, 0.3);
            color: #ffffff;
        }
         .form-group input[type="password"] {
             margin-bottom: 5px; /* Espace sous le champ mot de passe */
         }

         .password-info {
             font-size: 0.9em;
             color: #aaaaaa; /* Texte d'information plus clair */
             margin-top: 5px;
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
        <form action="traitement_inscription.php" method="POST" class="inscription-form">
            <h2>Créer un compte client</h2>
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
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
                <p class="password-info">Minimum 8 caractères.</p>
            </div>
            <div class="form-group">
                <label for="adresse1">Adresse Ligne 1 :</label>
                <input type="text" id="adresse1" name="adresse1" required>
            </div>
            <div class="form-group">
                <label for="adresse2">Adresse Ligne 2 (optionnel) :</label>
                <input type="text" id="adresse2" name="adresse2">
            </div>
            <div class="form-group">
                <label for="ville">Ville :</label>
                <input type="text" id="ville" name="ville" required>
            </div>
            <div class="form-group">
                <label for="code_postal">Code Postal :</label>
                <input type="text" id="code_postal" name="code_postal" required>
            </div>
            <div class="form-group">
                <label for="pays">Pays :</label>
                <input type="text" id="pays" name="pays" required>
            </div>
             <div class="form-group">
                <label for="telephone">Numéro de téléphone :</label>
                <input type="tel" id="telephone" name="telephone" required>
            </div>
             <div class="form-group">
                <label for="carte_etudiante">Carte Étudiante :</label>
                <input type="text" id="carte_etudiante" name="carte_etudiante" required>
            </div>

            <button type="submit" class="submit-btn">Créer le compte</button>
        </form>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 