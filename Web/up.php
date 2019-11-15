<?php
 
function Hex2String($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

$input = file_get_contents('php://input');
$link = mysqli_connect('localhost', 'xxx', 'xxx', 'xxx');
mysqli_set_charset($link,'utf8');


$file = 'people.txt';



$manage =  json_decode($input, true);



$Payload = Hex2String($manage['Payload']); //46.6565,11.1502|0.0000|0.0000
$parts = explode('-', $Payload );
$cor = explode(',', $parts[0] );

$lat = $cor[0];
$lng = $cor[1];
$rid = $parts[1];
$air = $parts[2];




$date = mysqli_real_escape_string($link,$manage['DT']);
$snr = mysqli_real_escape_string($link,$manage['Extra']['JSONRXINFO'][0]['loRaSNR']);
$rssi = mysqli_real_escape_string($link,$manage['Extra']['JSONRXINFO'][0]['rssi']);
$bandwidth = mysqli_real_escape_string($link,$manage['Extra']['bandwidth']);
$cnt = mysqli_real_escape_string($link,$manage['Extra']['fCnt']);


$mysqltime = substr($date, 0, -6);



$sql = "INSERT INTO `lora`(`LAT`, `LNG`, `DATE`, `SNR`, `RSSI`, `BANDWIDTH`, `CNT`,`RID`, `RAW`) VALUES ('$lat','$lng','$mysqltime','$snr','$rssi','$bandwidth', '$cnt', '$rid', '$input') ";


$result = mysqli_query($link,$sql);




 

 ?>