<?php
@include 'connection.php';
$link = connectDB();
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
// Egy elvégzett projekt leadása
$action = mysqli_real_escape_string($link, $_GET['action']);
$project_id_get = mysqli_real_escape_string($link, $_GET['project_id']);

if ($action == 'finish') {
    $query_update_project = "UPDATE project SET status = 'finished' WHERE project_id = '$project_id_get';";
    mysqli_query($link, $query_update_project);
}
header('Location: projects.php');
?>