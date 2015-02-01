<?php
/*
To do:
1 Output header content
*/
if(isset($home))
{
  $home = "";
}
else
{
  $home = "../";
}
?>
<a href="/" class="logo-link" title="Home">
	<img <?php echo "src=".$home."media/img/logo.svg";?> alt="Roomies" class="logo-img">
</a>
