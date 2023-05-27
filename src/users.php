<!-- // ? funkcionális elvárások: Az alkalmazásban kell legyen mód minden adatbázisban tárolt adat kiolvasására az -->
<!-- // ? adatbázisból, azok megjelenítésére, új adatok bevitelére és a meglévő adatok módosítására. (Tehát nem elegendő, -->
<!-- // ? ha csak írni, vagy csak olvasni tudjuk az adatot, szerkeszteni is tudni kell azokat.) -->
<?php if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>

<?php
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true || $_SESSION['access_level'] != 'admin') {
    header('Location: index.php');
    exit;
}

@include 'connection.php';
$link = connectDB();

if (isset($_POST['submit_search'])) {
    // ? funkcionális elvárás: Legyen lehetőség az adatbázis legalább egyik táblájában keresni
    // ? funkcionális elvárás: A felhasználó által beírt bemenetet ellenőrizni kell mielőtt adatbázisba írjuk. SQL injection elleni védelmet biztosítani kell.
    // % hozzáfuzése a string-ekhez a LIKE miatt
    $search_id = "%" . mysqli_real_escape_string($link, $_POST['search_id']) . "%";
    $search_username = "%" . mysqli_real_escape_string($link, $_POST['search_username']) . "%";
    $search_email = "%" . mysqli_real_escape_string($link, $_POST['search_email']) . "%";
    $search_query = "SELECT * FROM user WHERE user_id LIKE '$search_id' AND username LIKE '$search_username' AND email LIKE '$search_email' ";

    // Megnézi melyik checkbox-ok vannak megjelolve a hozzáférési szintek kozul
    $search_access = array(isset($_POST['search_admin']), isset($_POST['search_guest']), isset($_POST['search_employee']), isset($_POST['search_project_lead']));
    // Ha legalább 1 checkbox meg van jelolve
    if (in_array(1, $search_access)) {
        // Ha bármelyik 'search_admin', 'search_guest' stb. be van pipálva (isset == true) akkor belép az IF-be
        $search_query .= " AND access_level IN (";
        // A checkbox értekek hozzárendelése az array elemeihez
        $access_temp = array('admin', 'guest', 'employee', 'project_lead');
        // A megjelolt checkbox-ok értékeit hozzáfuzi a query-hez
        // minden iterációban az adott elem értékét hozzárendeli $value-hoz és az index-ét pedig a $key-hez,
        // ezzel a $key-vel tudjuk kiválasztani az $access_temp megfelelo elemét, hogy azt majd hozzáadjuk a $search_query-hez
        foreach ($search_access as $key => $value) {
            if ($value) {
                $search_query .= "'$access_temp[$key]',";
            }
        }
        // eltávolítja az utolsó paraméter utáni ,-t hogy ne dobjon az SQL hibát
        $search_query[-1] = " ";
        $search_query .= ")";
    }
    // Ha csak az egyik checkbox van megjelolve, akkor az annak megfelelo értékre kell keresni, ha mind2 vagy egyik sincs megjelolve akkor meg a gyakorlatban nem szurunk rá
    if (isset($_POST['search_dark']) && !isset($_POST['search_light'])) {
        $search_query .= " AND dark_mode = '1'";
    } else if (isset($_POST['search_light']) && !isset($_POST['search_dark'])) {
        $search_query .= " AND dark_mode = '0'";
    }

    $sort_param = mysqli_real_escape_string($link, $_POST['sort_param']);
    $sort_order = mysqli_real_escape_string($link, $_POST['sort_order']);
    $sort_param = "$sort_param $sort_order";
    $search_query .= "ORDER BY $sort_param";

    $search_result = mysqli_query($link, $search_query);
}
if (isset($_POST['submit_search_delete'])) {
    $_POST = array();
}

// A bootstrap ennek segítségével jelzi, melyik oldalon vagyunk éppen a menuben
$site = 'user';
include 'menu.php' ?>
<div class="btn-container">
    <a href="users_insert.php" class="button">Új felhasználó hozzáadása</a>
</div>
<div class="form-container form-container-profile">
    <form action="" method="post">
        <!-- // ? funkcionális elvárás: Az adatmódosításkor, felvitelnél figyelni kell a hibás értékek kiszűrésére, -->
        <!-- // ? például üresen hagyott mezők, értelmetlen értékek (szöveg beírása szám helyett stb.). Ezeket jelezni kell a -->
        <!-- // ? felhasználónak. -->
        <h4>Keresés</h4>
        <label for="search_id">ID: </label>
        <input class="form-control" type="text" name="search_id"
            placeholder="<?= isset($_POST['search_id']) ? $_POST['search_id'] : '' ?>">
        <label for="search_username">Felhasználónév: </label>
        <input class="form-control" type="text" name="search_username"
            placeholder="<?= isset($_POST['search_username']) ? $_POST['search_username'] : '' ?>">
        <label for="search_email">Email cím: </label>
        <input class="form-control" type="text" name="search_email"
            placeholder="<?= isset($_POST['search_email']) ? $_POST['search_email'] : '' ?>">
        <div class=" table-search">
            <table>
                <tr>
                    <td><input class="form-check-input" type="checkbox" name="search_admin"
                            <?= isset($_POST['search_admin']) ? 'CHECKED' : '' ?>></td>
                    <td><label for="search_admin"> Admin</label></td>
                    <td><input class="form-check-input" type="checkbox" name="search_project_lead"
                            <?= isset($_POST['search_project_lead']) ? 'CHECKED' : '' ?>></td>
                    <td><label for="search_project_lead"> Projektvezető</label></td>
                    <td><input class="form-check-input" type="checkbox" name="search_employee"
                            <?= isset($_POST['search_employee']) ? 'CHECKED' : '' ?>></td>
                    <td><label for="search_employee"> Alkalmazott</label></td>
                    <td><input class="form-check-input" type="checkbox" name="search_guest"
                            <?= isset($_POST['search_guest']) ? 'CHECKED' : '' ?>></td>
                    <td><label for="search_guest"> Vendég</label></td>
                </tr>
            </table>
        </div>
        <div class="table-search">
            <table>
                <tr>
                    <td><input class="form-check-input" type="checkbox" name="search_dark"
                            <?= isset($_POST['search_dark']) ? 'CHECKED' : '' ?>></td>
                    <td><label for="search_dark"> Sötét mód</label></td>
                    <td><input class="form-check-input" type="checkbox" name="search_light"
                            <?= isset($_POST['search_light']) ? 'CHECKED' : '' ?>></td>
                    <td><label for="search_light"> Világos mód</label></td>

                </tr>
            </table>
        </div>

        <label for="sort_param">Rendezés: </label>
        <select name="sort_param" class="form-select">
            <option value="user_id">ID</option>
            <option value="username">Név</option>
            <option value="email">Email</option>
            <option value="access_level">Hozzáférési szint</option>
        </select>
        <select name="sort_order" class="form-select">
            <option value="ASC">növekvő</option>
            <option value="DESC">csökkenő</option>
        </select>
        <input type="submit" name="submit_search" class="form-btn" value="Keresés">
        <input type="submit" name="submit_search_delete" class="form-btn delete-button" value="Keresés törlése">
    </form>
</div>
<?php
// ? funkcionális elvárás: Legyen lehetőség az adatbázis legalább egyik táblájában keresni
if (isset($_POST['submit_search'])) {
    // Ha indult keresés, akkor táblázatba kiírja az eredményt
    echo '<div class="table-container">
            <table  class="table-hover table"><tr>
            <caption><h4>Szűrt adatbázis</h4></caption>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email cím</th>
                    <th>Hashelt jelszó</th>
                    <th>Hozzáférési szint</th>
                    <th>Sötét mód</th>
                    <th></th>
                    <th></th>
                </tr>';
    while ($search_row = mysqli_fetch_array($search_result)) {
        // fa-pen-to-square: edit/módosítás ikon
        // fa-trash: kuka/torlés ikon
        echo '<tr>
                        <td>' . $search_row['user_id'] . '</td>
                        <td>' . $search_row['username'] . '</td>
                        <td>' . $search_row['email'] . '</td>
                        <td>' . $search_row['password'] . '</td>
                        <td>' . $search_row['access_level'] . '</td>
                        <td>' . $search_row['dark_mode'] . '</td>
                        <td><a href="users_edit.php?user_id_get=' . $search_row['user_id'] . '
                                                    &email_get=' . $search_row['email'] . '
                                                    &username_get=' . $search_row['username'] . '
                                                    &dark_mode_get=' . $search_row['dark_mode'] . '
                                                    &access_level_get=' . $search_row['access_level'] . '"> <i
                                                    class="fa-solid fa-pen-to-square" style="color: ' . $color_code . ';"></i> </a></td>
                        <td><a href="users_delete.php?id=' . $search_row['user_id'] . '"> <i class="fa-solid fa-trash"
                        style="color: ' . $color_code . ';"></i> </a></td>
                        </tr>';
    }
    echo '</table></div>;';

}
// Minden felhasználó kiírása
$result = mysqli_query($link, "SELECT * FROM user ORDER BY user_id");
if (mysqli_num_rows($result) > 0) {
    echo '
    <div class="table-container">
        <table class=" table-hover table">
            <caption>
                <h4>Felhasználói adatbázis</h4>
            </caption>
            <tr>
                <th>ID</th>
                <th>Felhasználónév</th>
                <th>Email cím</th>
                <th>Hashelt jelszó</th>
                <th>Hozzáférési szint</th>
                <th>Sötét mód</th>
                <th></th>
                <th></th>
            </tr>';
    while ($row = mysqli_fetch_array($result)) {
        echo '<tr>
                    <td>' . $row['user_id'] . '</td>
                    <td>' . $row['username'] . '</td>
                    <td>' . $row['email'] . '</td>
                    <td>' . $row['password'] . '</td>
                    <td>' . $row['access_level'] . '</td>
                    <td>' . $row['dark_mode'] . '</td>
                    <td>
                    <a href="users_edit.php?user_id_get=' . $row['user_id'] . '&email_get=' . $row['email'] . '&username_get=' . $row['username'] . '&dark_mode_get=' . $row['dark_mode'] . '&access_level_get=' . $row['access_level'] . '"> <i class="fa-solid fa-pen-to-square" style="color: ' . $color_code . '"></i> </a>
                    </td>
                    <td>
                    <a href="users_delete.php?id=' . $row['user_id'] . '"> <i class="fa-solid fa-trash" style="color:' . $color_code . ';"></i> </a>
                    </td>';
    }
    echo '</table></div>';
}
?>
<br>
<?php
closeDB($link);
include 'footer.php'; ?>