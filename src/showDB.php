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
$result = mysqli_query($link, "SELECT * FROM user ORDER BY user_id");


if (isset($_POST['submit_search'])) {
    $query = "SELECT * FROM user WHERE user_id LIKE ? AND username LIKE ? AND email LIKE ? ";

    // hozzáfuzi a string-ekhez a %-t, hogy mukodjon a LIKE
    $search_id = "%" . mysqli_real_escape_string($link, $_POST['search_id']) . "%";
    $search_username = "%" . mysqli_real_escape_string($link, $_POST['search_username']) . "%";
    $search_email = "%" . mysqli_real_escape_string($link, $_POST['search_email']) . "%";

    // Megnézi melyik checkbox-ok vannak megjelolve a hozzáférési szintek kozul
    $search_access = array(isset($_POST['search_admin']), isset($_POST['search_guest']), isset($_POST['search_employee']), isset($_POST['search_project_lead']));
    // Ha legalább 1 checkbox meg van jelolve
    if (in_array(1, $search_access)) {
        $query .= " AND access_level IN (";
        // A checkbox értekek hozzárendelése az array elemeihez
        $access_temp = array('admin', 'guest', 'employee', 'project_lead');
        // A megjelolt checkbox-ok értékeit hozzáfuzi a query-hez
        // minden iterációban az adott elem értékét hozzárendeli $value-hoz és az index-ét pedig a $key-hez,
        // ezzel a $key-vel tudjuk kiválasztani az $access_temp megfelelo elemét, hogy azt majd hozzáadjuk a $query-hez
        foreach ($search_access as $key => $value) {
            if ($value) {
                $query .= "'$access_temp[$key]',";
            }
        }
        // eltávolítja az utolsó paraméter utáni ,-t hogy ne dobjon az SQL hibát
        $query[-1] = " ";
        $query .= ")";
    }
    // Ha csak az egyik checkbox van megjelolve, akkor az annak megfelelo értékre kell keresni, ha mind2 vagy egyik sincs megjelolve akkor meg a gyakorlatban nem szurunk rá
    if (isset($_POST['search_dark']) && !isset($_POST['search_light'])) {
        $query .= " AND dark_mode = '1'";
    } else if (isset($_POST['search_light']) && !isset($_POST['search_dark'])) {
        $query .= " AND dark_mode = '0'";
    }

    $sort_param = $_POST['sort_param'];
    $sort_order = $_POST['sort_order'];
    $sort_param = "$sort_param $sort_order";

    $query .= "ORDER BY $sort_param";

    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "sss", $search_id, $search_username, $search_email);
    mysqli_stmt_execute($stmt);
    $search_result = mysqli_stmt_get_result($stmt);
}

// if (isset($_POST['submit_search'])) {
//     $sql = "SELECT * FROM user WHERE";
//     // hozzáfuzi a string-ekhez a %-t, hogy mukodjon a LIKE
//     $search_id = "%" . mysqli_real_escape_string($link, $_POST['search_id']) . "%";
//     //$search_id = "%$search_id%";
//     $sql .= " user_id LIKE $search_id";

//     $search_username = "%" . mysqli_real_escape_string($link, $_POST['search_username']) . "%";
//     //$search_username = "%$search_username%";
//     $sql .= " AND username LIKE $search_username";

//     $search_email = "%" . mysqli_real_escape_string($link, $_POST['search_email']) . "%";
//     //$search_email = "%$search_email%";
//     $sql .= " AND email LIKE $search_email";

//     $search_access = array(isset($_POST['search_admin']), isset($_POST['search_guest']), isset($_POST['search_employee']), isset($_POST['search_project_lead']));
//     if (in_array(1, $search_access)) {
//         $sql .= " AND access_level IN (";
//         $access_temp = array('admin', 'guest', 'employee', 'project_lead');
//         foreach ($search_access as $key => $value) {
//             if ($value) {
//                 $sql .= "'$access_temp[$key]',";
//             }
//         }
//         // eltávolítja az utolsó paraméter utáni ,-t hogy ne dobjon az SQL hibát
//         $sql[-1] = " ";
//         $sql .= ")";
//     }

//     $sort_param = $_POST['sort_param'];
//     $sort_order = $_POST['sort_order'];
//     $sort_conc = "$sort_param $sort_order";
//     $sql .= " ORDER BY $sort_conc";
//     echo "<script>console.log('$sql');</script>";
//     $search_result = mysqli_query($link, $sql);
// }

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
            <input type="text" requierd name="search_id" placeholder="ID">
            <input type="text" requierd name="search_username" placeholder="Felhasználónév">
            <input type="text" requierd name="search_email" placeholder="Email cím">
            <input type="checkbox" requierd name="search_admin" placeholder="Hozzáférési szint">
            <label for="search_admin">Admin</label>
            <input type="checkbox" requierd name="search_guest" placeholder="Hozzáférési szint">
            <label for="search_guest">Vendég</label>
            <input type="checkbox" requierd name="search_employee" placeholder="Hozzáférési szint">
            <label for="search_employee">Alkalmazott</label>
            <input type="checkbox" requierd name="search_project_lead" placeholder="Hozzáférési szint">
            <label for="search_project_lead">Projekt vezető</label>
            <input type="checkbox" requierd name="search_dark" placeholder="Sötét mód">
            <label for="search_dark">Sötét mód</label>
            <input type="checkbox" requierd name="search_light" placeholder="Világos mód">
            <label for="search_light">Világos mód</label>
            <label for="sort_param">Rendezés: </label>
            <select name="sort_param" id="sort_param">
                <option value="username">Név</option>
                <option value="user_id">ID</option>
                <option value="email">Email</option>
                <option value="access_level">Hozzáférési szint</option>
            </select>
            <select name="sort_order" id="sort_order">
                <option value="ASC">növekvő</option>
                <option value="DESC">csökkenő</option>
            </select>
            <input type="submit" name="submit_search" class="form-btn" value="Keresés">
            <input type="submit" name="submit_search_delete" class="form-btn" value="Keresés törlése">
        </form>
        <?php
        if (isset($_POST['submit_search'])) {
            echo '<table><tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email cím</th>
                    <th>Hashelt jelszó</th>
                    <th>Hozzáférési szint</th>
                    <th>Sötét mód</th>
                </tr>';
            while ($search_row = mysqli_fetch_array($search_result)) {
                echo '<tr>
                        <td>' . $search_row['user_id'] . '</td>
                        <td>' . $search_row['username'] . '</td>
                        <td>' . $search_row['email'] . '</td>
                        <td>' . $search_row['password'] . '</td>
                        <td>' . $search_row['access_level'] . '</td>
                        <td>' . $search_row['dark_mode'] . '</td>
                        </tr>';
            }
        }
        ?>

        </table>
    </div>
    <div>
        <table class="db-table">
            <tr>
                <th>ID</th>
                <th>Felhasználónév</th>
                <th>Email cím</th>
                <th>Hashelt jelszó</th>
                <th>Hozzáférési szint</th>
                <th>Sötét mód</th>
                <th></th>
                <th></th>
            </tr>
            <?php while ($row = mysqli_fetch_array($result)): ?>
                <tr>
                    <td>
                        <?= $row['user_id'] ?>
                    </td>
                    <td>
                        <?= $row['username'] ?>
                    </td>
                    <td>
                        <?= $row['email'] ?>
                    </td>
                    <td>
                        <?= $row['password'] ?>
                    </td>
                    <td>
                        <?= $row['access_level'] ?>
                    </td>
                    <td>
                        <?= $row['dark_mode'] ?>
                    </td>
                    <td>
                        <a href="delete_user.php?id=<?= $row['user_id'] ?>">Törlés</a>
                    </td>
                    <td>
                        <a
                            href="edit_user.php?user_id_=<?= $row['user_id'] ?>&email_=<?= $row['email'] ?>&username_=<?= $row['username'] ?>&dark_mode_=<?= $row['dark_mode'] ?>&access_level_=<?= $row['access_level'] ?>">Módosítás</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <div>
        <a href="insert_user.php">Új felhasználó hozzáadása</a>
    </div>
    <?php include 'footer.php' ?>
</body>

</html>