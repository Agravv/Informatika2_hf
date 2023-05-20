<?php
@include 'connection.php';
$link = connectDB();

$action = mysqli_real_escape_string($link, $_GET['action']);
$project_id = mysqli_real_escape_string($link, $_GET['project_id']);

if ($action == 'finish') {
    $stmt = mysqli_prepare($link, "UPDATE project SET status = 'finished' WHERE project_id = ? ");
    mysqli_stmt_bind_param($stmt, "s", $project_id);
    mysqli_stmt_execute($stmt);
}
header('Location: projects.php');
?>