<?php
/*
You are going to make the html for the profile of the current 
logged in user.
The header would already be included.
Please read the gantt chart note for this task (task 10)

<!DOCTYPE>, <html> and <body> already started
*/

// Initialise the session (do not modify this)
define("REQUIRE_SESSION", true);
include '../../inc/init.php';

// Controls the relative path of the stylesheets in "head.php"
$dots = "../";
// The title of the page
if(!isset($_GET['u']))
{
  // I'm on my profile
  $title = "My profile";
  $mine = true;
}
else
{
  // I'm on another user's profile
  $title = "$_GET[u]'s profile";
}
// Include head and header
require_once __ROOT__."/inc/html/head.php";
require_once __ROOT__."/inc/html/header.$ioStatus.php";
// Page begins here, html and body tags are opened in head, closed in footer. Also, main div is closed in footer
?>
<!-- Main content -->
<div class="main">
  Something

<?php require_once __ROOT__."/inc/html/footer.php";?>