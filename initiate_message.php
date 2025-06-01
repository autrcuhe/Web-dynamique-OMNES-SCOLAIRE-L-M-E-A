<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
    header('Location: connexion.php');
    exit();
}

$user_id = $_SESSION['utilisateur']['id'];

// Récupérer l'ID du professeur depuis l'URL
$professeur_id = filter_input(INPUT_GET, 'professeur_id', FILTER_VALIDATE_INT);

// Vérifier si l'ID du professeur est valide et différent de l'ID de l'utilisateur
if ($professeur_id === null || $professeur_id === false || $professeur_id <= 0 || $professeur_id === $user_id) {
    // Rediriger avec un message d'erreur ou vers une page d'erreur si l'ID est invalide
    
    header('Location: messages.php');
    exit();
}

try {
    // Vérifier si une conversation (au moins un message) existe déjà entre ces deux utilisateurs
    $stmt_check_conv = $pdo->prepare("SELECT COUNT(*) FROM messages 
                                     WHERE (sender_user_id = ? AND receiver_prof_id = ?) 
                                        OR (sender_user_id = ? AND receiver_user_id = ?)");
   
     $stmt_check_conv = $pdo->prepare("SELECT COUNT(*) FROM messages 
                                      WHERE (sender_user_id = ? AND receiver_prof_id = ?) 
                                         OR (receiver_user_id = ? AND sender_user_id = ?)"); // Correction ici pour vérifier dans les deux sens si l'interlocuteur est un user (prof)
    $stmt_check_conv->execute([$user_id, $professeur_id, $user_id, $professeur_id]); // Paramètres corrigés
    $conv_exists = $stmt_check_conv->fetchColumn();

    // Si aucune conversation n'existe, insérer un premier message par défaut
    if ($conv_exists == 0) {
        $default_message = "Bonjour Professeur,"; // Message par défaut
        $timestamp = date('Y-m-d H:i:s'); // Horodatage actuel
        $type = 'communication'; // Type de message

        // Pour l'insertion, il faut que le receiver_prof_id soit bien l'ID du professeur
        $stmt_insert_message = $pdo->prepare("INSERT INTO messages (sender_user_id, receiver_prof_id, message_content, timestamp, type) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert_message->execute([$user_id, $professeur_id, $default_message, $timestamp, $type]);
        $_SESSION['message_statut'] = ['type' => 'success', 'texte' => 'Nouvelle conversation initiée.'];
    } else {
         $_SESSION['message_statut'] = ['type' => 'info', 'texte' => 'Conversation existante.'];
    }

    // Rediriger vers la page de messagerie avec l'ID du professeur sélectionné
    header('Location: messages.php?interlocuteur_id=' . $professeur_id);
    exit();

} catch (PDOException $e) {
    // Gérer les erreurs de base de données
    error_log("Erreur lors de l'initiation du message : " . $e->getMessage());
    // Rediriger avec un message d'erreur
    $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur lors de l\'envoi du premier message.'];
    header('Location: messages.php'); // Rediriger vers la page de messages sans ID spécifique
    exit();
}

?> 