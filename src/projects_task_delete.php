<?php
if (isset($_GET['task_id_get']) && isset($_GET['project_id_get'])) {
    @include 'connection.php';
    $link = connectDB();
    // ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
    $task_id_get = mysqli_real_escape_string($link, $_GET['task_id_get']);
    $project_id_get = mysqli_real_escape_string($link, $_GET['project_id_get']);
    $query_delete_task = "DELETE FROM task WHERE task_id = '$task_id_get';";
    mysqli_query($link, $query_delete_task);
    closeDB($link);
    header('Location: projects_task.php?project_id=' . $project_id_get . '');
}
?>