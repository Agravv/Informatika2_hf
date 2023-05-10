<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>

<?php
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header('Location: index.php');
    exit;
}

if (!(isset($_GET['user_id_']) && isset($_GET['email_']) && isset($_GET['username_']) && isset($_GET['dark_mode_']) && isset($_GET['access_level_']))) {
    header("Location: showDB.php?info=edit_failed");
    exit;
}

@include 'connection.php';
$link = connectDB();

function getFieldValue($fieldName, $defaultValue, $connection)
{
    if (isset($_POST[$fieldName]) && trim($_POST[$fieldName]) !== "") {
        return mysqli_real_escape_string($connection, $_POST[$fieldName]);
    }
    return $defaultValue;
}

if (isset($_POST['submit_edit'])) {
    // isset(x) && trim(x) !== "" biztosan megmondja hogy uresen van-e hagyva az adott form input
    $edited_username = getFieldValue('edited_username', $_GET['username_'], $link);
    if (isset($_POST['edited_username']) && trim($_POST['edited_username']) !== "") {
        $query_username_already_used = "SELECT * FROM user WHERE username = '$edited_username'";
        $result = mysqli_query($link, $query_username_already_used);
        if (mysqli_num_rows($result) > 0) {
            $already_in_use_error[] = 'A felhasználónév már foglalt';
        }
    }

    $edited_email = getFieldValue('edited_email', $_GET['email_'], $link);
    if (isset($_POST['edited_email']) && trim($_POST['edited_email']) !== "") {
        $query_email_already_used = "SELECT * FROM user WHERE email = '$edited_email'";
        $result = mysqli_query($link, $query_email_already_used);
        if (mysqli_num_rows($result) > 0) {
            $already_in_use_error[] = 'Az email cím már foglalt';
        }
    }

    $edited_user_id = getFieldValue('edited_user_id', $_GET['user_id_'], $link);
    if (isset($_POST['edited_user_id']) && trim($_POST['edited_user_id']) !== "") {
        $query_user_id_already_used = "SELECT * FROM user WHERE user_id = '$edited_user_id'";
        $result = mysqli_query($link, $query_user_id_already_used);
        if (mysqli_num_rows($result) > 0) {
            $already_in_use_error[] = 'Az ID már foglalt';
        }
    }
    $edited_dark_mode = mysqli_real_escape_string($link, $_POST['edited_dark_mode_select']);
    $edited_access_level = mysqli_real_escape_string($link, $_POST['edited_access_level_select']);
    if (!isset($already_in_use_error)) {
        $edited_query = "UPDATE user SET 
                            username = '$edited_username',
                            email = '$edited_email', 
                            user_id = '$edited_user_id', 
                            dark_mode = '$edited_dark_mode', 
                            access_level = '$edited_access_level' 
                            WHERE user_id = '$edited_user_id'";
        mysqli_query($link, $edited_query);
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
    <div>
        <form action="" method="post">
            <table class="edit-table">
                <tr>
                    <th></th>
                    <th>Jelenlegi</th>
                    <th>Új</th>
                </tr>
                <tr>
                    <td>felhasználói azonosító</td>
                    <td>
                        <?= $_GET['user_id_'] ?>
                    </td>
                    <td><input type="number" name="edited_user_id"></td>
                </tr>
                <tr>
                    <td>email cím</td>
                    <td>
                        <?= $_GET['email_'] ?>
                    </td>
                    <td><input type="email" name="edited_email"></td>
                </tr>
                <tr>
                    <td>felhasználónév</td>
                    <td>
                        <?= $_GET['username_'] ?>
                    </td>
                    <td><input type="text" name="edited_username"></td>
                </tr>
                <tr>
                    <td>sötét mód</td>
                    <td>
                        <?= $_GET['dark_mode_'] ?>
                    </td>
                    <td><select name="edited_dark_mode_select" id="dark_mode_select">
                            <option value="Dark">Sötét mód</option>
                            <option value="Light">Világos mód</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>hozzáférési szint</td>
                    <td>
                        <?= $_GET['access_level_'] ?>
                    </td>
                    <td><select name="edited_access_level_select" id="edited_access_level_select">
                            <option value="guest">Vendég</option>
                            <option value="admin">Admin</option>
                            <option value="employee">Alkalmazott</option>
                            <option value="project_lead">Projekt vezető</option>
                        </select>
                    </td>
                </tr>
            </table>
            <input type="submit" name="submit_edit" value="Adatok módosítása" class="form-btn">
            <?php
            if (isset($already_in_use_error)) {
                foreach ($already_in_use_error as $already_in_use_error) {
                    echo '<span class="error-msg">' . $already_in_use_error . '</span>';
                }
                ;
            }
            ;
            ?>
        </form>
    </div>

    <?php include 'footer.php' ?>
</body>

</html>