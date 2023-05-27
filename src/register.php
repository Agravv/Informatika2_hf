<?php
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_POST['submit'])) {
    @include 'connection.php';
    $link = connectDB();
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    // ? Pontozási szempontok: Felhasználó kezelés jelszóval (nem plain textben tárolva): 10p
    $hashed_password = password_hash(mysqli_real_escape_string($link, $_POST['password']), PASSWORD_DEFAULT);
    $query_email = "SELECT * FROM user WHERE email = '$email'";
    $result_email = mysqli_query($link, $query_email);
    $query_username = "SELECT * FROM user WHERE username = '$username'";
    $result_username = mysqli_query($link, $query_username);

    // ? funckionális elvárás: Legalább két, nem triviális reguláris kifejezés használata: 5p
    // Kell tartalmaznia >1 számot (0-9), >1 nagybetut, >1 kisbetut és >6 karakter hosszú
    $password_regex = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})$/';

    // csak betuket, szamokat és ._ karaktereket tartalmazhat. A speciális karakterek nem kovethetik egymást illetve nem lehetnek a string elején vagy végén
    // azért 50 a maximális hossz, mert ennyi fér el az adatbázisban
    $username_regex = '/^(?=[a-zA-Z0-9ÁÉÍÓÖŐÚÜŰáéíóöőúüű._]{4,50}$)(?!.*[_.]{2})[^_.].*[^_.]$/';
    // hibakezelés
    if (mysqli_num_rows($result_email) > 0) {
        $error[] = 'Ez az email cím már használatban van.';
    } else if (mysqli_num_rows($result_username) > 0) {
        $error[] = 'Ez a felhasználónév már foglalt. Adjon meg másikat.';
    } else if (mysqli_real_escape_string($link, $_POST['password']) != mysqli_real_escape_string($link, $_POST['cpassword'])) {
        $error[] = 'A jelszavak nem egyeznek. Próbálja újra.';
    } else if (!preg_match($password_regex, mysqli_real_escape_string($link, $_POST['password']))) {
        $regex_error[] = 'A jelszónak tartalmaznia kell legalább 6 karakter, kisbetűt, nagybetűt és számot!';
    } else if (!preg_match($username_regex, mysqli_real_escape_string($link, $_POST['username']))) {
        $regex_error[] = 'A felhasználónévnek legalább 4 karaktert tartalmaznia melyek: betűk, számok és . _ !<br>A speciális karakterek nem követhetik egymást és nem lehetnek a név elején vagy végén!';
    } else {
        $insert = "INSERT INTO user (username,password,email) VALUES('$username','$hashed_password','$email')";
        mysqli_query($link, $insert);
        closeDB($link);
        header('Location: index.php');
        exit;
    }
    closeDB($link);
}
$on_login_page = true; // ez a változó jelzi a menu-nek, hogy ne írja ki a bejelnetkezést a jobb felso sarokba
include 'menu.php' ?>
<div class="form-container">
    <form action="" method="post">
        <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
        <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
        <!-- // ? felhasználónak. -->
        <h3>Regisztrálj most!</h3>
        <?php
        if (isset($error)) {
            foreach ($error as $error) {
                echo '<span class="message error">' . $error . '</span>';
            }
        }
        ?>
        <?php
        if (isset($regex_error)) {
            foreach ($regex_error as $regex_error) {
                echo '<span class="message regex-error">' . $regex_error . '</span>';
            }
        }
        ?>
        <label for="username">Felhasználónév:</label>
        <input class="form-control" type="text" name="username" required>
        <label for="email">Email cím:</label>
        <input class="form-control" type="email" name="email" required>
        <label for="password">Jelszó:</label>
        <input class="form-control" type="password" name="password" required>
        <label for="cpassword">Jelszó megerősítés:</label>
        <input class="form-control" type="password" name="cpassword" required>
        <input type="submit" name="submit" value="Regisztráció" class="form-btn">
        <p>Van már felhasználód? <a href="login.php">Jelentkezz be!</a></p>
    </form>
</div>
<?php include 'footer.php'; ?>