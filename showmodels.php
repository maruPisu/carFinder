<?php

$brandId = $_GET["brand"];


class Obj{
	public $m_id;
	public $model_name;
	public $model_url_us;
    
    public function __construct(int $id, string $mn, string $url = null) {
        $this->m_id = $id;
        $this->model_name = $mn;
        $this->model_url_us = $url;
    }
}
$notInRidc = array();

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
$sql = "select name from brand where id = {$brandId}";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	//   output data of each row
	while($row = $result->fetch_assoc()) {
    
		echo "<h3>{$row["name"]} cars</h3>";
	}
} else {
	echo "0 results";
}

$sql = "select m.b_id, m.m_id, m.brand_name, m.model_name, m.model_url_us , count(r.m_id) as cnt
from model_brand as m left join model_ridc as r on m.m_id = r.m_id
where b_id = {$brandId}
group by m.b_id, m.m_id, m.brand_name, m.model_name, m.model_url_us 
order by model_name";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	//   output data of each row
	while($row = $result->fetch_assoc()) {
        if($row["cnt"] > 0){
    
        	echo "<div>
            <a href = '?brand={$brandId}&model={$row["m_id"]}' > {$row["model_name"]} ({$row["cnt"]}) </a>
            <div style='float:right;'>"; 
            echo "
            <a href = '{$row["model_url_us"]}' target='_blank' > <img src='us.png' width = '20px' height = '20px'> </a>
            </div></div>
            <br>";
        }else{
            $thisCar = new Obj($row["m_id"], $row["model_name"], $row["model_url_us"]);
            $notInRidc[] = $thisCar;
        }
	}
    
    echo "<hr /><h3>Cars not in RiDC</h3>";
    
    foreach($notInRidc as $car){    
        	echo "<div>
            <a href = '?brand={$brandId}&model={$car->m_id}' > {$car->model_name} </a>
            <div style='float:right;'>"; 
            echo "
            <a href = '{$car->model_url_us}' target='_blank' > <img src='us.png' width = '20px' height = '20px'> </a>
            </div></div>
            <br>";
        
    }
} else {
	echo "0 results";
}

$conn->close();


?>
