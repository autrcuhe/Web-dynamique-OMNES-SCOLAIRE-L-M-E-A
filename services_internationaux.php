<?php
session_start();
require_once 'config.php';

// Définir la liste des catégories de services pour l'affichage
$categories = [
    ['id' => 'universites', 'nom' => 'Les universités partenaires'],
    ['id' => 'doubles-diplomes', 'nom' => 'Doubles diplômes à l\'international'],
    ['id' => 'langues', 'nom' => 'Apprentissage des langues'],
    ['id' => 'summer-school', 'nom' => 'Summer School'],
];


$salles = ["Salle P-425", "Salle B-101"]; // Exemple statique pour l'instant

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - Services Internationaux</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .services-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .services-container h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .service-category-list {
            margin-bottom: 30px;
            text-align: center;
        }
        .service-category-list button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .service-category-list button:hover {
            background-color: #2980b9;
        }
        .service-details {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            background-color: #f9f9f9;
            min-height: 150px; /* Minimum height to show the box */
        }
        .service-details h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .service-details p, .service-details ul {
            color: #555;
            line-height: 1.6;
        }
         .salles-info {
             margin-top: 20px;
             padding: 15px;
             border: 1px solid #eee;
             border-radius: 8px;
             background-color: #f9f9f9;
         }
         .salles-info h3 {
             color: #333;
             margin-top: 0;
             margin-bottom: 10px;
         }
         .salles-info p {
             color: #555;
         }
         .rdv-button-container {
             text-align: center;
             margin-top: 20px;
         }
         .rdv-button-container a {
             display: inline-block;
             background-color: #2ecc71; /* Vert */
             color: white;
             padding: 12px 25px;
             border-radius: 5px;
             text-decoration: none;
             font-size: 1.1em;
             transition: background-color 0.3s ease;
         }
         .rdv-button-container a:hover {
             background-color: #27ae60; /* Vert plus foncé */
         }
        /* Style spécifique pour le texte d'introduction */
         .services-container .intro-text {
             color: #000;
             text-align: center; /* Centrer le texte comme dans l'image relation.php */
             margin-bottom: 20px;
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
        <div class="services-container">
            <h2>Services Internationaux</h2>

            <p class="intro-text">Explorez les différentes catégories de services offerts par notre département de Relation Internationale :</p>

            <div class="service-category-list">
                <?php foreach($categories as $category): ?>
                    <button class="category-btn" data-category-id="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['nom']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div id="service-details" class="service-details">
                <!-- Les détails du service sélectionné s'afficheront ici -->
                <p>Sélectionnez une catégorie ci-dessus pour afficher les détails.</p>
            </div>

            <div class="salles-info">
                <h3>Salles où les services sont effectués :</h3>
                <p><?php echo htmlspecialchars(implode(", ", $salles)); ?></p>
                 <!-- Idéalement, ceci serait lié aux services spécifiques -->
            </div>

            <div class="rdv-button-container">
                <a href="formulaire.php?type=rdv&service=international" class="rdv-btn">Prendre rendez-vous avec la Relation Internationale</a>
            </div>

        </div>
    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>

    <script>
        const categoryButtons = document.querySelectorAll('.category-btn');
        const serviceDetailsDiv = document.getElementById('service-details');

        categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                const categoryId = button.dataset.categoryId;

                // Appel AJAX pour récupérer les détails du service
                fetch('get_international_service_details.php?category_id=' + categoryId)
                    .then(response => response.json())
                    .then(data => {
                        let detailsHTML = '';
                        if (data.error) {
                            detailsHTML = '<p style="color: red;">Erreur: ' + data.error + '</p>';
                        } else if (data.length === 0) {
                            detailsHTML = '<p>Aucune information disponible pour cette catégorie pour le moment.</p>';
                        } else {
                            // Construire l'HTML en fonction de la catégorie et des données reçues
                            detailsHTML += '<h3>' + button.textContent + '</h3>';
                            if (categoryId === 'universites') {
                                detailsHTML += '<h4>Universités partenaires :</h4><ul>';
                                data.forEach(uni => {
                                    detailsHTML += `<li><b>${uni.nom}</b> (${uni.pays}, ${uni.ville})<br>Email: ${uni.contact_email}<br>Site web: <a href="${uni.site_web}" target="_blank">${uni.site_web}</a>`;
                                    // Afficher les cours si l'université en a
                                    if (uni.cours && uni.cours.length > 0) {
                                        detailsHTML += '<br>Cours disponibles :';
                                        detailsHTML += '<ul>';
                                        uni.cours.forEach(c => {
                                            detailsHTML += `<li>${c.nom_cours} (${c.credits}, ${c.semestre})</li>`;
                                        });
                                        detailsHTML += '</ul>';
                                    }
                                    detailsHTML += '</li>'; // Fin de l'élément li pour l'université
                                });
                                detailsHTML += '</ul>';
                                 // Note: L'affichage des cours pour une université spécifique nécessiterait une interaction supplémentaire

                            } else if (categoryId === 'doubles-diplomes') {
                                detailsHTML += '<h4>Programmes de doubles diplômes :</h4><ul>';
                                data.forEach(prog => {
                                     detailsHTML += `<li><b>${prog.nom_programme}</b>`;
                                     if (prog.universite_nom) { detailsHTML += ` (avec ${prog.universite_nom})`; }
                                     detailsHTML += `<br>Durée: ${prog.duree}<br>Diplôme obtenu: ${prog.diplome_obtenu}</li>`;
                                });
                                detailsHTML += '</ul>';
                            } else if (categoryId === 'langues') {
                                detailsHTML += '<h4>Programmes d\'apprentissage des langues :</h4><ul>';
                                data.forEach(prog => {
                                     detailsHTML += `<li><b>${prog.nom_programme}</b> (${prog.langue}, ${prog.niveau})<br>Format: ${prog.format}<br>Durée: ${prog.duree}<br>Description: ${prog.description}</li>`;
                                });
                                detailsHTML += '</ul>';
                            } else if (categoryId === 'summer-school') {
                                detailsHTML += '<h4>Programmes Summer School :</h4><ul>';
                                data.forEach(prog => {
                                     detailsHTML += `<li><b>${prog.nom_programme}</b>`;
                                     if (prog.universite_nom) { detailsHTML += ` (à ${prog.universite_nom})`; }
                                     detailsHTML += `<br>Lieu: ${prog.ville}, ${prog.pays}<br>Durée: ${prog.duree}<br>Dates: ${prog.dates_debut} au ${prog.dates_fin}</li>`;
                                });
                                detailsHTML += '</ul>';
                            }
                       

                        }
                        serviceDetailsDiv.innerHTML = detailsHTML;
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des détails : ', error);
                        serviceDetailsDiv.innerHTML = '<p style="color: red;">Erreur lors du chargement des détails.</p>';
                    });
            });
        });

        

    </script>
</body>
</html> 