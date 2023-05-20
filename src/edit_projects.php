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
$id = $_SESSION['id'];
$get_project_id = mysqli_real_escape_string($link, $_GET['project_id']);
$status = mysqli_real_escape_string($link, $_GET['status']);
// A legordulo menube szukseges felhasználónevek lekérdezése
$query_select_users = "SELECT user_id, username
                        FROM user
                        WHERE access_level = 'employee';";
$result_select_users = mysqli_query($link, $query_select_users);
if (isset($_POST['submit_edit'])) {
    // $input_is_empty jelzi, hogy van-e bármelyik input mezobe input megadva
    $input_is_empty = true;
    $edited_update_query = "UPDATE project SET";
    if (isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "") {
        $edited_project_id = mysqli_real_escape_string($link, $_POST['edited_project_id']);
        $current_projects = mysqli_query($link, "SELECT project_id FROM project WHERE project_id = '$edited_project_id' ");
        $edited_update_query .= " project.project_id = '$edited_project_id',";
        $input_is_empty = false;
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
            $edited_update_query .= " WHERE project.project_id = '$get_project_id'";
            mysqli_query($link, $edited_update_query);
            // Kiosztott feladatok lekezelése
        } else if ($status == 'in_progress') {
            // Ha ures inputok vannak illetve nincs kiválasztva user
            if ($input_is_empty && $_POST['edited_assign_user'] == 'empty') {
                mysqli_begin_transaction($link);
                try {
                    $query_update_project = "UPDATE project 
                                            SET project.status = 'not_started' 
                                            WHERE project_id = '$get_project_id'";
                    $query_delete = "DELETE FROM user_has_project 
                                    WHERE project_project_id = '$get_project_id'
                                    AND user_id != '$id';";
                    mysqli_query($link, $query_update_project);
                    mysqli_query($link, $query_delete);

                    mysqli_commit($link);
                } catch (mysqli_sql_exception $exception) {
                    mysqli_rollback($link);
                    throw $exception;
                }
                // ures inputok viszont van kiválasztva user (csak átruházzuk valaki másra a feladatot)
            } else if ($input_is_empty && $_POST['edited_assign_user'] != 'empty') {
                $new_user_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                $query_update = "UPDATE user_has_project 
                                SET user_id = '$new_user_id' 
                                WHERE project_project_id = '$get_project_id'
                                AND user_id != '$id';";
                mysqli_query($link, $query_update);
            }
            // Vannak inputok
            else if (!$input_is_empty) {
                // A string végérol levagja a felesleges vesszot
                $edited_update_query[-1] = " ";
                $edited_update_query .= " WHERE project.project_id = '$get_project_id';";
                $new_user_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                // nincs user-hez való hozzárendelés
                if ($_POST['edited_assign_user'] == 'empty') {
                    if (!(isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "")) {
                        $edited_project_id = $get_project_id;
                    }
                    $query_modify_user_has_project = "DELETE FROM user_has_project 
                                                    WHERE project_project_id = '$edited_project_id'
                                                    AND project_project_id != '$id';";
                    $query_update = "UPDATE project 
                                    SET project.status = 'not_started' 
                                    WHERE project_id = '$edited_project_id';";
                    // van user-hez való hozzárendelés    
                } else if ($_POST['edited_assign_user'] != 'empty') {
                    if (!(isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "")) {
                        $edited_project_id = $get_project_id;
                    }
                    $query_modify_user_has_project = "UPDATE user_has_project
                                                    SET user_id = '$new_user_id'
                                                    WHERE project_project_id = '$edited_project_id'
                                                    AND user_id != '$id';";
                    $query_update = "SELECT 1 WHERE false;"; // nem csinál semmit
                }
                mysqli_begin_transaction($link);
                try {
                    mysqli_query($link, $edited_update_query);
                    mysqli_query($link, $query_modify_user_has_project);
                    mysqli_query($link, $query_update);

                    mysqli_commit($link);
                } catch (mysqli_sql_exception $exception) {
                    mysqli_rollback($link);
                    throw $exception;
                }
            }
            // Adminként van hozzáférés a befejezett projektek módosításához
        } else if ($status == 'finished') {
            // minden mezo uresen/alap állapotban hagyva
            if ($input_is_empty && ($_POST['edited_assign_user'] == 'empty') && ($_POST['edited_status'] == 'not_started')) {
                mysqli_begin_transaction($link);
                try {
                    $query_update_project = "UPDATE project 
                                            SET project.status = 'not_started' 
                                            WHERE project_id = '$get_project_id'";
                    $query_delete = "DELETE FROM user_has_project 
                                    WHERE project_project_id = '$get_project_id'";
                    mysqli_query($link, $query_update_project);
                    mysqli_query($link, $query_delete);
                    mysqli_commit($link);
                } catch (mysqli_sql_exception $exception) {
                    mysqli_rollback($link);
                    throw $exception;
                }
                // Ha a projekt hozzá van rendelve user-hez, akkor már in_progress statusban van, ezt az ellentmondást kezeli
            } else if (($_POST['edited_assign_user'] != 'empty') && ($_POST['edited_status'] == 'not_started')) {
                $error[] = 'A még ki nem osztott projektekhez nem lehet hozzárendelni felhasználót!';
                // Adatokat nem módosít, csak azt, hogy kihez van rendelve és milyen stádiumban van a projekt
            } else if ($input_is_empty && ($_POST['edited_assign_user'] != 'empty') && ($_POST['edited_status'] != 'not_started')) {
                $edited_status = mysqli_real_escape_string($link, $_POST['edited_status']);
                $new_user_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                mysqli_begin_transaction($link);
                try {
                    $query_update_project = "UPDATE project 
                                            SET project.status = '$edited_status' 
                                            WHERE project_id = '$get_project_id'";
                    $query_update = "UPDATE user_has_project 
                                    SET user_id = '$new_user_id'
                                    WHERE project_project_id = '$get_project_id'";
                    mysqli_query($link, $query_update_project);
                    mysqli_query($link, $query_update);
                    mysqli_commit($link);
                } catch (mysqli_sql_exception $exception) {
                    mysqli_rollback($link);
                    throw $exception;
                }
                // Ha vannak módosítandó adatok is (id,név...)
            } else if (!$input_is_empty) {
                // az projekt id beállítása standard értékre a késobbi konnyebb használathoz
                if (!(isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "")) {
                    $edited_project_id = $get_project_id;
                }
                $edited_update_query[-1] = " ";
                $edited_update_query .= " WHERE project.project_id = '$get_project_id';";
                $new_user_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                // az adatok módosulnak, a hozzárendeléseket megszunteti, tehát nem lehet más csak not_started állapotú
                if (($_POST['edited_assign_user'] == 'empty') && ($_POST['edited_status'] == 'not_started')) {
                    mysqli_begin_transaction($link);
                    try {
                        $query_update_project = "UPDATE project 
                                                SET project.status = 'not_started' 
                                                WHERE project_id = '$edited_project_id'";
                        $query_delete = "DELETE FROM user_has_project 
                                        WHERE project_project_id = '$edited_project_id'";
                        mysqli_query($link, $edited_update_query);
                        mysqli_query($link, $query_update_project);
                        mysqli_query($link, $query_delete);
                        mysqli_commit($link);
                    } catch (mysqli_sql_exception $exception) {
                        mysqli_rollback($link);
                        throw $exception;
                    }
                    // az adatok módosulnak és valami konkrét hozzárendelés+állapot módosítás is torténik
                } else if (($_POST['edited_assign_user'] != 'empty') && ($_POST['edited_status'] != 'not_started')) {
                    $edited_status = $_POST['edited_status'];
                    $new_user_id = $_POST['edited_assign_user'];
                    mysqli_begin_transaction($link);
                    try {
                        $query_update_project = "UPDATE project 
                                                SET project.status = '$edited_status' 
                                                WHERE project_id = '$edited_project_id'";
                        $query_update = "UPDATE user_has_project 
                                        SET user_id = '$new_user_id'
                                        WHERE project_project_id = '$edited_project_id'";
                        mysqli_query($link, $edited_update_query);
                        mysqli_query($link, $query_update_project);
                        mysqli_query($link, $query_update);
                        mysqli_commit($link);
                    } catch (mysqli_sql_exception $exception) {
                        mysqli_rollback($link);
                        throw $exception;
                    }
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