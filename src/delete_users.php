<?php
if (isset($_GET['id'])) {
    @include 'connection.php';
    $link = connectDB();
    $user_id = mysqli_real_escape_string($link, $_GET['id']);
    $eredmenyTemp = mysqli_query($link, "SELECT user_id FROM user WHERE user_id = '$user_id'");
    if (mysqli_num_rows($eredmenyTemp) == 0) {
        mysqli_close($link);
        header("Location: users.php?info=delete_failed");
        exit;
    }
    mysqli_query($link, "DELETE FROM user WHERE user_id = '$user_id'");
    mysqli_close($link);
    header("Location: users.php?info=delete_success");
    exit;
}
header("Location: users.php?info=delete_failed");
?>