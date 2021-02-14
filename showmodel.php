<?php
$modelId = $_GET['model'];
    
$wordsToHighlight = [
"turnier",
"touring",
"tourer",
"wagon",
"estate",
"grand",
"sportback"
];
    
function highlightName($name){
    global $wordsToHighlight;
    $ret = $name;
    foreach($wordsToHighlight as $word){
        $ret = str_ireplace($word, "<mark>{$word}</mark>", $ret);
    }
    return $ret;
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
$sql = "select model_name, brand_name from model_brand where m_id = {$modelId}";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	//   output data of each row
	while($row = $result->fetch_assoc()) {
    
		echo "<h3>Results for {$row["brand_name"]} {$row["model_name"]}</h3>";
	}
} else {
	echo "0 results";
}
$sql = "SELECT brand_name, model_name, ridc_name, year, b_l_f, b_l, b_w, url FROM model_ridc where m_id = {$modelId} order by year desc";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	//   output data of each row
    echo "<table data-table-theme='default zebra'>
    <thead>
    <tr>
        <th>Name</th><th>Year</th>
        <th>Boot lenght with rear seats down</th><th>Boot lenght with rear seats up</th><th>Boot width at narrowest point</th>
    </tr>
    </thead>
    <tbody>";
	while($row = $result->fetch_assoc()) {
        $name = highlightName($row["ridc_name"]);
        echo "<tr>
        <td><a href = '{$row["url"]}' target='_blank'>{$name}</a></td>
        <td> {$row["year"]} </td>
        <td>{$row["b_l_f"]}</td><td> {$row["b_l"]} </td><td> {$row["b_w"]} </td>
        </tr>";
	}
    echo "</tbody></table>";
} else {
	echo "0 results";
}

$conn->close();
?>
