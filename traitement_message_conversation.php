<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
    header('Location: connexion.php');
    exit();
}

$u = $_SESSION['utilisateur'];
$user_id = $u['id'];
$user_type = $u['type'];

// Vérifier si les données du formulaire sont envoyées
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interlocuteur_id = filter_input(INPUT_POST, 'interlocuteur_id', FILTER_VALIDATE_INT);
    $interlocuteur_type = filter_input(INPUT_POST, 'interlocuteur_type', FILTER_SANITIZE_STRING);
    $message_content = trim(filter_input(INPUT_POST, 'message_content', FILTER_SANITIZE_STRING));

    // Validation simple des données
    if ($interlocuteur_id && $interlocuteur_type && $message_content !== '') {
        try {
            // Insérer le message dans la base de données
            $sql_insert = "INSERT INTO messages (sender_user_id, receiver_user_id, receiver_prof_id, type, message_content, timestamp) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt_insert = $pdo->prepare($sql_insert);

            // Déterminer les IDs de l'expéditeur et du destinataire en fonction des types d'utilisateurs
            $sender_user_id = $user_id;
            $receiver_user_id = null;
            $receiver_prof_id = null;

            if ($user_type === 'client' && $interlocuteur_type === 'personnel') {
                // Client envoie à Professeur
                $receiver_prof_id = $interlocuteur_id;
                $message_type = 'communication';
            } elseif ($user_type === 'personnel' && $interlocuteur_type === 'client') {
                // Professeur envoie à Client
                $receiver_user_id = $interlocuteur_id;
                 $message_type = 'communication';
            } else {
                // Gérer d'autres cas ou afficher une erreur si la conversation est invalide
                $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Type de conversation invalide.'];
                 header('Location: messages.php'); // Rediriger sans interlocuteur
                 exit();
            }

            // Exécuter l'insertion si les types sont valides
            if ($receiver_user_id !== null || $receiver_prof_id !== null) {
                 $stmt_insert->execute([
                     $sender_user_id,
                     $receiver_user_id,
                     $receiver_prof_id,
                     $message_type,
                     $message_content
                 ]);

                // Rediriger avec un message de succès
              
            }

        } catch (PDOException $e) {
            // Gérer les erreurs de base de données
            $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur lors de l\'envoi du message : ' . $e->getMessage()];
        }
    } else {
        // Rediriger avec un message d'erreur (données manquantes ou invalides)
        $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur : Données du message manquantes ou invalides.'];
    }
}

// Rediriger vers la page de messagerie, en gardant l'interlocuteur sélectionné
if ($interlocuteur_id) {
    header('Location: messages.php?interlocuteur_id=' . htmlspecialchars($interlocuteur_id));
} else {
    header('Location: messages.php');
}
exit();

?> 