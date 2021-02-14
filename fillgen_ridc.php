<?php

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

class RidcCar{
	public $url;
	public $fullname;
	public $name;
	public $year;
    public $model_id;
    public $boot_lenght_tot;
    public $boot_lenght;
    public $boot_width;
    
    public function __toString()
    {
        return $this->name . ": " .
                " year: " . $this->year . 
                " model: " . $this->model_id . 
                " boot l tot: " . $this->boot_lenght_tot . 
                " boot l: " . $this->boot_lenght . 
                " boot w: " . $this->boot_width;
    }
}

$cars = array();


$url_basename = "https://www.ridc.org.uk";

function getNodesFromClass($finder, $class){
    $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]");  
    if(count($nodes) > 0){
        $val = $nodes[0]->getElementsByTagName("div")[1]->textContent;
        return substr($val,0, -2);
    }        
}

function getModelId($result){
    if ($result->num_rows > 1) {
        // take the biggest match... might not work fine
        $match = "";
        $match_id = 0;
        while($row = $result->fetch_assoc()) {
            if(strlen($match) < strlen($row{"model_name"})){
                $match = $row["model_name"];
                $match_id = $row["m_id"];
            }
            return $match_id;
        }      
    }else{
        while($row = $result->fetch_assoc()) {
            return $row["m_id"];
        }            
    }    
}

function fillCarDetails($url, $name, $brand, $brand_id){
    global $conn;
    $html = file_get_contents($url);
    $dom = new DOMDocument();
    @ $dom->loadHTML($html);

    $finder = new DomXPath($dom);
    $car = new RidcCar();
    $car->boot_lenght_tot = getNodesFromClass($finder, "field--name-field-length-of-boot-floor-back-"); 
    $car->boot_lenght = getNodesFromClass($finder,     "field field--name-field-length-of-boot-floor-back field--type-integer field--label-inline"); 
    $car->boot_width = getNodesFromClass($finder, "field--name-field-width-of-boot-floor-at-nar"); 
    $car->url = $url;
    $car->fullname = $name;
    if(is_numeric(substr($name, -4))){
        $car->year = substr($name, -4);
        $car->name = substr($name, 0, -5);
    }else{
        $car->year = 0;
        $car->name = $name;        
    }
    $car->name = trim(str_replace($brand, "", $car->name));
    
    if($brand == "BMW"){
        $serie = substr($car->name,0, 1);
        echo "***{$serie}***";
        if(is_numeric($serie)){
            echo "***{$serie} series***";
            $sql = "select m_id, brand_name, model_name from model_brand where '{$serie} series' like model_name and b_id = {$brand_id}";
            $result = $conn->query($sql);
            if ($result->num_rows == 1) {  //
                $car->model_id = getModelId($result);
                return $car;
            }
        }else{
            if($serie == "M"){
                $serie = substr($car->name, 1, 1);
                $sql = "select m_id, brand_name, model_name from model_brand where '{$serie} series' like model_name and b_id = {$brand_id}";
                $result = $conn->query($sql);
                if ($result->num_rows == 1) {  //
                    $car->model_id = getModelId($result);
                    return $car;
                }
            }
        }
    }
    
    //first word 100% match
    $firstWord = explode(" ",$car->name)[0];
    
    $sql = "select m_id, brand_name, model_name from model_brand where '{$firstWord}' like model_name and b_id = {$brand_id}";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {  //
        $car->model_id = getModelId($result);
        return $car;
    }
    //whole name %LIKE% match
    $sql = "select m_id, brand_name, model_name from model_brand where '{$car->name}' like concat('%',model_name,'%') and b_id = {$brand_id}";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $car->model_id = getModelId($result);
        return $car;
    }
    
    //same as before removing dashes
    if(strpos($car->name, "-")){
        $name2 = str_replace("-", " ", $car->name);
    }
    if(isset($name2)){
        $sql = "select m_id, brand_name, model_name from model_brand where '{$name2}' like concat('%',model_name,'%') and b_id = {$brand_id}";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $car->model_id = getModelId($result);
            return $car;
        }
    }
    
    //no match found :(
    return $car;
}

$brandId = $_GET["brand"];

$sql = "delete r from gen_ridc as r join model as m on m.id = r.model_id where m.brand ={$brandId}";

if ($conn->query($sql) === TRUE) {
      echo "records deleted successfully";
} else {
      echo "Error: " . $sql . "<br>" . $conn->error;
}

$sql = "select id, name, url_us, url_ridc from brand where url_ridc != '' and id = {$brandId}";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	//   output data of each row
	while($row = $result->fetch_assoc()) {
        $continue = true;
        $thisLink = $row["url_ridc"];
        $brand = str_replace("-", " " ,$row["name"]);
        $pageNum = 1;
        while($continue){
            $html = file_get_contents($thisLink);
            $dom = new DOMDocument();
            @ $dom->loadHTML($html);

            $finder = new DomXPath($dom);

            $classname = "list_inner";
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            foreach( $nodes as $h2 ) {
                $name = $h2->getElementsByTagName("header")[0]->getElementsByTagName("h3")[0]->getElementsByTagName("span")[0]->textContent;   
                $url = $url_basename . $h2->getAttribute("href");
                $thisCar = fillCarDetails($url, $name, $brand, $brandId);             
                echo strval($thisCar) . "<br>";
                $sql = "insert ignore into gen_ridc (full_name, name, url, model_id, year, b_l_f, b_l, b_w) 
                VALUES('{$thisCar->fullname}','{$thisCar->name}','{$thisCar->url}','{$thisCar->model_id}','{$thisCar->year}','{$thisCar->boot_lenght_tot}','{$thisCar->boot_lenght}','{$thisCar->boot_width}')";

                if ($conn->query($sql) === TRUE) {
                      echo "New record created successfully";
                } else {
                      echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
            $continue = strpos($html, "Go to next page") != false;
            $thisLink = $row["url_ridc"] . "?page={$pageNum}";
            $pageNum++;
        }
	}
} else {
	echo "0 results";
}

$conn->close();


?>
