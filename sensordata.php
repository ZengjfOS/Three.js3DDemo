<?php header("Access-Control-Allow-Origin: *") ?>
<?php
// echo '200 ok';
$user="root";
$password="aplex";
$database="sensor";
$mysqli = new mysqli("127.0.0.1", $user, $password, $database);
$aresult = $mysqli->query("select * from sensor order by id desc limit 0,1");
$row = $aresult->fetch_assoc();
//echo $row["pwd"];
if($row>0){
    echo '{"code":200,"data":{'.'"airPress":'.$row["AirPress"].',"temperature":'.$row["Temperature"].',"humidity":'.$row["Humidity"].',"noise" :'.$row["Noise"].'}}';
}else{
    echo '{"code":400}';
}
/* free result set */
$aresult->free();
/* close connection */
$mysqli->close();
?>