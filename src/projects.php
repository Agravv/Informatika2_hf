<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}

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