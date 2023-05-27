<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} else {
    header('Location: index.php');
}
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_POST['submit'])) {
    @include 'connection.php';
    $link = connectDB();
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    $login_query = "SELECT * FROM user WHERE email = '$email'";
    $result = mysqli_query($link, $login_query);
    $row = mysqli_fetch_array($result);
    closeDB($link);
    // ? Pontozási szempontok: Felhasználó kezelés jelszóval (nem plain textben tárolva): 10p
    if (isset($row['password']) && password_verify($password, $row['password'])) {
        $_SESSION['username'] = $row['username'];
        $_SESSION['id'] = $row['user_id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['access_level'] = $row['access_level'];
        $_SESSION['dark_mode'] = $row['dark_mode'];
        $_SESSION['password'] = $row['password'];
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
    <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
    <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
    <!-- // ? felhasználónak. -->
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