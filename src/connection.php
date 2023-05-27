<?php
function connectDB()
{
    $server = 'localhost';
    $user = 'root';
    $password = '';
    $db = 'project_management';
    $link = mysqli_connect($server, $user, $password, $db)
        or die("Kapcsolódási hiba: " . mysqli_error($link));
    mysqli_query($link, "set character_set_results='utf8'");
    mysqli_query($link, "set character_set_client='utf8'");
    return $link;
}

function closeDB($link)
{
    mysqli_close($link);
}
?>