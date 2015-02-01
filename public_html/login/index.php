<?php
/*
Do not initialise REQUIRE_SESSION. We do not need to check that. Initialise. Output
homepage content.
*/

// Includes the init file
require_once '../inc/init.php';
// If logged out, show homepage, then exit the script.

/* You may have the following php vars:

-If error during register/login:
>>>> $_GET('err') can be
-> 'confpass', if confirm password does not match with password
-> 'emailexists' if email already used
-> 'invalid' if the user inserted invalid characters
-> 'locked' if the user got the pass wrong >= 5 times in last 2 hrs (acc locked)
---> 't', the time in seconds until acc wil unlock
-> 'incorrect' if the pass/email are incorrect OR if the email does not exist

>>>> $ioStatus can be
-> 'in', if the user is logged in
-> 'out', if the user is not logged in
*/

// Include the head
$title = "Welcome Roomies";
$dots = "";
$home = 1;
require_once __ROOT__."/inc/html/head.php";
require_once __ROOT__."/inc/html/header.$ioStatus.php";

if(!LOGGED_IN)
{
?>
	<!-- Main content -->
	<div class="main">
		<!-- Hidden title -->
		<div class="not-mobile banner">
			<header>
				<h1 class="h1">Welcome to Roomies</h1>
				<p class="text">Find the perfect room-mate.</p>
			</header>
		</div>
		<!-- Sign in / Register -->
		<div class="column-wrapper">
			<!-- Sign in -->
			<div class="column-2">
				<div class="column-box">
					<div class="box-padding">
						<h2 class="h2" id="Sign_in">Sign in</h2>
						<form method="POST" name="signin" action="./login/index.php" onsubmit="return this.email.value?this.password.value?true:(this.password.focus(),false):(this.email.focus(),false)">
							<input type="text" name="login" placeholder="Email/Username" class="input block" required>
							<input type="password" name="password" placeholder="Password" class="input block" required pattern=".{6,25}" title="6 to 25 characters">
							<input type="submit" value="Sign in" class="input-button block">
						</form>
					</div>
				</div>
			</div>
			<!-- Register -->
			<div class="column-2">
				<div class="column-box">
					<div class="box-padding">
						<h2 class="h2" id="Register">Register</h2>
						<form method="POST" name="register" action="./confirm/" onsubmit="return this.registerEmail.value?this.registerPassword.value?this.registerPassword.value===this.registerConfirmPassword.value?true:(this.registerConfirmPassword.focus(),false):(this.registerPassword.focus(),false):(this.registerEmail.focus(),false)">
							<input type="email" name="registerEmail" placeholder="Email" class="input block" required>
							<input type="password" name="registerPassword" placeholder="Password" class="input block" required pattern=".{6,25}" title="6 to 25 characters">
							<input type="password" name="registerConfirmPassword" placeholder="Confirm Password" class="input block" required pattern=".{6,25}" title="6 to 25 characters">
              <input type="text" name="registerUsername" placeholder="Username" class="input block" required pattern=".{4,25}" title="4 to 25 characters">
							<p class="small-text">By registering, you agree to our
								<a href="#terms" class="link">Terms</a> and
								<a href="#privacy" class="link">Privacy Policy</a>, including our
								<a href="#cookies" class="link">Cookie Use</a>.
							</p>
							<input type="submit" value="Register" class="input-button block">
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- Accommodation Reviews -->
		<div class="box">
			<div class="box-padding">
				<h2 class="h2" id="Accommodation_reviews">Accommodation reviews</h2>
				<form method="GET">
					<select name="filter" class="select has-submit" required>
						<option class="option" value="" selected>Choose a University</option>
						<option class="option" value="1">University of Manchester</option>
					</select
					><input type="submit" value="Filter" class="input-button select-submit">
					<a href="#" class="link-button float-right">View All</a>
				</form>
			</div>
		</div>
<?php require_once __ROOT__."/inc/html/footer.php";?>
<?php
exit();
}// if(!LOGGED_IN)


// Else, we show the homepage for logged in users
?>
<!--html code for logged in homepage-->

	<html>
	<head>
		<link rel="stylesheet" type="text/css" href="styles.css">
	</head>

	<body class = "body">

	<div class = "left-box">

		<p class = "h1"> Hello, username! </p>
		
		<img class = "img" src="profile.jpg" alt="profile picture" style="width:125px;height:125px;" >
		
		
	    	<ul class = "ul">
	    		<li class = "link-button"> <a href = "#profile" class = "links">Friend Requests </a></li>
	    		<br>
	    		<li class = "links-box"> <a href = "#profile" class = "links">Messages </a></li>
	    		<br>
	    		<li class = "links-box"> <a href = "#profile" class = "links">Review </a></li>
	    		<br>
	    	</ul>
	    </div>
	</div>

    <div class = "column-box"> 
    	<p class = "h2">Recent Matches</p> 
	    <ul class = "search-element">
	    <li > Whatever #1 </li>
	    <br>
	    <li> Whatever #2 </li>
	    <br>
		</ul>
	</div>

    <div class = "reviews"> 
    	<p class = "h2"> Popular Accommodation </p>
    	<ul class = "review-element">
    		<li> Whitworth Park </li>
    		<br>
    		<li> Dalton-Ellis 	</li>
    		<br>
    		<li> Burkhardt House</li>
    	</ul>

    </div>
</body>
</html>



<?php 
if(isset($_GET['logout']))
{
  session_destroy();
  header("Location: .");
  exit();
}

//Check if the user completed their profile
if(isset($_SESSION['notComplete']))
{
	header("Location: complete-register/");
  exit();
}

// Check if user has completed their details in $comp boolean
$id = $_SESSION['user']['id'];
$stmt = $con->prepare("SELECT completed FROM rdetails WHERE profile_filter_id = $id");
$stmt->execute();
$stmt->bindColumn(1, $comp);
$stmt->fetch();


?>
  <!--Main content-->
  <div class="main">
    <?php if(!$comp){include "./complete-register/optionalDetails.php";}?>

    <?php require_once __ROOT__."/inc/html/footer.php";?>
  </div>
</body>
</html>