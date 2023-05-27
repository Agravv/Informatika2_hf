<?php
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_GET['id'])) {
    @include 'connection.php';
    $link = connectDB();
    $user_id_get = mysqli_real_escape_string($link, $_GET['id']);
    $result = mysqli_query($link, "SELECT * FROM user WHERE user_id = '$user_id_get'");
    if (mysqli_num_rows($result) != 0) {
        $row = mysqli_fetch_array($result);
        $query_delete_user = "DELETE FROM user WHERE user_id = '$user_id_get'";
        $query_update_project = "UPDATE project 
                                    LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                                    LEFT JOIN user ON user.user_id = user_has_project.user_id
                                    SET project.status = 'not_started'
                                    WHERE user.user_id = '$user_id_get'
                                    AND project.status = 'in_progress';";
        if ($row['access_level'] == 'project_lead') {
            $query_delete_join = "DELETE FROM user_has_project
                        WHERE user_has_project.project_id IN (SELECT project.project_id 
                        FROM project 
                        LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id 
                        LEFT JOIN user ON user.user_id = user_has_project.user_id 
                        WHERE user.user_id = '$user_id_get'
                        AND project.status != 'finished');";
        } else {
            $query_delete_join = "SELECT 1 WHERE false;";
        }
        mysqli_begin_transaction($link);
        try {
            mysqli_query($link, $query_update_project);
            mysqli_query($link, $query_delete_join);
            mysqli_query($link, $query_delete_user);

            mysqli_commit($link);
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($link);
            throw $exception;
        }
    }
    closeDB($link);
}
header("Location: users.php");
?>