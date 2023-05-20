<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}

if (!(isset($_GET['user_id_get']) && isset($_GET['email_get']) && isset($_GET['username_get']) && isset($_GET['dark_mode_get']) && isset($_GET['access_level_get']))) {
    header("Location: users.php");
    exit;
}

@include 'connection.php';
$link = connectDB();

if (isset($_POST['submit_edit'])) {
    $edited_query = "UPDATE user SET";
    $not_empty = false;
    $edit_username = false;
    $edit_email = false;
    $edit_id = false;
    $edit_dark_mode = false;
    $edit_access_level = false;
    $id = $_GET['user_id_get'];
    // isset(x) && trim(x) !== "" biztosan megmondja hogy uresen van-e hagyva az adott form input
    if (isset($_POST['edited_username']) && trim($_POST['edited_username']) !== "") {
        $edited_username = mysqli_real_escape_string($link, $_POST['edited_username']);
        $query_username_already_used = "SELECT * FROM user WHERE username = '$edited_username'";
        $result = mysqli_query($link, $query_username_already_used);
        if (mysqli_num_rows($result) > 0) {
            $already_in_use_error[] = 'A felhasználónév már foglalt';
        }
        $edited_query .= " username = '$edited_username',";
        $not_empty = true;
        $edit_username = true;
    }

    if (isset($_POST['edited_email']) && trim($_POST['edited_email']) !== "") {
        $edited_email = mysqli_real_escape_string($link, $_POST['edited_email']);
        $query_email_already_used = "SELECT * FROM user WHERE email = '$edited_email'";
        $result = mysqli_query($link, $query_email_already_used);
        if (mysqli_num_rows($result) > 0) {
            $already_in_use_error[] = 'Az email cím már foglalt';
        }
        $edited_query .= " email = '$edited_email',";
        $not_empty = true;
        $edit_email = true;
    }

    if (isset($_POST['edited_user_id']) && trim($_POST['edited_user_id']) !== "") {
        $edited_user_id = mysqli_real_escape_string($link, $_POST['edited_user_id']);
        $query_user_id_already_used = "SELECT * FROM user WHERE user_id = '$edited_user_id'";
        $result = mysqli_query($link, $query_user_id_already_used);
        if (mysqli_num_rows($result) > 0) {
            $already_in_use_error[] = 'Az ID már foglalt';
        }
        $edited_query .= " user_id = '$edited_user_id',";
        $not_empty = true;
        $edit_id = true;
    }
    if (isset($_POST['edited_dark_mode_select']) && trim($_POST['edited_dark_mode_select']) !== "") {
        $edited_dark_mode = mysqli_real_escape_string($link, $_POST['edited_dark_mode_select']);
        $edited_query .= " dark_mode = b'$edited_dark_mode',";
        $not_empty = true;
        $edit_dark_mode = true;
    }
    if (isset($_POST['edited_access_level_select']) && trim($_POST['edited_access_level_select']) !== "") {
        $edited_access_level = mysqli_real_escape_string($link, $_POST['edited_access_level_select']);
        $edited_query .= " access_level = '$edited_access_level',";
        $not_empty = true;
        $edit_access_level = true;
    }
    if ($not_empty) {
        $edited_query[-1] = " ";
    }
    $edited_query .= " WHERE user_id = '$id'";

    if (!isset($already_in_use_error)) {
        mysqli_query($link, $edited_query);
        if ($_SESSION['id'] == $_GET['user_id_get']) {
            if ($edit_username) {
                $_SESSION['username'] = $edited_username;
            }
            if ($edit_email) {
                $_SESSION['email'] = $edited_email;
            }
            if ($edit_id) {
                $_SESSION['id'] = $edited_user_id;
            }
            if ($edit_access_level) {
                $_SESSION['access_level'] = $edited_access_level;
            }
            if ($edit_dark_mode) {
                $_SESSION['dark_mode'] = $edited_dark_mode;
            }
        }
        header('Location: users.php');
    }
}
closeDB($link);
?>
<?php include 'menu.php' ?>
<div class="form-container">
    <form action="" method="post">
        <h4>Felhasználói adatok módosítása</h4>
        <label for="edited_user_id">ID: </label>
        <input type="number" name="edited_user_id" class="form-control" placeholder="<?= $_GET['user_id_get'] ?>">

        <label for="edited_email">Email: </label>
        <input type="email" name="edited_email" class="form-control" placeholder="<?= $_GET['email_get'] ?>">

        <label for="edited_username">Felhasználónév: </label>
        <input type="text" name="edited_username" class="form-control" placeholder="<?= $_GET['username_get'] ?>">

        <?php
        $access = array("admin" => "Admin", "project_lead" => "Projektvezető", "employee" => "Alkalmazott", "guest" => "Vendég");
        ?>
        <label for="edited_access_level_select">Hozzáférési szint: </label>
        <select name="edited_access_level_select" class="form-select">
            <option value="<?= $_GET['access_level_get'] ?>" selected>
                <?php
                $key = $_GET['access_level_get'];
                echo $access[$key];
                ?>
            </option>
            <?php
            foreach ($access as $key => $value) {
                if ($key != $_GET['access_level_get']) {
                    echo '<option value="' . $key . '">' . $value . '</option>';
                }
            }
            ?>
        </select>

        <label for="edited_dark_mode_select">Mód: </label>
        <select name="edited_dark_mode_select" class="form-select">
            <?php
            if ($_GET['dark_mode_get'] == '1') {
                echo '<option value="1">Sötét mód</option>
                        <option value="0">Világos mód</option>';
            } else {
                echo '<option value="0">Világos mód</option>
                        <option value="1">Sötét mód</option>';
            }
            ?>
        </select>
        <input type="submit" name="submit_edit" value="Adatok módosítása" class="form-btn">
        <?php
        if (isset($already_in_use_error)) {
            foreach ($already_in_use_error as $already_in_use_error) {
                echo '<span class="message error">' . $already_in_use_error . '</span>';
            }
        }
        ?>
    </form>
</div>
<?php include 'footer.php' ?>