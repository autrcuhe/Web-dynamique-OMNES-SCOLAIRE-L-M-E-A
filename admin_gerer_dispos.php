<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    // Rediriger si pas connecté ou pas admin
    header('Location: index.php'); // Ou une page d'erreur
    exit();
}

$u = $_SESSION['utilisateur'];

// Récupérer la liste des professeurs (utilisateurs de type 'personnel')
try {
    $stmt_professeurs = $pdo->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE type = 'personnel' ORDER BY nom, prenom");
    $stmt_professeurs->execute();
    $professeurs = $stmt_professeurs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Gérer l'erreur de base de données
    error_log("Erreur lors de la récupération des professeurs pour la gestion des dispos : " . $e->getMessage());
    $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur lors du chargement des professeurs.'];
    $professeurs = []; // Assurer que $professeurs est un tableau vide en cas d'erreur
}

// Récupérer l'ID de l'utilisateur professeur sélectionné depuis l'URL (si présent)
$professeur_utilisateur_id = filter_input(INPUT_GET, 'professeur_id', FILTER_VALIDATE_INT);
$professeur_selectionne = null;
$creneaux_professeur = []; // Variable pour stocker les créneaux directs

// Si un professeur utilisateur est sélectionné, récupérer ses informations et ses créneaux
if ($professeur_utilisateur_id) {
    try {
        // Récupérer les informations de l'utilisateur professeur sélectionné
        $stmt_select_prof_user = $pdo->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE id = ? AND type = 'personnel'");
        $stmt_select_prof_user->execute([$professeur_utilisateur_id]);
        $professeur_selectionne = $stmt_select_prof_user->fetch(PDO::FETCH_ASSOC);

        if ($professeur_selectionne) {
            // --- Récupérer l'ID réel du professeur depuis la table professeurs --- PROBLÈME AVEC L'ID UTILISATEUR / ID PROF
            // Nous devons récupérer l'ID de la table professeurs en utilisant l'utilisateur_id
            $stmt_get_real_prof_id = $pdo->prepare("SELECT id FROM professeurs WHERE utilisateur_id = ?");
            $stmt_get_real_prof_id->execute([$professeur_utilisateur_id]);
            $real_professeur_id = $stmt_get_real_prof_id->fetchColumn();

            if ($real_professeur_id) {
                 // Récupérer les créneaux disponibles pour ce professeur réel
                $stmt_creneaux = $pdo->prepare("SELECT * FROM creneaux_disponibles WHERE professeur_id = ? ORDER BY date, heure_debut");
                $stmt_creneaux->execute([$real_professeur_id]);
                $creneaux_professeur = $stmt_creneaux->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Gérer le cas où un utilisateur 'personnel' n'a pas d'entrée dans la table 'professeurs'
                $_SESSION['message_statut'] = ['type' => 'warning', 'texte' => 'Aucune entrée correspondante trouvée dans la table professeurs pour cet utilisateur.'];
                $professeur_selectionne = null; // Ne pas afficher la section de gestion
                $professeur_utilisateur_id = null;
            }

        } else {
            // L'ID utilisateur dans l'URL n'est pas valide ou n'est pas un professeur
             $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Utilisateur professeur sélectionné invalide.'];
             $professeur_utilisateur_id = null; // Réinitialiser pour ne pas afficher la section de gestion
        }

    } catch (PDOException $e) {
        error_log("Erreur lors du chargement des créneaux du professeur (admin_gerer_dispos): " . $e->getMessage());
        $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur lors du chargement des créneaux.'];
        $professeur_selectionne = null; // Ne pas afficher la section de gestion en cas d'erreur grave
        $professeur_utilisateur_id = null;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les créneaux - Omnes Scolaire</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques pour cette page */
        .prof-list, .creneau-section {
            margin-top: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }
        .prof-item {
            margin-bottom: 10px;
        }
        .prof-item a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .prof-item a:hover {
            text-decoration: underline;
        }
         .creneau-list table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .creneau-list th, .creneau-list td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
            color: #fff;
        }
        .creneau-list th {
            background-color: #333;
            color: #fff;
        }
        .creneau-list tbody tr:nth-child(even) {
            background-color: #222;
        }
        .creneau-list tbody tr:hover {
            background-color: #444;
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
         .add-creneau-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
         .add-creneau-form .form-group {
            margin-bottom: 15px;
         }
         .add-creneau-form label {
            display: block;
            margin-bottom: 5px;
            color: #cccccc;
         }
          .add-creneau-form input[type="date"], .add-creneau-form input[type="time"] {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #555;
            background-color: rgba(0, 0, 0, 0.3);
            color: #fff;
          }
           .form-actions button {
             background: #1abc9c;
             color: white;
             border: none;
             padding: 10px 20px;
             border-radius: 5px;
             cursor: pointer;
             margin-right: 10px;
           }
           .form-actions button:hover {
             background: #16a085;
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
        <h2>Gérer les créneaux de rendez-vous</h2>

        <div class="prof-list">
            <h3>Sélectionnez un professeur :</h3>
            <?php if (!empty($professeurs)): ?>
                <ul>
                    <?php foreach ($professeurs as $prof): ?>
                        <li class="prof-item">
                            <a href="admin_gerer_dispos.php?professeur_id=<?php echo htmlspecialchars($prof['id']); ?>">
                                <?php echo htmlspecialchars($prof['prenom'] . ' ' . $prof['nom']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Aucun professeur trouvé.</p>
            <?php endif; ?>
        </div>

        <?php if ($professeur_selectionne): // Afficher la section de gestion si un professeur est sélectionné ?>
            <div class="creneau-section">
                <h3>Créneaux pour <?php echo htmlspecialchars($professeur_selectionne['prenom'] . ' ' . $professeur_selectionne['nom']); ?></h3>

                <div class="creneau-list">
                    <h4>Créneaux existants :</h4>
                    <?php if (!empty($creneaux_professeur)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Heure Début</th>
                                    <th>Heure Fin</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($creneaux_professeur as $creneau): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($creneau['date']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($creneau['heure_debut'], 0, 5)); ?></td>
                                        <td><?php echo htmlspecialchars(substr($creneau['heure_fin'], 0, 5)); ?></td>
                                        <td class="status-<?php echo htmlspecialchars($creneau['statut']); ?>"><?php echo htmlspecialchars($creneau['statut']); ?></td>
                                        <td>
                                            <form action="admin_process_dispos.php" method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_creneau">
                                                <input type="hidden" name="creneau_id" value="<?php echo htmlspecialchars($creneau['id']); ?>">
                                                <input type="hidden" name="professeur_id_utilisateur" value="<?php echo htmlspecialchars($professeur_utilisateur_id); ?>">
                                                <button type="submit">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Aucun créneau défini pour ce professeur.</p>
                    <?php endif; ?>
                </div>

                <div class="add-creneau-form">
                    <h4>Ajouter un nouveau créneau :</h4>
                    <form action="admin_process_dispos.php" method="post">
                        <input type="hidden" name="action" value="add_creneau">
                        <input type="hidden" name="professeur_id_utilisateur" value="<?php echo htmlspecialchars($professeur_utilisateur_id); ?>">
                        <div class="form-group">
                            <label for="creneau_date">Date :</label>
                            <input type="date" name="creneau_date" id="creneau_date" required>
                        </div>
                        <div class="form-group">
                            <label for="creneau_heure_debut">Heure de Début :</label>
                            <input type="time" name="creneau_heure_debut" id="creneau_heure_debut" required>
                        </div>
                        <div class="form-group">
                            <label for="creneau_heure_fin">Heure de Fin :</label>
                            <input type="time" name="creneau_heure_fin" id="creneau_heure_fin" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit">Ajouter Créneau</button>
                        </div>
                    </form>
                </div>

            </div>
        <?php endif; ?>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 
