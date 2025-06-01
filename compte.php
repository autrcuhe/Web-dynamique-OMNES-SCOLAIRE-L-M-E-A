<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['utilisateur'])) {
    header('Location: connexion.php');
    exit();
}
$u = $_SESSION['utilisateur'];

// Retire le code qui récupère les rendez-vous du professeur car ils ne sont plus affichés ici
$rendezvous_prof = [];
$professeur_info = null;

// Ancien code pour récupérer les rendez-vous du professeur (supprimé)
// if ($u['type'] === 'personnel') { 
//     $sql_rdv_prof = "SELECT 
//                         r.id as rdv_id,
//                         u.nom as client_nom,
//                         u.prenom as client_prenom,
//                         cd.date as creneau_date,
//                         cd.heure_debut as creneau_heure_debut,
//                         cd.heure_fin as creneau_heure_fin,
//                         r.motif
//                     FROM rendezvous r
//                     JOIN utilisateurs u ON r.client_id = u.id
//                     JOIN creneaux_disponibles cd ON r.creneau_id = cd.id
//                     WHERE r.professeur_id = ?
//                     ORDER BY cd.date DESC, cd.heure_debut ASC";
//     $stmt_rdv_prof = $pdo->prepare($sql_rdv_prof);
//     $stmt_rdv_prof->execute([$u['id']]); 
//     $rendezvous_prof = $stmt_rdv_prof->fetchAll(PDO::FETCH_ASSOC);
// }

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon compte - Omnes Scolaire</title>
    <link rel="stylesheet" href="style.css">
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
        <h2>Bienvenue, <?php echo htmlspecialchars($u['prenom'] . ' ' . $u['nom']); ?> !</h2>
        <p><b>Type de compte :</b> <?php echo htmlspecialchars($u['type']); ?></p>
        <p><b>Email :</b> <?php echo htmlspecialchars($u['email']); ?></p>

        <?php
        // Afficher les messages de statut (succès ou erreur)
        if (isset($_SESSION['message_statut'])) {
            $message_statut = $_SESSION['message_statut'];
            echo '<div class="message-' . htmlspecialchars($message_statut['type']) . '">' . htmlspecialchars($message_statut['texte']) . '</div>';
            unset($_SESSION['message_statut']); // Supprimer le message après l'affichage
        }
        ?>

        <?php if ($u['type'] === 'admin'): ?>
            <p>Vous êtes administrateur. Vous pouvez gérer les utilisateurs et les services.</p>
        <?php else: // Affichage pour les clients/étudiants et personnels non-admin ?>
            <p>Vous êtes connecté. Utilisez les onglets de navigation pour accéder aux différentes sections du site.</p>
        <?php endif; ?>

        <!-- Bouton de déconnexion -->
        <div class="logout-container" style="margin-top: 30px; text-align: center;">
            <a href="deconnexion.php" class="action-btn" style="background-color: #e74c3c;">Se déconnecter</a>
        </div>

    </main>
    <footer>
        <p>Contact : contact@omnes-scolaire.fr | Adresse : 10 rue Exemple, Paris</p>
    </footer>
</body>
</html> 