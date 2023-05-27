<!-- // ? funkcionális elvárások: Az alkalmazásban kell legyen mód minden adatbázisban tárolt adat kiolvasására az -->
<!-- // ? adatbázisból, azok megjelenítésére, új adatok bevitelére és a meglévő adatok módosítására. (Tehát nem elegendő, -->
<!-- // ? ha csak írni, vagy csak olvasni tudjuk az adatot, szerkeszteni is tudni kell azokat.) -->
<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}
// A bootstrap ennek segítségével jelzi, melyik oldalon vagyunk éppen a menuben
$site = 'project';
include 'menu.php';
if ($_SESSION['access_level'] == 'employee') {
    include 'projects_employee.php';
} else if ($_SESSION['access_level'] == 'project_lead') {
    include 'projects_lead.php';
} else if ($_SESSION['access_level'] == 'admin') {
    include 'projects_admin.php';
}
include 'footer.php';
?>