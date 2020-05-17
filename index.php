<?php
    require_once "pdo.php";
    require_once "util.php";
    require_once "header.php";
    session_start();

// Retrieve the Profiles from the database
$stmt = $pdo->query('SELECT * FROM Profile');
$count = $stmt->rowCount();

?>
<div class="container">
    <h1>Michael Jun's JSON CRUD</h1>
    <?php flashMessages();

    // Check if we are logged in!
    if ( ! isset($_SESSION['name']) ) { ?>
        <p><a href="login.php">Please log in</a></p>
            <br>
        <p><a href="https://www.wa4e.com/assn/res-education/" target="_blank">Specification for this Application</a></p>
        <p>An attempt to
            <a href="add.php" target="_blank">ADD</a>,
            <a href="edit.php" target="_blank">EDIT</a>,
            <a href="delete.php" target="_blank">DELETE</a>, or
            <a href="school.php" target="_blank">SCHOOL</a>
            data without logging in should fail with an error message.</p>
    <?php } else { ?>
        <?php flashMessages();

        if ( $count == 0 ) {
            echo '<p style="color:darkred">'. "No Rows found</p>\n";
        }
        echo('<table>'."\n");
        while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
            echo "<tr><td>";
            echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
            echo(htmlentities($row['first_name']) . ' ' . htmlentities($row['last_name']));
            echo('</a>');
            echo("</td><td>");
            echo(htmlentities($row['headline']));
            echo("</td><td>");
            echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
            echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
            echo("</td></tr>\n");
        }
        ?>
        </table>
            <br>
        <h4><a href="school.php?term=Uni" target="_blank">JSON Check: school.php</a></h4>
            <br>
        <p><a href="add.php">Add New Entry</a></p>
        <p>Please <a href="logout.php">Log Out</a> when you are done.</p>
    <?php } ?>
</div>
<?php require_once "footer.php"; ?>