<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté (client ou personnel)
if (!isset($_SESSION['utilisateur'])) {
    // Rediriger si pas connecté
    header('Location: connexion.php');
    exit();
}

$u = $_SESSION['utilisateur']; // Utilisateur connecté
$user_id = $u['id'];
$user_type = $u['type'];

$rendezvous = [];
$sql = "";

// Requête pour les clients : voir les rendez-vous
if ($user_type === 'client') {
    $sql = "SELECT 
                r.id as rdv_id,
                -- Sélectionne le nom du professeur ou du service
                COALESCE(p.nom, s.nom) as interlocuteur_nom,
                COALESCE(p.prenom, '') as interlocuteur_prenom, -- Prénom du professeur (vide pour service)
                p.bureau,
                p.telephone as interlocuteur_telephone,
                p.email as interlocuteur_email,
                p.acces,
                p.materielles_demandes,
                s.nom as service_nom, -- Nom du service (NULL pour professeur)
                cd.date as creneau_date,
                cd.heure_debut as creneau_heure_debut,
                cd.heure_fin as creneau_heure_fin,
                r.motif
            FROM rendezvous r
            LEFT JOIN professeurs p ON r.professeur_id = p.id
            JOIN creneaux_disponibles cd ON r.creneau_id = cd.id
            LEFT JOIN services s ON r.service_id = s.id
            WHERE r.client_id = ? AND (r.professeur_id IS NOT NULL OR r.service_id IS NOT NULL) -- S'assurer qu'il est lié soit à un prof, soit à un service
            ORDER BY cd.date DESC, cd.heure_debut ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($user_type === 'personnel') { // Requête pour les personnels : voir les rendez-vous avec leurs clients (étudiants)
    $sql = "SELECT 
                r.id as rdv_id,
                u.nom as interlocuteur_nom,
                u.prenom as interlocuteur_prenom,
                u.telephone as interlocuteur_telephone,
                u.email as interlocuteur_email,
                u.adresse_ligne1,
                u.adresse_ligne2,
                u.ville,
                u.code_postal,
                u.pays,
                u.carte_etudiante,
                cd.date as creneau_date,
                cd.heure_debut as creneau_heure_debut,
                cd.heure_fin as creneau_heure_fin,
                r.motif
            FROM rendezvous r
            JOIN utilisateurs u ON r.client_id = u.id
            JOIN creneaux_disponibles cd ON r.creneau_id = cd.id
            WHERE r.professeur_id = ?
            ORDER BY cd.date DESC, cd.heure_debut ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Rendez-vous - Omnes Scolaire</title>
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
        <h2>Mes Rendez-vous Prévus</h2>

        <?php if (isset($_GET['succes']) && $_GET['succes'] === 'annulation'): ?>
            <p style="color: green;">Votre rendez-vous a été annulé avec succès.</p>
        <?php endif; ?>

        <?php if (isset($_GET['erreur'])): ?>
            <?php if ($_GET['erreur'] === 'rdv_introuvable'): ?>
                <p style="color: red;">Erreur : Rendez-vous introuvable ou vous n'avez pas les droits pour l'annuler.</p>
            <?php elseif ($_GET['erreur'] === 'annulation_echec'): ?>
                <p style="color: red;">Erreur : Une erreur est survenue lors de l'annulation du rendez-vous. Veuillez réessayer.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (count($rendezvous) > 0): ?>
            <div class="rendezvous-list">
                <?php foreach ($rendezvous as $rdv): ?>
                    <div class="rendezvous-card">
                        <?php if ($user_type === 'client'): // Affichage pour les clients ?>
                            <?php if (!empty($rdv['service_nom'])): // C'est un RDV international ?>
                                <h3>Rendez-vous avec <?php echo htmlspecialchars($rdv['service_nom']); ?></h3>
                            <?php else: // C'est un RDV professeur ?>
                                <h3>Rendez-vous avec <?php echo htmlspecialchars($rdv['interlocuteur_prenom'] . ' ' . $rdv['interlocuteur_nom']); ?> (Professeur)</h3>
                            <?php endif; ?>
                            <?php if (!empty($rdv['bureau'])): // Informations spécifiques professeur ?>
                                <p><b>Salle/Bureau :</b> <?php echo htmlspecialchars($rdv['bureau'] ?? 'N/A'); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($rdv['acces'])): ?>
                                <p><b>Accès :</b> <?php echo htmlspecialchars($rdv['acces']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($rdv['materielles_demandes'])): ?>
                                <p><b>Matériels demandés :</b> <?php echo htmlspecialchars($rdv['materielles_demandes']); ?></p>
                            <?php endif; ?>
                        <?php elseif ($user_type === 'personnel'): // Affichage pour les personnels ?>
                             <h3>Rendez-vous avec <?php echo htmlspecialchars($rdv['interlocuteur_prenom'] . ' ' . $rdv['interlocuteur_nom']); ?> (Étudiant)</h3>
                            <p><b>Email :</b> <?php echo htmlspecialchars($rdv['interlocuteur_email'] ?? 'N/A'); ?></p>
                            <p><b>Téléphone :</b> <?php echo htmlspecialchars($rdv['interlocuteur_telephone'] ?? 'N/A'); ?></p>
                             <p><b>Carte Étudiante :</b> <?php echo htmlspecialchars($rdv['carte_etudiante'] ?? 'N/A'); ?></p>
                        <?php endif; ?>

                        <p><b>Date :</b> <?php echo htmlspecialchars($rdv['creneau_date']); ?></p>
                        <p><b>Heure :</b> <?php echo htmlspecialchars(substr($rdv['creneau_heure_debut'], 0, 5) . ' - ' . substr($rdv['creneau_heure_fin'], 0, 5)); ?></p>
                        <p><b>Motif :</b> <?php echo nl2br(htmlspecialchars($rdv['motif'])); ?></p>

                        <div class="rendezvous-actions">
                             <?php if ($user_type === 'client'): // Seuls les clients peuvent annuler leur RDV ?>
                                <a href="annuler_rdv.php?rdv_id=<?php echo $rdv['rdv_id']; ?>" class="action-btn cancel-btn" onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?');">Annulation de RDV</a>
                             <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Vous n'avez aucun rendez-vous prévu pour le moment.</p>
        <?php endif; ?>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 