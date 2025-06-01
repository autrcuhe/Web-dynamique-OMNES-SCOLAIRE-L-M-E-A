<?php
require_once 'config.php';

// Récupération des départements
$stmt = $pdo->query("SELECT * FROM departements");
$departements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des professeurs par département
function getProfesseurs($departement_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM professeurs WHERE departement_id = ?");
    $stmt->execute([$departement_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omnes Scolaire - L'Enseignement</title>
    <link rel="stylesheet" href="style.css">
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
        <h2>L'Enseignement</h2>
        <div class="departements">
            <?php foreach($departements as $departement): ?>
                <button class="departement-btn" onclick="showDept(<?php echo $departement['id']; ?>)">
                    <?php echo htmlspecialchars($departement['nom']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <div id="enseignants">
            <!-- Les enseignants s'affichent ici -->
        </div>
        <script>
        function showDept(deptId) {
            fetch('get_professeurs.php?departement_id=' + deptId)
                .then(response => response.json())
                .then(professeurs => {
                    let html = '';
                    professeurs.forEach(e => {
                        console.log('Professeur en cours de traitement:', e);
                        html += `<div class='prof-card'>
                            <div class='prof-header'>
                                ${e.photo ? `<img src='${e.photo}' alt='${e.prenom} ${e.nom}' class='prof-photo'>` : '<div class="prof-photo-placeholder">Pas de photo</div>'}
                                <div class='prof-info'>
                                    <h2>${e.prenom} ${e.nom}</h2>
                                    <p><b>Département :</b> ${e.departement_nom}</p>
                                    <p><b>Salle :</b> ${e.bureau}</p>
                                    <p><b>Téléphone :</b> ${e.telephone}</p>
                                    <p><b>Email :</b> ${e.email}</p>
                                    ${e.acces ? `<p><b>Accès :</b> ${e.acces}</b></p>` : ''}
                                    ${e.materielles_demandes ? `<p><b>Matériels demandés :</b> ${e.materielles_demandes}</b></p>` : ''}
                                </div>
                            </div>
                            <div class='prof-actions'>
                                <a href='formulaire.php?professeur_id=${e.id}&type=rdv' class='action-btn'>Prendre un RDV</a>
                                <a href='initiate_message.php?professeur_id=${e.user_id}' class='action-btn'>Communiquer avec le professeur</a>
                                ${e.cv ? `<a href='view_cv.php?id=${e.id}' class='cv-btn'>Voir son CV</a>` : ''}
                            </div>
                        </div>`;
                    });
                    document.getElementById('enseignants').innerHTML = html;
                });
        }
        // Afficher le premier département par défaut
        showDept(1);
        </script>
    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 