<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Récupérer la liste des professeurs
$stmt = $pdo->query("
    SELECT p.id, p.nom, p.prenom, p.bureau, p.telephone, p.email as professeur_email, p.departement_id, u.id as utilisateur_id 
    FROM professeurs p 
    LEFT JOIN utilisateurs u ON p.utilisateur_id = u.id 
    ORDER BY p.nom, p.prenom
");
$professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste du Personnel - Admin Panel - Omnes Scolaire</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .personnel-list {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
            padding-bottom: 30px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }
        .personnel-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: #f4f4f4;
        }
        .personnel-table th,
        .personnel-table td {
            padding: 12px;
            padding-bottom: 18px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        .personnel-table td:last-child {
            padding-right: 30px;
        }
        .personnel-table th {
            background-color: rgba(0, 0, 0, 0.3);
            font-weight: bold;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
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
        <div class="personnel-list">
            <h2>Liste du Personnel</h2>
            
            <table class="personnel-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Département</th>
                        <th>Bureau</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professeurs as $professeur): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($professeur['id']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['nom']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['professeur_email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($professeur['departement_id']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['bureau']); ?></td>
                        <td><?php echo htmlspecialchars($professeur['telephone']); ?></td>
                        <td>
                            <form action="traitement_admin_delete_personnel.php" method="POST" style="display: inline-block;">
                                <input type="hidden" name="professeur_id" value="<?php echo $professeur['id']; ?>">
                                <input type="hidden" name="utilisateur_id" value="<?php echo $professeur['utilisateur_id'] ?? ''; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre du personnel ?')">
                                    Supprimer
                                </button>
                            </form>
                            <a href="admin_gerer_cv.php?professeur_id=<?php echo $professeur['id']; ?>" class="admin-btn">Gérer le CV</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
</body>
</html> 