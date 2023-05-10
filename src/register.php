<?php
@include 'connection.php';
$link = connectDB();
if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $hashed_password = password_hash(mysqli_real_escape_string($link, $_POST['password']), PASSWORD_DEFAULT);

    // todo? email verificaion password verification
    $query_email = "SELECT * FROM user WHERE email = '$email'";
    $result_email = mysqli_query($link, $query_email);
    $query_username = "SELECT * FROM user WHERE email = '$username'";
    $result_username = mysqli_query($link, $query_username);

    // Kell tartalmaznia >1 számot (0-9), >1 nagybetut, >1 kisbetut és >6 karakter hosszú
    $password_regex = '/^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$/';

    // csak betuket, szamokat és ._ karaktereket tartalmazhat. A speciális karakterek nem kovethetik egymást illetve nem lehetnek a string elején vagy végén
    // azért 50 a maximális hossz, mert ennyi fér el az adatbázisban
    $username_regex = '/^(?=[a-zA-Z0-9._]{4,50}$)(?!.*[_.]{2})[^_.].*[^_.]$/';


    if (mysqli_num_rows($result_email) > 0) {
        $error[] = 'Az email már használatban van';
    } else if (mysqli_num_rows($result_username) > 0) {
        $error[] = 'A felhasználónév már használatban van';
    } else if (mysqli_real_escape_string($link, $_POST['password']) != mysqli_real_escape_string($link, $_POST['cpassword'])) {
        $error[] = 'A jelszavak nem egyeztek meg';
    } else if (!preg_match($password_regex, mysqli_real_escape_string($link, $_POST['password']))) {
        $regex_error[] = 'A jelszónak tartalmaznia kell legalább 6 karakter, kisbetűt, nagybetűt és számot!';
    } else if (!preg_match($username_regex, mysqli_real_escape_string($link, $_POST['username']))) {
        $regex_error[] = 'A felhasználónévnek legalább 4 karaktert tartalmaznia melyek: betűk, számok és . _ !<br>A speciális karakterek nem követhetik egymást és nem lehetnek a név elején vagy végén!';
    } else {
        $insert = "INSERT INTO user (username,password,email) VALUES('$username','$hashed_password','$email')";
        mysqli_query($link, $insert);
        header('Location: index.php');
        exit;
    }
}
?>

<!doctype HTML>
<html lang="hu">

<head>
    <title>Register form</title>
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
    <?php $on_login_page = true;
    include 'menu.php' ?>
    <div class="form-container">

        <form action="" method="post">
            <h3>Regisztrálj most!</h3>
            <?php
            if (isset($error)) {
                foreach ($error as $error) {
                    echo '<span class="error-msg">' . $error . '</span>';
                }
                ;
            }
            ;
            ?>
            <?php
            if (isset($regex_error)) {
                foreach ($regex_error as $regex_error) {
                    echo '<span class="regex-msg">' . $regex_error . '</span>';
                }
                ;
            }
            ;
            ?>
            <input type="text" name="username" required placeholder="Felhasználónév">
            <input type="email" name="email" required placeholder="Email cím">
            <input type="password" name="password" required placeholder="Jelszó">
            <input type="password" name="cpassword" required placeholder="Jelszó még egyszer">
            <input type="submit" name="submit" value="Regisztráció" class="form-btn">
            <p>Van már felhasználód? <a href="login.php">Jelentkezz be!</a></p>

        </form>

    </div>
    <?php include 'footer.php'; ?>
</body>

</html>