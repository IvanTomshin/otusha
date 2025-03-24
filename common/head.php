<?php
header("content-type: text/html; encoding=utf-8");

?><!DOCTYPE html>
<html lang="RU">
<head>
<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta http-equiv="Content-Language" content="ru-RU" />
<link rel="stylesheet" href="/common/style.css" />
<title><?php echo $_title;?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
</head>
<body>

<header>
  <!--NAV-->
  <nav>
    <ul>
      <li><a href="/login">Login</a></li>
<!--      <li><a href="#">Contact</a></li> //-->
    </ul> 
    <h2>OTUS Social network</h2>
  </nav>  
</header> 


<?php if ($_GET['msg']>'') { ?>
<br><br>
<section class="about-me" id="me">
  <p><?php echo $_GET['msg']?></p>
</section>
<?php } ?>



<div class="main">