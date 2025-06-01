<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Vérifier si les IDs sont présents
if (!isset($_POST['professeur_id']) || !isset($_POST['utilisateur_id'])) {
    $_SESSION['message_statut'] = [
        'type' => 'error',
        'texte' => 'Données manquantes pour la suppression.'
    ];
    header('Location: admin_liste_personnel.php');
    exit();
}

$professeur_id = $_POST['professeur_id'];
$utilisateur_id = $_POST['utilisateur_id'];

try {
    // Démarrer une transaction
    $pdo->beginTransaction();

    // Supprimer d'abord le professeur
    $stmt = $pdo->prepare("DELETE FROM professeurs WHERE id = ?");
    $stmt->execute([$professeur_id]);

    // Ensuite supprimer l'utilisateur associé
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$utilisateur_id]);

    // Valider la transaction
    $pdo->commit();

    $_SESSION['message_statut'] = [
        'type' => 'success',
        'texte' => 'Le membre du personnel a été supprimé avec succès.'
    ];
} catch (PDOException $e) {
    // En cas d'erreur, annuler la transaction
    $pdo->rollBack();
    
    $_SESSION['message_statut'] = [
        'type' => 'error',
        'texte' => 'Une erreur est survenue lors de la suppression : ' . $e->getMessage()
    ];
}

// Rediriger vers la liste du personnel
header('Location: admin_liste_personnel.php');
exit(); 