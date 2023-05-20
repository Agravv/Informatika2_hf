<?php
@include 'connection.php';
$link = connectDB();
$id = $_SESSION['id'];
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
        $query_insert = "INSERT INTO user_has_project (user_has_project.user_id,user_has_project.project_project_id) 
                            VALUES ('$assign_to_employee','$assign_project')";
        $query_update = "UPDATE project 
                            SET project.status = 'in_progress' 
                            WHERE project_id = '$assign_project'";
        mysqli_query($link, $query_insert);
        mysqli_query($link, $query_update);

        mysqli_commit($link);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($link);
        throw $exception;
    }
}
if (isset($_POST['submit_create'])) {
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
                                    VALUES ('$new_id','$new_title','$new_description','$new_due_date')";
        $insert_id = mysqli_insert_id($link);
        $query_insert_connection = "INSERT INTO user_has_project (project_project_id,user_id)
                                    VALUES ('$new_id','$insert_id');";
        mysqli_query($link, $query_insert_project);
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
                        LEFT JOIN user_has_project ON user_has_project.project_project_id = project_id
                        LEFT JOIN user ON user.user_id = user_has_project.user_id
                        WHERE project.status = 'not_started'
                        AND user.user_id = '$id'
                        ORDER BY due_date;";
$result_not_started = mysqli_query($link, $query_not_started);
if (mysqli_num_rows($result_not_started) > 0) {
    echo '<div class="table-container"><table class="user-table table-hover table">
                <caption><h4>Kiosztásra váró projektek</h4></caption><tr>
                <th>Projekt ID</th>
                <th>Projekt név</th>
                <th>Projekt leírás</th>
                <th>Projekt határideje</th>
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
            // ? mi van itt?
            echo '<td><select name="assign_employee" class="form-select">
                    <option value="empty" selected></option>';
            while ($row_employee = mysqli_fetch_array($result_all_employee)) {
                echo '<option value="' . $row_employee['user_id'] . '_' . $row['project_id'] . '">' . $row_employee['username'] . '</option>';
            }
            echo '  </select></td>';

        }
        echo '</select></td><td>
                    <input type="submit" name="submit_assign" value="Kiosztás" class="button" style="font-size:1rem">
                    </td>
                    </form>
                <td><a href="edit_projects.php?status=not_started&project_id=' . $row['project_id'] . '"> <i
                class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                <td><a href="delete_projects.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                style="color: ' . $color_code . ';"></i> </a></td>
                </tr>';
    }
    echo '</table></div>';
}
// Folyamatban lévő projektek
$query_in_progress_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.access_level
                                FROM project
                                LEFT JOIN user_has_project ON project.project_id = user_has_project.project_project_id
                                LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                WHERE project.status = 'in_progress'
                                AND access_level = 'project_lead'
                                AND user.user_id = '$id'
                                ORDER BY due_date;";
$result_in_progress_leader = mysqli_query($link, $query_in_progress_leader);
if (mysqli_num_rows($result_in_progress_leader) > 0) {
    echo '<div class="table-container"><table class="user-table table-hover table">
            <caption><h4>Folyamatban lévő projektek</h4></caption><tr>
            <th>Projekt ID</th>
            <th>Projekt név</th>
            <th>Projekt leírás</th>
            <th>Projekt határideje</th>
            <th>Projekten dolgozik</th>
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
        $temp_project_id = $row['project_id'];
        $query_in_progress_employee = "SELECT project.project_id,project.status, user.username, user.access_level
                                    FROM project 
                                    LEFT JOIN user_has_project ON user_has_project.project_project_id = project_id
                                    LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                    WHERE project.status = 'in_progress'
                                    AND access_level = 'employee'
                                    AND project_id = '$temp_project_id';";
        $result_in_progress_employee = mysqli_query($link, $query_in_progress_employee);
        if (mysqli_num_rows($result_in_progress_employee) > 0) {
            $row_employee = mysqli_fetch_array($result_in_progress_employee);
            echo '<td>' . $row_employee['username'] . '</td>';
        } else {
            echo '<td> </td>';
        }
        echo '<td><a href="edit_projects.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i
                class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                <td><a href="delete_projects.php?status=in_progress&project_id=' . $row['project_id'] . '"> <i class="fa-solid fa-trash"
                style="color: ' . $color_code . ';"></i> </a></td></tr>';
    }
    echo '</table></div>';
}
// Projekt archívum
$query_finished_leader = "SELECT project.project_id,project.title,project.description,project.due_date,project.status, user.username, user.access_level
                            FROM project 
                            LEFT JOIN user_has_project ON user_has_project.project_project_id = project_id
                            LEFT JOIN user ON user_has_project.user_id = user.user_id 
                            WHERE project.status = 'finished'
                            AND access_level = 'project_lead'
                            AND user.user_id = '$id'
                            ORDER BY due_date;";
$result_finished_leader = mysqli_query($link, $query_finished_leader);
if (mysqli_num_rows($result_finished_leader) > 0) {
    echo '<div class="table-container"><table  class="user-table table-hover table">
            <caption><h4>Projekt archívum</h4></caption><tr>
            <th>Projekt ID</th>
            <th>Projekt név</th>
            <th>Projekt leírás</th>
            <th>Projekt határideje</th>
            <th>Projekten dolgozott</th>';
    echo '</tr>';
    while ($row = mysqli_fetch_array($result_finished_leader)) {
        echo '<tr>
                <form action="" method="post">
                <td>' . $row['project_id'] . '</td>
                <td>' . $row['title'] . '</td>
                <td>' . $row['description'] . '</td>
                <td>' . $row['due_date'] . '</td>';
        // <td>' . $row['username'] . '</td>';
        $temp_project_id = $row['project_id'];
        $query_finished_employee = "SELECT project.project_id,project.status, user.username, user.access_level
                                    FROM project 
                                    LEFT JOIN user_has_project ON user_has_project.project_project_id = project_id
                                    LEFT JOIN user ON user_has_project.user_id = user.user_id 
                                    WHERE project.status = 'finished'
                                    AND access_level = 'employee'
                                    AND project_id = '$temp_project_id';";
        $result_finished_employee = mysqli_query($link, $query_finished_employee);
        if (mysqli_num_rows($result_finished_employee) > 0) {
            $row_employee = mysqli_fetch_array($result_finished_employee);
            echo '<td>' . $row_employee['username'] . '</td>';
        } else {
            echo '<td> </td>';
        }
        echo '</tr>';
    }
    echo '</table></div>';
}

closeDB($link);
?>
<br>