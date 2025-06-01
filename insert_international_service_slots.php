<?php
require_once 'config.php'; 

try {
    $pdo->beginTransaction();

    // 1. Insérer le service "Relation Internationale"
    $service_nom = 'Relation Internationale';
    $stmt = $pdo->prepare("SELECT id FROM services WHERE nom = ?");
    $stmt->execute([$service_nom]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    $service_id = null;
    if ($service) {
        $service_id = $service['id'];
        echo "Service \"{$service_nom}\" existe déjà avec l'ID : " . $service_id . "<br>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (nom) VALUES (?) ON DUPLICATE KEY UPDATE nom=nom");
        $stmt->execute([$service_nom]);
        $service_id = $pdo->lastInsertId();
         echo "Service \"{$service_nom}\" inséré avec l'ID : " . $service_id . "<br>";
    }

    

    $creneaux_a_inserer = [
        // Exemple de créneaux pour Lundi PM
        ['date' => '2025-10-27', 'heure_debut' => '14:00:00', 'heure_fin' => '14:20:00', 'statut' => 'disponible', 'professeur_id' => NULL, 'service_id' => $service_id],
        ['date' => '2025-10-27', 'heure_debut' => '14:20:00', 'heure_fin' => '14:40:00', 'statut' => 'disponible', 'professeur_id' => NULL, 'service_id' => $service_id],
        ['date' => '2025-10-27', 'heure_debut' => '14:40:00', 'heure_fin' => '15:00:00', 'statut' => 'reservé', 'professeur_id' => NULL, 'service_id' => $service_id],
        // Exemple de créneaux pour Mercredi AM/PM
        ['date' => '2025-10-29', 'heure_debut' => '09:00:00', 'heure_fin' => '09:20:00', 'statut' => 'disponible', 'professeur_id' => NULL, 'service_id' => $service_id],
        ['date' => '2025-10-29', 'heure_debut' => '09:20:00', 'heure_fin' => '09:40:00', 'statut' => 'disponible', 'professeur_id' => NULL, 'service_id' => $service_id],
        ['date' => '2025-10-29', 'heure_debut' => '14:00:00', 'heure_fin' => '14:20:00', 'statut' => 'disponible', 'professeur_id' => NULL, 'service_id' => $service_id],
        // Exemple de créneaux pour Vendredi AM/PM
        ['date' => '2025-10-31', 'heure_debut' => '09:00:00', 'heure_fin' => '09:20:00', 'statut' => 'disponible', 'professeur_id' => NULL, 'service_id' => $service_id],
         ['date' => '2025-10-31', 'heure_debut' => '14:00:00', 'heure_fin' => '14:20:00', 'statut' => 'reservé', 'professeur_id' => NULL, 'service_id' => $service_id],
    ];

    $stmt_insert_creneau = $pdo->prepare("INSERT INTO creneaux_disponibles (date, heure_debut, heure_fin, statut, professeur_id, service_id) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($creneaux_a_inserer as $creneau) {
        // Vérifier si un créneau identique existe déjà pour éviter les doublons (optionnel)
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM creneaux_disponibles WHERE date = ? AND heure_debut = ? AND service_id = ?");
        $stmt_check->execute([$creneau['date'], $creneau['heure_debut'], $service_id]);
        if ($stmt_check->fetchColumn() == 0) {
             $stmt_insert_creneau->execute([
                $creneau['date'],
                $creneau['heure_debut'],
                $creneau['heure_fin'],
                $creneau['statut'],
                $creneau['professeur_id'],
                $creneau['service_id']
            ]);
             echo "Créneau inséré pour le " . $creneau['date'] . " à " . substr($creneau['heure_debut'], 0, 5) . ".<br>";
        } else {
            echo "Créneau existant pour le " . $creneau['date'] . " à " . substr($creneau['heure_debut'], 0, 5) . ". Ignoré.<br>";
        }
    }

    $pdo->commit();

    echo "Processus d'insertion des créneaux de la Relation Internationale terminé.\n";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Erreur lors de l'insertion des données : " . $e->getMessage() . "\n";
}

?> 