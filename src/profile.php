<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}

@include 'connection.php';
$link = connectDB();
$temp_id = $_SESSION["id"];
if (isset($_POST['submit_new_username'])) {
    $username_regex = '/^(?=[a-zA-Z0-9ÁÉÍÓÖŐÚÜŰáéíóöőúüű._]{4,50}$)(?!.*[_.]{2})[^_.].*[^_.]$/';
    $new_username = mysqli_real_escape_string($link, $_POST['new_username']);
    if (!preg_match($username_regex, $new_username)) {
        $error_username[] = "A felhasználónévnek legalább 4 karaktert tartalmaznia melyek: betűk, számok és . _ !<br>A speciális karakterek nem követhetik egymást és nem lehetnek a név elején vagy végén!";
    }
    $check = mysqli_query($link, "SELECT user_id FROM user WHERE username = '$new_username'");
    if (mysqli_num_rows($check) != 0) {
        $error_username[] = "A felhasználónév már foglalt!";
    } else {
        $stmt = mysqli_prepare($link, "UPDATE user SET username = ? WHERE user_id = ? ");
        mysqli_stmt_bind_param($stmt, "ss", $new_username, $temp_id);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) == 0) {
            $error_username[] = "Valami hiba tortent!";
        }
        $_SESSION['username'] = $new_username;
        $success_username[] = "Sikeresen megváltoztatta a felhasználónevét!";
    }
}

if (isset($_POST['submit_new_pw'])) {
    $current_hashed_password = password_hash(mysqli_real_escape_string($link, $_POST['current_password']), PASSWORD_DEFAULT);
    $query = "SELECT password FROM user WHERE user_id = '$temp_id'";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $password_db);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_reset($stmt);
    if (password_verify($_POST['current_password'], $password_db)) {
        $password_regex = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})$/';
        $new_password = mysqli_real_escape_string($link, $_POST['new_password']);
        $new_cpassword = mysqli_real_escape_string($link, $_POST['new_cpassword']);
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        if (preg_match($password_regex, $new_password)) {
            if (password_verify($new_cpassword, $hashed_new_password)) {
                $query = "UPDATE user SET password = '$hashed_new_password' WHERE user_id = '$temp_id'";
                mysqli_query($link, $query);
                $_SESSION['password'] = $hashed_new_password;
                $success_pw[] = 'Sikeres jelszóváltoztatás';
            } else {
                $error_pw[] = 'Jelszavak nem egyeznek meg';
            }
        } else {
            $regex_error[] = 'A jelszónak tartalmaznia kell legalább 6 karakter, kisbetűt, nagybetűt és számot!';
        }
    } else {
        $error_pw[] = 'Hibás jelszó';
    }
}

if (isset($_POST['submit_new_mode'])) {
    $mode = $_POST['radio-button'];
    $query = "UPDATE user SET dark_mode = b'$mode' WHERE user_id = '$temp_id'";
    mysqli_query($link, $query);
    $_SESSION['dark_mode'] = $mode;
}

if (isset($_POST['delete'])) {
    mysqli_begin_transaction($link);
    try {
        // Az in_progress projekteket vissza kell állítani not_startedre
        // a finished projekteken nem kell változtatni - azok user nélkul lesznek megjelenítve
        $query_reset_project = "UPDATE project 
                                INNER JOIN user_has_project ON user_has_project.project_project_id = project_id
                                INNER JOIN user ON user_has_project.user_id = user.user_id
                                SET status = 'not_started' 
                                WHERE user.user_id = '$temp_id'
                                AND status = 'in_progress'";
        $query_user_has_project = "DELETE FROM user_has_project WHERE user_id = '$temp_id'";
        $query_user = "DELETE FROM user WHERE user_id = '$temp_id'";
        mysqli_query($link, $query_reset_project);
        mysqli_query($link, $query_user_has_project);
        mysqli_query($link, $query_user);
        mysqli_commit($link);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($link);
        throw $exception;
    }

    $query = "SELECT username FROM user WHERE user_id = '$temp_id'";
    $result = mysqli_query($link, $query);
    closeDB($link);
    if (mysqli_num_rows($result) > 0) {
        $error_delete[] = 'Hiba történt törlés közben';
        header('Location: profile.php');
    } else {
        session_destroy();
        header('Location: index.php');
    }
}
closeDB($link);
?>
<?php include 'menu.php' ?>
<div class="form-container form-container-profile">
    <h1>Profil</h1>
    <form action="" method="post">
        <h4>Felhasználónév megváltoztatása</h4>
        <?php
        if (isset($error_username)) {
            foreach ($error_username as $error_username) {
                echo '<span class="message error">' . $error_username . '</span>';
            }
        }
        if (isset($success_username)) {
            foreach ($success_username as $success_username) {
                echo '<span class="message success">' . $success_username . '</span>';
            }
        }
        ?>
        <label for="new_username">Új felhasználónév: </label>
        <input class="form-control" type="text" name="new_username" required>
        <input type="submit" name="submit_new_username" value="Felhasználónév módosítása" class="form-btn">
    </form>

    <form action="" method="post">
        <h4>Jelszó megváltoztatása</h4>
        <?php
        if (isset($error_pw)) {
            foreach ($error_pw as $error_pw) {
                echo '<span class="message error">' . $error_pw . '</span>';
            }
        }
        if (isset($success_pw)) {
            foreach ($success_pw as $success_pw) {
                echo '<span class="message success">' . $success_pw . '</span>';
            }
        }
        if (isset($regex_error)) {
            foreach ($regex_error as $regex_error) {
                echo '<span class="message regex-error">' . $regex_error . '</span>';
            }
        }
        ?>
        <label for="current_password">Jelenlegi jelszó: </label>
        <input class="form-control" type="password" name="current_password" required>
        <label for="new_password">Új jelszó: </label>
        <input class="form-control" type="password" name="new_password" required>
        <label for="new_cpassword">Új jelszó megerősítése: </label>
        <input class="form-control" type="password" name="new_cpassword" required>
        <input type="submit" name="submit_new_pw" value="Jelszó módosítása" class="form-btn">
    </form>
    <?php
    if (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] == 1) {
        $dark = 'checked';
        $light = '';
    } else {
        $dark = '';
        $light = 'checked';
    }
    ?>
    <form action="" method="post">
        <table>
            <tr>
                <td>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio-button" id="dark" value="1" <?= $dark ?>>
                        <label class="form-check-label" for="dark">
                            Sötét mód
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio-button" id="light" value="0" <?= $light ?>>
                        <label class="form-check-label" for="light">
                            Világos mód
                        </label>
                    </div>
                </td>
            </tr>
        </table>
        <input type="submit" name="submit_new_mode" value="Mentés" class="form-btn">
    </form>

    <form action="" method="post">
        <?php
        if (isset($error_delete)) {
            foreach ($error_delete as $error_delete) {
                echo '<span class="message error">' . $error_delete . '</span><br>';
            }
        } ?>
        <input type="submit" name="delete" value="Felhasználó törlése" class="form-btn delete-button">
    </form>

</div>
<br>
<?php include 'footer.php' ?>