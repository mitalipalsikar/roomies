<?php
/*
Page that lets user confirm their email. If user tries to log in but has not
confirmed their email, they are sent to this page.
Also, they are sent here right after registration.
User can confirm completing the input box with the code
or accessing this page with conf variable set to the conf code
If temp user not in session, a "user" get variable is needed for checking in db
*/
define('REQUIRE_SESSION', FALSE);
require_once '../inc/init.php';

if(isset($_GET['conf']))
{
  // Check if user is in session, or clicked link from email without being in session
  if(isset($_SESSION['tempUser']))
  {
    if($_GET['conf'] == $_SESSION['tempUser']['conf'])
    {
      // Means the user inserted correct conf code, so insert into db
      $email = $_SESSION['tempUser']['email'];
      // Get pass and salt from temp table
      $stmt = $con->prepare("SELECT temp_pass, temp_salt, temp_username FROM rtempusers WHERE temp_email = '$email'");
      $stmt->execute();
      $stmt->bindColumn(1, $pass);
      $stmt->bindColumn(2, $salt);
      $stmt->bindColumn(3, $username);
      $stmt->fetch();

      // Insert new user into users table
      $stmt = $con->prepare("INSERT INTO rusers (user_email, username, user_pass, user_salt) 
                            VALUES ('$email', '$username', '$pass', '$salt')");
      $stmt->execute();

      // Delete from temp users
      $stmt = $con->prepare("DELETE FROM rtempusers WHERE temp_email = '$email'");
      $stmt->execute();
      
      // Get user's id, and set the user in session
      $stmt = $con->prepare("SELECT user_id FROM rusers WHERE user_email = '$email'");
      $stmt->execute();
      $stmt->bindColumn(1, $id);
      $stmt->fetch();

      if($id)
      {
        $_SESSION['user']['id'] = $id;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['username'] = $username;
      }

      // Send user to index
      header('Location: .');
      exit();
    }// if get[conf] == session [tempuser]
  }// if isset temp user
  else
  {
    // Check if both conf and username are set
    if(isset($_GET['user']))
    {
      // Get the values
      $username = htmlentities(filter_input(INPUT_GET, 'user', FILTER_SANITIZE_STRING));
      $confCode = htmlentities(filter_input(INPUT_GET, 'conf', FILTER_SANITIZE_STRING));

      // Check the db if code is correct to user
      $stmt = $con->prepare("SELECT conf FROM rtempusers WHERE temp_username = $username");
      $stmt->execute();
      $stmt->bindColumn(1, $dbConfCode);
      $stmt->fetch();

      if($dbConfCode == $confCode)
      {
        // Set user in session with their email, and the same confirm
        $stmt = $con->prepare("SELECT temp_email FROM rtempusers WHERE temp_username = $username");
        $stmt->execute();
        $stmt->bindColumn(1, $email);
        $_SESSION['tempUser']['email'] = $email;
        header('Location /confirm.php?conf='.$confCode);
      }
    }
    else
    {
      // Something wrong, not found
      header("Location: /errors/notfound.php");
    }
  }// else
}
else
{
  // WTF are you here for then?
  header("Location: /errors/notfound.php");
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>
      Welcome to Roomies!
    </title>

    <!--STUFF-->
  </head>
  <body>

    <!--Header, etc-->

    <!-- test form-->
    <form method="GET" action="">
      <input type="text" name="conf" placeholder="Input confirmation code">
      <button type="submit">
    </form>
  </body>
</html>