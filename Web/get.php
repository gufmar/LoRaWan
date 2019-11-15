<?php

$limit = 1;

if (isset($_GET['limit'])) {
    $limit = $_GET['limit'];
}

$link = mysqli_connect('localhost', 'xxx', 'xxx', 'xxx');
mysqli_set_charset($link, 'utf8');

$sql    = "SELECT `ID`, `LAT`, `LNG`, `DATE`, `SNR`, `RSSi`, `BANDWIDTH`, `CNT`, `RAW` FROM `lora` where RID IS NOT NULL and LAT > 46.4838 and LNG < 11.2907 order by ID DESC LIMIT $limit";
$result = mysqli_query($link, $sql);

$json_array = array();
while ($row = mysqli_fetch_assoc($result)) {
    $json_array[] = $row;
}

$json = array();

$json['data'] = $json_array;
/*echo '<pre>';
print_r(json_encode($json_array));
echo '</pre>';*/
echo json_encode($json);
