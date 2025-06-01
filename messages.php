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

// Récupérer l'ID de l'interlocuteur sélectionné depuis l'URL
$interlocuteur_id = filter_input(INPUT_GET, 'interlocuteur_id', FILTER_VALIDATE_INT);
$current_interlocutor = null; // Pour stocker les informations de l'interlocuteur sélectionné

$conversation_messages = [];
$conversations = []; // Pour stocker la liste des conversations

// --- Logique pour gérer l'interlocuteur courant et récupérer les conversations et messages ---

// 1. Si un interlocuteur_id est présent dans l'URL (et n'est pas l'ID système 0),
//    récupérer les informations de cet utilisateur pour le définir comme l'interlocuteur courant.
//    Cela garantit l'affichage de la zone de message même s'il n'y a pas encore de messages.
if ($interlocuteur_id !== null && $interlocuteur_id != 0) {
    $stmt_current = $pdo->prepare("SELECT id, nom, prenom, type FROM utilisateurs WHERE id = ?");
    $stmt_current->execute([$interlocuteur_id]);
    $current_interlocutor = $stmt_current->fetch(PDO::FETCH_ASSOC);
}

// 2. Récupérer tous les messages impliquant l'utilisateur connecté,
//    pour identifier les interlocuteurs uniques et construire la liste de conversations.
//    On cherche les messages où l'utilisateur est l'expéditeur ou le destinataire.
//    On doit considérer les messages entre client-prof et prof-client.

$sql_conversations = "SELECT DISTINCT
                          CASE
                              -- Si l'utilisateur connecté est l'expéditeur, l'interlocuteur est le destinataire (user ou prof)
                              WHEN m.sender_user_id = ? AND EXISTS (SELECT 1 FROM utilisateurs WHERE id = m.receiver_prof_id AND type = 'personnel') THEN m.receiver_prof_id -- Client envoie à Prof (type personnel)
                              -- Si l'utilisateur connecté est le destinataire (user), l'interlocuteur est l'expéditeur
                              WHEN m.receiver_user_id = ? AND m.sender_user_id != 0 AND EXISTS (SELECT 1 FROM utilisateurs WHERE id = m.sender_user_id AND type = 'personnel') THEN m.sender_user_id -- Client reçoit de Prof (type personnel)
                              -- Si l'utilisateur connecté est le destinataire (prof), l'interlocuteur est l'expéditeur
                              WHEN m.receiver_prof_id = ? AND m.sender_user_id != 0 AND EXISTS (SELECT 1 FROM utilisateurs WHERE id = m.sender_user_id AND type = 'client') THEN m.sender_user_id -- Prof reçoit de Client (type client)
                              -- Inclure les messages système reçus par l'utilisateur comme une conversation séparée (ID 0)
                              WHEN m.sender_user_id = 0 AND m.receiver_user_id = ? THEN 0
                          END as interlocuteur_id
                       FROM messages m
                       WHERE m.type = 'communication' AND (
                          (m.sender_user_id = ? AND EXISTS (SELECT 1 FROM utilisateurs WHERE id = m.receiver_prof_id AND type = 'personnel')) OR -- Client envoie à Prof
                          (m.receiver_user_id = ? AND m.sender_user_id != 0 AND EXISTS (SELECT 1 FROM utilisateurs WHERE id = m.sender_user_id AND type = 'personnel')) OR -- Client reçoit de Prof
                          (m.receiver_prof_id = ? AND m.sender_user_id != 0 AND EXISTS (SELECT 1 FROM utilisateurs WHERE id = m.sender_user_id AND type = 'client')) OR -- Prof reçoit de Client
                          (m.sender_user_id = 0 AND m.receiver_user_id = ?) -- Messages système reçus par Client
                       )";

$stmt_conversations = $pdo->prepare($sql_conversations);
$stmt_conversations->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$interlocuteur_ids_from_messages = $stmt_conversations->fetchAll(PDO::FETCH_COLUMN);

// Combiner les IDs trouvés dans les messages avec l'interlocuteur de l'URL si présent
$all_interlocutor_ids = $interlocuteur_ids_from_messages;
if ($interlocuteur_id !== null && $interlocuteur_id != 0 && !in_array($interlocuteur_id, $all_interlocutor_ids)) {
    // Vérifier que l'interlocuteur de l'URL est bien de type personnel s'il est ajouté ici
    $stmt_check_type = $pdo->prepare("SELECT type FROM utilisateurs WHERE id = ?");
    $stmt_check_type->execute([$interlocuteur_id]);
    $interlocuteur_type_from_url = $stmt_check_type->fetchColumn();
    if ($interlocuteur_type_from_url === 'personnel') {
         $all_interlocutor_ids[] = $interlocuteur_id;
    }
}

// Pour chaque ID d'interlocuteur (maintenant incluant potentiellement celui de l'URL),
// récupérer ses informations (nom, prénom, type) pour construire la liste des conversations.
foreach ($all_interlocutor_ids as $id) {
    if ($id === 0) { // Cas spécial pour les messages système
        $conversations[] = ['id' => 0, 'nom' => 'Notifications', 'prenom' => '', 'type' => 'système'];
    } elseif ($id !== null) { // Cas pour les utilisateurs normaux
         $stmt_interlocuteur = $pdo->prepare("SELECT id, nom, prenom, type FROM utilisateurs WHERE id = ?");
         $stmt_interlocuteur->execute([$id]);
         $interlocuteur_info = $stmt_interlocuteur->fetch(PDO::FETCH_ASSOC);

         if ($interlocuteur_info) {
             $conversations[] = $interlocuteur_info;
         }
    }
}

// 3. Si un interlocuteur courant a été défini (via l'URL),
//    récupérer les messages de cette conversation (qui sera vide s'il n'y en a pas).
if ($current_interlocutor) { // Vérifier si un interlocuteur courant valide est défini
     // Récupérer les messages de communication avec l'interlocuteur
     // Construire la requête SQL et les paramètres conditionnellement
     $sql_conversation_messages_base = "SELECT 
                                        m.*, 
                                        sender.nom as sender_nom, 
                                        sender.prenom as sender_prenom
                                     FROM messages m
                                     LEFT JOIN utilisateurs sender ON m.sender_user_id = sender.id
                                     WHERE m.type = 'communication' AND ";

     $params = [];

     // Utiliser l'ID de l'interlocuteur courant pour la requête des messages
     $target_interlocutor_id_for_messages = $current_interlocutor['id'];

     if ($target_interlocutor_id_for_messages != 0) {
         // Requête pour les messages entre l'utilisateur connecté et l'interlocuteur courant (prof/client)
         $sql_conversation_messages_condition = "(
             (m.sender_user_id = ? AND m.receiver_user_id = ?) 
             OR (m.sender_user_id = ? AND m.receiver_prof_id = ?)
             OR (m.receiver_user_id = ? AND m.sender_user_id = ?) /* Messages reçus par l'utilisateur courant */
             OR (m.receiver_prof_id = ? AND m.sender_user_id = ?) /* Messages reçus par l'utilisateur courant (si prof) */
         )";
         $params = [
             $user_id, $target_interlocutor_id_for_messages,
             $user_id, $target_interlocutor_id_for_messages,
             $user_id, $target_interlocutor_id_for_messages,
             $user_id, $target_interlocutor_id_for_messages
         ];
     } else {
         // Requête pour les messages système reçus par l'utilisateur connecté (seulement si l'interlocuteur courant est le système)
         $sql_conversation_messages_condition = "(m.sender_user_id = 0 AND m.receiver_user_id = ?)";
         $params = [$user_id]; // Les messages système sont toujours reçus par l'utilisateur connecté (client)
     }

     $sql_conversation_messages = $sql_conversation_messages_base . $sql_conversation_messages_condition . " ORDER BY m.timestamp ASC";

     $stmt_conversation_messages = $pdo->prepare($sql_conversation_messages);
     $stmt_conversation_messages->execute($params);
   
    $conversation_messages = $stmt_conversation_messages->fetchAll(PDO::FETCH_ASSOC);

}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messagerie - Omnes Scolaire</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styles spécifiques à la page de messagerie */
        .message-container {
            display: flex;
            height: calc(100vh - 150px); /* Ajuster la hauteur en fonction du header/footer */
            max-width: 1200px; /* Largeur maximale du conteneur */
            margin: 20px auto; /* Centrer le conteneur */
            background: rgba(255, 255, 255, 0.08); /* Fond légèrement transparent */
            border-radius: 10px;
            overflow: hidden; /* Masquer le contenu qui dépasse */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .conversation-list {
            width: 300px; /* Largeur de la liste des conversations */
            border-right: 1px solid rgba(255, 255, 255, 0.1); /* Séparateur */
            overflow-y: auto; /* Ajouter une barre de défilement si nécessaire */
            padding: 10px;
            background: rgba(0, 0, 0, 0.1); /* Fond légèrement plus sombre pour la liste */
        }

        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05); /* Séparateur d'items */
            cursor: pointer;
            transition: background 0.2s;
            color: #f4f4f4; /* Texte clair */
        }

        .conversation-item:hover {
            background: rgba(255, 255, 255, 0.1); /* Changement de couleur au survol */
        }

        .conversation-item.active {
            background: rgba(255, 255, 255, 0.15); /* Couleur pour la conversation sélectionnée */
        }

        .conversation-item h4 {
            margin: 0 0 5px 0;
            color: #3498db; /* Couleur d'accentuation pour le nom */
        }

        .conversation-item p {
            margin: 0;
            font-size: 0.9em;
            color: #cccccc; /* Couleur du texte d'aperçu */
        }

        .message-area {
            flex-grow: 1; /* Prend l'espace restant */
            display: flex;
            flex-direction: column;
            padding: 20px;
            position: relative; /* Pour positionner le formulaire d'envoi */
        }

        .message-list {
            flex-grow: 1; /* Prend l'espace restant pour les messages */
            overflow-y: auto; /* Ajouter une barre de défilement pour les messages */
            padding-right: 10px; /* Espace pour la barre de défilement */
            margin-bottom: 80px; /* Espace pour le formulaire d'envoi */
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            max-width: 80%; /* Largeur maximale du message */
        }

        .message.sent {
            align-self: flex-end; /* Messages envoyés à droite */
            background-color: #3498db; /* Couleur des messages envoyés */
            color: #fff;
        }

        .message.received {
            align-self: flex-start; /* Messages reçus à gauche */
            background-color: #555; /* Couleur des messages reçus */
            color: #fff;
        }

        .message p {
            margin: 0 0 5px 0;
        }

        .message .timestamp {
            font-size: 0.8em;
            color: rgba(255, 255, 255, 0.6); /* Couleur claire pour l'horodatage */
            text-align: right; /* Aligner l'horodatage à droite */
        }

        .message-form-area {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            background: rgba(0, 0, 0, 0.3); /* Fond du formulaire d'envoi */
            border-top: 1px solid rgba(255, 255, 255, 0.1); /* Séparateur */
        }

        .message-form-area form {
            display: flex;
            gap: 10px;
        }

        .message-form-area textarea {
            flex-grow: 1; /* Prend l'espace maximal */
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #555;
            background-color: rgba(0, 0, 0, 0.4);
            color: #fff;
            resize: none; /* Désactiver le redimensionnement */
            font-size: 1em;
        }

        .message-form-area button {
            padding: 10px 20px;
            background: #3498db; /* Couleur du bouton d'envoi */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .message-form-area button:hover {
            background: #2980b9; /* Couleur au survol */
        }
         /* Style pour les messages de statut (succès/erreur) */
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .message-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

    </style>
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
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
         <?php
        // Afficher les messages de statut (succès ou erreur)
        if (isset($_SESSION['message_statut'])) {
            $message_statut = $_SESSION['message_statut'];
            echo '<div class="message-' . htmlspecialchars($message_statut['type']) . '">' . htmlspecialchars($message_statut['texte']) . '</div>';
            unset($_SESSION['message_statut']); // Supprimer le message après l'affichage
        }
        ?>
        <div class="message-container">
            <div class="conversation-list">
                <h3>Conversations</h3>
                <ul>
                    <?php foreach ($conversations as $conv): ?>
                        <li class="conversation-item <?php echo ($interlocuteur_id == $conv['id']) ? 'active' : ''; ?>">
                            <a href="messages.php?interlocuteur_id=<?php echo htmlspecialchars($conv['id']); ?>" style="text-decoration: none; color: inherit; display: block;">
                                <h4><?php echo htmlspecialchars($conv['prenom'] . ' ' . $conv['nom']); ?></h4>
                                <p><?php echo htmlspecialchars(ucfirst($conv['type'])); ?></p>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="message-area">
                <?php if ($interlocuteur_id !== null && $current_interlocutor): ?>
                    <h3>Conversation avec <?php echo htmlspecialchars($current_interlocutor['prenom'] . ' ' . $current_interlocutor['nom']); ?></h3>
                    <div class="message-list">
                        <?php if (!empty($conversation_messages)): ?>
                            <?php foreach ($conversation_messages as $message): ?>
                                <div class="message <?php echo ($message['sender_user_id'] === $user_id || $message['sender_user_id'] === 0) ? 'sent' : 'received'; ?>">
                                    <p><b><?php echo ($message['sender_user_id'] === 0) ? 'Système' : htmlspecialchars($message['sender_prenom'] . ' ' . $message['sender_nom']); ?> :</b> <?php echo nl2br(htmlspecialchars($message['message_content'])); ?></p>
                                    <span class="timestamp"><?php echo htmlspecialchars($message['timestamp']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Aucun message dans cette conversation.</p>
                        <?php endif; ?>
                    </div>

                    
                        <div class="message-form-area">
                            <?php if ($interlocuteur_id != 0): // Ne pas afficher le formulaire d'envoi si c'est la conversation système ?>
                                <form action="traitement_message_conversation.php" method="post">
                                    <input type="hidden" name="interlocuteur_id" value="<?php echo htmlspecialchars($interlocuteur_id); ?>">
                                    <input type="hidden" name="interlocuteur_type" value="<?php echo htmlspecialchars($current_interlocutor['type']); ?>">
                                    <textarea name="message_content" rows="2" placeholder="Écrivez votre message..." required></textarea>
                                    <button type="submit">Envoyer</button>
                                </form>
                            <?php else: ?>
                                <p>Vous ne pouvez pas répondre directement aux messages système.</p>
                            <?php endif; ?>
                        </div>
                    
                <?php else: ?>
                    <p>Sélectionnez une conversation dans la liste.</p>
                <?php endif; ?>
            </div>
        </div>

    </main>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
    </footer>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour afficher les messages d'une conversation spécifique
        function displayConversation(interlocutorId) {
            // Retirer la classe 'active' de tous les éléments de conversation
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });

            // Ajouter la classe 'active' à l'élément de conversation sélectionné
            const selectedConversation = document.querySelector(`.conversation-item[data-interlocutor-id='${interlocutorId}']`);
            if (selectedConversation) {
                selectedConversation.classList.add('active');

                // Mettre à jour l'URL avec l'ID de l'interlocuteur
                history.pushState(null, '', `messages.php?interlocuteur_id=${interlocutorId}`);

                // Appeler une fonction (qui devra être implémentée en PHP ou via AJAX) 
                // pour charger et afficher les messages de cette conversation.
                // Pour l'instant, nous allons simplement afficher un message temporaire
                // ou recharger la page pour que PHP gère l'affichage.
                // La méthode la plus simple pour l'instant est de recharger la page
                // avec le nouveau paramètre, mais une solution AJAX serait plus fluide.
                // Recharge la page pour que PHP charge les bons messages
                window.location.href = `messages.php?interlocuteur_id=${interlocutorId}`;
            }
        }

        // Gérer les clics sur les éléments de conversation
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.addEventListener('click', function() {
                const interlocutorId = this.getAttribute('data-interlocutor-id');
                if (interlocutorId) {
                    displayConversation(interlocutorId);
                }
            });
        });

        // Au chargement de la page, vérifier s'il y a un interlocuteur_id dans l'URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialInterlocutorId = urlParams.get('interlocuteur_id');
        if (initialInterlocutorId) {
            // Trouver l'élément de conversation correspondant et simuler un clic
            // pour activer la conversation et charger les messages.
            // Nous devons attendre que les éléments de conversation soient ajoutés au DOM.
            // Une approche plus robuste serait d'utiliser un Event Delegation
            // sur le conteneur des conversations si les items sont ajoutés dynamiquement après le chargement du DOM initial.
            // Étant donné que les conversations sont générées en PHP au chargement de la page,
            // nous pouvons directement chercher l'élément.
            const initialConversationItem = document.querySelector(`.conversation-item[data-interlocutor-id='${initialInterlocutorId}']`);
            if (initialConversationItem) {
                // Simuler un clic pour déclencher l'affichage de la conversation
                initialConversationItem.click();
            } else if (initialInterlocutorId != 0) { // Si l'interlocuteur ID existe mais n'est pas dans la liste (et n'est pas le système)
                 // Cela peut arriver si l'utilisateur n'a pas encore eu de conversation avec ce professeur.
                 // Dans ce cas, nous devrons peut-être initier une nouvelle conversation.
                 // Pour l'instant, on peut soit afficher un message, soit rediriger.
                 // On va simplement recharger la page sans paramètre pour afficher la liste par défaut.
                 // Mieux vaut ne rien faire et laisser la liste vide si aucune conversation passée.
                 // Laissez PHP gérer l'affichage initial si l'interlocuteur_id est présent.
                 // Le code PHP au début du fichier gère déjà la récupération des messages si $interlocuteur_id est défini.
                 // Nous n'avons donc pas besoin de recharger ou simuler un clic ici.
                 // L'important est que l'élément de conversation correspondant soit marqué comme actif.

                 // Ajouter une classe 'active' si l'interlocuteur_id correspond
                 const convItemToActivate = document.querySelector(`.conversation-item[data-interlocutor-id='${initialInterlocutorId}']`);
                 if(convItemToActivate) {
                     convItemToActivate.classList.add('active');
                 }

                 // Nous devons également nous assurer que la zone de message affiche le nom de l'interlocuteur sélectionné
                 // et que le formulaire d'envoi est prêt à envoyer à cet interlocuteur.
                 // Ces parties sont gérées par PHP lors du chargement de la page si $current_interlocutor est défini.
                 // Aucun code JS supplémentaire n'est strictement nécessaire ici pour le chargement initial si PHP le gère bien.
            }
        }
    });
    </script>
</body>
</html> 