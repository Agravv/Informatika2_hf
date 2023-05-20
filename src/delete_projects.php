<?php
if (isset($_GET['project_id'])) {
    @include 'connection.php';
    $link = connectDB();
    $project_id = $_GET['project_id'];
    $query_delete = "DELETE FROM project WHERE project_id = '$project_id';";
    if (isset($_GET['status']) && $_GET['status'] == 'not_started') {
        mysqli_query($link, $query_delete);
    } else if (isset($_GET['status']) && ($_GET['status'] == 'in_progress' || $_GET['status'] == 'finished')) {
        mysqli_begin_transaction($link);
        try {
            $query = "DELETE FROM user_has_project WHERE project_project_id = '$project_id';";
            mysqli_query($link, $query);
            mysqli_query($link, $query_delete);
            mysqli_commit($link);
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }
    }
    closeDB($link);
    header('Location: projects.php');
}
?>