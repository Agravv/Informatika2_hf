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

if (isset($_POST['submit_new_pw'])) {
    $new_password = mysqli_real_escape_string($link, $_POST['new_password']);
    $new_cpassword = mysqli_real_escape_string($link, $_POST['new_cpassword']);
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    if (password_verify($new_cpassword, $hashed_new_password)) {
        $temp_id = $_SESSION['id'];
        $query = "UPDATE user SET password = '$hashed_new_password' WHERE user_id = '$temp_id'";
        mysqli_query($link, $query);
        $success['pwchange'] = 'Sikeres jelszó változtatás';
        header('Location: profile.php');
    } else {
        $error['pwmatch'] = 'Jelszavak nem egyeznek meg';
        header('Location: profile.php');
    }
}

if (isset($_POST['delete'])) {
    $temp_id = $_SESSION["id"];
    $query = "DELETE FROM user WHERE user_id = '$temp_id'";
    mysqli_query($link, $query);
    $query = "SELECT username FROM user WHERE user_id = '$temp_id'";
    $result = mysqli_query($link, $query);
    if (mysqli_num_rows($result) > 0) {
        $error['delete'] = 'Hiba történt törlés közben';
        header('Location: profile.php');
    } else {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
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
    <div>
        <h1>Profil</h1>
        <h2>Jelszó megváltoztatása</h2>
        <div class="form-container-prof">
            <form action="" method="post">
                <?php
                if (isset($error)) {
                    foreach ($error as $error) {
                        echo '<span class="error-msg">' . $error['pwmatch'] . '</span>';
                    }
                }
                if (isset($success)) {
                    foreach ($success as $success) {
                        echo '<span class="error-msg">' . $success['pwchange'] . '</span>';
                    }
                }
                ?>
                <input type="password" name="new_password" required placeholder="Új jelsó">
                <input type="password" name="new_cpassword" required placeholder="Új jelszó még egyszer">
                <input type="submit" name="submit_new_pw" value="Jelszó megváltoztatása" class="form-btn">
            </form>
        </div>
        <h2>Felhasználó törlése</h2>
        <div class="form-container-prof">
            <form action="" method="post">
                <input type="submit" name="delete" value="Felhasználó törlése" class="form-btn">
            </form>
        </div>
    </div>

    <?php include 'footer.php' ?>
</body>

</html>