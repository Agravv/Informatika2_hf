<?php
if (isset($_GET['project_id'])) {
    @include 'connection.php';
    $link = connectDB();
    // ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
    $project_id_get = mysqli_real_escape_string($link, $_GET['project_id']);
    $query_delete_project = "DELETE FROM project WHERE project_id = '$project_id_get';";
    if (isset($_GET['status']) && $_GET['status'] == 'not_started') {
        // Ha a projekt not_started státuszban van akkor minden további nélkul lehet torolni
        mysqli_query($link, $query_delete_project);
    } else if (isset($_GET['status']) && ($_GET['status'] == 'in_progress' || $_GET['status'] == 'finished')) {
        // Ha a projekt NEM not_started státuszban van akkor a kapcsolótáblában is kell torléseket végezni
        mysqli_begin_transaction($link);
        try {
            $query_delete_join = "DELETE FROM user_has_project WHERE project_id = '$project_id_get';";
            mysqli_query($link, $query_delete_join);
            mysqli_query($link, $query_delete_project);
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