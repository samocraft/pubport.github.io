<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'connection.php';

$sql = "SELECT * FROM projects";
$all_data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Oussama seddiki</title>
  <link href="./assets/css/lightbox.css" rel="stylesheet">
  <!--
    - favicon
  -->
  <link rel="shortcut icon" href="./assets/images/Screenshot 2024-07-15 185357.png" type="image/x-icon">

  <!--
    - custom css link
  -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="./assets/css/ig-post-overlay.css">
  <link rel="stylesheet" href="./assets/css/portfolio.css">
  

  <!--
    - google font link
  -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>

  <!--
    - #MAIN
  -->

  <main>

    <!--
      - #SIDEBAR
    -->

    <aside class="sidebar" data-sidebar>

      <div class="sidebar-info">

      

  <figure class="avatar-box">
    <img src="./assets/images/my-avatar.png" alt="Seddiki Oussama" width="80">
  </figure>

      

        <div class="info-content">
          <h1 class="name" title="Seddiki Oussama">Seddiki Oussama</h1>

          <p class="title">Artistic director</p>
        </div>

        <button class="info_more-btn" data-sidebar-btn>
          <span>Show Contacts</span>

          <ion-icon name="chevron-down"></ion-icon>
        </button>

      </div>

      <div class="sidebar-info_more">

        <div class="separator"></div>

        <ul class="contacts-list">

          <li class="contact-item">

            <div class="icon-box">
              <ion-icon name="mail-outline"></ion-icon>
            </div>

            <div class="contact-info">
              <p class="contact-title">Email</p>

              <a href="sdioussama2@gmail.com" class="contact-link">sdioussama2@gmail.com</a>
            </div>

          </li>

          <li class="contact-item">

            <div class="icon-box">
              <ion-icon name="phone-portrait-outline"></ion-icon>
            </div>

            <div class="contact-info">
              <p class="contact-title">Phone</p>

              <a href="tel:+12133522795" class="contact-link">+213 779972316</a>
            </div>

          </li>


          <li class="contact-item">

            <div class="icon-box">
              <ion-icon name="location-outline"></ion-icon>
            </div>

            <div class="contact-info">
              <p class="contact-title">Location</p>

              <address>Algiers algeria</address>
            </div>

          </li>

        </ul>

        <div class="separator"></div>

        <ul class="social-list">
<!--
          <li class="social-item">
            <a href="#" class="social-link">
              <ion-icon name="logo-facebook"></ion-icon>
            </a>
          </li>
          -->

          <li class="social-item">
            <a href="https://x.com/samocraft12" class="social-link">
              <ion-icon name="logo-twitter"></ion-icon>
            </a>
          </li>

          <li class="social-item">
            <a href="https://www.instagram.com/oussama.seddiki1/" class="social-link">
              <ion-icon name="logo-instagram"></ion-icon>
            </a>
          </li>

        </ul>

      </div>

    </aside>





    <!--
      - #main-content
    -->

    <div class="main-content">

      <!--
        - #NAVBAR
      -->

      <nav class="navbar">
    <ul class="navbar-list">
        <li class="navbar-item">
            <a href="?page=about" class="navbar-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'about') ? 'active' : ''; ?>">About</a>
        </li>
        <li class="navbar-item">
            <a href="?page=resume" class="navbar-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'resume') ? 'active' : ''; ?>">Resume</a>
        </li>
        <li class="navbar-item">
            <a href="?page=portfolio" class="navbar-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'portfolio') ? 'active' : ''; ?>">Portfolio</a>
        </li>
        <li class="navbar-item">
            <a href="?page=contact" class="navbar-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'contact') ? 'active' : ''; ?>">Contact</a>
        </li>
    </ul>
</nav>

<div class="content">
    <article class="about active" data-page="about">
    <?php
    // Check if 'page' is set in the query string and is valid
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        switch ($page) {
            case 'resume':
                include 'resume.php';
                break;
            case 'portfolio':
                include 'portfolio.php';
                break;
            case 'contact':
                include 'contact.php';
                break;
            default:
                include 'home.php'; // Fallback for invalid page values
        }
    } else {
        include 'home.php'; // Default page if 'page' is not set
    }
    ?>
    </article>
</div>

    </div>

    </main>

  <!--
    - ionicon link
  -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
  
<!-- Just before the closing body tag -->
<script src="./assets/js/script.js"></script>
<script src="./assets/js/ig-post-overlay.js"></script>
<script src="./assets/js/portfolio.js" defer></script>

</body>
</html>
