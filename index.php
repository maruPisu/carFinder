<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include 'config.php';
?>
<html>
<head> 
<link rel="stylesheet" href="style.css">
<title>Car finder</title> </head>
<body>
<div class = "container">
  <header class="header">
<h1> Car Finder</h1>
  </header>
  
  <div class = "sidebar">
  <?php
	include 'showbrands.php';
  ?>
  </div>
  <div>
<?php

if(isset($_GET["fill"])){
    if($_GET["fill"] == "ridc"){    //fillers are only callable via get param
        include 'fillgen_ridc.php';
    } else if($_GET["fill"] == "us"){
        include 'fillgen_us.php';
    } else if($_GET["fill"] == "models"){
        include 'fillmodels.php';
    }
}else if(isset($_GET["brand"])){
    //brand get param
	include 'showmodels.php';
	//include 'fillmodels.php';
}
?>
</div>
  <div class = "content">
<?php

if(isset($_GET["model"])){
    //brand get param
	include 'showmodel.php';
}
?>
</div>
<div class ="footer">
</div>
</div>
</body>
</html>
