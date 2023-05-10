<?php
@include 'connection.php';
$link = connectDB();
if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    $query = "SELECT * FROM user WHERE email = '$email'";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_db, $username_db, $password_db, $email_db, $dark_mode_db, $access_level_db);
    mysqli_stmt_fetch($stmt);
    if (password_verify($password, $password_db)) {
        session_start();
        $_SESSION['username'] = $username_db;
        $_SESSION['id'] = $id_db;
        $_SESSION['email'] = $email_db;
        $_SESSION['access_level'] = $access_level_db;
        $_SESSION['dark_mode'] = $dark_mode_db;
        $_SESSION['password'] = $password_db;
        $_SESSION['loggedin'] = true;
        header('Location: index.php');
    } else {
        $error[] = 'Hibás email cím vagy jelszó!';
    }
}
closeDB($link);
?>

<!doctype HTML>
<html lang="hu">

<head>
    <title>Login form</title>
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

    <?php $on_login_page = true;
    include 'menu.php' ?>
    <div class="form-container">
        <form action="" method="post">
            <h3>Bejelentkezés</h3>
            <?php
            if (isset($error)) {
                foreach ($error as $error) {
                    echo '<span class="error-msg">' . $error . '</span>';
                }
            }
            ?>
            <input type="email" name="email" required placeholder="Email">
            <input type="password" name="password" required placeholder="Jelszó">
            <input type="submit" name="submit" value="Bejelentkezés" class="form-btn">
            <p>Nincs még felhasználód? <a href="register.php">Regisztrálj most!</a></p>
        </form>

    </div>
    <?php include 'footer.php'; ?>
</body>

</html>