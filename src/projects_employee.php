<?php
@include 'connection.php';
$link = connectDB();
$session_user_id = mysqli_real_escape_string($link, $_SESSION['id']);
$query_in_progress = "SELECT project.project_id,project.title,project.description,project.due_date,project.status 
                        FROM user 
                        INNER JOIN user_has_project ON user_has_project.user_id = user.user_id 
                        INNER JOIN project ON project.project_id = user_has_project.project_id
                        WHERE user.user_id = $session_user_id 
                        AND project.status = 'in_progress'
                        GROUP BY project.project_id
                        ORDER BY due_date;";
$result_in_progress = mysqli_query($link, $query_in_progress);
$query_finished = "SELECT project.project_id,project.title,project.description,project.due_date,project.status 
                    FROM user 
                    INNER JOIN user_has_project ON user_has_project.user_id = user.user_id 
                    INNER JOIN project ON project.project_id = user_has_project.project_id
                    WHERE user.user_id = $session_user_id 
                    AND project.status = 'finished'
                    GROUP BY project.project_id
                    ORDER BY due_date;";
$result_finished = mysqli_query($link, $query_finished);
closeDB($link);

// Saját projektek
if (mysqli_num_rows($result_in_progress) > 0) {
    echo '<div class="table-container">
            <table class="table-hover table">
                <caption><h4>Saját projektek</h4></caption>
                <tr>
                    <th>Project neve</th>
                    <th>Leírás</th>
                    <th>Határidő</th>
                    <th></th>
                    <th></th>
                </tr>';
    while ($row = mysqli_fetch_array($result_in_progress)) {
        echo '<tr>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>
                    <td><button type="button" class="button"><a href="projects_helper.php?action=finish&project_id=' . $row["project_id"] . '">Lead</a></button></td>
                    <td><button type="button" class="button"><a href="projects_task.php?project_id=' . $row["project_id"] . '">Feladatok</a></button></td>
                    </tr>';
    }
    echo '</table></div>';
} else {
    echo '<div class="msg">
            <h3>Jelenleg nincsenek kiosztott projektjei!</h3>
            </div>';
}
?>

<?php
// befejezett projektek
if (mysqli_num_rows($result_finished) > 0) {
    echo '<div class="table-container">
                <table class="table-hover table">
                <caption><h4>Befejezett projektek</h4></caption>
                    <tr>
                        <th>Project neve</th>
                        <th>Leírás</th>
                        <th>Határidő</th>
                    </tr>';
    while ($row = mysqli_fetch_array($result_finished)) {
        echo '<tr>
                    <td>' . $row['title'] . '</td>
                    <td>' . $row['description'] . '</td>
                    <td>' . $row['due_date'] . '</td>
                    </tr>';
    }
    echo '</table></div>';
} else {
    echo '<div class="msg">
            <h3>Nincsenek befejezett projektjei!</h3>
            </div>';
}
?>