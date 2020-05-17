<?php
    require_once "pdo.php";
    require_once "util.php";
    require_once "header.php";
    session_start();
    denyAccess();

// If the user requested cancel, go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM Profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Record deleted';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that profile_id is present
if ( ! isset($_GET['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, email, headline, profile_id, user_id FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<div class="container">
    <h3>Confirm: Deleting <?= htmlentities($row['first_name']) . " " . htmlentities($row['last_name'])?></h3>
    <form method="post">
        <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
        <input type="submit" name="delete" value="Delete">
        <input type="submit" name="cancel" value="Cancel"></p>
    </form>
</div>
<?php require_once "footer.php"; ?>