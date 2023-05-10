<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>

<?php
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}
@include 'connection.php';
$link = connectDB();
$id = $_SESSION['id'];
$query_assigned = "SELECT project.project_id,project.title,project.description,project.due_date,project.status 
                    FROM user 
                    INNER JOIN user_has_project ON user_has_project.user_id = user.user_id 
                    INNER JOIN project ON project.project_id = user_has_project.project_project_id
                    WHERE user.user_id = $id AND project.status = 'not_started'
                    GROUP BY project_id
                    ORDER BY due_date;";
$result_assigned = mysqli_query($link, $query_assigned);
$query_in_progress = "SELECT project.project_id,project.title,project.description,project.due_date,project.status 
                        FROM user 
                        INNER JOIN user_has_project ON user_has_project.user_id = user.user_id 
                        INNER JOIN project ON project.project_id = user_has_project.project_project_id
                        WHERE user.user_id = $id AND project.status = 'in_progress'
                        GROUP BY project_id
                        ORDER BY due_date;";
$result_in_progress = mysqli_query($link, $query_in_progress);
$query_finished = "SELECT project.project_id,project.title,project.description,project.due_date,project.status 
                    FROM user 
                    INNER JOIN user_has_project ON user_has_project.user_id = user.user_id 
                    INNER JOIN project ON project.project_id = user_has_project.project_project_id
                    WHERE user.user_id = $id AND project.status = 'finished'
                    GROUP BY project_id
                    ORDER BY due_date;";
$result_finished = mysqli_query($link, $query_finished);

?>


<!doctype HTML>
<html lang="hu">

<head>
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ"
        crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/59746e632a.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../assets/icon.ico">

</head>

<body class="bg-image">
    <?php include 'menu.php' ?>
    <?php
    if (isset($_GET['info'])) {
        if ($_GET['info'] == 'update_failed') {
            echo 'Hiba tortent';
        }
    }
    ?>
    <h4>Kiosztott feladatok</h4>
    <table>
        <tr>
            <th>Project neve</th>
            <th>Leírás</th>
            <th>Határido</th>
            <th></th>
        </tr>
        <?php
        if (mysqli_num_rows($result_assigned) > 0) {
            while ($row = mysqli_fetch_array($result_assigned)) {
                echo '<tr>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>
                    <td><button type="button"><a href="projects_helper.php?action=accept&project_id=' . $row["project_id"] . '">Felvesz</a></button></td>
                    </tr>';
            }
        }
        ?>
    </table>
    <h4>Felvett feladatok</h4>
    <table>
        <tr>
            <th>Project neve</th>
            <th>Leírás</th>
            <th>Határido</th>
            <th></th>
        </tr>
        <?php
        if (mysqli_num_rows($result_in_progress) > 0) {
            while ($row = mysqli_fetch_array($result_in_progress)) {
                echo '<tr>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>
                    <td><button type="button"><a href="projects_helper.php?action=finish&project_id=' . $row["project_id"] . '">Lead</a></button></td>
                    </tr>';
            }
        }
        ?>
    </table>
    <h4>Elvégzett feladatok</h4>
    <table>
        <tr>
            <th>Project neve</th>
            <th>Leírás</th>
            <th>Határido</th>
            <th></th>
        </tr>
        <?php
        if (mysqli_num_rows($result_finished) > 0) {
            while ($row = mysqli_fetch_array($result_finished)) {
                echo '<tr>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>
                    </tr>';
            }
        }
        ?>
    </table>
    <?php include 'footer.php' ?>
</body>

</html>