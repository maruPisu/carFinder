<?php

class CarUs{
	public $name;
}

$cars = array();

$modelId = $_GET["model"];

// retrieve url from model page

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$sql = "select brand_name, brand_url_us, brand_url_ridc from model_brand where m_id = {$modelId}";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	//   output data of each row
	while($row = $result->fetch_assoc()) {
        $url_us = $row["brand_url_us"];
        $url_ridc = $row["brand_url_ridc"];
        $brand = $row["brand_name"];
        echo "showing {$brand} <br />";
		echo "<a href = '{$url_us}' > UltimateSpecs </a>";
        if($url_ridc != ""){
            echo " - <a href = '{$url_ridc}' > RiDC </a> <br />";
        }
	}
} else {
	echo "0 results";
}
echo "</ hr>";

die();

$html = file_get_contents($url_us);
$dom = new DOMDocument();
@ $dom->loadHTML($html);

$finder = new DomXPath($dom);

$classname = "home_models_over";
$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
foreach( $nodes as $h2 ) {
	//$name = str_replace(" ", "-",trim($h2->textContent));
    $name = $h2->getElementsByTagName("h2")[0]->textContent;
	$car = new CarUs();
	$car->name = $name;
	$cars[] = $car;
}

$conn = new mysqli($servername, $username, $password, $dbname);
 //Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}


foreach($cars as $car){
	echo "<a href = '?model={$car->name}'>{$car->name}</a> </br>";
	$sql = "insert ignore into model (name, brand) VALUES('{$car->name}', '{$brandId}')";

	if ($conn->query($sql) === TRUE) {
		  echo "New record created successfully";
	} else {
		  echo "Error: " . $sql . "<br>" . $conn->error;
	}
}
$conn->close();
?>
