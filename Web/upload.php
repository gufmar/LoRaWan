<?php
$target_dir    = "uploads/";
$target_file   = ($target_dir . "GPSLOG.TXT");
$uploadOk      = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if (isset($_POST["submit"])) {

    if (isset($_POST["password"])) {
        $pass = $_POST["password"];

        if ($pass == "xxxx") {

            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

                $link = mysqli_connect('localhost', 'xxx', 'xxx', 'xxx');
                mysqli_set_charset($link, 'utf8');

                $file_lines = file($target_file);
                foreach ($file_lines as $line) {
                    //echo $line; //46.6561,11.1477|0.0000|0.0000|201962610110|0|2019-6-26 10:11:0

                    $teile = explode("|", $line);
                    $gps   = $teile[0]; // gps
                    $gpsA  = explode(",", $gps);
                    $lat   = $gpsA[0];
                    $lon   = $gpsA[1];
                    // echo $teile[1]; // höhe
                    // echo $teile[2]; // speed
                    $rid = $teile[3]; // rid
                    // echo $teile[4]; // lora result
                    $date = $teile[5]; // date

                    $sql    = "SELECT * FROM `lora` where RID = '$rid'";
                    $result = mysqli_query($link, $sql);

                    if (mysqli_num_rows($result) == 0) {
                        // echo $rid;

                        $inssql = "INSERT INTO `lora`(`LAT`, `LNG`, `DATE`, `RID`, `RAW`, `RSSI`) VALUES ('$lat', '$lon',
						'$date', '$rid', '$line', '999')";
                        $insresult = mysqli_query($link, $inssql);


                        if ($insresult == 1){

                        	echo $rid . " OK<br>";
                        } else {

                        	echo $rid . " KO " . $line . "   " . mysqli_error($link) . "<br>";
                        }

                        

                        // break;
                    }

                }

             

                echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";

            } else {
                echo "Sorry, there was an error uploading your file.";
            }

        } else {

            echo "wrong pass";
        }

    }

}
