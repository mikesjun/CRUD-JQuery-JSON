<?php
    require_once "pdo.php";
    require_once "util.php";
    require_once "header.php";
    session_start();

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser when Cancel is pressed
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';

if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['email']) < 0 || strlen($_POST['pass']) < 0 ) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: login.php");
        return;
    }

    $check = hash('md5', $salt . $_POST['pass']);
    $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
    $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ( $row !== false ) {
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        // Redirect the browser to index.php
        header("Location: index.php");
        return;
    } else {
        $_SESSION['error'] = "Incorrect Password";
        header("Location: login.php");
        return;
    }
}
?>
<div class="container">
    <h1>Please Log In</h1>
    <?php flashMessages(); ?>
    <form method="POST" action="login.php">
        <p><label for="email">Email </label>
            <input type="text" name="email" id="email"></p>
        <p><label for="id_1723">Password </label>
            <input type="password" name="pass" id="id_1723"></p>
        <p><input type="submit" onclick="return doValidate();" value="Log In">
            <input type="submit" name="cancel" value="Cancel"></p>
    </form>
    <script>
        function doValidate() {
            console.log('Validating...');
            try {
                addr = document.getElementById('email').value;
                pw = document.getElementById('id_1723').value;
                console.log("Validating addr="+addr+" pw="+pw);

                if (addr == null || addr == "" || pw == null || pw == "") {
                    alert("Both fields must be filled out");
                    return false;
                }
                if (addr.indexOf('@') == -1 ) {
                    alert("Invalid email address");
                    return false;
                }
                return true;
            } catch(e) {
                return false;
            }
            return false;
        }
    </script>
</div>
<?php require_once "footer.php"; ?>