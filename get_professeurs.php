<?php
require_once 'config.php';

if (isset($_GET['departement_id'])) {
    $departement_id = (int)$_GET['departement_id'];
    
    $stmt = $pdo->prepare("SELECT 
                            p.*, 
                            d.nom as departement_nom,
                            u.id as user_id, u.type as user_type
                          FROM professeurs p
                          JOIN departements d ON p.departement_id = d.id
                          JOIN utilisateurs u ON p.utilisateur_id = u.id
                          WHERE p.departement_id = ?");
    $stmt->execute([$departement_id]);
    $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    if ($departement_id === 1) {
        file_put_contents('debug_dept_1.log', "Résultat pour Département ID 1:\n" . print_r($professeurs, true) . "\n", FILE_APPEND);
    }

    header('Content-Type: application/json');
    echo json_encode($professeurs);
} else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'ID du département manquant']);
}
?> 
