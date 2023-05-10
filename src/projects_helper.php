<?php
@include 'connection.php';
$link = connectDB();

$action = $_GET['action'];
$project_id = $_GET['project_id'];
if ($action == 'accept') {
    mysqli_query($link, "UPDATE project SET status = 'in_progress' WHERE project_id = '$project_id'");
    // todo error check
    header('Location: projects.php');
} else if ($action == 'finish') {
    mysqli_query($link, "UPDATE project SET status = 'finished' WHERE project_id = '$project_id'");
    // todo error check
    header('Location: projects.php');
}
header('Location: projects.php?info=update_failed');
?>