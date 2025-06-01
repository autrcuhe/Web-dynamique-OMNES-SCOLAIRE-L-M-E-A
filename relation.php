<?php
// Page Relation Internationale
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - Relation Internationale</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques pour la page Relation Internationale */
        .international-section {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .international-section h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .contact-info {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .contact-info img {
            width: 100px;
            height: auto;
            margin-right: 20px;
            border-radius: 4px;
        }
        .contact-info div {
            flex-grow: 1;
        }
        .contact-info p {
            margin: 5px 0;
            color: #155724;
        }
        .action-btn {
            display: inline-block;
            background-color: #ffda6a;
            color: #333;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }
        .action-btn:hover {
            background-color: #ffc107;
        }
        .services-list {
            margin-top: 20px;
        }
        .services-list h3 {
            color: #333;
            margin-top: 0;
            border-bottom: 2px solid #3498db;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .services-list p {
            color: #555;
            line-height: 1.6;
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
        <div class="international-section">
            <h2>Relation Internationale</h2>
            
            <div class="contact-info">
                <img src="image/Image2.JPEG" alt="Illustration Relation Internationale">
                <div>
                    <h3>Relation Internationale</h3>
                    <p><b>Salle :</b> P-425</p>
                    <p><b>Téléphone :</b> +33 01 12 13 14 15</p>
                    <p><b>Email :</b> relation-internationale@omnes-intl.fr</p>
                    <a href="services_internationaux.php" class="action-btn">Nos services</a> <!-- Lien à modifier si une page spécifique pour les services existe -->
                </div>
            </div>

            <div class="services-list">
                <h3>En savoir plus sur les services :</h3>
                <p>En cliquant sur le bouton « Nos services », on va trouver des services fournis par la Relation Internationale, tels que « Les universités partenaires », « Doubles diplômes à l'international », « Apprentissage des langues », « Summer School », etc. On va trouver également les salles où ces services seront effectués. En sélectionnant une catégorie, par exemple, « Les universités partenaires », on va trouver des informations liées sur le cours qu'on peut prendre à l'international mais crédité à l'Omnes Scolaire, le nom de l'université, ses coordonnées, et la personne à contacter. Ensuite, il y a un calendrier disponible sur un semestre à l'international. On peut prendre un RDV avec le bureau de la relation internationale en cliquant sur un bouton disponible dans la page web. Ensuite, on est assuré de notre RDV avec la relation internationale.</p>
                <!-- On pourrait détailler les services ici si nécessaire -->
            </div>

        </div>
    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 