<?php
session_start();
require_once 'config.php';

// Log pour le débogage
// file_put_contents('debug_log.txt', "Méthode de requête : " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
// file_put_contents('debug_log.txt', "POST data : " . print_r($_POST, true) . "\n\n", FILE_APPEND);

// Vérifier si l'utilisateur est connecté (nécessaire pour envoyer un message ou prendre RDV)
if (!isset($_SESSION['utilisateur'])) {
    // Rediriger vers la page de connexion si non connecté
    header('Location: connexion.php');
    exit();
}

$utilisateur_id = $_SESSION['utilisateur']['id'];
$utilisateur_type = $_SESSION['utilisateur']['type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['type'])) {
        $type = $_POST['type'];

        // Vérifier si l'utilisateur est bien un client pour prendre un rendez-vous
        if ($type === 'rdv' && $utilisateur_type !== 'client') {
            $_SESSION['message_statut'] = [
                'type' => 'danger',
                'texte' => 'Seuls les clients peuvent prendre un rendez-vous.'
            ];
            // Rediriger vers la page d'où vient la soumission (formulaire.php)
            header('Location: formulaire.php'); // Assurez-vous que c'est la bonne page de formulaire
            exit();
        }

        // Début de la transaction
        $pdo->beginTransaction();

        try {
            if ($type === 'rdv') {
                // --- Logique de prise de rendez-vous ---

                // Vérifier si c'est un RDV international ou professeur
                if (isset($_POST['service_id'])) {
                    // --- Logique RDV International ---
                    if (isset($_POST['creneau_id'], $_POST['motif'], $_POST['service_id'])) {
                        $creneau_id = (int)$_POST['creneau_id'];
                        $motif = trim($_POST['motif']);
                        $service_id = (int)$_POST['service_id'];

                        // 1. Vérifier si le créneau est toujours disponible et appartient au service
                        $stmt_creneau = $pdo->prepare("SELECT * FROM creneaux_disponibles WHERE id = ? AND service_id = ? AND statut = 'disponible'");
                        $stmt_creneau->execute([$creneau_id, $service_id]);
                        $creneau = $stmt_creneau->fetch(PDO::FETCH_ASSOC);

                        if ($creneau) {
                            // 2. Mettre à jour le statut du créneau à 'reservé'
                            $stmt_update_creneau = $pdo->prepare("UPDATE creneaux_disponibles SET statut = 'reservé' WHERE id = ?");
                            $stmt_update_creneau->execute([$creneau_id]);

                            // 3. Enregistrer le rendez-vous dans la table rendezvous (service_id)
                            $stmt_insert_rdv = $pdo->prepare("INSERT INTO rendezvous (client_id, service_id, creneau_id, motif) VALUES (?, ?, ?, ?)");
                            $stmt_insert_rdv->execute([$utilisateur_id, $service_id, $creneau_id, $motif]);

                            $new_appointment_id = $pdo->lastInsertId();

                            // Récupérer les informations du service (pour le message)
                            $stmt_service = $pdo->prepare("SELECT nom FROM services WHERE id = ?");
                            $stmt_service->execute([$service_id]);
                            $service_info = $stmt_service->fetch(PDO::FETCH_ASSOC);
                            $service_nom = $service_info ? $service_info['nom'] : 'Service inconnu';

                            // 5. Insérer le message de confirmation du système pour l'étudiant
                            $confirmation_message_etudiant = "Votre rendez-vous avec " . $service_nom . " a été confirmé pour le " . date('d/m/Y', strtotime($creneau['date'])) . " à " . substr($creneau['heure_debut'], 0, 5) . ".\nMotif : " . $motif;
                            $stmt_insert_msg_etudiant = $pdo->prepare("INSERT INTO messages (sender_user_id, receiver_user_id, message_content, type, appointment_id) VALUES (?, ?, ?, ?, ?)");
                            // Note: sender_user_id 0 pour système ou un autre ID approprié si défini
                             // Note: receiver_user_id est le client qui prend le RDV
                            $stmt_insert_msg_etudiant->execute([0, $utilisateur_id, $confirmation_message_etudiant, 'communication', $new_appointment_id]);

                            // Si tout s'est bien passé, commiter la transaction
                            $pdo->commit();

                            // Rediriger vers la page de confirmation du RDV international
                            header('Location: confirmation.php?status=success&type=rdv_international&rdv_id=' . $new_appointment_id);
                            exit();

                        } else {
                            // Créneau non valide ou déjà pris
                            $pdo->rollBack();
                            header('Location: confirmation.php?status=error&type=rdv_international&message=Ce créneau n\'est plus disponible ou est invalide.');
                            exit();
                        }
                    } else {
                        // Données du formulaire RDV international manquantes
                        $pdo->rollBack();
                        header('Location: confirmation.php?status=error&type=rdv_international&message=Données du formulaire de rendez-vous international incomplètes.');
                        exit();
                    }

                } elseif (isset($_POST['professeur_id'])) {
                    // --- Logique de prise de rendez-vous professeur (existante) ---
                    if (isset($_POST['creneau_id'], $_POST['motif'], $_POST['professeur_id'])) {
                        $creneau_id = (int)$_POST['creneau_id'];
                        $motif = trim($_POST['motif']);
                        $professeur_id = (int)$_POST['professeur_id'];

                        // 1. Vérifier si le créneau est toujours disponible et appartient au professeur
                        $stmt_creneau = $pdo->prepare("SELECT * FROM creneaux_disponibles WHERE id = ? AND professeur_id = ? AND statut = 'disponible'");
                        $stmt_creneau->execute([$creneau_id, $professeur_id]);
                        $creneau = $stmt_creneau->fetch(PDO::FETCH_ASSOC);

                        if ($creneau) {
                            // file_put_contents('debug_log.txt', "Log: Créneau trouvé et disponible.\n", FILE_APPEND);

                            // 2. Mettre à jour le statut du créneau à 'reservé'
                            $stmt_update_creneau = $pdo->prepare("UPDATE creneaux_disponibles SET statut = 'reservé' WHERE id = ?");
                            $stmt_update_creneau->execute([$creneau_id]);
                            // file_put_contents('debug_log.txt', "Log: Statut du créneau mis à jour.\n", FILE_APPEND);

                            // 3. Enregistrer le rendez-vous dans la table rendezvous
                            // file_put_contents('debug_log.txt', "Log: Tentative d'insertion RDV avec utilisateur_id=" . $utilisateur_id . ", professeur_id=" . $professeur_id . ", creneau_id=" . $creneau_id . ", motif=" . $motif . "\n", FILE_APPEND);
                            $stmt_insert_rdv = $pdo->prepare("INSERT INTO rendezvous (client_id, professeur_id, creneau_id, motif) VALUES (?, ?, ?, ?)");
                            $stmt_insert_rdv->execute([$utilisateur_id, $professeur_id, $creneau_id, $motif]);
                            // file_put_contents('debug_log.txt', "Log: Rendez-vous inséré dans la table rendezvous.\n", FILE_APPEND);

                            // Récupérer l'ID du rendez-vous inséré
                            $new_appointment_id = $pdo->lastInsertId();
                            // file_put_contents('debug_log.txt', "Log: Last Insert ID récupéré : " . $new_appointment_id . "\n", FILE_APPEND);

                            // Récupérer les informations du professeur
                            $stmt_prof = $pdo->prepare("SELECT nom, prenom FROM utilisateurs WHERE id = ?");
                            $stmt_prof->execute([$professeur_id]);
                            $prof_info = $stmt_prof->fetch(PDO::FETCH_ASSOC);
                            $prof_nom_complet = $prof_info ? $prof_info['prenom'] . ' ' . $prof_info['nom'] : 'Professeur inconnu';

                            // Récupérer les informations de l'étudiant
                            $stmt_etudiant = $pdo->prepare("SELECT nom, prenom FROM utilisateurs WHERE id = ?");
                            $stmt_etudiant->execute([$utilisateur_id]);
                            $etudiant_info = $stmt_etudiant->fetch(PDO::FETCH_ASSOC);
                            $etudiant_nom_complet = $etudiant_info ? $etudiant_info['prenom'] . ' ' . $etudiant_info['nom'] : 'Étudiant inconnu';

                            // 5. Insérer les messages de confirmation du système
                            // Message pour l'étudiant
                            $confirmation_message_etudiant = "Votre rendez-vous avec " . $prof_nom_complet . " a été confirmé pour le " . $creneau['date'] . " à " . substr($creneau['heure_debut'], 0, 5) . ".\nMotif : " . $motif;
                            $stmt_insert_msg_etudiant = $pdo->prepare("INSERT INTO messages (sender_user_id, receiver_user_id, message_content, type, appointment_id) VALUES (?, ?, ?, ?, ?)");
                            $exec_etudiant = $stmt_insert_msg_etudiant->execute([$professeur_id, $utilisateur_id, $confirmation_message_etudiant, 'communication', $new_appointment_id]);
                            // file_put_contents('debug_log.txt', "Log traitement.php: Insertion msg etudiant - Executé: " . ($exec_etudiant ? 'Oui' : 'Non') . "\n", FILE_APPEND);
                            // if (!$exec_etudiant) {
                            //      file_put_contents('debug_log.txt', "Log traitement.php: Erreur insertion msg etudiant - Info: " . print_r($stmt_insert_msg_etudiant->errorInfo(), true) . "\n", FILE_APPEND);
                            // }

                            // Si tout s'est bien passé, commiter la transaction
                            $pdo->commit();
                            // file_put_contents('debug_log.txt', "Log: Transaction commitée.\n", FILE_APPEND);

                            // Rediriger vers la page de confirmation du RDV
                            // file_put_contents('debug_log.txt', "Log: Redirection vers confirmation.php\n", FILE_APPEND);
                            header('Location: confirmation.php?status=success&type=rdv&rdv_id=' . $new_appointment_id);
                            exit();

                        } else {
                            // Créneau non valide ou déjà pris
                            $pdo->rollBack();
                            header('Location: confirmation.php?status=error&type=rdv&message=Ce créneau n\'est plus disponible ou est invalide.');
                            exit();
                        }
                    } else {
                        // Données du formulaire RDV manquantes
                        $pdo->rollBack();
                        header('Location: confirmation.php?status=error&type=rdv&message=Données du formulaire de rendez-vous incomplètes.');
                        exit();
                    }

                } elseif ($type === 'message') {
                    // --- Logique d'envoi de message (NOUVEAU) ---
                    if (isset($_POST['professeur_id'], $_POST['sujet'], $_POST['message'])) {
                        $professeur_id = (int)$_POST['professeur_id'];
                        $sujet = trim($_POST['sujet']);
                        $message_content = trim($_POST['message']);

                        // Insérer le message dans la table messages
                        // Note : Pour une vraie messagerie, il faudrait aussi un message pour le professeur.
                        // Ici, on enregistre le message envoyé PAR l'étudiant pour qu'il le voie dans son historique.
                        $full_message = "Sujet : " . $sujet . "\n\n" . $message_content;
                        $stmt_insert_message = $pdo->prepare("INSERT INTO messages (sender_user_id, receiver_prof_id, message_content, type) VALUES (?, ?, ?, 'communication')");
                        $stmt_insert_message->execute([$utilisateur_id, $professeur_id, $full_message]);

                        // Si tout s'est bien passé, commiter la transaction
                        $pdo->commit();

                        // Rediriger vers une page de confirmation ou de retour
                        header('Location: confirmation.php?status=success&type=message');
                        exit();

                    } else {
                        // Données du formulaire message manquantes
                        $pdo->rollBack();
                        header('Location: confirmation.php?status=error&type=message&message=Données du formulaire message incomplètes.');
                        exit();
                    }
                }
            }
        } catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $pdo->rollBack();
            // Gérer l'erreur (affichage, log, etc.)
            // Pour le debug:
            echo "Erreur : " . $e->getMessage();
            // header('Location: confirmation.php?status=error&message=Une erreur est survenue lors du traitement.');
            // exit();
        }
    }
}
// Si la requête n'est pas POST ou si le type n'est pas défini, rediriger
header('Location: index.php');
exit();
?> 