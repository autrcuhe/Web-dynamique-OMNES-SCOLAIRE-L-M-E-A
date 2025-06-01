<?php
require_once 'config.php';

header('Content-Type: application/json');

$categoryId = $_GET['category_id'] ?? '';
$data = null;

try {
    switch ($categoryId) {
        case 'universites':
            // Récupérer les universités
            $stmt_universites = $pdo->query("SELECT * FROM universites_partenaires");
            $universites = $stmt_universites->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer tous les cours internationaux
            $stmt_cours = $pdo->query("SELECT * FROM cours_internationaux");
            $cours = $stmt_cours->fetchAll(PDO::FETCH_ASSOC);

            // Associer les cours aux universités
            $universites_avec_cours = [];
            foreach ($universites as $uni) {
                $uni['cours'] = [];
                foreach ($cours as $c) {
                    if ($c['universite_id'] == $uni['id']) {
                        $uni['cours'][] = $c;
                    }
                }
                $universites_avec_cours[] = $uni;
            }
            $data = $universites_avec_cours;
            break;
        case 'doubles-diplomes':
            // Joindre avec universites_partenaires pour afficher le nom de l'université si pertinent
            $stmt = $pdo->query("SELECT pd.*, up.nom as universite_nom 
                               FROM programmes_doubles_diplomes pd
                               LEFT JOIN universites_partenaires up ON pd.universite_partenaire_id = up.id");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'langues':
            $stmt = $pdo->query("SELECT * FROM programmes_langues");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'summer-school':
            // Joindre avec universites_partenaires pour afficher le nom de l'université si pertinent
            $stmt = $pdo->query("SELECT pss.*, up.nom as universite_nom 
                               FROM programmes_summer_school pss
                               LEFT JOIN universites_partenaires up ON pss.universite_partenaire_id = up.id");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        default:
            $data = [['info' => 'Sélectionnez une catégorie pour afficher les détails.']];
    }

    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
}
?> 