<!--  // ? funkcionális elvárás: Az alkalmazásnak legyen egységes fejléce, lábléce és menüje, 
      // ? amelyek minden oldalon megjelennek. A menüből legyenek elérhetők a főbb funkciók.-->

<?php if (session_status() == PHP_SESSION_NONE) {
  session_start();
} ?>

<!doctype HTML>
<html lang="hu" <?php if (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == '1') {
  echo 'data-bs-theme="dark"';
} ?>>

<head>
  <title>Project Manager</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Project Manager weboldal, projektek előrehaladásának a követésére alkalmas">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
    rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ"
    crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
    crossorigin="anonymous"></script>
  <script src="https://kit.fontawesome.com/59746e632a.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="style.css">
  <?php
  // ? Pontozási szempontok: CSS váltás (skin cserélése) az alkalmazásból: 10p
  if (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == '1') {
    $color_code = '#ffffff'; // fehér
    echo '<link rel="stylesheet" href="style_dark.css">';
  } else {
    $color_code = '#333333'; // Dark Charcoal - "Sötét szén"
    echo '<link rel="stylesheet" href="style_light.css">';
  }
  ?>

  <link rel="icon" type="image/x-icon" href="../assets/icon.ico">
</head>

<body class="bg-image">

  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand">Project Manager</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav  nav-underline me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?php if ($site == 'index') {
              // echo 'active' : bootstrap aktív linkként kezeli, kiemeli a tobbi kozul
              echo 'active';
            } ?>" href="index.php">Főoldal
            </a>
          </li>

          <?php
          if (isset($_SESSION['access_level']) && $_SESSION['access_level'] != 'guest') {
            echo '<li class="nav-item"><a class="nav-link ';
            if ($site == 'project') {
              echo 'active';
            }
            echo '" href="projects.php">Projektek</a></li>';
          }

          if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 'admin') {
            echo '<li class="nav-item"><a class="nav-link ';
            if ($site == 'user') {
              echo 'active';
            }
            echo '" href="users.php">Felhasználók</a></li>';
          }
          ?>
        </ul>
      </div>
      <?php
      if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
        // fa-user: felhasználó ikon
        // fa-right-to-bracket: kijelentkezés ikon
        echo '<ul class="nav-item dropdown">
             <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
             Bejelentkezve mint: ' . $_SESSION["username"] . '
             </button>
             <ul class="dropdown-menu dropdown-menu-end">
             <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-user" style="color: ' . $color_code . ';"></i>  Profil</a></li>
             <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-to-bracket" style="color: ' . $color_code . ';"></i> Kijelentkezés</a></li>
             </ul>
             </ul>';
      } else if (!isset($on_login_page)) {
        // Ha a login oldalon vagyunk, akkor nem jelenik meg a jobb felso sarokabn a "Bejelentkezés" felirat
        echo '<li class="d-flex" role="search">
            <a class="nav-link" href="login.php">Bejelentkezés</a>
            </li>';
      }
      ?>
    </div>
  </nav>