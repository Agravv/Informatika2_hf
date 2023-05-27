<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}

@include 'connection.php';
$link = connectDB();
$session_user_id = mysqli_real_escape_string($link, $_SESSION["id"]);
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_POST['submit_new_username'])) {
    // ? Pontozási szempontok: Legalább két, nem triviális reguláris kifejezés használata: 5p
    $username_regex = '/^(?=[a-zA-Z0-9ÁÉÍÓÖŐÚÜŰáéíóöőúüű._]{4,50}$)(?!.*[_.]{2})[^_.].*[^_.]$/';
    $new_username = mysqli_real_escape_string($link, $_POST['new_username']);
    if (!preg_match($username_regex, $new_username)) {
        $error_username[] = "A felhasználónévnek legalább 4 karaktert tartalmaznia melyek: betűk, számok és . _ !<br>A speciális karakterek nem követhetik egymást és nem lehetnek a név elején vagy végén!";
    }
    $check = mysqli_query($link, "SELECT user_id FROM user WHERE username = '$new_username'");
    if (mysqli_num_rows($check) != 0) {
        $error_username[] = "A felhasználónév már foglalt!";
    } else {
        $query_new_username = "UPDATE user SET username = '$new_username' WHERE user_id = '$session_user_id';";
        mysqli_query($link, $query_new_username);
        if (mysqli_affected_rows($link) == 0) {
            $error_username[] = "Valami hiba történt!";
        } else {
            $_SESSION['username'] = $new_username;
            $success_username[] = "Sikeresen megváltoztatta a felhasználónevét!";
        }
    }
}
// ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
if (isset($_POST['submit_new_pw'])) {
    // ? Pontozási szempontok: Felhasználó kezelés jelszóval (nem plain textben tárolva): 10p
    $query_select_password = "SELECT password FROM user WHERE user_id = '$session_user_id'";
    $result_select_password = mysqli_query($link, $query_select_password);
    $result_array = mysqli_fetch_array($result_select_password);
    $password_db = $result_array['password'];
    // ? Pontozási szempontok: Felhasználó kezelés jelszóval (nem plain textben tárolva): 10p
    if (password_verify($_POST['current_password'], $password_db)) {
        // ? Pontozási szempontok: Legalább két, nem triviális reguláris kifejezés használata: 5p
        $password_regex = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})$/';
        $new_password = mysqli_real_escape_string($link, $_POST['new_password']);
        $new_cpassword = mysqli_real_escape_string($link, $_POST['new_cpassword']);
        // ? Pontozási szempontok: Felhasználó kezelés jelszóval (nem plain textben tárolva): 10p
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        if (preg_match($password_regex, $new_password)) {
            if (password_verify($new_cpassword, $hashed_new_password)) {
                // a new_cpassword-ot hasonlítja a new_password-hoz, tuladjonképpen ellenorzi, megegyeznek-e a megadott jelszavak
                $query_update_password = "UPDATE user SET password = '$hashed_new_password' WHERE user_id = '$session_user_id'";
                mysqli_query($link, $query_update_password);
                $_SESSION['password'] = $hashed_new_password;
                if (mysqli_affected_rows($link) == 1) {
                    $success_pw[] = 'Sikeres jelszóváltoztatás';
                } else {
                    $error_pw[] = 'Valami hiba történt jelszóváltoztatás közben';
                }
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

// ? Pontozási szempontok: CSS váltás (skin cserélése) az alkalmazásból: 10p
// ? Pontozási szempontok: Kiválasztott CSS (vagy egyéb, megjelenésre vonatkozó beállítás) felhasználónkénti tárolása: 5p
if (isset($_POST['submit_new_mode'])) {
    $dark_mode = mysqli_real_escape_string($link, $_POST['radio-button']);
    $query = "UPDATE user SET dark_mode = b'$dark_mode' WHERE user_id = '$session_user_id'";
    mysqli_query($link, $query);
    $_SESSION['dark_mode'] = $dark_mode;
}

if (isset($_POST['delete'])) {
    mysqli_begin_transaction($link);
    try {
        // Az in_progress projekteket vissza kell állítani not_startedre
        // a finished projekteken nem kell változtatni - azok user nélkul lesznek megjelenítve
        $query_reset_project = "UPDATE project 
                                INNER JOIN user_has_project ON user_has_project.project_id = project.project_id
                                INNER JOIN user ON user_has_project.user_id = user.user_id
                                SET status = 'not_started' 
                                WHERE user.user_id = '$session_user_id'
                                AND project.status = 'in_progress'";
        $query_user_has_project = "DELETE FROM user_has_project WHERE user_id = '$session_user_id'";
        $query_user = "DELETE FROM user WHERE user_id = '$session_user_id'";
        mysqli_query($link, $query_reset_project);
        mysqli_query($link, $query_user_has_project);
        mysqli_query($link, $query_user);
        mysqli_commit($link);
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($link);
        throw $exception;
    }

    $query_validate_delete = "SELECT username FROM user WHERE user_id = '$session_user_id'";
    $result_validate_delete = mysqli_query($link, $query_validate_delete);
    closeDB($link);
    if (mysqli_num_rows($result_validate_delete) > 0) {
        // Ha mégis megtaláljuk az adott ID-t a táblában akkor hiba tortént
        $error_delete[] = 'Hiba történt törlés közben';
        header('Location: profile.php');
    } else {
        session_destroy();
        header('Location: index.php');
    }
}
if ($_SESSION['access_level'] == 'employee') {
    // Statszitika kiírásához szukséges lekérdezések
    $stat_project_query = "SELECT COUNT(title) as count_title
                            FROM (SELECT title 
                                FROM project 
                                LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id 
                                LEFT JOIN user ON user.user_id = user_has_project.user_id 
                                WHERE user.user_id = '$session_user_id' 
                                AND project.status = 'finished' 
                                GROUP BY project.project_id) AS data;";
    $stat_task_query = "SELECT COUNT(name) as count_name
                        FROM (SELECT task.name 
                            FROM project 
                            LEFT JOIN user_has_project ON user_has_project.project_id = project.project_id 
                            LEFT JOIN user ON user.user_id = user_has_project.user_id 
                            LEFT JOIN task ON task.project_project_id = project.project_id 
                            WHERE user.user_id = '$session_user_id' 
                            AND project.status = 'finished' 
                            GROUP BY project.project_id) AS data;";
    $result_project_query = mysqli_query($link, $stat_project_query);
    $result_project_count = mysqli_fetch_array($result_project_query);
    $result_task_query = mysqli_query($link, $stat_task_query);
    $result_task_count = mysqli_fetch_array($result_task_query);
}
closeDB($link);
include 'menu.php' ?>
<div class="form-container form-container-profile">
    <h1>Profil</h1>
    <?php
    if ($_SESSION['access_level'] == 'employee') {
        echo '<div class="stat-container">
                <h4>Statisztikák</h4>
                <ul>
                    <li>Elvégzett projektek: ' . $result_project_count['count_title'] . '</li>
                    <li>Elvégzett feladatok: ' . $result_task_count['count_name'] . ' </li>
                </ul>
            </div>';
    }
    ?>

    <form action="" method="post">
        <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
        <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
        <!-- // ? felhasználónak. -->
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
        <!-- // ? Pontozási szempontok: CSS váltás (skin cserélése) az alkalmazásból: 10p -->
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