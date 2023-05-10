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
if (isset($_POST['submit_new_user'])) {
    $password_regex = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$/';
    $username_regex = '/^(?=[a-zA-Z0-9._]{4,50}$)(?!.*[_.]{2})[^_.].*[^_.]$/';

    $new_username = mysqli_real_escape_string($link, $_POST['new_username']);
    if (isset($_POST['new_username'])) {
        $query_username_already_used = "SELECT * FROM user WHERE username = '$new_username'";
        $result = mysqli_query($link, $query_username_already_used);
        if (mysqli_num_rows($result) > 0) {
            $insert_error[] = 'A felhasználónév már foglalt';
        } else if (!preg_match($username_regex, mysqli_real_escape_string($link, $_POST['new_username']))) {
            $insert_error[] = 'A felhasználónévnek legalább 4 karaktert tartalmaznia melyek: betűk, számok és . _ !<br>A speciális karakterek nem követhetik egymást és nem lehetnek a név elején vagy végén!';
        }
    } else {
        $insert_error[] = 'Adjon meg felhasználó nevet!';
    }
    $new_email = mysqli_real_escape_string($link, $_POST['new_email']);
    if (isset($_POST['new_email'])) {
        $query_email_already_used = "SELECT * FROM user WHERE email = '$new_email'";
        $result = mysqli_query($link, $query_email_already_used);
        if (mysqli_num_rows($result) > 0) {
            $insert_error[] = 'Az email cím már foglalt';
        }
    } else {
        $insert_error[] = 'Adjon meg email címet!';
    }
    $new_user_id = mysqli_real_escape_string($link, $_POST['new_user_id']);
    if (isset($_POST['new_user_id'])) {
        $query_user_id_already_used = "SELECT * FROM user WHERE user_id = '$new_user_id'";
        $result = mysqli_query($link, $query_user_id_already_used);
        if (mysqli_num_rows($result) > 0) {
            $insert_error[] = 'Az ID már foglalt';
        }
    } else {
        $insert_error[] = 'Adjon meg ID-t!';
    }
    $new_dark_mode = mysqli_real_escape_string($link, $_POST['new_dark_mode_select']);
    $new_access_level = mysqli_real_escape_string($link, $_POST['new_access_level_select']);
    if (isset($_POST['new_password']) && isset($_POST['new_cpassword'])) {
        if (mysqli_real_escape_string($link, $_POST['new_password']) != mysqli_real_escape_string($link, $_POST['new_cpassword'])) {
            $insert_error[] = 'A jelszavak nem egyeznek meg';
        } else if (!preg_match($password_regex, mysqli_real_escape_string($link, $_POST['new_password']))) {
            $insert_error[] = 'A jelszónak tartalmaznia kell legalább 6 karakter, kisbetűt, nagybetűt és számot!';
        }
    } else {
        $insert_error[] = 'Adjon meg jelszót';
    }

    $new_hashed_password = password_hash(mysqli_real_escape_string($link, $_POST['new_password']), PASSWORD_DEFAULT);
    if (!isset($insert_error)) {
        $new_query = "INSERT INTO user (user_id,username,password,email,dark_mode,access_level)
                        VALUES ('$new_user_id', '$new_username','$new_hashed_password','$new_email', '$new_dark_mode', '$new_access_level')";
        mysqli_query($link, $new_query);
        header('Location: showDB.php');
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
    <form action="" method="post">
        <h3>Új felhasználó adatainak bevitele</h3>
        <input type="number" name="new_user_id" required placeholder="ID">
        <input type="text" name="new_username" required placeholder="Felhasználónév">
        <input type="email" name="new_email" required placeholder="Email cím">
        <input type="password" name="new_password" required placeholder="Jelszó">
        <input type="password" name="new_cpassword" required placeholder="Új jelszó">
        <select name="new_dark_mode_select" id="dark_mode_select">
            <option value="Dark">Sötét mód</option>
            <option value="Light">Világos mód</option>
        </select>
        <select name="new_access_level_select" id="new_access_level_select">
            <option value="guest">Vendég</option>
            <option value="admin">Admin</option>
            <option value="employee">Alkalmazott</option>
            <option value="project_lead">Projekt vezető</option>
        </select>
        <input type="submit" name="submit_new_user" value="Felhasználó hozzáadása" class="form-btn">

    </form>
    <?php
    if (isset($insert_error)) {
        foreach ($insert_error as $insert_error) {
            echo '<span class="error-msg">' . $insert_error . '</span>';
        }
        ;
    }
    ;
    ?>
    <?php include 'footer.php' ?>
</body>

</html>