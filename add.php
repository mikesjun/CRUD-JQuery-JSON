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

// Handle incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name'])
    && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

    // Data validation
    $msg = validateProfile();
    if (is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }
    // Validate position entries, if present
    $msg = validatePos();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }
    // Run if Data is valid
    $stmt = $pdo->prepare('INSERT INTO Profile 
            (user_id, first_name, last_name, email, headline, summary) 
            VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );
    $profile_id = $pdo->lastInsertId();

    // Insert Position entries
    $rank = 1;
    for ( $i = 1; $i <= 9; $i++ ) {
        if ( ! isset($_POST['year' . $i]) ) continue;
        if ( ! isset($_POST['desc' . $i]) ) continue;
        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];

        $stmt = $pdo->prepare('INSERT INTO Position 
            (profile_id, rank, year, description) 
            VALUES ( :pid, :rank, :year, :desc )');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }

    // Insert Education entries
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if ( ! isset($_POST['edu_year' . $i]) ) continue;
        if ( ! isset($_POST['edu_school' . $i]) ) continue;
        $year = $_POST['edu_year' . $i];
        $school = $_POST['edu_school' . $i];

        // Lookup the school if it is there
        $institution_id = false;
        $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :name');
        $stmt->execute(array(':name' => $school));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) $institution_id = $row['institution_id'];

        // If there was no institution, insert it
        if ($institution_id === false) {
            $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:name)');
            $stmt->execute(array(':name' => $school));
            $institution_id = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare('INSERT INTO Education 
            (profile_id, rank, year, institution_id) VALUES ( :pid, :rank, :year, :iid )');
        $stmt->execute(array(
                ':pid' => $profile_id,
                ':rank' => $rank,
                ':year' => $year,
                ':iid' => $institution_id)
        );
        $rank++;
    }


    $_SESSION['success'] = "Profile added.";
    header( 'Location: index.php' ) ;
    return;
}
?>
<div class="container">
    <h1>Adding Profile for: <?= htmlentities($_SESSION['name']); ?></h1>
    <?php flashMessages(); ?>
    <form method="post">
        <p><label>First Name: </label>
            <input type="text" name="first_name" size="60"/></p>
        <p><label>Last Name: </label>
            <input type="text" name="last_name" size="60"/></p>
        <p><label>Email: </label>
            <input type="text" name="email" size="30"/></p>
        <p><label>Headline:</label>
            <input type="text" name="headline" size="60"/></p>
        <p><label>Summary: </label><br>
            <textarea name="summary" rows="4" cols="80"></textarea></p>
        <p><label>Position: <input type="submit" id="addPos" value="+"></label>
            <div id="position_fields"></div></p>
        <p><label>Education: <input type="submit" id="addEdu" value="+"></label>
            <div id="edu_fields"></div></p><br>
        <p>
            <input type="submit" name="add" value="Add">
            <input type="submit" name="cancel" value="Cancel"></p>
    </form>

<script>
    countPos = 0;
    countEdu = 0;

    // https://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function () {
        window.console && console.log("Document ready called");

        $('#addPos').click(function (event) {
        event.preventDefault(); // http://api.jquery.com/event.preventdefault/

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