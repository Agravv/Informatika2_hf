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
$task_id_get = mysqli_real_escape_string($link, $_GET['task_id_get']);
$project_id_get = mysqli_real_escape_string($link, $_GET['project_id_get']);
$input_is_empty = true;
$edited_task_id = $task_id_get;
$result_select_task = mysqli_query($link, "SELECT * FROM task WHERE task_id = '$task_id_get';");
$current_task = mysqli_fetch_array($result_select_task);
if (isset($_POST['submit_edit'])) {
    $edited_update_query = "UPDATE task SET";
    if (isset($_POST['edited_task_id']) && trim($_POST['edited_task_id']) !== "") {
        $edited_task_id = mysqli_real_escape_string($link, $_POST['edited_task_id']);
        $current_tasks = mysqli_query($link, "SELECT task_id FROM task WHERE task_id = '$edited_task_id';");
        $edited_update_query .= " task_id = '$edited_task_id',";
        $input_is_empty = false;
    }
    if ((isset($_POST['edited_tasks_id']) && trim($_POST['edited_tasks_id']) !== "") && (mysqli_num_rows($current_tasks) != 0)) {
        $already_in_use_error[] = 'Az ID már foglalt';
    } else {
        if (isset($_POST['edited_task_name']) && trim($_POST['edited_task_name']) !== "") {
            $edited_task_name = mysqli_real_escape_string($link, $_POST['edited_task_name']);
            $edited_update_query .= " name = '$edited_task_name',";
            $input_is_empty = false;
        }
        if (isset($_POST['edited_task_description']) && trim($_POST['edited_task_description']) !== "") {
            $edited_task_description = mysqli_real_escape_string($link, $_POST['edited_task_description']);
            $edited_update_query .= " description = '$edited_task_description',";
            $input_is_empty = false;
        }
        if (isset($_POST['edited_assign_project']) && trim($_POST['edited_assign_project']) !== "") {
            $edited_assign_project = mysqli_real_escape_string($link, $_POST['edited_assign_project']);
            $edited_update_query .= " project_project_id = '$edited_assign_project',";
            $input_is_empty = false;
        }
    }
    if (!isset($already_in_use_error) && !$input_is_empty) {
        $edited_update_query[-1] = " ";
        $edited_update_query .= " WHERE task_id = '$task_id_get';";
        mysqli_query($link, $edited_update_query);
        header('Location: projects_task.php?project_id=' . $project_id_get . '');
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
        <h3>
            <?= $current_task['name'] ?>
        </h3>
        <hr>
        <h4>Feladat adatainak módosítása</h4>
        <?php
        if (isset($error)) {
            foreach ($error as $error) {
                echo '<span class="message error">' . $error . '</span>';
            }
            echo '<br>';
        }
        ?>

        <label for="edited_task_id">Új feladat ID: </label>
        <input type="number" class="form-control" name="edited_task_id">

        <label for="edited_task_name">Új feladat név: </label>
        <input type="text" class="form-control" name="edited_task_name">

        <label for="edited_task_description">Új feladat leírás: </label>
        <input type="text" class="form-control" name="edited_task_description">

        <label for="edited_assign_project">Hozzárendelés projekthez: </label>
        <select name="edited_assign_project" class="form-select">
            <?php
            if ($_SESSION['access_level'] == 'project_lead') {
                $query_select_project = "SELECT * FROM project 
                            INNER JOIN user_has_project ON user_has_project.project_id = project.project_id
                            INNER JOIN user ON user.user_id = user_has_project.user_id
                            WHERE user.user_id = '$session_user_id'
                            ORDER BY project.project_id";
            } else if ($_SESSION['access_level'] == 'admin') {
                $query_select_project = "SELECT * FROM project ORDER BY project_id;";
            }
            $result_select_project = mysqli_query($link, $query_select_project);
            while ($row = mysqli_fetch_array($result_select_project)) {
                echo '<option value="' . $row['project_id'] . '"';
                if ($project_id_get == $row['project_id']) {
                    echo 'selected';
                }
                echo '>(' . $row['project_id'] . ') ' . $row['title'] . '</option>';
            }
            echo '</select>';
            ?>
            <input type="submit" name="submit_edit" value="Módosítás" class="form-btn">
    </form>
</div>
<?php include 'footer.php';
closeDB($link);
?>