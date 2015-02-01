<?php
/*
Set REQUIRE_SESSION (false). Set REQUIRE_NO_SESSION (true). Initalise. Receive
possible _POST['email'], _POST['password1'], and _POST['password2'] from
register form. Validate and check against database. If valid, create unique
validation code, add this to temp table with email and encrypted password, send
mail for validation (for now, just header to
../confirm?code=<confirmationcode>), and output confirm message. If any
invalid, output error messages along with form.
*/

// To implement CSRF token

define('REQUIRE_SESSION', FALSE);
require_once '../../inc/init.php';

// If these are set, proceed. Else, something wrong happened
if(isset($_POST['registerEmail'], $_POST['registerPassword'], $_POST['registerConfirmPassword'], 
  $_POST['registerUsername']))
{
  $username = htmlentities(filter_input(INPUT_POST, 'registerUsername', FILTER_SANITIZE_STRING));
  $email = htmlentities(filter_input(INPUT_POST, 'registerEmail', FILTER_VALIDATE_EMAIL));
  $password1 = htmlentities(filter_input(INPUT_POST, 'registerPassword', FILTER_SANITIZE_STRING));
  $password2 = htmlentities(filter_input(INPUT_POST, 'registerConfirmPassword', FILTER_SANITIZE_STRING));

  // Check if invalid characters
  if($password1 != $_POST['registerPassword'] || $password2 != $_POST['registerConfirmPassword'] 
    || $email != $_POST['registerEmail'] || $username != $_POST['registerUsername'])
  {
    header('Location: ../?err=invalid');
    exit();
  }

  // Check if confirm pass == pass
  if($password1 != $password2)
  {
    header('Location: ../?err=confpass');
    exit();
  }

  // Check if email or username already registered
  $stmt = $con->prepare("SELECT user_id FROM rusers 
                          WHERE user_email = '$email' OR username = '$username'");
  $stmt->execute();
  if($stmt->rowCount() >= 1)
  {
    $stmt = null;
    header('Location: ../?err=emailexists');
    exit();
  }

  $stmt = $con->prepare("SELECT temp_id FROM rtempusers 
                          WHERE temp_email = '$email' OR temp_username = '$username'");
  $stmt->execute();
  if($stmt->rowCount() >= 1)
  {
    $stmt = null;
    header('Location: ../?err=emailexists');
    exit();
  }

  // Create random salt, hash the pass
  $salt = mt_rand();
  $password = hash('sha256', $password1.$salt);


  // Create confirmation code, insert user in temp table
  $confCode = substr(mt_rand(), 0, 6);

  $stmt = $con->prepare("INSERT INTO rtempusers (temp_email, temp_username, temp_pass, temp_salt, conf) 
                         VALUES ('$email', '$username', '$password', '$salt', '$confCode')");
  $stmt->execute();

  // Save temp user into session
  $_SESSION['tempUser']['email'] = $email;
  $_SESSION['tempUser']['username'] = $username;
  $_SESSION['tempUser']['conf'] = $confCode;

  header("Location: ./?user=$username&conf=$confCode");
  // Send mail to user with conf code, disabled for now
  /*
  
  $to = "$email";
  $subject = "Confirmation Token";
  
  $message = "Hello, dear user,<br><br> Here is your confirmation token. Please copy
              it in the confirmation box and submit.<br>    $confCode<br><br>Regards,
               Roomies team.
  
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
  $headers .= 'From: <webmaster@roomies.co.uk >';
  
  if(!mail($to, $subject, $message, $headers))
  {
    $error_msg .= "Problem with sending mail";
  }
  */

  // Send user to confirm page with correct conf code. Enabled for testing
  $stmt = null;
}

/*
Page that lets user confirm their email. If user tries to log in but has not
confirmed their email, they are sent to this page.
Also, they are sent here right after registration.
User can confirm completing the input box with the code
or accessing this page with conf variable set to the conf code
If temp user not in session, a "user" get variable is needed for checking in db
*/

// Complete registration for given username
function completeReg($con, $username)
{
  $stmt = $con->prepare("SELECT temp_email, temp_pass, temp_salt FROM rtempusers
                          Where temp_username = '$username'");
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  $stmt = $con->prepare("INSERT INTO rusers (username, user_email, user_pass, user_salt)
                          VALUES ('$username', '$result[temp_email]', '$result[temp_pass]',
                                   '$result[temp_salt]')");
  $stmt->execute();

  $stmt = $con->prepare("SELECT user_id FROM rusers WHERE username = '$username'");
  $stmt->execute();
  $stmt->bindColumn(1, $id);

  $stmt = $con->prepare("DELETE FROM rtempusers WHERE temp_username = '$username'");
  $stmt->execute();

  // Set session user vars. To be implemented as objects
  $_SESSION['user']['username'] = $username;
  $_SESSION['user']['id'] = $id;
  $_SESSION['user']['email'] = $result['temp_email'];

  $stmt = null;
  header('Location: ../complete-register/');
  exit();
}



if(isset($_SESSION['tempUser']))
{
  $tempUsername = $_SESSION['tempUser']['username'];
}
else if(isset($_GET['user'], $_GET['conf']))
{
  $tempUsername = $_GET['user'];
}
else
{
  header('Location: ../?err=needlogin');
  exit();
}

// Get the db conf code for this user
$stmt = $con->prepare("SELECT conf FROM rtempusers WHERE temp_username = '$tempUsername'");
$stmt->execute();
$stmt->bindColumn(1, $dbConf);
$stmt->fetch();

// Check if the user exists. If it does, he must have a conf code.s
if($stmt->rowCount() != 1)
{
  $stmt = null;
  require_once '../../init/html/notfound.php';
  exit();
}

// If confirm code is not in GET, output box
if(isset($_GET['conf']))
{
  $showError = false;

  if($_GET['conf'] == $dbConf)
  {
    // Correct conf code => put user in perm users table and login
    $stmt = null;
    completeReg($con, $tempUsername);
  }
  else
  {
    $showError = true;
    $stmt = null;
  }
}

echo $_SESSION['tempUser']['conf'];

if(isset($_GET['logout']))
{
  session_destroy();
  header("Location: ../");
  exit();
}

$title = "Confirm";
$dots = "../"

?>
<?php require_once __ROOT__."/inc/html/head.php";?>
    <?php require_once __ROOT__."/inc/html/header.".$ioStatus.".php";?>
    <!--Header, etc-->

    <!-- test form-->
    <form method="GET" action="">
      <input type="text" name="conf" placeholder="Input confirmation code">
      <button type="submit">Submit</button>
      <a href="./?logout=yes">logout</a>
    </form>
    <?php require_once __ROOT__."/inc/html/footer.php";?>
  </body>
</html>