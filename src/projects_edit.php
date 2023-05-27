<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Csak projekt vezetok láthatják az oldalt
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true || ($_SESSION['access_level'] != 'project_lead' && $_SESSION['access_level'] != 'admin')) {
    header('Location: index.php');
    exit;
}

@include 'connection.php';
$link = connectDB();
$session_user_id = mysqli_real_escape_string($link, $_SESSION['id']);
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
$project_id_get = mysqli_real_escape_string($link, $_GET['project_id']);
$status = mysqli_real_escape_string($link, $_GET['status']);
// A legordulo menube szukseges felhasználónevek lekérdezése
$query_select_users = "SELECT user_id, username
                        FROM user
                        WHERE access_level = 'employee';";
$result_select_users = mysqli_query($link, $query_select_users);
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_POST['submit_edit'])) {
    // $input_is_empty jelzi, hogy van-e bármelyik input mezobe input megadva
    $input_is_empty = true;
    $modified_id = false;
    $edited_update_query = "UPDATE project SET";
    if (isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "") {
        $edited_project_id = mysqli_real_escape_string($link, $_POST['edited_project_id']);
        $current_projects = mysqli_query($link, "SELECT project_id FROM project WHERE project_id = '$edited_project_id' ");
        $edited_update_query .= " project.project_id = '$edited_project_id',";
        $input_is_empty = false;
        $modified_id = true;
    }
    // Az ID nem lehet duplikálva
    if ((isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "") && (mysqli_num_rows($current_projects) != 0)) {
        $already_in_use_error[] = 'Az ID már foglalt';
    } else {
        if (isset($_POST['edited_project_title']) && trim($_POST['edited_project_title']) !== "") {
            $edited_title = mysqli_real_escape_string($link, $_POST['edited_project_title']);
            $edited_update_query .= " project.title = '$edited_title',";
            $input_is_empty = false;
        }
        if (isset($_POST['edited_project_description']) && trim($_POST['edited_project_description']) !== "") {
            $edited_description = mysqli_real_escape_string($link, $_POST['edited_project_description']);
            $edited_update_query .= " project.description = '$edited_description',";
            $input_is_empty = false;
        }
        if (isset($_POST['edited_project_due_date']) && trim($_POST['edited_project_due_date']) !== "") {
            $edited_due_date = mysqli_real_escape_string($link, $_POST['edited_project_due_date']);
            $edited_update_query .= " project.due_date = '$edited_due_date',";
            $input_is_empty = false;
        }
        // kiosztásra váró feladatok, amikor van konkrét input
        if (!$input_is_empty && ($status == 'not_started')) {
            $edited_update_query[-1] = " ";
            $edited_update_query .= " WHERE project.project_id = '$project_id_get'";
            // update-elni kell a TASK táblát is, mert a projekt ID-je kulso kulcsa a TASK táblának
            if ($modified_id) {
                $edited_task = "UPDATE task 
                                SET project_project_id = '$edited_project_id' 
                                WHERE project_project_id = '$project_id_get'";
            } else {
                $edited_task = "SELECT 1 WHERE false;";
            }
            mysqli_begin_transaction($link);
            try {
                mysqli_query($link, $edited_update_query);
                mysqli_query($link, $edited_task);

                mysqli_commit($link);
            } catch (mysqli_sql_exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }

            // Kiosztott feladatok lekezelése
        } else if ($status == 'in_progress') {
            // Ha ures inputok vannak illetve nincs kiválasztva user
            if ($input_is_empty && $_POST['edited_assign_user'] == 'empty') {
                // ha nincs alkalmazott hozzárendelve, akkor not_started-re kell állítani
                mysqli_begin_transaction($link);
                try {
                    $query_update_project = "UPDATE project 
                                            SET project.status = 'not_started' 
                                            WHERE project_id = '$project_id_get'";
                    $query_delete_join = "DELETE FROM user_has_project 
                                            WHERE project_id = '$project_id_get'
                                            AND user_id != '$session_user_id';";
                    mysqli_query($link, $query_update_project);
                    mysqli_query($link, $query_delete_join);

                    mysqli_commit($link);
                } catch (mysqli_sql_exception $exception) {
                    mysqli_rollback($link);
                    throw $exception;
                }
                // ures inputok viszont van kiválasztva user (csak átruházzuk valaki másra a feladatot)
            } else if ($input_is_empty && $_POST['edited_assign_user'] != 'empty') {
                // csak a hozzárendelés módosul
                $new_user_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                $query_update_join = "UPDATE user_has_project 
                                        SET user_id = '$new_user_id' 
                                        WHERE project_id = '$project_id_get'
                                        AND user_id != '$session_user_id';";
                mysqli_query($link, $query_update_join);
            }
            // Vannak inputok
            else if (!$input_is_empty) {
                // A string végérol levagja a felesleges vesszot
                $edited_update_query[-1] = " ";
                $edited_update_query .= " WHERE project.project_id = '$project_id_get';";
                $new_user_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                // nincs user-hez való hozzárendelés
                if ($_POST['edited_assign_user'] == 'empty') {
                    // ha nincs alkalmazott hozzárendelve, akkor not_started-re kell állítani
                    if (!(isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "")) {
                        $edited_project_id = $project_id_get;
                    }
                    $query_modify_user_has_project = "DELETE FROM user_has_project 
                                                        WHERE project_id = '$edited_project_id'
                                                        AND user_id != '$session_user_id';";
                    $query_update_project = "UPDATE project 
                                    SET project.status = 'not_started' 
                                    WHERE project_id = '$edited_project_id';";
                    // van user-hez való hozzárendelés    
                } else if ($_POST['edited_assign_user'] != 'empty') {
                    if (!(isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "")) {
                        $edited_project_id = $project_id_get;
                    }
                    $query_modify_user_has_project = "UPDATE user_has_project
                                                        SET user_id = '$new_user_id'
                                                        WHERE project_id = '$edited_project_id'
                                                        AND user_id != '$session_user_id';";
                    $query_update_project = "SELECT 1 WHERE false;"; // nem csinál semmit
                }
                // update-elni kell a TASK táblát is, mert a projekt ID-je kulso kulcsa a TASK táblának
                if ($modified_id) {
                    $edited_task = "UPDATE task 
                                    SET project_project_id = '$edited_project_id' 
                                    WHERE project_project_id = '$project_id_get'";
                } else {
                    $edited_task = "SELECT 1 WHERE false;";
                }
                mysqli_begin_transaction($link);
                try {
                    mysqli_query($link, $edited_update_query);
                    mysqli_query($link, $query_modify_user_has_project);
                    mysqli_query($link, $query_update_project);
                    mysqli_query($link, $edited_task);

                    mysqli_commit($link);
                } catch (mysqli_sql_exception $exception) {
                    mysqli_rollback($link);
                    throw $exception;
                }
            }
        }
    }
    if (!isset($error)) {
        header('Location: projects.php');
    }
}

include 'menu.php';
if (isset($already_in_use_error)) {
    foreach ($already_in_use_error as $already_in_use_error) {
        echo '<span class="message error">' . $already_in_use_error . '</span>';
    }
}
?>
<div class="form-container">
    <form action="" method="post">
        <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
        <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
        <!-- // ? felhasználónak. -->
        <h4>Projekt adatainak módosítása</h4>
        <?php
        if (isset($error)) {
            foreach ($error as $error) {
                echo '<span class="message error">' . $error . '</span>';
            }
            echo '<br>';
        }
        ?>

        <label for="edited_project_id">Új projekt ID: </label>
        <input type="number" class="form-control" name="edited_project_id">

        <label for="edited_project_title">Új projekt név: </label>
        <input type="text" class="form-control" name="edited_project_title">

        <label for="edited_project_description">Új projekt leírás: </label>
        <input type="text" class="form-control" name="edited_project_description">

        <label for="edited_project_due_date">Új projekt határidő: </label>
        <input type="date" class="form-control" name="edited_project_due_date">
        <?php
        if ($status == 'in_progress' || $status == 'finished') {
            echo '<label for="edited_assign_user">Hozzárendelés: </label>';
            $query_select_users = "SELECT user_id, username
                                    FROM user
                                    WHERE access_level = 'employee';";
            $result_select_users = mysqli_query($link, $query_select_users);
            if (mysqli_num_rows($result_select_users) > 0) {
                echo '<select name="edited_assign_user" class="form-select">
                        <option value="empty"></option>';
                while ($row_user = mysqli_fetch_array($result_select_users)) {
                    echo '<option value="' . $row_user['user_id'] . '">' . $row_user['username'] . '</option>';
                }
                echo '</select>';
            }
            if ($status == 'finished') {
                echo '<label for="edited_status">Állapot: </label>
                        <select name="edited_status" class="form-select">
                        <option value="finished">Befejezett</option>
                        <option value="not_started">Még nem kezdődött el</option>
                        <option value="in_progress">Folyamatban</option>
                        </select>';
            }
        }
        ?>
        <input type="submit" name="submit_edit" value="Módosítás" class="form-btn">
    </form>
</div>
<?php include 'footer.php';
closeDB($link);
?>