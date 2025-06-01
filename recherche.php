<?php
// Page Recherche
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - Recherche</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques pour la barre de recherche (similaire à l'image) */
        .search-bar-container {
            display: flex;
            justify-content: center; /* Centrer horizontalement */
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: #f2f2f2; /* Fond clair pour la barre */
            border-radius: 30px; /* Bords arrondis */
            padding: 5px 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .search-bar input[type="text"] {
            border: none;
            background: none;
            outline: none;
            padding: 10px;
            font-size: 1em;
            width: 300px; /* Largeur de l'input */
        }

        .search-bar button {
            border: none;
            background-color: #a08d5a; /* Couleur du bouton Rechercher */
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .search-bar button:hover {
            background-color: #8a7b4d; /* Couleur au survol */
        }

        .search-icon {
            padding: 0 10px;
            color: #555; /* Couleur de l'icône */
        }

        /* Styles pour les résultats de recherche */
        .search-results {
            margin-top: 20px;
        }

        .result-item {
            background-color: rgba(255, 255, 255, 0.1); /* Fond léger pour chaque résultat */
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            color: #f4f4f4; /* Texte clair */
        }

        .result-item h3 {
            margin-top: 0;
            color: #3498db; /* Couleur d'accentuation */
        }

        .result-item p {
            margin-bottom: 5px;
            color: #cccccc; /* Texte secondaire */
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
        <h2>Recherche</h2>

        <div class="search-bar-container">
            <form action="recherche.php" method="GET">
                <div class="search-bar">
                    <span class="search-icon">&#x1F50D;</span> <!-- Icône loupe -->
                    <input type="text" name="query" placeholder="Nom ou Spécialité ou Etablissement" value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
                    <button type="submit">Rechercher</button>
                </div>
            </form>
        </div>

        <div class="search-results">
            <?php
            // La logique de recherche et l'affichage des résultats seront ajoutés ici
            if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
                require_once 'config.php';
                $search_query = '%' . trim($_GET['query']) . '%';

                // Recherche dans les professeurs (nom, prénom, laboratoire de recherche, nom du département)
                $sql_professeurs = "SELECT 
                                    u.id as user_id,
                                    u.nom, 
                                    u.prenom, 
                                    p.laboratoire_recherche, -- Sélectionner la colonne laboratoire de recherche
                                    d.nom as departement_nom, -- Sélectionner le nom du département
                                    p.email, -- Ajouter l'email du professeur
                                    p.telephone, -- Ajouter le téléphone du professeur
                                    p.bureau, -- Ajouter le bureau du professeur
                                    p.acces, -- Ajouter l'accès du professeur
                                    p.materielles_demandes -- Ajouter les matériels demandés par le professeur
                                FROM utilisateurs u
                                LEFT JOIN professeurs p ON u.id = p.utilisateur_id -- Utiliser LEFT JOIN au cas où un utilisateur 'personnel' n'aurait pas d'entrée dans la table professeurs
                                LEFT JOIN departements d ON p.departement_id = d.id -- Laisser la jointure pour afficher le département si nécessaire
                                WHERE u.type = 'personnel'
                                AND (
                                    u.nom LIKE ? 
                                    OR u.prenom LIKE ? 
                                    OR p.laboratoire_recherche LIKE ? -- Recherche par laboratoire de recherche
                                    OR d.nom LIKE ? -- Ajout de la recherche par nom de département
                                )";
                $stmt_professeurs = $pdo->prepare($sql_professeurs);
                $stmt_professeurs->execute([$search_query, $search_query, $search_query, $search_query]);
                $professeurs_results = $stmt_professeurs->fetchAll(PDO::FETCH_ASSOC);

                // Recherche dans les services (nom)
                $sql_services = "SELECT 
                                s.id as service_id,
                                s.nom,
                                s.description
                                FROM services s
                                WHERE s.nom LIKE ? OR s.description LIKE ?";
                $stmt_services = $pdo->prepare($sql_services);
                $stmt_services->execute([$search_query, $search_query]);
                $services_results = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

                // Afficher les résultats pour les professeurs
                if (count($professeurs_results) > 0) {
                    echo "<h3>Professeurs trouvés :</h3>";
                    foreach ($professeurs_results as $prof) {
                        echo "<div class=\"result-item\">\n";
                        echo "<h3>" . htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']) . "</h3>\n";
                        // Afficher le laboratoire de recherche si présent
                        if (!empty($prof['laboratoire_recherche'])) {
                            echo "<p>Laboratoire de recherche : " . htmlspecialchars($prof['laboratoire_recherche']) . "</p>\n";
                        }
                        // Vous pouvez aussi afficher le département si vous le souhaitez, la jointure est toujours là
                        if (!empty($prof['departement_nom'])) {
                            echo "<p>Département : " . htmlspecialchars($prof['departement_nom']) . "</p>\n";
                        }
                        // Afficher les informations principales si présentes
                        if (!empty($prof['email'])) {
                            echo "<p>Email : " . htmlspecialchars($prof['email']) . "</p>\n";
                        }
                        if (!empty($prof['telephone'])) {
                            echo "<p>Téléphone : " . htmlspecialchars($prof['telephone']) . "</p>\n";
                        }
                        if (!empty($prof['bureau'])) {
                            echo "<p>Bureau : " . htmlspecialchars($prof['bureau']) . "</p>\n";
                        }
                        if (!empty($prof['acces'])) {
                            echo "<p>Accès : " . htmlspecialchars($prof['acces']) . "</p>\n";
                        }
                        if (!empty($prof['materielles_demandes'])) {
                            echo "<p>Matériels demandés : " . htmlspecialchars($prof['materielles_demandes']) . "</p>\n";
                        }
                        // Lien vers la page d'enseignement/profil du professeur ?
                       
                        echo "</div>\n";
                    }
                } else {
                    echo "<p>Aucun professeur trouvé.</p>";
                }

                // Afficher les résultats pour les services
                if (count($services_results) > 0) {
                    echo "<h3>Services/Établissements trouvés :</h3>";
                    foreach ($services_results as $service) {
                        echo "<div class=\"result-item\">\n";
                        echo "<h3>" . htmlspecialchars($service['nom']) . "</h3>\n";
                         if (!empty($service['description'])) {
                             echo "<p>" . nl2br(htmlspecialchars($service['description'])) . "</p>\n";
                         }
                        // Lien vers la page du service si applicable ?
                        // echo "<p><a href=\"services.php?service_id=" . $service['service_id'] . "\">En savoir plus</a></p>\n";
                        echo "</div>\n";
                    }
                } else {
                    echo "<p>Aucun service/établissement trouvé.</p>";
                }

                // Si aucun résultat dans les deux catégories
                if (count($professeurs_results) === 0 && count($services_results) === 0) {
                    echo "<p>Aucun résultat trouvé pour votre recherche.</p>";
                }

            } else {
                echo "<p>Veuillez entrer un terme de recherche.</p>";
            }
            ?>
        </div>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 