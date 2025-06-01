<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'client') {
    // Rediriger si pas connecté ou pas client
    header('Location: connexion.php');
    exit();
}

$client_id = $_SESSION['utilisateur']['id'];
$rdv_id = isset($_GET['rdv_id']) ? (int)$_GET['rdv_id'] : 0;

// Vérifier si l'ID du rendez-vous est valide et appartient bien au client connecté
$stmt_check_rdv = $pdo->prepare("SELECT id, creneau_id FROM rendezvous WHERE id = ? AND client_id = ?");
$stmt_check_rdv->execute([$rdv_id, $client_id]);
$rendezvous_a_annuler = $stmt_check_rdv->fetch(PDO::FETCH_ASSOC);

// Si le rendez-vous n'existe pas ou n'appartient pas à ce client, rediriger
if (!$rendezvous_a_annuler) {
    header('Location: rendezvous.php?erreur=rdv_introuvable');
    exit();
}

$creneau_id = $rendezvous_a_annuler['creneau_id'];

// Démarrer une transaction
$pdo->beginTransaction();

try {
    // 1. Supprimer le rendez-vous de la table rendezvous
    $stmt_delete_rdv = $pdo->prepare("DELETE FROM rendezvous WHERE id = ?");
    $stmt_delete_rdv->execute([$rdv_id]);

    // 2. Mettre à jour le statut du créneau correspondant à 'disponible'
    $stmt_update_creneau = $pdo->prepare("UPDATE creneaux_disponibles SET statut = 'disponible' WHERE id = ?");
    $stmt_update_creneau->execute([$creneau_id]);

    // Valider la transaction
    $pdo->commit();

    // Rediriger vers la page des rendez-vous avec un message de succès
    header('Location: rendezvous.php?succes=annulation');
    exit();

} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    $pdo->rollBack();
    // Rediriger vers la page des rendez-vous avec un message d'erreur
    error_log('Erreur lors de l\'annulation de RDV : ' . $e->getMessage()); // Log l'erreur
    header('Location: rendezvous.php?erreur=annulation_echec');
    exit();
}
?> 