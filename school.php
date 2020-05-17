<?php
    require_once "pdo.php";
    session_start();

// If the user is not logged in, deny access
if ( ! isset($_SESSION['name']) ){
    die('<div class="container"><b>'.'Not logged in'.'</b></div>');
}

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array( ':prefix' => $_REQUEST['term']."%"));

$retval = array();
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    $retval[] = $row['name'];
}

echo(json_encode($retval, JSON_PRETTY_PRINT));