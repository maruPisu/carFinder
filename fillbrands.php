<?php
class Car{
	public $name;
	public $url1;
	public $url2;
}

$cars = array();

function url_exists($url) {
	// Use get_headers() function 
	 $headers = @get_headers($url); 
	   
	   // Use condition to check the existence of URL 
	 if($headers && strpos( $headers[0], '404')) { 
		return false;
	 } 
	 else { 
		return true;
	 } 
}

$html = file_get_contents("https://www.ultimatespecs.com/");
$dom = new DOMDocument();
@ $dom->loadHTML($html);

$finder = new DomXPath($dom);

$classname = "home_brand_over";
$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
foreach( $nodes as $h2 ) {
	$name = str_replace(" ", "-",trim($h2->textContent));
	if(strpos($name, "Motorcycles") !== false or strcmp($name, "View-more") == 0){
		continue;
	}

	$url1 = "http://www.ultimatespecs.com/car-specs/{$name}-models";
	$url2 = "https://www.ridc.org.uk/features-reviews/out-and-about/choosing-car/make/{$name}";
	$car = new Car();
	$car->name = $name;
	$car->url1 = $url1;
	$car->url2 = $url2;
	$cars[] = $car;
}

$conn = new mysqli($servername, $username, $password, $dbname);
 //Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}


foreach($cars as $car){
	echo "<a href = '{$car->url1}'>{$car->name}</a>";
	if(url_exists($car->url2)){
		echo "- <a href = '{$car->url2}'>RiDC</a><br />";
	}else{
		$car->url2 = "";
		echo "<br />";
	}
	$sql = "insert into brand (name, url_us, url_ridc)
		VALUES('{$car->name}', '{$car->url1}', '{$car->url2}')";

	if ($conn->query($sql) === TRUE) {
		  echo "New record created successfully";
	} else {
		  echo "Error: " . $sql . "<br>" . $conn->error;
	}
}
$conn->close();
?>
