<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}
@include 'connection.php';
$link = connectDB();
if (isset($_GET['project_id']) && trim($_GET['project_id']) !== "") {
    $project_id_get = mysqli_real_escape_string($link, $_GET['project_id']);
} else {
    header('Location: projects.php');
}

if (isset($_POST['new_task_submit'])) {
    // nem kotelezo input az ID, ekkor az AUTO_INCREMENT fogja megadni, ehhez kell a NULL
    if (isset($_POST['task_id']) && trim($_POST['task_id']) !== "") {
        $new_id = mysqli_real_escape_string($link, $_POST['task_id']);
    } else {
        $new_id = NULL;
    }
    $new_task_name = mysqli_real_escape_string($link, $_POST['task_name']);
    if (isset($_POST['task_description']) && trim($_POST['task_description']) !== "") {
        $new_task_description = mysqli_real_escape_string($link, $_POST['task_description']);
    } else {
        $new_task_description = "";
    }
    $query_insert_task = "INSERT INTO task (task_id,name,description,project_project_id) 
                        VALUES ('$new_id','$new_task_name','$new_task_description','$project_id_get')";
    mysqli_query($link, $query_insert_task);
}

include 'menu.php';
if ($_SESSION['access_level'] == 'admin' || $_SESSION['access_level'] == 'project_lead') {
    echo '
        <div class="form-container form-container-profile">
            <form action="" method="post">
                <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
                <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
                <!-- // ? felhasználónak. -->
                <h4>Új feladat hozzáadása</h4>
                <label for="task_id">ID: </label>
                <input type="number" name="task_id" class="form-control">
                <label for="task_name">Feladat neve: </label>
                <input type="text" name="task_name" class="form-control" required>
                <label for="task_description">Feladat leírása: </label>
                <input type="text" name="task_description" class="form-control">
                <input type="submit" name="new_task_submit" value="Hozzáadás" class="form-btn">
            </form>
        </div>';
}
$result_select_task = mysqli_query($link, "SELECT * FROM task WHERE project_project_id = '$project_id_get' ORDER BY task_id");
if (mysqli_num_rows($result_select_task) > 0) {
    echo '
    <div class="table-container">
        <table class=" table-hover table">
            <caption>
                <h4>Feladatok</h4>
            </caption>
            <tr>
                <th>ID</th>
                <th>Feladat neve</th>
                <th>Leírás</th>';
    if ($_SESSION['access_level'] == 'admin' || $_SESSION['access_level'] == 'project_lead') {
        echo '<th></th>';

    }
    echo '<th></th>
            </tr>';
    while ($row = mysqli_fetch_array($result_select_task)) {
        echo '<tr>
                    <td>' . $row['task_id'] . '</td>
                    <td>' . $row['name'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>';
        if ($_SESSION['access_level'] == 'admin' || $_SESSION['access_level'] == 'project_lead') {
            echo '<a href="projects_task_edit.php?task_id_get=' . $row['task_id'] . '&project_id_get=' . $project_id_get . '"> <i class="fa-solid fa-pen-to-square" style="color:' . $color_code . ';"></i> </a>
                    </td>
                    <td>
                    <a href="projects_task_delete.php?task_id_get=' . $row['task_id'] . '&project_id_get=' . $project_id_get . '"> <i class="fa-solid fa-trash" style="color:' . $color_code . ';"></i> </a>
                    </td></tr>';
        }
    }
    echo '</table></div>';
} else {
    echo '<div class="msg">
            <h3>A projekthez nincsenek kitűzött feladatok!</h3>
            </div>';
}
?>
<br>
<?php
include 'footer.php';
?>