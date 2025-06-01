<?php
// Page d'accueil Omnes Scolaire
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omnes Scolaire - Accueil</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>  
</head>
<body id="body">
    <video autoplay muted loop id="background-video">
        <source src="image/video.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la vidéo en arrière-plan.
    </video>
    <header>
        <h1>Omnes Scolaire</h1>
        <nav>
        <a href="index.php">Accueil</a>
    <a href="parcourir.php">Tout Parcourir</a>
    <a href="recherche.php">Recherche</a>
    <a href="rendezvous.php">Rendez-vous</a>
    <?php if (isset($_SESSION['utilisateur'])): ?>
        <a href="messages.php">Messages</a>
        <a href="compte.php">Votre compte</a>
         <?php if ($_SESSION['utilisateur']['type'] === 'admin'): ?>
            <a href="admin_panel.php">Admin Panel</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="connexion.php">Connexion</a>
    <?php endif; ?>
        </nav>
    </header>
    <main>
        <section id="bienvenue">
            <h2>Bienvenue sur Omnes Scolaire</h2>
            <p>Votre plateforme pour prendre un rendez-vous scolaire pour la communauté Omnes Education.</p>
        </section>

    </main>

    <div class="evenements-carrousel-container">
        <section id="evenement">
            <h3>Événement de la semaine</h3>
            <p>Journée Portes Ouvertes Virtuelles le 22 juin ! Explorez nos campus et échangez avec nos étudiants et professeurs en ligne.</p>
        </section>

        <section id="carrousel">
            <ul>
                <li><img src="image/Image1.jpeg" alt="Image 1"/></li>  
                <li><img src="image/Image2.jpeg" alt="Image 2"/></li>  
                <li><img src="image/Image3.jpg" alt="Image 3"/></li>
            </ul>  
            <button class="precedent">precedent</button>
            <button class="suivant">suivant</button>
        </section>
    </div>

    <script>
$(document).ready(function(){
   var $carrousel = $('#carrousel') ; // on cible le bloc du carrousel  
   var $img = $('#carrousel img') ; // on cible les images contenues dans le carrousel
   
   var indexImg = $img.length - 1 ; // on définit l'index du dernier élément  
   var i = 0 ;// on initialise un compteur  
   var $currentImg = $img.eq(i); // enfin, on cible l'image courante, qui possède l'index i (0 pour l'instant)  

   $img.css('display', 'none'); // on cache les images  
   $currentImg.css('display', 'block'); // on affiche seulement l'image courante  

   $('.suivant').click(function(){
     i++;
     if (i <= indexImg){
         $img.fadeOut(400, function() { // Animation fadeOut
            $img.css('display', 'none');
            $currentImg = $img.eq(i);
            $currentImg.css('display', 'block').fadeIn(400); // Animation fadeIn
         });
      }
      else{
         i = indexImg;
      }
   });

   $('.precedent').click(function(){
      i--;
      if (i >= 0){
         $img.fadeOut(400, function() { // Animation fadeOut
            $img.css('display', 'none');
            $currentImg = $img.eq(i);
            $currentImg.css('display', 'block').fadeIn(400); // Animation fadeIn
         });
      }
      else{
         i = 0;
      }
   });

   function slideImg(){
      setTimeout(function(){
         if (i < indexImg){
            i++;
         }
         else {
            i = 0;
         }
         $img.fadeOut(400, function() { // Animation fadeOut
            $img.css('display', 'none');
            $currentImg = $img.eq(i);
            $currentImg.css('display', 'block').fadeIn(400); // Animation fadeIn
         });
         slideImg();
      }, 4000);
   }

slideImg();
});

        </script>
    <footer>
        <p>Contact : omnes.education@gmail.com | Tél : 0624354678 | Adresse : 10 rue Sextius Michel, 75015 Paris</p>
        <!-- Google Map à ajouter ici plus tard -->
    </footer>
</body>
</html> 