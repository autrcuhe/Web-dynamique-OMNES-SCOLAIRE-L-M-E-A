<?php
require_once 'config.php'; // Assuming config.php is in the same directory or accessible

try {
    // SQL statements
    $sql = "
    -- Insérer les catégories de services
    INSERT INTO categories_services_internationaux (nom, description) VALUES
    ('Les universités partenaires', 'Explorez nos partenariats avec des universités à l''étranger.'),
    ('Doubles diplômes à l''international', 'Programmes permettant d''obtenir deux diplômes.'),
    ('Apprentissage des langues', 'Cours et ressources pour l''amélioration linguistique.'),
    ('Summer School', 'Programmes courts et intensifs durant l''été à l''étranger.');

    -- Insérer des universités partenaires
    INSERT INTO universites_partenaires (nom, pays, ville, contact_email, site_web) VALUES
    ('Université Technique de Munich', 'Allemagne', 'Munich', 'info@tum.de', 'https://www.tum.de/'),
    ('Université de Tokyo', 'Japon', 'Tokyo', 'admission@u-tokyo.ac.jp', 'https://www.u-tokyo.ac.jp/'),
    ('Université de Toronto', 'Canada', 'Toronto', 'future.students@utoronto.ca', 'https://www.utoronto.ca/'),
    ('École Polytechnique Fédérale de Lausanne', 'Suisse', 'Lausanne', 'info@epfl.ch', 'https://www.epfl.ch/'),
    ('Université Tsinghua', 'Chine', 'Pékin', 'admission@tsinghua.edu.cn', 'https://www.tsinghua.edu.cn/');

    -- Insérer des cours internationaux (liés à l'Université Technique de Munich - ID 1)
    -- Note : Les IDs des universités peuvent varier si d'autres données existent ou si l'ordre d'insertion change.
    -- Il serait préférable d'utiliser les noms pour lier si possible ou de vérifier les IDs après l'insertion des universités.
    -- Pour l'exemple, j'assume que TUM a l'ID 1.
    INSERT INTO cours_internationaux (universite_id, nom_cours, description, credits, semestre) VALUES
    (1, 'Advanced Robotics', 'Cours sur la robotique avancée et l'IA.', '6 ECTS', 'Automne 2024'),
    (1, 'Sustainable Energy Systems', 'Étude des systèmes énergétiques durables.', '5 ECTS', 'Printemps 2025');

    -- Insérer des programmes de doubles diplômes (liés à l'EPFL - ID 4)
    -- J'assume que l'EPFL a l'ID 4.
    INSERT INTO programmes_doubles_diplomes (universite_partenaire_id, nom_programme, duree, diplome_obtenu) VALUES
    (4, 'Master Ingénierie Structurale Internationale', '2 ans', 'Master of Science in Civil Engineering (double degree)');

    -- Insérer des salles
    INSERT INTO salles_international (nom, localisation) VALUES
    ('Salle P-425', 'Bâtiment P, 4ème étage'),
    ('Salle B-101', 'Bâtiment B, 1er étage'),
    ('Salle C-302', 'Bâtiment C, 3ème étage');
    ";

    $pdo->exec($sql);

    echo "Données internationales insérées avec succès.\n";

} catch (PDOException $e) {
    echo "Erreur lors de l'insertion des données internationales : " . $e->getMessage() . "\n";
}

?> 