<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} else {
    header('Location: index.php');
}

if (isset($_POST['submit'])) {
    @include 'connection.php';
    $link = connectDB();
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    $query = "SELECT * FROM user WHERE email = '$email'";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_db, $username_db, $password_db, $email_db, $dark_mode_db, $access_level_db);
    mysqli_stmt_fetch($stmt);
    closeDB($link);
    if (password_verify($password, $password_db)) {
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
?>

<?php $on_login_page = true; // ez a változó jelzi a menu-nek, hogy ne írja ki a bejelnetkezést a jobb felso sarokba
include 'menu.php' ?>
<div class="form-container">
    <form action="" method="post">
        <h3>Bejelentkezés</h3>
        <?php
        if (isset($error)) {
            foreach ($error as $error) {
                echo '<span class="message error">' . $error . '</span>';
            }
        }
        ?>
        <label for="email">Email: </label>
        <input class="form-control" type="email" name="email" required>
        <label for="password">Jelszó: </label>
        <input class="form-control" type="password" name="password" required>
        <input type="submit" name="submit" value="Bejelentkezés" class="form-btn">
        <p>Nincs még felhasználód? <a href="register.php">Regisztrálj most!</a></p>
    </form>

</div>
<?php include 'footer.php'; ?>