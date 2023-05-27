<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true || $_SESSION['access_level'] != 'admin') {
    header('Location: index.php');
    exit;
}

@include 'connection.php';
$link = connectDB();
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_POST['submit_new_user'])) {
    $password_regex = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})$/';
    $username_regex = '/^(?=[a-zA-Z0-9ÁÉÍÓÖŐÚÜŰáéíóöőúüű._]{4,50}$)(?!.*[_.]{2})[^_.].*[^_.]$/';

    if (isset($_POST['new_username']) && trim($_POST['new_username']) !== "") {
        $new_username = mysqli_real_escape_string($link, $_POST['new_username']);
        $query_username_already_used = "SELECT * FROM user WHERE username = '$new_username'";
        // megnézi, hogy foglalt-e már a név
        $result_username_already_used = mysqli_query($link, $query_username_already_used);
        if (mysqli_num_rows($result_username_already_used) > 0) {
            $insert_error[] = 'A felhasználónév már foglalt';
        } else if (!preg_match($username_regex, mysqli_real_escape_string($link, $_POST['new_username']))) {
            $insert_regex_error[] = 'A felhasználónévnek legalább 4 karaktert tartalmaznia melyek: betűk, számok és . _ !<br>A speciális karakterek nem követhetik egymást és nem lehetnek a név elején vagy végén!';
        }
    } else {
        $insert_error[] = 'Adjon meg felhasználó nevet!';
    }
    if (isset($_POST['new_email']) && trim($_POST['new_email']) !== "") {
        $new_email = mysqli_real_escape_string($link, $_POST['new_email']);
        $query_email_already_used = "SELECT * FROM user WHERE email = '$new_email'";
        // megnézi, hogy foglalt-e az email
        $result_email_already_used = mysqli_query($link, $query_email_already_used);
        if (mysqli_num_rows($result_email_already_used) > 0) {
            $insert_error[] = 'Az email cím már foglalt';
        }
    } else {
        $insert_error[] = 'Adjon meg email címet!';
    }
    if (isset($_POST['new_user_id']) && trim($_POST['new_user_id']) !== "") {
        $new_user_id = mysqli_real_escape_string($link, $_POST['new_user_id']);
        $query_user_id_already_used = "SELECT * FROM user WHERE user_id = '$new_user_id'";
        // megnézi, hogy foglalt-e az ID
        $result_user_id_already_used = mysqli_query($link, $query_user_id_already_used);
        if (mysqli_num_rows($result_user_id_already_used) > 0) {
            $insert_error[] = 'Az ID már foglalt';
        }
    }
    $new_dark_mode = mysqli_real_escape_string($link, $_POST['new_dark_mode_select']);
    $new_access_level = mysqli_real_escape_string($link, $_POST['new_access_level_select']);
    if (isset($_POST['new_password']) && trim($_POST['new_password']) !== "") {
        if (mysqli_real_escape_string($link, $_POST['new_password']) != mysqli_real_escape_string($link, $_POST['new_cpassword'])) {
            $insert_error[] = 'A jelszavak nem egyeznek meg';
        } else if (!preg_match($password_regex, mysqli_real_escape_string($link, $_POST['new_password']))) {
            $insert_regex_error[] = 'A jelszónak tartalmaznia kell legalább 6 karakter, kisbetűt, nagybetűt és számot!';
        }
    } else {
        $insert_error[] = 'Adjon meg jelszót';
    }
    // ? Pontozási szempontok: Felhasználó kezelés jelszóval (nem plain textben tárolva): 10p
    $new_hashed_password = password_hash(mysqli_real_escape_string($link, $_POST['new_password']), PASSWORD_DEFAULT);
    if (!isset($insert_error) && !isset($insert_regex_error)) {
        $query_insert_new_user = "INSERT INTO user (user_id,username,password,email,dark_mode,access_level)
                        VALUES ('$new_user_id', '$new_username','$new_hashed_password','$new_email', b'$new_dark_mode', '$new_access_level')";
        mysqli_query($link, $query_insert_new_user);
        header('Location: users.php');
    }
}
closeDB($link);

include 'menu.php' ?>
<div class="form-container">
    <form action="" method="post">
        <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
        <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
        <!-- // ? felhasználónak. -->
        <h3>Új felhasználó adatai</h3>
        <?php
        if (isset($insert_error)) {
            foreach ($insert_error as $insert_error) {
                echo '<span class="message error">' . $insert_error . '</span>';
            }
        }
        if (isset($insert_regex_error)) {
            foreach ($insert_regex_error as $insert_regex_error) {
                echo '<span class="message regex-error">' . $insert_regex_error . '</span>';
            }
        }
        ?>
        <label for="new_user_id">ID: </label>
        <input class="form-control" type="number" name="new_user_id" value="NULL">
        <label for="new_username">Felhasználónév: </label>
        <input class="form-control" type="text" name="new_username" required>
        <label for="new_email">Email cím: </label>
        <input class="form-control" type="email" name="new_email" required>
        <label for="new_password">Jelszó: </label>
        <input class="form-control" type="password" name="new_password" required>
        <label for="new_cpassword">Jelszó megerősítés: </label>
        <input class="form-control" type="password" name="new_cpassword" required>
        <label for="new_dark_mode_select">Mód: </label>
        <select class="form-select" name="new_dark_mode_select" id="dark_mode_select">
            <option value="1">Sötét</option>
            <option value="0">Világos</option>
        </select>
        <label for="new_access_level_select">Jogosultsági szint: </label>
        <select class="form-select" name="new_access_level_select" id="new_access_level_select">
            <option value="guest">Vendég</option>
            <option value="employee">Alkalmazott</option>
            <option value="project_lead">Projektvezető</option>
            <option value="admin">Admin</option>
        </select>
        <input type="submit" name="submit_new_user" value="Felhasználó hozzáadása" class="form-btn">
    </form>
</div>
<?php include 'footer.php' ?>