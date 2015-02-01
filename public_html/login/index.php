<?php
/*
Set REQUIRE_SESSION (false). Set REQUIRE_NO_SESSION (true). Initalise. 
Receive by POST 'email' and 'password'
Check the log table to see if brute-force attack (more than 5 wrong pass in last 2 hours)
-> if it is, show error (possibly blocking the account for time/sending email)
Check the password against the db pass
-> if it's valid, log in
-> if not valid, record in log table
*/

define('REQUIRE_SESSION', FALSE);
require_once '../../inc/init.php';
echo "shit";
// If these are set, proceed. Else, something wrong happened
if(isset($_POST['login'], $_POST['password']))
{

  $login = htmlentities($_POST['login']);
  $password = htmlentities(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));

  // Check if valid chars
  if($login != $_POST['login'] || $password != $_POST['password'])
  {
    header("Location: ../?err=invalid");
    exit();
  }

  /*
  Check if brute-force attack. If user got the pass wrong over 5 times in last two hours
  then something is not right. So it will throw an error.
  */
  $stmt = $con->prepare("SELECT log_time FROM rlog WHERE log_email = '$login' 
                          OR log_username = '$login'  LIMIT 1 OFFSET 4");
  $stmt->execute();
  $stmt->bindColumn(1, $time);
  $stmt->fetch();


  if($stmt->rowCount() == 1 && (strtotime($time) + 7200 > time()))
  {
    // Error acc blocked, must pass at least 2 hrs
    $stmt = null;
    $timeLeft = strtotime($time) + 7200 - time();
    header("Location: ../?err=locked&t=".$timeLeft);
    exit();
  }

  // Check the pass against the one in db. If incorrect, will be logged
  $stmt = $con->prepare("SELECT user_id, user_pass, username, user_salt FROM rusers 
                          WHERE user_email = '$login' OR username = '$login' ");
  $stmt->execute();
  $stmt->bindColumn(1, $id);
  $stmt->bindColumn(2, $dbPassword);
  $stmt->bindColumn(3, $username);
  $stmt->bindColumn(4, $salt);
  $stmt->fetch();

  if(($stmt->rowCount() == 1) && (hash('sha256', $password.$salt) == $dbPassword))
  {
    // Successfully logged in
    $_SESSION['user']['id'] = $id;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['username'] = $username;

    // Check whether the user has completed his profile
    $stmt = $con->prepare("SELECT profile_filter_id FROM rdetails WHERE profile_filter_id = $id");
    $stmt->execute();
    $stmt->bindColumn(1, $profileId);
    $stmt->fetch();

    if(!$stmt->rowCount())
    {
      // The user has to complete his profile
      $stmt = null;
      $_SESSION['notComplete'] = true;
      header("Location: ../complete-register/");
      exit();
    }

    $stmt = null;
    header("Location: ../");
    exit();
  }
  else
  {
    if($stmt->rowCount() == 1)
    {
      // The pass is wrong so log it
      $timeStamp = gmdate("Y-m-d H:i:s", time());
      $stmt = $con->prepare("INSERT INTO rlog (log_email, log_time, log_username) 
                              VALUES ('$email', '$timeStamp', '$username')");
      $stmt->execute();

      $stmt = null;
      header("Location: ../?err=incorrect1");
      exit();
    }
    else
    {
      // No email was found. Check temp users
      $stmt = $con->prepare("SELECT temp_username, temp_pass, temp_salt, conf, temp_email 
                              FROM rtempusers
                              WHERE temp_email = '$login' OR temp_username = '$login'");
      $stmt->execute();
      $stmt->bindColumn(1, $tempUsername);
      $stmt->bindColumn(2, $tempDbPassword);
      $stmt->bindColumn(3, $tempSalt);
      $stmt->bindColumn(4, $confCode);
      $stmt->bindColumn(5, $tempEmail);
      $stmt->fetch();

      if(($stmt->rowCount() == 1) && (hash('sha256', $password.$tempSalt) == $tempDbPassword))
      {
        // The user is in temp table, so send him to conf page
        $_SESSION['tempUser']['username'] = $tempUsername;
        $_SESSION['tempUser']['conf'] = $confCode;
        $_SESSION['tempUser']['email'] = $tempEmail;

        $stmt = null;
        header("Location: ../confirm/");
        exit();
      }
      else
      {
        $stmt = null;
        header("Location: ../?err=incorrect2");
        exit();
      }
    }
  }
}
else
{
  // Should go to 404
  echo "fuck not found";
}
?>