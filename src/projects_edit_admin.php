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
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_GET['project_id'])) {
    $project_id_get = mysqli_real_escape_string($link, $_GET['project_id']);
}
if (isset($_GET['status'])) {
    $status_get = mysqli_real_escape_string($link, $_GET['status']);
}
if (isset($_GET['has_leader'])) {
    $has_leader = mysqli_real_escape_string($link, $_GET['has_leader']);
}
if (isset($_GET['leader_id'])) {
    $leader_id_get = mysqli_real_escape_string($link, $_GET['leader_id']);
}
// A legordulo menube szukseges felhasználónevek lekérdezése
$query_select_users = "SELECT user_id, username
                        FROM user
                        WHERE access_level = 'employee';";
$result_select_users = mysqli_query($link, $query_select_users);
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_POST['submit_edit'])) {
    // $input_is_empty jelzi, hogy van-e bármelyik input mezobe input megadva
    $input_is_empty = true;
    $id_is_modified = false;
    $edited_update_query = "UPDATE project SET";
    if (isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "") {
        $edited_project_id = mysqli_real_escape_string($link, $_POST['edited_project_id']);
        $current_projects = mysqli_query($link, "SELECT project_id FROM project WHERE project_id = '$edited_project_id';");
        $edited_update_query .= " project.project_id = '$edited_project_id',";
        $input_is_empty = false;
        $id_is_modified = true;
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
        if (!$input_is_empty && ($status_get == 'not_started') && (!$has_leader)) {
            $edited_update_query[-1] = " ";
            $edited_update_query .= " WHERE project.project_id = '$project_id_get'";
            if ($id_is_modified) {
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
        } else if (($status_get == 'not_started') && ($has_leader)) {
            // Kiosztott feladatok lekezelése. Csak módosítja, kihez van hozzárendelve
            // A hozzárendelést és az adatmódosítást kulon-kulon, egymástól fuggetlenul dolgozza fel
            // Majd mindenképpen a tranzakción keresztul futtatja le mind2-t
            if ($_POST['edited_assign_leader'] != 'empty') {
                $edited_project_leader = mysqli_real_escape_string($link, $_POST['edited_assign_leader']);
                // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok szerkesztésére.
                $query = "UPDATE user_has_project
                            SET user_id = '$edited_project_leader'
                            WHERE project_id = '$project_id_get'
                            AND user_id = '$leader_id_get'";
            } else {
                // Ha "empty" a hozzárendelés input, akkor toroljuk a kapcsolatot a kapcsolótáblából
                // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok törlésére.
                $query = "DELETE FROM user_has_project
                            WHERE project_id = '$project_id_get'
                            AND user_id = '$leader_id_get'";
            }
            if (!$input_is_empty) {
                $edited_update_query[-1] = " ";
                $edited_update_query .= " WHERE project.project_id = '$project_id_get'";
            } else {
                $edited_update_query = "SELECT 1 WHERE false;";
            }
            if ($id_is_modified) {
                $edited_task = "UPDATE task 
                                SET project_project_id = '$edited_project_id' 
                                WHERE project_project_id = '$project_id_get'";
            } else {
                $edited_task = "SELECT 1 WHERE false;";
            }
            mysqli_begin_transaction($link);
            try {
                mysqli_query($link, $query);
                mysqli_query($link, $edited_update_query);
                mysqli_query($link, $edited_task);

                mysqli_commit($link);
            } catch (mysqli_sql_exception $exception) {
                mysqli_rollback($link);
                throw $exception;
            }
        } else if ($status_get == 'in_progress') {
            if ($input_is_empty) {
                if ($_POST['edited_assign_leader'] != 'empty') {
                    if ($_POST['edited_assign_user'] == 'empty') {
                        // in_progress + ures alkalmazott + kijelolt vezeto ==> a projekt vezetot módosítja
                        $edited_leader_id = mysqli_real_escape_string($link, $_POST['edited_assign_leader']);
                        mysqli_begin_transaction($link);
                        try {
                            // csak akkor lehet in_progress, ha alkalmazotthoz és vezetohoz is hozzá van rednelve, 
                            // ezért át kell állítani not_started-re
                            $query_update_project = "UPDATE project 
                                                        SET project.status = 'not_started' 
                                                        WHERE project_id = '$project_id_get'";
                            // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok törlésére.
                            $query_delete = "DELETE FROM user_has_project 
                                                WHERE project_id = '$project_id_get'
                                                AND user_id != '$leader_id_get';";
                            // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok szerkesztésére.
                            $query_update_join = "UPDATE user_has_project
                                                        SET user_id = '$edited_leader_id'
                                                        WHERE project_id = '$project_id_get'
                                                        AND user_id = '$leader_id_get';";
                            mysqli_query($link, $query_update_project);
                            mysqli_query($link, $query_delete);
                            mysqli_query($link, $query_update_join);

                            mysqli_commit($link);
                        } catch (mysqli_sql_exception $exception) {
                            mysqli_rollback($link);
                            throw $exception;
                        }
                    } else if ($_POST['edited_assign_user'] != 'empty') {
                        // alkalmazott és vezeto módosítása
                        $edited_employee_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                        $edited_leader_id = mysqli_real_escape_string($link, $_POST['edited_assign_leader']);
                        // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok szerkesztésére.
                        mysqli_begin_transaction($link);
                        try {
                            $query_update_employee = "UPDATE user_has_project 
                                                        SET user_id = '$edited_employee_id' 
                                                        WHERE project_id = '$project_id_get'
                                                        AND user_id != '$leader_id_get';";
                            $query_update_leader = "UPDATE user_has_project 
                                                    SET user_id = '$edited_leader_id' 
                                                    WHERE project_id = '$project_id_get'
                                                    AND user_id = '$leader_id_get';";
                            mysqli_query($link, $query_update_employee);
                            mysqli_query($link, $query_update_leader);

                            mysqli_commit($link);
                        } catch (mysqli_sql_exception $exception) {
                            mysqli_rollback($link);
                            throw $exception;
                        }
                    }
                } else {
                    // Ha a vezeto "empty", akkor lényegtelen milyen input jon az alkalmazottra, 
                    // ilyenkor biztosan not_startedbe kerul és nincs hozzárendelve user
                    // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok törlésére.
                    $query_delete = "DELETE FROM user_has_project
                                        WHERE project_id = '$project_id_get';";
                    $query_update = "UPDATE project
                                        SET project.status = 'not_started'
                                        WHERE project_id = '$project_id_get';";
                    mysqli_begin_transaction($link);
                    try {
                        mysqli_query($link, $query_delete);
                        mysqli_query($link, $query_update);

                        mysqli_commit($link);
                    } catch (mysqli_sql_exception $exception) {
                        mysqli_rollback($link);
                        throw $exception;
                    }
                }
            }
            // Vannak adat inputok
            else if (!$input_is_empty) {
                $edited_update_query[-1] = " ";
                $edited_update_query .= " WHERE project.project_id = '$project_id_get';";
                if ($id_is_modified) {
                    $edited_task = "UPDATE task 
                                    SET project_project_id = '$edited_project_id' 
                                    WHERE project_project_id = '$project_id_get'";
                } else {
                    $edited_task = "SELECT 1 WHERE false;";
                }
                if ($_POST['edited_assign_leader'] != 'empty') {
                    $edited_leader_id = mysqli_real_escape_string($link, $_POST['edited_assign_leader']);
                    // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok szerkesztésére.
                    $query_update_assigned_leader = "UPDATE user_has_project
                                                        SET user_id = '$edited_leader_id'
                                                        WHERE user_id = '$leader_id_get';";
                    // nincs user-hez való hozzárendelés
                    if ($_POST['edited_assign_user'] == 'empty') {
                        if (!(isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "")) {
                            $edited_project_id = $project_id_get;
                        }
                        // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok törlésére.
                        $query_modify_user_has_project = "DELETE FROM user_has_project 
                                                            WHERE project_id = '$edited_project_id'
                                                            AND user_id != '$leader_id_get';";
                        $query_update_status = "UPDATE project 
                                                SET project.status = 'not_started' 
                                                WHERE project_id = '$edited_project_id';";
                        // van user-hez való hozzárendelés    
                    } else if ($_POST['edited_assign_user'] != 'empty') {
                        $new_user_id = mysqli_real_escape_string($link, $_POST['edited_assign_user']);
                        if (!(isset($_POST['edited_project_id']) && trim($_POST['edited_project_id']) !== "")) {
                            $edited_project_id = $project_id_get;
                        }
                        // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok szerkesztésére.
                        $query_modify_user_has_project = "UPDATE user_has_project
                                                            SET user_id = '$new_user_id'
                                                            WHERE project_id = '$edited_project_id'
                                                            AND user_id != '$leader_id_get';";
                        $query_update_status = "SELECT 1 WHERE false;"; // nem csinál semmit
                    }

                    mysqli_begin_transaction($link);
                    try {
                        mysqli_query($link, $edited_update_query);
                        mysqli_query($link, $query_modify_user_has_project);
                        mysqli_query($link, $query_update_status);
                        mysqli_query($link, $query_update_assigned_leader);
                        mysqli_query($link, $edited_task);

                        mysqli_commit($link);
                    } catch (mysqli_sql_exception $exception) {
                        mysqli_rollback($link);
                        throw $exception;
                    }
                } else {
                    // Ha a vezeto "empty", akkor lényegtelen milyen input jon az alkalmazottra, 
                    // ilyenkor biztosan not_startedbe kerul és nincs hozzárendelve user
                    // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok törlésére.
                    $query_delete = "DELETE FROM user_has_project
                                    WHERE project_id = '$project_id_get';";
                    $query_update = "UPDATE project
                                        SET project.status = 'not_started'
                                        WHERE project_id = '$project_id_get';";
                    mysqli_begin_transaction($link);
                    try {
                        mysqli_query($link, $query_update);
                        mysqli_query($link, $edited_update_query);
                        mysqli_query($link, $query_delete);
                        mysqli_query($link, $edited_task);

                        mysqli_commit($link);
                    } catch (mysqli_sql_exception $exception) {
                        mysqli_rollback($link);
                        throw $exception;
                    }
                }
            }
            // Adminként van hozzáférés a befejezett projektek adatainak módosításához
            // itt már nem lehet a projekt kiosztásán változtatni
        } else if ($status_get == 'finished') {
            $edited_update_query[-1] = " ";
            $edited_update_query .= " WHERE project.project_id = '$project_id_get';";
            if ($id_is_modified) {
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
        if ($has_leader == 'true' && $status_get != 'finished') {
            echo '<label for="edited_assign_leader">Projektvezető: </label>';
            $query_select_leader = "SELECT user_id, username
                                    FROM user
                                    WHERE access_level = 'project_lead';";
            $result_select_leader = mysqli_query($link, $query_select_leader);
            if (mysqli_num_rows($result_select_leader) > 0) {
                // ? funkcionális elvárás: Fontos, hogy a felületen az adatok elérése a felhasználó számára kényelmes módon történjen.
                echo '<select name="edited_assign_leader" class="form-select">
                        <option value="empty"></option>';
                while ($row_leader = mysqli_fetch_array($result_select_leader)) {
                    echo '<option value="' . $row_leader['user_id'] . '">' . $row_leader['username'] . '</option>';
                }
                echo '</select>';
            }
        }
        if ($status_get == 'in_progress') {
            echo '<label for="edited_assign_user">Alkalmazott: </label>';
            $query_select_users = "SELECT user_id, username
                                    FROM user
                                    WHERE access_level = 'employee';";
            $result_select_users = mysqli_query($link, $query_select_users);
            if (mysqli_num_rows($result_select_users) > 0) {
                // ? funkcionális elvárás: Fontos, hogy a felületen az adatok elérése a felhasználó számára kényelmes módon történjen.
                // ? funkcionális elvárás: adatbázistáblák közötti külső kulcs kapcsolatok szerkesztésére és törlésére.
                echo '<select name="edited_assign_user" class="form-select">
                        <option value="empty"></option>';
                while ($row_user = mysqli_fetch_array($result_select_users)) {
                    echo '<option value="' . $row_user['user_id'] . '">' . $row_user['username'] . '</option>';
                }
                echo '</select>';
            }
            if ($status_get == 'finished') {
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