<?php
if (isset($_GET['id'])) {
    @include 'connection.php';
    $link = connectDB();
    $id = $_GET['id'];
    $eredmenyTemp = mysqli_query($link, "SELECT user_id FROM user WHERE user_id = " . mysqli_real_escape_string($link, $id));
    if (mysqli_num_rows($eredmenyTemp) == 0) {
        mysqli_close($link);
        header("Location: showDB.php?info=delete_failed");
        exit;
    }

    $query = "DELETE FROM user WHERE user_id = " . mysqli_real_escape_string($link, $id);

    mysqli_query($link, $query);
    mysqli_close($link);
    header("Location: showDB.php?info=delete_success");
    exit;
}
header("Location: showDB.php?info=delete_failed");
?>