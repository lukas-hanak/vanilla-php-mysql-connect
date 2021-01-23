<?php
include 'sqlCmd.php';

error_reporting(E_ERROR | E_PARSE);
setlocale(LC_ALL, 'cs_CZ.UTF-8');
session_start();
$connection;

if (isset($_POST["hostname"])) $_SESSION["hostname"] = $_POST["hostname"];
if (isset($_POST["username"])) $_SESSION["username"] = $_POST["username"];
if (isset($_POST["password"])) $_SESSION["password"] = $_POST["password"];

if (!empty($_SESSION["hostname"]) && !empty($_SESSION["username"])) {
    $connection = mysqli_connect($_SESSION["hostname"], $_SESSION["username"], $_SESSION["password"]);
    if (!mysqli_connect_errno()) {
        mysqli_query($connection, $cmdSetCharacter);
        mysqli_query($connection, $cmdSetNames);
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="windows-1250">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Connection - Hanák</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>

<nav class="header-container">
    <p class="header-text">MySQL Databáze</p>
</nav>

<!--Připojení k MySQL serveru-->
<fieldset class="form-container-full">
    <legend>Připojení k MySQL serveru</legend>
    <?php connectToMySQLForm() ?>
</fieldset>

<?php
  if (mysqli_connect_errno()) {
    exit();
  } 
?>


<div class="form-wrap">
    <!--Výběr databáze ze serveru-->
    <?php
        if (isset($connection)) {
    ?>
    <fieldset class="form-container">
        <legend>Výběr databáze</legend>
        <?php selectDatabase() ?>
    </fieldset>
    <?php
        }
    ?>

    <!--Výběr tabulky z vybraného databáze-->
    <?php
        if (isset($connection) && isset($_GET['database'])) {
            $database = $_GET['database'];
    ?>
    <fieldset class="form-container">
        <legend>Výběr tabulky</legend>
        <?php selectTable()?>
    </fieldset>
    <?php
        }
    ?>
</div>

<!--Vypsání všech hodnot z vybrané tabulky-->
<?php
    if (isset($connection) && isset($_GET['database']) && isset($_GET['table'])) {
        echo '<b>Vybraná tabulka:</b><input class="table-input" name="table" id="table" readonly type="text" value='.$_GET['table'].'>';
        renderTable();
    }
?>

</body>
</html>

<?php
function connectToMySQLForm() {
    echo '<form name="database-connect" method="post" action='.$_SERVER["PHP_SELF"].'>';
        echo "<div class='full-width'>";
            echo "<label for='hostname'>Hostname:</label><input name='hostname' id='hostname' type='text' value=''>";
            echo "<label for='username'>Username:</label><input name='username' id='username' type='text' value=''>";
            echo "<label for='password'>Password:</label><input name='password' id='password' type='password' value=''>";
            echo "<input class='connect-input' id='submit' type='submit' value='Připojit'>";
        echo "</div>";
        echo "<div class='status-container'>";
            if (isset($GLOBALS['connection']) && !mysqli_connect_errno()) {
                echo "<label for='status'>Status: <input readonly id='status' style='background-color: green; color: white;' value='Připojeno'>";
            } else {
                echo "<label for='status'>Status: <input readonly id='status' style='background-color: red; color: white;' value='Nepřipojeno'>";
            }
        echo "</div>";
    echo "</form>";
}
?>

<?php
function selectDatabase() {
    echo '<form name="database-select" method="get" action='.$_SERVER["PHP_SELF"].'>';
        echo '<b>Server:</b><input class="server-input" name="database" id="database" readonly type="text" value='.$_SESSION["hostname"].'>';
        echo '<select class="full-width" name="database">';
        $_SESSION["databases"] = mysqli_query($GLOBALS["connection"], $GLOBALS["cmdShowDb"]);
            while ($row = mysqli_fetch_array($_SESSION["databases"])) {
                echo "<option value='$row[0]'>".$row[0]."</option>";
            }
            echo '<input type="submit" value="Připojit"/>';
        echo '</select>';
    echo '</form>';
}
?>

<?php
function selectTable() {
    echo '<form name="table-select" method="get" action='.$_SERVER["PHP_SELF"].'>';
        echo '<b>Vybraná databáze:</b><input class="server-input" name="database" id="database" readonly type="text" value='.$GLOBALS["database"].'>';
        echo '<select class="full-width" name="table">';
                if ($result = mysqli_query($GLOBALS['connection'], "SHOW TABLES FROM ".$GLOBALS['database'])) {
                    while ($row = mysqli_fetch_array($result)) {
                        echo "<option value='$row[0]'>".$row[0]."</option>";
                    }
                }
            echo '<input type="submit" value="Zobrazit data"/>';
        echo '</select>';
    echo '</form>';
}
?>

<?php
function renderTable() {
    $database = $_GET['database'];
    $table = $_GET['table'];
    $cmdFetchData = "SELECT * FROM ".$database.".".$table;
    $data = mysqli_query($GLOBALS["connection"], $cmdFetchData);
    echo '<table>';
        echo '<tr>';
            if ($row = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                foreach (array_keys($row) as $value) {
                    if (isset($value)) {
                        echo '<th>'.$value.'</t>';
                    }
                }
            }
        echo '</tr>';
        $data2 = mysqli_query($GLOBALS["connection"], $cmdFetchData);
        while ($row = mysqli_fetch_array($data2, MYSQLI_ASSOC)) {
            echo "<tr>";
                foreach ($row as $key=>$value) {
                    if (isset($value)) {
                        echo '<td>'.$value.'</td>';
                    }
                }
            echo "</tr>";
        }
    echo '</table>';
}
?>

<?php
    if (isset($connection)) {
        mysqli_close($connection);
    }
?>