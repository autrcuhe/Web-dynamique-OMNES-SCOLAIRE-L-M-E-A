<?php
require_once 'config.php';

$is_international_service = isset($_GET['service']) && $_GET['service'] === 'international';
$professeur_id = null;
$type = $is_international_service ? 'rdv' : ($_GET['type'] ?? null);
$professeur = null;
$service_id = null;
$tous_les_creneaux = []; // Initialiser la variable ici

if ($is_international_service) {
    // Récupérer l'ID du service "Relation Internationale"
    $service_nom = 'Relation Internationale';
    $stmt_service = $pdo->prepare("SELECT id, nom FROM services WHERE nom = ?");
    $stmt_service->execute([$service_nom]);
    $service = $stmt_service->fetch(PDO::FETCH_ASSOC);

    if ($service) {
        $service_id = $service['id'];
        $destinataire_nom = $service['nom'];

        // Récupérer les créneaux disponibles pour ce service
        $stmt_creneaux = $pdo->prepare("SELECT * FROM creneaux_disponibles WHERE service_id = ? ORDER BY date, heure_debut");
        $stmt_creneaux->execute([$service_id]);
        $tous_les_creneaux = $stmt_creneaux->fetchAll(PDO::FETCH_ASSOC);

        // Grouper les créneaux par date pour l'affichage
        $creneaux_groupes_par_date = [];
        foreach ($tous_les_creneaux as $creneau) {
            $creneaux_groupes_par_date[$creneau['date']][] = $creneau;
        }

    } else {
        // Gérer le cas où le service n'est pas trouvé (rediriger ou afficher une erreur)
        die("Service Relation Internationale introuvable."); // Simple message d'erreur pour l'instant
    }

} else {
    if (isset($_GET['professeur_id']) && isset($_GET['type'])) {
        $professeur_id = (int)$_GET['professeur_id'];
        
        if (!in_array($type, ['rdv', 'message'])) {
            header('Location: enseignement.php');
            exit();
        }
        
        $stmt = $pdo->prepare("SELECT * FROM professeurs WHERE id = ?");
        $stmt->execute([$professeur_id]);
        $professeur = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$professeur) {
            header('Location: enseignement.php');
            exit();
        }

        // Fetch all time slots for the professor (only if it's a professor RDV)
        if ($type === 'rdv') {
            $stmt_creneaux = $pdo->prepare("SELECT * FROM creneaux_disponibles WHERE professeur_id = ? AND statut = 'disponible' ORDER BY date, heure_debut");
            $stmt_creneaux->execute([$professeur_id]);
            $tous_les_creneaux = $stmt_creneaux->fetchAll(PDO::FETCH_ASSOC);
        }
         $destinataire_nom = htmlspecialchars($professeur['prenom'] . ' ' . $professeur['nom']);

    } else {
        header('Location: enseignement.php');
        exit();
    }
}

// Fetch client phone number if logged in (applicable for both professor and international RDV)
$client_telephone = '';
if (isset($_SESSION['utilisateur']) && $_SESSION['utilisateur']['type'] === 'client') {
    $stmt_tel = $pdo->prepare("SELECT telephone FROM utilisateurs WHERE id = ? AND type = 'client'");
    $stmt_tel->execute([$_SESSION['utilisateur']['id']]);
    $client_info = $stmt_tel->fetch(PDO::FETCH_ASSOC);
    if ($client_info && $client_info['telephone']) {
        $client_telephone = $client_info['telephone'];
    }
}

$titre = '';
$action_target = '';


if ($is_international_service) {
    $titre = 'Prendre un Rendez-vous avec la Relation Internationale';
    $action_target = 'traitement.php?service_id=' . $service_id; // Passer l'ID du service

} else { // C'est un formulaire professeur (RDV ou message)
    $titre = $type === 'rdv' ? 'Prendre un Rendez-vous' : 'Envoyer un Message';
    $action_target = 'traitement.php'; // Le script traitement gère déjà les RDV/messages professeurs
}

$action_btn_text = $type === 'rdv' ? 'Confirmer le rendez-vous' : 'Envoyer le message';


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - <?php echo $titre; ?></title>
    <link rel="stylesheet" href="style.css">
     <style>
        /* Styles spécifiques pour les tableaux de créneaux */
        .dispo-rdv-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .dispo-rdv-table th, .dispo-rdv-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            color: #000; /* Texte en noir pour être visible sur fond clair */
        }
        .dispo-rdv-table th {
            background-color: #f2f2f2;
            color: #333;
        }
        .dispo-rdv-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .dispo-rdv-table tbody tr:hover {
            background-color: #e9e9e9;
        }
        .status-disponible {
            color: green;
            font-weight: bold;
        }
        .status-reservé {
            color: orange;
        }
         .status-annulé {
             color: red;
         }
         .creneau-header {
             background-color: #d3eaf2; /* Couleur légère pour l'en-tête de date */
             font-weight: bold;
             color: #333; /* Texte plus sombre pour l'en-tête de date */
         }

         /* Style pour le titre principal du formulaire */
         main h2 {
             color: #333; /* Texte plus sombre pour le titre principal */
             text-align: center;
             margin-bottom: 20px;
         }

    </style>
</head>
<body>
    <header>
        <h1>Omnes Scolaire</h1>
        <?php
        // Menu dynamique
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
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
                <a href="deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2><?php echo $titre; ?> avec <?php echo $destinataire_nom; ?></h2>
        
        <form action="<?php echo $action_target; ?>" method="POST" class="<?php echo $type; ?>-form">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <?php if (!$is_international_service): ?>
            <input type="hidden" name="professeur_id" value="<?php echo $professeur_id; ?>">
            <?php else: // Ajouter le service_id pour les RDV internationaux ?>
             <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
            <?php endif; ?>

            <?php if ($type === 'rdv'): ?>

                <?php if ($is_international_service): ?>
                    <!-- Tableau de disponibilités pour la Relation Internationale -->
                    <div class="form-group">
                        <label>Sélectionnez une date et un créneau disponibles :</label>
                        <table class="dispo-rdv-table">
                            <thead>
                                <tr>
                                    <th>Jour</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Statut</th>
                                    <th>Sélectionner</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($tous_les_creneaux) > 0): ?>
                                    <?php foreach ($creneaux_groupes_par_date as $date => $creneaux_du_jour): ?>
                                        <tr class="creneau-header">
                                            <td colspan="5">Le <?php echo date('d/m/Y', strtotime($date)); ?> (<?php echo date('l', strtotime($date)); ?>)</td>
                                        </tr>
                                        <?php foreach ($creneaux_du_jour as $creneau): ?>
                                        <tr>
                                            <td></td> <!-- Jour vide car déjà dans l'en-tête de date -->
                                            <td><?php echo htmlspecialchars($creneau['date']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($creneau['heure_debut'], 0, 5) . ' - ' . substr($creneau['heure_fin'], 0, 5)); ?></td>
                                            <td class="status-<?php echo htmlspecialchars($creneau['statut']); ?>"><?php echo htmlspecialchars($creneau['statut']); ?></td>
                                            <td>
                                                <?php if ($creneau['statut'] === 'disponible'): ?>
                                                    <input type="radio" name="creneau_id" value="<?php echo $creneau['id']; ?>" required>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Aucun créneau configuré pour le moment pour la Relation Internationale.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: // Formulaire RDV professeur ?>
                    <!-- Tableau de disponibilités (existant) -->
                    <div class="form-group">
                        <label>Sélectionnez un créneau disponible :</label>
                        <table class="dispo-rdv-table">
                            <thead>
                                <tr>
                                    <th>Jour</th>
                                    <th>Date</th>
                                    <th>Heure</th>
                                    <th>Statut</th>
                                    <th>Sélectionner</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($tous_les_creneaux) > 0): ?>
                                    <?php foreach ($tous_les_creneaux as $creneau): ?>
                                        <tr>
                                            <td><?php echo date('l', strtotime($creneau['date'])); ?></td>
                                            <td><?php echo htmlspecialchars($creneau['date']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($creneau['heure_debut'], 0, 5) . ' - ' . substr($creneau['heure_fin'], 0, 5)); ?></td>
                                            <td class="status-<?php echo htmlspecialchars($creneau['statut']); ?>"><?php echo htmlspecialchars($creneau['statut']); ?></td>
                                            <td>
                                                <?php if ($creneau['statut'] === 'disponible'): ?>
                                                    <input type="radio" name="creneau_id" value="<?php echo $creneau['id']; ?>" required>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Aucun créneau configuré pour le moment.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="motif">Motif du rendez-vous :</label>
                    <textarea id="motif" name="motif" rows="4" required></textarea>
                </div>

            <?php else: // type === 'message' ?>
                <div class="form-group">
                    <label for="sujet">Sujet :</label>
                    <input type="text" id="sujet" name="sujet" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message :</label>
                    <textarea id="message" name="message" rows="6" required></textarea>
                </div>
            <?php endif; ?>
            
            <button type="submit" class="submit-btn"><?php echo $action_btn_text; ?></button>
        </form>
    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>

    <script>
    // Supprimer le JavaScript du calendrier interactif
    console.log('JavaScript pour formulaire.php adapté');
    </script>

</body>
</html> 