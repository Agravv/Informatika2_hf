<?php if (session_status() == PHP_SESSION_NONE) {
  session_start();
} ?>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Project Manager</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="projects.php">Projektek</a>
        </li>
        <?php
        if (isset($_SESSION['access_level']) && $_SESSION['access_level'] == 'admin') {
          echo '<li class="nav-item"><a class="nav-link active" href="showDB.php">Adatbázis</a></li>';
        }
        ?>
      </ul>
    </div>
    <?php
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
      echo '<ul class="nav-item dropdown">
             <button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
             Bejelentkezve mint: ' . $_SESSION["username"] . '
             </button>
             <ul class="dropdown-menu dropdown-menu-end">
             <li><a class="dropdown-item" href="profile.php">Profil</a></li>
             <li><a class="dropdown-item" href="logout.php">Kijelentkezés</a></li>
             </ul>
             </ul>';
    } else if (!isset($on_login_page)) {
      echo '<li class="d-flex" role="search">
            <a class="nav-link" href="login.php">Bejelentkezés</a>
            </li>';
    }
    ?>
  </div>
</nav>