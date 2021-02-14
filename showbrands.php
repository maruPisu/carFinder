<?php
		echo "<h3>Brands</h3>";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$sql = "select id, name, url_us, url_ridc from brand";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	//   output data of each row
	while($row = $result->fetch_assoc()) {
    
		echo "<div>
        <a href = '?brand={$row["id"]}' > {$row["name"]} </a>
        <div style='float:right;'>"; 
        if($row["url_ridc"] != ""){
            echo "<div class = 'crop' style = 'display:inline-block;''><a href = '{$row["url_ridc"]}' target='_blank' >  <img src='ridc.png'>  </a></div>";
        }
        echo "
		<a href = '{$row["url_us"]}' target='_blank' > <img src='us.png' width = '20px' height = '20px'> </a>
        </div></div>
        <br>";
	}
} else {
	echo "0 results";
}

$conn->close();


?>
