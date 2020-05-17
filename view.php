<?php
    require_once "pdo.php";
    require_once "util.php";
    require_once "header.php";
    session_start();

// Make sure the REQUEST parameter is present
if ( ! isset($_REQUEST['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

// Main Profile Section
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :pid");
$stmt->execute(array(":pid" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$he = htmlentities($row['headline']);
$su = htmlentities($row['summary']);
$profile_id = $row['profile_id'];

// JQuery Position/Education Section
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>
<div class="container">
    <h3>Profile #<?= $profile_id . ': ' . $fn . ' ' . $ln ?></h3>
    <br>
    <table>
        <tr>
            <td>
                <b>Name: </b>
                <?= $fn . ' ' . $ln ?>
            </td>
            <td>
                <b>Email: </b>
                <?= $em ?>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <?= $he ?>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                <?= $su ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4>Positions: </h4>
                <ul>
                    <?php
                    foreach( $positions as $list ) {
                        echo("<li>".htmlentities($list['year']).": ".htmlentities($list['description'])."</li>");
                    }
                    ?>
                </ul>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h4>Education: </h4>
                <ul>
                    <?php
                    foreach( $schools as $school ) {
                        echo("<li>".htmlentities($school['year']).": ".htmlentities($school['name'])."</li>");
                    }
                    ?>
                </ul>
            </td>
        </tr>
    </table><br><br>
    <a href="index.php">Done</a>
</div>
<?php require_once "footer.php"; ?>