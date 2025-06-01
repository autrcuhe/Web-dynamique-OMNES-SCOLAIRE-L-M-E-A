<?php
require_once 'config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['type'] !== 'admin') {
    // Rediriger si pas connecté ou pas admin
    header('Location: index.php'); // Ou une page d'erreur/accès refusé
    exit();
}

// Vérifier si la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Rediriger si la méthode n'est pas POST
    header('Location: admin_panel.php'); // Rediriger vers le panneau admin ou une page d'erreur
    exit();
}

// Utiliser FILTER_UNSAFE_RAW et caster en string pour remplacer FILTER_SANITIZE_STRING déprécié
$action = (string)filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW);
// On reçoit ici l'ID utilisateur du professeur depuis le formulaire
$professeur_utilisateur_id = filter_input(INPUT_POST, 'professeur_id_utilisateur', FILTER_VALIDATE_INT);

// Vérifier l'ID de l'utilisateur (qui est l'ID dans la table utilisateurs)
if (!$professeur_utilisateur_id) {
    $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'ID utilisateur du professeur manquant ou invalide.'];
    header('Location: admin_panel.php');
    exit();
}

// Assurer que l'ID utilisateur appartient bien à un utilisateur de type 'personnel' et récupérer l'ID correspondant dans la table professeurs
try {
    $stmt_check_prof_user = $pdo->prepare("SELECT id FROM utilisateurs WHERE id = ? AND type = 'personnel'");
    $stmt_check_prof_user->execute([$professeur_utilisateur_id]);
    $professeur_user_exists = $stmt_check_prof_user->fetchColumn();

    if (!$professeur_user_exists) {
         $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Utilisateur sélectionné introuvable ou non autorisé.'];
         header('Location: admin_panel.php');
         exit();
    }

    // Maintenant, trouver l'ID correspondant dans la table 'professeurs' en utilisant l'utilisateur_id
    $stmt_get_real_prof_id = $pdo->prepare("SELECT id FROM professeurs WHERE utilisateur_id = ?");
    $stmt_get_real_prof_id->execute([$professeur_utilisateur_id]);
    $real_professeur_id = $stmt_get_real_prof_id->fetchColumn();

    if (!$real_professeur_id) {
         $_SESSION['message_statut'] = ['type' => 'danger', "texte" => "Impossible de trouver l'entrée professeur correspondante pour cet utilisateur."];
         header('Location: admin_panel.php');
         exit();
    }

} catch (PDOException $e) {
     error_log("Erreur DB lors de la vérification/récupération prof admin_process_dispos: " . $e->getMessage()); // Log l'erreur détaillée
      $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur de base de données lors de la vérification/récupération du professeur : ' . $e->getMessage()]; // Afficher l'erreur détaillée
      header('Location: admin_panel.php');
      exit();
 }

// Rediriger vers la page de gestion des créneaux du professeur utilisateur
$redirect_url = 'admin_gerer_dispos.php?professeur_id=' . $professeur_utilisateur_id;

switch ($action) {
    case 'add_creneau':
        $creneau_date = (string)filter_input(INPUT_POST, 'creneau_date', FILTER_UNSAFE_RAW);
        $creneau_heure_debut = (string)filter_input(INPUT_POST, 'creneau_heure_debut', FILTER_UNSAFE_RAW);
        $creneau_heure_fin = (string)filter_input(INPUT_POST, 'creneau_heure_fin', FILTER_UNSAFE_RAW);

        if (!$creneau_date || !$creneau_heure_debut || !$creneau_heure_fin) {
            $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Données du créneau incomplètes.'];
        } else {
            try {
                // Insérer le créneau directement dans la table creneaux_disponibles
                $stmt_insert_creneau = $pdo->prepare("INSERT INTO creneaux_disponibles (professeur_id, date, heure_debut, heure_fin, statut) VALUES (?, ?, ?, ?, ?)");

                // Utiliser le real_professeur_id récupéré pour l'insertion
                if ($stmt_insert_creneau->execute([$real_professeur_id, $creneau_date, $creneau_heure_debut, $creneau_heure_fin, 'disponible'])) {
                    $_SESSION['message_statut'] = ['type' => 'success', 'texte' => 'Créneau ajouté avec succès.'];
                } else {
                     $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur lors de l\'ajout du créneau.'];
                }

            } catch (PDOException $e) {
                 error_log("Erreur insertion créneau admin_process_dispos: " . $e->getMessage());
                 $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur de base de données lors de l\'ajout du créneau : ' . $e->getMessage()];
            } catch (Exception $e) {
                 error_log("Erreur logique ajout créneau admin_process_dispos: " . $e->getMessage());
                 $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur lors de l\'ajout du créneau : ' . $e->getMessage() ];
            }
        }
        break;

    case 'delete_creneau':
        $creneau_id = filter_input(INPUT_POST, 'creneau_id', FILTER_VALIDATE_INT);

        if (!$creneau_id) {
            $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'ID du créneau manquant ou invalide.'];
        } else {
            try {
                // Vérifier que le créneau appartient bien au professeur sélectionné avant de supprimer
                // Utiliser le real_professeur_id pour la vérification
                $stmt_check_owner = $pdo->prepare("SELECT COUNT(*) FROM creneaux_disponibles WHERE id = ? AND professeur_id = ?");
                $stmt_check_owner->execute([$creneau_id, $real_professeur_id]);

                if ($stmt_check_owner->fetchColumn() > 0) {
                    $stmt_delete = $pdo->prepare("DELETE FROM creneaux_disponibles WHERE id = ?");
                    if ($stmt_delete->execute([$creneau_id])) {
                        $_SESSION['message_statut'] = ['type' => 'success', 'texte' => 'Créneau supprimé avec succès.'];
                    } else {
                        $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur lors de la suppression du créneau.'];
                    }
                } else {
                     $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Créneau introuvable ou n\'appartient pas à ce professeur.'];
                }

            } catch (PDOException $e) {
                 error_log("Erreur supp créneau admin_process_dispos: " . $e->getMessage());
                 $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Erreur de base de données lors de la suppression.'];
            }
        }
        break;

    default:
        $_SESSION['message_statut'] = ['type' => 'danger', 'texte' => 'Action inconnue.'];
        break;
}

header('Location: ' . $redirect_url);
exit();
?> 