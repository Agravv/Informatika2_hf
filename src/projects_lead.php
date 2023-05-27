<?php
@include 'connection.php';
$link = connectDB();
$session_user_id = mysqli_real_escape_string($link, $_SESSION['id']);
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
// A projektvezetonek kiosztott porjektet, a projektvezeto kiosztja egy alkalmazottnak
if (isset($_POST['submit_assign'])) {
    // osszefuzot string-et kapunk a POST-on belul, ezt szétszedvbe kapunk 2 paramétert
    $option_string_employee = explode("_", mysqli_real_escape_string($link, $_POST['assign_employee']));
    if ($option_string_employee[0] == 'empty') {
        header("Location: projects.php");
    }
    $assign_to_employee = $option_string_employee[0];
    $assign_project = $option_string_employee[1];

    mysqli_begin_transaction($link);
    try {
        $query_insert_join = "INSERT INTO user_has_project (user_has_project.user_id,user_has_project.project_id) 
                                VALUES ('$assign_to_employee','$assign_project')";
        $query_update_project = "UPDATE project 
                            SET project.status = 'in_progress' 
                            WHERE project_id = '$assign_project'";
        mysqli_query($link, $query_insert_join);
        mysqli_query($link, $query_update_project);

        mysqli_commit($link);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($link);
        throw $exception;
    }
}
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
// Projekt létrehozásda
if (isset($_POST['submit_create'])) {
    // Ha ures az ID input, akkor az AUTO_INCREMENT határozza meg az ID-t, ehhez kell a NULL
    if (isset($_POST['project_id']) && trim($_POST['project_id']) !== "") {
        $new_id = mysqli_real_escape_string($link, $_POST['project_id']);
    } else {
        $new_id = NULL;
    }
    $new_title = mysqli_real_escape_string($link, $_POST['project_title']);
    $new_description = mysqli_real_escape_string($link, $_POST['project_description']);
    $new_due_date = mysqli_real_escape_string($link, $_POST['project_due_date']);

    mysqli_begin_transaction($link);
    try {
        $query_insert_project = "INSERT INTO project (project_id,title,project.description,due_date)
                                    VALUES ($new_id,'$new_title','$new_description','$new_due_date')";
        mysqli_query($link, $query_insert_project);
        $insert_id = mysqli_insert_id($link); // visszatér az AUTO_INCREMENT legfrissebb értékével
        $query_insert_connection = "INSERT INTO user_has_project (project_id,user_id)
                                    VALUES ($insert_id,$session_user_id);";
        mysqli_query($link, $query_insert_connection);

        mysqli_commit($link);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($link);
        throw $exception;
    }
}
?>
<div class="form-container-profile form-container">
    <form action="" method="post">
        <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
        <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
        <!-- // ? felhasználónak. -->
        <h4>Új projekt létrehozása</h4>
        <label for="project_id">Projekt ID: </label>
        <input class="form-control" type="number" name="project_id">
        <label for="project_title">Projekt név: </label>
        <input class="form-control" type="text" name="project_title" required>
        <label for="project_description">Projekt leírás: </label>
        <input class="form-control" type="text" name="project_description">
        <label for="project_due_date">Projekt határideje: </label>
        <input class="form-control" type="date" name="project_due_date" required>
        <input type="submit" name="submit_create" value="Létrehozás" class="form-btn">
    </form>
</div>

<?php
// Kiosztásra váró projektek
$query_not_started = "SELECT project.project_id,project.title,project.description,project.due_date,project.status 
                        FROM project
                        LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                        LEFT JOIN user ON user.user_id = user_has_project.user_id
                        WHERE project.status = 'not_started'
                        AND user.user_id = '$session_user_id'
                        ORDER BY due_date;";
$result_not_started = mysqli_query($link, $query_not_started);
if (mysqli_num_rows($result_not_started) > 0) {
    echo '<div class="table-container"><table class="table-hover table">
                <caption><h4>Kiosztásra váró projektek</h4></caption><tr>
                <th>Projekt ID</th>
                <th>Projekt név</th>
                <th>Projekt leírás</th>
                <th>Projekt határideje</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th></tr>';
    while ($row = mysqli_fetch_array($result_not_started)) {
        echo '<tr>
            <form action="" method="post">
                    <td>' . $row['project_id'] . '</td>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>';

        $temp_project_id = $row['project_id'];
        $query_all_employee = "SELECT user_id, username
                                FROM user
                                WHERE access_level = 'employee';";
        $result_all_employee = mysqli_query($link, $query_all_employee);
        if (mysqli_num_rows($result_all_employee) > 0) {
            // ? funkcionális elvárás: Fontos, hogy a felületen az adatok elérése a felhasználó számára kényelmes módon történjen.
            echo '<td><select name="assign_employee" class="form-select">
                    <option value="empty" selected></option>';
            while ($row_employee = mysqli_fetch_array($result_all_employee)) {
                echo '<option value="' . $row_employee['user_id'] . '_' . $row['project_id'] . '">' . $row_employee['username'] . '</option>';
            }
            echo '  </select></td>';

        }
        // fa-pen-to-square: edit/módosítás ikon
        // fa-trash: kuka/torlés ikon
        // padding-right: 20px -> a kiosztás és feladatok gomb elkulonítése
        echo '</select></td><td style="padding-right:20px">
                    <input type="submit" name="submit_assign" value="Kiosztás" class="button" style="font-size:1rem">
                    </td>
                    </form>
                <td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>
                <td><a href="projects_edit.php?status=not_started&project_id=' . $row['project_id'] . '"> <i
                class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                <td><a href="projects_delete.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                style="color: ' . $color_code . ';"></i> </a></td>
                </tr>';
    }
    echo '</table></div>';
}
// Folyamatban lévő projektek
$query_in_progress_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.access_level
                                FROM project
                                LEFT JOIN user_has_project ON project.project_id = user_has_project.project_id
                                LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                WHERE project.status = 'in_progress'
                                AND access_level = 'project_lead'
                                AND user.user_id = '$session_user_id'
                                ORDER BY due_date;";
$result_in_progress_leader = mysqli_query($link, $query_in_progress_leader);
if (mysqli_num_rows($result_in_progress_leader) > 0) {
    echo '<div class="table-container"><table class="table-hover table">
            <caption><h4>Folyamatban lévő projektek</h4></caption><tr>
            <th>Projekt ID</th>
            <th>Projekt név</th>
            <th>Projekt leírás</th>
            <th>Projekt határideje</th>
            <th>Projekten dolgozik</th>
            <th></th>
            <th></th>
            <th></th></tr>';
    while ($row = mysqli_fetch_array($result_in_progress_leader)) {
        echo '<tr>
        <form action="" method="post">
                <td>' . $row['project_id'] . '</td>
                <td>' . $row['title'] . '</td>
                <td>' . $row['description'] . '</td>
                <td>' . $row['due_date'] . '</td>';
        // <td>' . $row['username'] . '</td>';
        // ? funkcionális elvárás: Legyen lehetőség az adatbázistáblák közötti külső kulcs kapcsolatok megjelenítésére, szerkesztésére és törlésére.
        $temp_project_id = $row['project_id'];
        $query_in_progress_employee = "SELECT project.project_id,project.status, user.username, user.access_level
                                    FROM project 
                                    LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                                    LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                    WHERE project.status = 'in_progress'
                                    AND access_level = 'employee'
                                    AND project.project_id = '$temp_project_id';";
        $result_in_progress_employee = mysqli_query($link, $query_in_progress_employee);
        if (mysqli_num_rows($result_in_progress_employee) > 0) {
            $row_employee = mysqli_fetch_array($result_in_progress_employee);
            echo '<td>' . $row_employee['username'] . '</td>';
        } else {
            echo '<td> </td>';
        }
        echo '<td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>';
        // fa-pen-to-square: edit/módosítás ikon
        // fa-trash: kuka/torlés ikon
        echo '<td><a href="projects_edit.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i
                class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                <td><a href="projects_delete.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                style="color: ' . $color_code . ';"></i> </a></td></tr>';
    }
    echo '</table></div>';
}
// Projekt archívum
$query_finished_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.access_level
                            FROM project 
                            LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                            LEFT JOIN user ON user_has_project.user_id = user.user_id 
                            WHERE project.status = 'finished'
                            AND access_level = 'project_lead'
                            AND user.user_id = '$session_user_id'
                            ORDER BY due_date;";
$result_finished_leader = mysqli_query($link, $query_finished_leader);
if (mysqli_num_rows($result_finished_leader) > 0) {
    echo '<div class="table-container"><table  class="table-hover table">
            <caption><h4>Projekt archívum</h4></caption><tr>
            <th>Projekt ID</th>
            <th>Projekt név</th>
            <th>Projekt leírás</th>
            <th>Projekt határideje</th>
            <th>Projekten dolgozott</th>
            <th></th>';
    echo '</tr>';
    while ($row = mysqli_fetch_array($result_finished_leader)) {
        echo '<tr>
                <form action="" method="post">
                <td>' . $row['project_id'] . '</td>
                <td>' . $row['title'] . '</td>
                <td>' . $row['description'] . '</td>
                <td>' . $row['due_date'] . '</td>';
        // ? funkcionális elvárás: Legyen lehetőség az adatbázistáblák közötti külső kulcs kapcsolatok megjelenítésére, szerkesztésére és törlésére.
        $temp_project_id = $row['project_id'];
        $query_finished_employee = "SELECT project.project_id,project.status, user.username, user.access_level
                                    FROM project 
                                    LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                                    LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                    WHERE project.status = 'finished'
                                    AND access_level = 'employee'
                                    AND project.project_id = '$temp_project_id';";
        $result_finished_employee = mysqli_query($link, $query_finished_employee);
        if (mysqli_num_rows($result_finished_employee) > 0) {
            $row_employee = mysqli_fetch_array($result_finished_employee);
            echo '<td>' . $row_employee['username'] . '</td>';
        } else {
            echo '<td> </td>';
        }
        echo '<td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>';
        echo '</tr>';
    }
    echo '</table></div>';
}

closeDB($link);
?>
<br>