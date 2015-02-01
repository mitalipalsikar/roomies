<?php
/*
To do:
1 Open header
2 Include header.php
3 Output drop-down menu
4 Close header
*/
?>
<div class="header">
  <div class="header-padding">
    <div class="header-div">
    </div>
    <div class="header-div logo">
      <?php require_once "header.php";?>
    </div>
    <div class="header-div">
      <ul class="ul header-menu" style="height:auto;">
      <li class="li">
        <a class="a" href="<?php echo $home;?>profile/" title="My profile">
          <?php echo $_SESSION['user']['username'];?>
        </a>
      </li>
      <li class="li">
        <a class="a settings door" href="?logout=yes" title="Logout">
        </a>
      </li>
      <li class="li">
        <a class="a settings cog" href="<?php echo $home;?>settings/" title="Account Settings">
        </a>
      </li>
    </ul>
    </div>
  </div>
</div>
<div class="header-space"></div>
