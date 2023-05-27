<?php
@include 'connection.php';
$link = connectDB();
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
// Projekt hozzárendelése projektvezetohoz
if (isset($_POST['submit_assign_to_leader'])) {
    // osszefuzot string-et kapunk a POST-on belul, ezt szétszedvbe kapunk 2 paramétert
    $option_string = explode("_", mysqli_real_escape_string($link, $_POST['assign']));
    // 0-ás index-ben adja vissza az elso paramétert, 1-essel a másodikat ...
    $assign_to = $option_string[0];
    $assign_project = $option_string[1];
    $query_assign_to_leader = "INSERT INTO user_has_project (user_has_project.user_id,user_has_project.project_id) 
                                VALUES ('$assign_to','$assign_project')";
    mysqli_query($link, $query_assign_to_leader);
}
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
// Projekt hozzárendelése alkalmazotthoz
if (isset($_POST['submit_assign_to_employee'])) {
    // osszefuzot string-et kapunk a POST-on belul, ezt szétszedvbe kapunk 2 paramétert
    $option_string = explode("_", mysqli_real_escape_string($link, $_POST['assign']));
    // 0-ás index-ben adja vissza az elso paramétert, 1-essel a másodikat ...
    $assign_to = $option_string[0];
    $assign_project = $option_string[1];
    mysqli_begin_transaction($link);
    try {
        $query_assign_to_employee = "INSERT INTO user_has_project (user_has_project.user_id,user_has_project.project_id) 
                                        VALUES ('$assign_to','$assign_project')";
        $query_update_status = "UPDATE project 
                        SET project.status = 'in_progress' 
                        WHERE project_id = '$assign_project'";
        mysqli_query($link, $query_assign_to_employee);
        mysqli_query($link, $query_update_status);

        mysqli_commit($link);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($link);
        throw $exception;
    }
}
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
// új projekt létrehozása
if (isset($_POST['submit_create'])) {
    // nem kotelezo input az ID, ekkor az AUTO_INCREMENT fogja megadni, ehhez kell a NULL
    if (isset($_POST['porject_id']) && trim($_POST['project_id']) !== "") {
        $new_id = mysqli_real_escape_string($link, $_POST['project_id']);
    } else {
        $new_id = NULL;
    }
    $new_title = mysqli_real_escape_string($link, $_POST['project_title']);
    $new_description = mysqli_real_escape_string($link, $_POST['project_description']);
    $new_due_date = mysqli_real_escape_string($link, $_POST['project_due_date']);
    $query_insert = "INSERT INTO project (project_id,title,project.description,due_date)
                    VALUES ('$new_id','$new_title','$new_description','$new_due_date')";
    mysqli_query($link, $query_insert);
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
                        WHERE project.status = 'not_started'
                        ORDER BY due_date;";
$result_not_started = mysqli_query($link, $query_not_started);
if (mysqli_num_rows($result_not_started) > 0) {
    echo '<div class="table-container"><table class="table-hover table">
                <caption><h4>Kiosztásra váró projektek</h4></caption>
                <th>ID</th>
                <th>Projekt név</th>
                <th>Projekt leírás</th>
                <th>Projekt határideje</th>
                <th>Projekt vezető</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>';
    while ($row = mysqli_fetch_array($result_not_started)) {
        $row_project_id = $row['project_id'];
        $query_not_started_with_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status 
                                            FROM project
                                            LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                                            LEFT JOIN user ON user_has_project.user_id = user.user_id
                                            WHERE project.status = 'not_started'
                                            AND user_has_project.user_id IS NULL
                                            AND project.project_id = '$row_project_id'
                                            ORDER BY due_date;";
        $result_not_started_with_leader = mysqli_query($link, $query_not_started_with_leader);
        if (mysqli_num_rows($result_not_started_with_leader) > 0) {
            echo '<tr>
                    <form action="" method="post">
                    <td>' . $row['project_id'] . '</td>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>';
            $query_leader = "SELECT user_id, username
                                FROM user
                                WHERE access_level = 'project_lead';";
            $result_leader = mysqli_query($link, $query_leader);

            if (mysqli_num_rows($result_leader) > 0) {
                // ? funkcionális elvárás: Fontos, hogy a felületen az adatok elérése a felhasználó számára kényelmes módon történjen.
                // legordulo menu, az adatbázisban lévo projektvezetok nevével
                echo '<td><select name="assign" class="form-select">';
                while ($row_leader = mysqli_fetch_array($result_leader)) {
                    echo '<option value="' . $row_leader['user_id'] . '_' . $row['project_id'] . '">' . $row_leader['username'] . '</option>';
                }
                // padding-right: 20px -> a kiosztás és feladatok gomb elkulonítése
                echo '  </select></td><td style="padding-right:20px">
                        <input type="submit" name="submit_assign_to_leader" value="Kiosztás" class="button" style="font-size:1rem">
                        </td>
                        </form>';

            } else {
                echo '<td> </td><td> </td>';
            }
            echo '<td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>';
            // fa-pen-to-square: edit/módosítás ikon
            // fa-trash: kuka/torlés ikon
            echo '<td><a href="projects_edit_admin.php?status=not_started&has_leader=false&project_id=' . $row['project_id'] . '"> <i
                        class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                        <td><a href="projects_delete.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                        style="color: ' . $color_code . ';"></i> </a></td>
                        </tr>';
        }
    }
    echo '</table></div>';
}
// Hozzárendelésre váró projektek
$query_not_started_with_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status,user.username, user.user_id
                                    FROM project
                                    LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                                    LEFT JOIN user ON user_has_project.user_id = user.user_id
                                    WHERE project.status = 'not_started'
                                    AND user.access_level = 'project_lead'
                                    ORDER BY due_date;";
$result_not_started_with_leader = mysqli_query($link, $query_not_started_with_leader);
if (mysqli_num_rows($result_not_started_with_leader) > 0) {
    echo '<div class="table-container"><table class="table-hover table">
                <caption><h4>Hozzárendelésre váró projektek</h4></caption>
                <th>ID</th>
                <th>Projekt név</th>
                <th>Projekt leírás</th>
                <th>Projekt határideje</th>
                <th>Projekt vezető</th>
                <th>Alkalmazott</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>';
    while ($row = mysqli_fetch_array($result_not_started_with_leader)) {
        echo '<tr>
            <form action="" method="post">
                    <td>' . $row['project_id'] . '</td>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>
                    <td>' . $row['username'] . '</td>';
        $query_employee = "SELECT user_id, username
                            FROM user
                            WHERE access_level = 'employee';";
        $result_employee = mysqli_query($link, $query_employee);

        if (mysqli_num_rows($result_employee) > 0) {
            // ? funkcionális elvárás: Fontos, hogy a felületen az adatok elérése a felhasználó számára kényelmes módon történjen.
            // legordulo menu, az adatbázisban lévo alkalmazottak nevével
            echo '<td><select name="assign" class="form-select">';
            while ($row_employee = mysqli_fetch_array($result_employee)) {
                echo '<option value="' . $row_employee['user_id'] . '_' . $row['project_id'] . '">' . $row_employee['username'] . '</option>';
            }
            // fa-pen-to-square: edit/módosítás ikon
            // fa-trash: kuka/torlés ikon
            // padding-right: 20px -> a kiosztás és feladatok gomb elkulonítése
            echo '  </select></td><td style="padding-right:20px">
                        <input type="submit" name="submit_assign_to_employee" value="Kiosztás" class="button" style="font-size:1rem">
                        </td>
                        </form>
                        <td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>
                        <td><a href="projects_edit_admin.php?status=not_started&has_leader=true&project_id=' . $row['project_id'] . '&leader_id=' . $row['user_id'] . '"> <i
                        class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                        <td><a href="projects_delete.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                        style="color: ' . $color_code . ';"></i> </a></td>
                        </tr>';
        }
    }
    echo '</table></div>';
}
// Folyamatban lévő projektek
$query_in_progress_employee = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.user_id
                                FROM user 
                                INNER JOIN user_has_project ON user_has_project.user_id = user.user_id 
                                INNER JOIN project ON project.project_id = user_has_project.project_id
                                WHERE project.status = 'in_progress'
                                AND user.access_level = 'employee'
                                ORDER BY due_date;";
$result_in_progress_employee = mysqli_query($link, $query_in_progress_employee);
if (mysqli_num_rows($result_in_progress_employee) > 0) {
    echo '<div class="table-container"><table class="table-hover table">
            <caption><h4>Folyamatban lévő projektek</h4></caption>
            <th>ID</th>
            <th>Projekt név</th>
            <th>Projekt leírás</th>
            <th>Projekt határideje</th>
            <th>Projekten dolgozik</th>
            <th>Projektet vezeti</th>
            <th></th>
            <th></th>
            <th></th>';
    while ($row = mysqli_fetch_array($result_in_progress_employee)) {
        echo '<tr>
        <form action="" method="post">
                <td>' . $row['project_id'] . '</td>
                <td>' . $row['title'] . '</td>
                <td>' . $row['description'] . '</td>
                <td>' . $row['due_date'] . '</td>
                <td>' . $row['username'] . '</td>';
        // ? funkcionális elvárás: Legyen lehetőség az adatbázistáblák közötti külső kulcs kapcsolatok megjelenítésére, szerkesztésére és törlésére.
        $project = $row['project_id'];
        $query_in_progress_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.user_id
                                        FROM user 
                                        INNER JOIN user_has_project ON user_has_project.user_id = user.user_id 
                                        INNER JOIN project ON project.project_id = user_has_project.project_id
                                        WHERE project.status = 'in_progress'
                                        AND user.access_level = 'project_lead'
                                        AND project.project_id = '$project';";
        $result_in_progress_leader = mysqli_query($link, $query_in_progress_leader);
        if (mysqli_num_rows($result_in_progress_leader) == 0) {
            echo '<td> </td>';
        } else {
            $row_lead = mysqli_fetch_array($result_in_progress_leader);
            echo '<td>' . $row_lead['username'] . '</td>';
        }
        echo '<td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>';
        // fa-pen-to-square: edit/módosítás ikon
        // fa-trash: kuka/torlés ikon
        echo '<td><a href="projects_edit_admin.php?status=in_progress&has_leader=true&project_id=' . $row['project_id'] . '&leader_id=' . $row_lead['user_id'] . '"> <i
                class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                <td><a href="projects_delete.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                style="color: ' . $color_code . ';"></i> </a></td>';
    }
    echo '</table></div>';
}
// Projekt archívum
$query_finished = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username
                            FROM project 
                            LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                            LEFT JOIN user ON user_has_project.user_id = user.user_id 
                            WHERE project.status = 'finished'
                            GROUP BY project.project_id
                            ORDER BY due_date;";
$result_finished = mysqli_query($link, $query_finished);
if (mysqli_num_rows($result_finished) > 0) {
    echo '<div class="table-container"><table  class="table-hover table">
            <caption><h4>Projekt archívum</h4></caption>
            <th>ID</th>
            <th>Projekt név</th>
            <th>Projekt leírás</th>
            <th>Projekt határideje</th>
            <th>Projekten dolgozott</th>
            <th>Projektet vezette</th>
            <th></th>
            <th></th>
            <th></th>';
    while ($row = mysqli_fetch_array($result_finished)) {
        echo '<tr>
                <td>' . $row['project_id'] . '</td>
                <td>' . $row['title'] . '</td>
                <td>' . $row['description'] . '</td>
                <td>' . $row['due_date'] . '</td>';
        // ? funkcionális elvárás: Legyen lehetőség az adatbázistáblák közötti külső kulcs kapcsolatok megjelenítésére, szerkesztésére és törlésére.
        $project_finished = $row['project_id'];
        $query_finished_employee = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.user_id
                                            FROM project 
                                            LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                                            LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                            WHERE project.status = 'finished'
                                            AND project.project_id = '$project_finished'
                                            AND user.access_level = 'employee';";
        $result_finished_employee = mysqli_query($link, $query_finished_employee);
        if (mysqli_num_rows($result_finished_employee) == 0) {
            echo '<td> </td>';
        } else {
            $row_employee = mysqli_fetch_array($result_finished_employee);
            echo '<td>' . $row_employee['username'] . '</td>';
        }
        $query_finished_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.user_id
                                    FROM project 
                                    LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id
                                    LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                    WHERE project.status = 'finished'
                                    AND project.project_id = '$project_finished'
                                    AND user.access_level = 'project_lead';";
        $result_finished_leader = mysqli_query($link, $query_finished_leader);
        if (mysqli_num_rows($result_finished_leader) == 0) {
            echo '<td> </td>';
        } else {
            $row_leader = mysqli_fetch_array($result_finished_leader);
            echo '<td>' . $row_leader['username'] . '</td>';
        }
        echo '<td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>';
        // fa-pen-to-square: edit/módosítás ikon
        // fa-trash: kuka/torlés ikon
        echo '<td><a href="projects_edit_admin.php?status=finished&has_leader=';
        if (mysqli_num_rows($result_finished_leader) != 0) {
            echo 'true';
        } else
            echo 'false';
        echo '&project_id=' . $row['project_id'] . '';
        if (mysqli_num_rows($result_finished_leader) != 0) {
            echo '&leader_id=' . $row_leader['user_id'] . '">';
        } else
            echo '"';

        echo ' <i class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                <td><a href="projects_delete.php?status=finished&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                style="color: ' . $color_code . ';"></i> </a></td>';
    }
    echo '</table></div>';
}

closeDB($link);
?>
<br>