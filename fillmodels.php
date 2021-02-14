<?php
class Car{
	public $name;
    public $url_us;
}


function fixName($name){
    if($name == "Focus (Europe)"){
        $retname = "Focus";
    }
    return trim($retname);
}

$brandIdGet = $_GET["brand"];

function fillModels($brandId){
    global $servername, $username, $password, $dbname;
    // retrieve url from brand page

    $cars = array();
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "select name, url_us, url_ridc from brand where id = {$brandId}";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        //   output data of each row
        while($row = $result->fetch_assoc()) {
            $url_us = $row["url_us"];
            $url_ridc = $row["url_ridc"];
            $brand = $row["name"];
            echo "showing {$brand} <br />";
            echo "<a href = '{$row["url_us"]}' > UltimateSpecs </a>";
            if($row["url_ridc"] != ""){
                echo " - <a href = '{$row["url_ridc"]}' > RiDC </a> <br />";
            }
        }
    } else {
        echo "0 results";
    }
    echo "<hr />";
    
    $html = file_get_contents($url_us);
    $dom = new DOMDocument();
    @ $dom->loadHTML($html);
    $finder = new DomXPath($dom);

    $classname = "home_models_over";
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
    foreach( $nodes as $h2 ) {
        //$name = str_replace(" ", "-",trim($h2->textContent));
        $name = $h2->getElementsByTagName("h2")[0]->textContent;
        $anode = $h2->parentNode->parentNode;
        //echo $anode->nodeName . " - " . $anode->getAttribute("href");
        $link = "https://www.ultimatespecs.com" . $anode->getAttribute("href"); 
        
        $car = new Car();
        $car->name = fixName($name);
        $car->url_us = $link;
        $cars[] = $car;
    }

    foreach($cars as $car){
        echo "<a href = '?model={$car->name}'>{$car->name}</a> </br>";
        $sql = "insert ignore into model (name, brand, url_us) VALUES('{$car->name}', '{$brandId}', '{$car->url_us}')";

        if ($conn->query($sql) === TRUE) {
              echo "New record created successfully";
        } else {
              echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    $conn->close();
}

fillModels(18);
//for($i = 1; $i <54 ;$i++){
//    fillModels($i);
//}

?>
