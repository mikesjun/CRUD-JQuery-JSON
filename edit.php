<?php
    require_once "pdo.php";
    require_once "util.php";
    require_once "header.php";
    session_start();
    denyAccess();

// Make sure the REQUEST parameter is present
if ( ! isset($_REQUEST['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

// If the user requested cancel, go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

// Load up the profile
$stmt = $pdo->prepare('SELECT * FROM Profile WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array(':prof' => $_REQUEST['profile_id'], ':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    return;
}

$fn = htmlentities($profile['first_name']);
$ln = htmlentities($profile['last_name']);
$em = htmlentities($profile['email']);
$he = htmlentities($profile['headline']);
$su = htmlentities($profile['summary']);
$uid = $profile['user_id'];
$pid = $profile['profile_id'];

$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

// Handle incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name'])
    && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

    // Data validation
    $msg = validateProfile();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_POST["profile_id"]);
        return;
    }
    // Validate position entries, if present
    $msg = validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_POST["profile_id"]);
        return;
    }

    // TODO: VALIDATE EDUCATION

    // Run if Data is valid
    $stmt = $pdo->prepare('UPDATE Profile SET
        first_name = :fn, last_name = :ln, email = :em, headline = :he, summary = :su 
        WHERE profile_id = :pid AND user_id=:uid');
    $stmt->execute(array(
            ':pid' => $_REQUEST['profile_id'],
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'])
    );

    // Clear out the old Position entries
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id = :pid');
    $stmt->execute(array(':pid' => $_REQUEST['profile_id']));

    // Insert position entries
    insertPositions($pdo, $_REQUEST['profile_id']);

    // Clear out the old Education entries
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id = :pid');
    $stmt->execute(array(':pid' => $_REQUEST['profile_id']));

    // Insert Education entries
    insertEducations($pdo, $_REQUEST['profile_id']);

    $_SESSION['success'] = 'Profile updated';
    header('Location: index.php');
    return;
}

// Load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>
<div class="container">
    <h3>Edit Profile: <?= htmlentities($_SESSION['name']); ?></h3>
    <?php flashMessages(); ?>
    <form method="post">
        <p><label>First Name: </label>
            <input type="text" name="first_name" size="60" value="<?= $fn ?>"/></p>
        <p><label>Last Name: </label>
            <input type="text" name="last_name" size="60" value="<?= $ln ?>"/></p>
        <p><label>Email: </label>
            <input type="text" name="email" size="30"value="<?= $em ?>"/></p>
        <p><label>Headline:</label>
            <input type="text" name="headline" size="60" value="<?= $he ?>"/></p>
        <p><label>Summary: </label><br>
            <textarea name="summary" rows="4" cols="80" value="<?= $su ?>"><?= $su ?></textarea></p>
        <input type="hidden" name="profile_id" value="<?= $pid ?>">
        <p><label>Position: <input type="submit" id="addPos" value="+"></label>
            <div id="position_fields">
        <?php
        $posCount = 0;
        if (count($positions) > 0 ) {
            foreach ($positions as $position) {
                $posCount++;
                echo('<div id="position' . $posCount . '">');
                echo('<p>Year: <input type="text" size="10" name="year' . $posCount . '"');
                echo('value="' . $position['year'] . '"/> ');
                echo('<input type="button" value="-"');
                echo('onclick="$(\'#position' . $posCount . '\').remove(); return false;">');
                echo("</p>\n");
                echo('<textarea name="desc' . $posCount . '" rows="2" cols="80">');
                echo(htmlentities($position['description']));
                echo("</textarea></div><br>");
            }
        }
        ?>
            </div></p>
        <p><label>Education: <input type="submit" id="addEdu" value="+"></label>
            <div id="edu_fields">
        <?php
        $eduCount = 0;
        if (count($schools) > 0 ) {
            foreach ($schools as $school) {
                $eduCount++;
                echo('<div id="edu' . $eduCount . '">');
                echo('<p>Year: <input type="text" size="10" name="edu_year' . $eduCount . '" ');
                echo('value="' . $school['year'] . '"/> &nbsp;&nbsp;&nbsp;');
                echo('School: <input type="text" size="40" name="edu_school' . $eduCount . '" ');
                echo('class="school" value="' . $school['name'] . '"/> ');
                echo('<input type="button" value="-"');
                echo('onclick="$(\'#edu' . $eduCount . '\').remove(); return false;">');
                echo("</p></div>");
            }
        }
        ?>
            </div></p>
        <br>
        <p> <input type="submit" value="Save"/>
            <input type="submit" name="cancel" value="Cancel"></p>
    </form>
<script>
    countPos = <?= $posCount ?>;
    countEdu = <?= $eduCount ?>;

    // https://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function () {
        window.console && console.log("Document ready called");

        $('#addPos').click(function (event) {
            //http://api.jquery.com/event.preventdefault/
            event.preventDefault();
            if( countPos >= 9 ) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position " + countPos);
            $('#position_fields').append(
                '<div id="position'+countPos+'"> \
        <p>Year: <input type="text" size="10" name="year'+countPos+'" value="" /> \
        <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove(); return false;"></p> \
        <p><textarea name="desc'+countPos+'" rows="2" cols="80"></textarea></p>\
            </div>');
        });

        $('#addEdu').click(function(event){
            event.preventDefault();
            if ( countEdu >= 9 ) {
                alert("Maximum of nine education entries exceeded");
                return;
            }
            countEdu++;
            window.console && console.log("Adding education "+countEdu);

            // Grab some HTML with hot spots and insert into the DOM
            var source = $("#edu-template").html();
            $('#edu_fields').append(source.replace(/@COUNT@/g, countEdu));

            $('.school').autocomplete({
                source: "school.php"
            });
        });

        $('.school').autocomplete({
            source: "school.php"
        });
    });
</script>

<!-- HTML with substitution hot spots  -->
<script id="edu-template" type="text">
    <div id="edu@COUNT@">
    <p>Year: <input type="text" size="10" name="edu_year@COUNT@" value="" />&nbsp;&nbsp;&nbsp;
    School: <input type="text" size="40" name="edu_school@COUNT@" class="school" value="" />
    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;">
    </p></div>
</script>

</div>
<?php require_once "footer.php"; ?>