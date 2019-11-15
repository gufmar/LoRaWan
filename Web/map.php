<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link crossorigin="" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" rel="stylesheet" />
    <script crossorigin="" integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og==" src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js">
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/de.js">
    </script>


     <style type="text/css">
        #mapid {
            height: 80vh;
        }
    </style>
</head>

<body>
<h1>LORA TRACKER</h1>

<div id="mapid">
</div>

<p id="time"></p>
<p id="raw"></p>



<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="text" name="password" placeholder="password" id="password">
    <input type="submit" value="Upload File" name="submit">
</form>


</body>


<script type="text/javascript">

    moment.locale('de');
    var ortKreis = {};
    var schwarzKreis = {};
    var marker;
    var date;
    var map = L.map('mapid').setView([46.656484, 11.150090], 15);
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);



    function timeout() {
        setTimeout(function () {
            $.ajax({
                type: 'GET',
                url: 'get.php',
                success: function (data) {
                    if (data != null) {

                        var obj = JSON.parse(data);

                        var o = obj.data[0];

                         if (o.LAT != null && o.LAT > 0 && o.LNG != null && o.LNG > 0 && date != o.DATE){

                            var v  = ((o.RSSi / -1) - min);


                            if (map.hasLayer(marker) == true) {
                                map.removeLayer(marker);
                            }
                            marker = L.marker([o.LAT, o.LNG]).addTo(map);



                            $('#time').html(moment(o.DATE).add(1, 'hours').format('L LTS'));

                            date = o.DATE;


                         }





                    }



                }
            });
            timeout();
        }, 1000);
    }
    timeout();


    var min = 75;
    var max  = 100;


    var percentColors = {};


    // 46.4838 and LNG < 11.2907

    var pointA = new L.LatLng(46.5838, 11.2907);
    // L.marker(pointA).addTo(map).bindPopup("A");
    var pointB = new L.LatLng(46.4838, 11.2907);

    var pointC = new L.LatLng(46.4838, 11.1907);
     // L.marker(pointC).addTo(map).bindPopup("C");;
    var pointList = [pointA, pointB, pointC];

    var firstpolyline = new L.Polyline(pointList, {
        color: 'red',
        weight: 3,
        opacity: 1,
        smoothFactor: 1
    });
    firstpolyline.addTo(map);

    $.ajax({
                type: 'GET',
                url: 'get.php?limit=1000000',
                success: function (data) {
                    //console.log(data);
                    if (data != null) {

                        var obj = JSON.parse(data);

                        $.each(obj.data, function(idx, elem) {

                            if (min > elem.RSSi < -999){
                                if (min > elem.RSSi / -1){
                                    min = elem.RSSi / -1;
                                }


                                if (max < elem.RSSi / -1){
                                    max = elem.RSSi / -1;
                                }
                            }



                        });


                        max = max - min;


                      //   console.log();

                      //   var o = obj.data[0];

                         // if (o.LAT != null && o.LAT > 0 && o.LNG != null && o.LNG > 0){

                         //     var v  = ((o.RSSi / -1) - min);

                         //     L.marker([o.LAT, o.LNG]).addTo(map).bindPopup("RSSi: " + o.RSSi + " ("+v+")<br>SNR: " + o.SNR + "<br>BANDWIDTH: " + o.BANDWIDTH + "<br>CNT: " + o.CNT + "<br>DATE: " + moment(o.DATE).add(1, 'hours').format('L LTS'));


                         // }





                        percentColors = [
                        { pct: 0, color: { r: 0x00, g: 0xff, b: 0 } },
                        { pct: ((max / 100) * 50), color: { r: 0xff, g: 0xff, b: 0 } },
                        { pct: (max - 5), color: { r: 0xff, g: 0x00, b: 0 } }];




                        $.each(obj.data, function(idx, elem) {


                            if (elem.LAT != null && elem.LAT > 0 && elem.LNG != null && elem.LNG > 0){



                                // console.log(elem.ID);

                                // BANDWIDTH: "125"
                                // CNT: "150"
                                // DATE: "2019-06-02 12:04:47"
                                // ID: "408"
                                // LAT: "46.66000000"
                                // LNG: "11.13760000"
                                // RSSi: "-106"
                                // SNR: "6.5"

                                var farbe = "red";


                                // if ()


                                var co;
                                var va = "";

                                if (elem.RSSi == 999){
                                    co = "black";

                                    schwarzKreis[elem.ID] = L.circle([elem.LAT, elem.LNG], {
                                        radius: 10,
                                        color: '#000',
                                        fillOpacity: 1,
                                        weight: 1,
                                        fillColor: co,
                                    }).addTo(map).bindPopup("RSSi: " + elem.RSSi + " ("+va+")<br>SNR: " + elem.SNR + "<br>BANDWIDTH: " + elem.BANDWIDTH + "<br>CNT: " + elem.CNT + "<br>DATE: " + moment(elem.DATE).add(1, 'hours').format('L LTS'));


                                } else {
                                    va = ((elem.RSSi / -1) - min);
                                    var ra = JSON.parse(elem.RAW);

                                    co = getColorForPercentage(va);

                                    var air = "";
                                    var tp = hex_to_ascii(ra.Payload);
                                    if (tp){
                                        var pa = tp.split("-");
                                        if (pa.length > 1){
                                            var airS  = pa[2].split(",");
                                            air =  "<br>pm1.0: " + airS[1] + "<br>pm2.5: "+ airS[1] + "<br>pm10: "+ airS[2];
                                        }
                                    }





                                    ortKreis[elem.ID] = L.circle([elem.LAT, elem.LNG], {
                                        radius: 10,
                                        color: '#000',
                                        fillOpacity: 1,
                                        weight: 1,
                                        fillColor: co,
                                    }).addTo(map).bindPopup("RSSi: " + elem.RSSi + " ("+va+")<br>SNR: " + elem.SNR + "<br>BANDWIDTH: " + elem.BANDWIDTH + "<br>CNT: " + elem.CNT + "<br>DATE: " + moment(elem.DATE).add(1, 'hours').format('L LTS')+ "<br>AIR:" + air);



                                }


                            }



                        });








                        // var obj = JSON.parse(data);
                        // map.setView([obj[0]['LAT'], obj[0]['LNG']], 17);
                        // if (map.hasLayer(ortKreis) == true) {
                        //     map.removeLayer(ortKreis);
                        // }
                        // ortKreis = L.circle([obj[0]['LAT'], obj[0]['LNG']], {
                        //     radius: 25
                        // }).addTo(map);
                        // if (map.hasLayer(ortKreisI) == true) {
                        //     map.removeLayer(ortKreisI);
                        // }
                        // ortKreisI = L.circle([obj[0]['LAT'], obj[0]['LNG']], {
                        //     radius: 1,
                        //     color: "red"
                        // }).addTo(map);

                        // $('#time').html(moment(obj[0]['DATE']).add(1, 'hours').format('L LTS'));
                        // $('#raw').html(data);
                    }



                }
            });





                                        var getColorForPercentage = function(pct) {
                        for (var i = 1; i < percentColors.length - 1; i++) {
                            if (pct <= percentColors[i].pct) {
                                break;
                            }
                        }
                        var lower = percentColors[i - 1];
                        var upper = percentColors[i];
                        var range = upper.pct - lower.pct;
                        var rangePct = (pct - lower.pct) / range;
                        var pctLower = 1 - rangePct;
                        var pctUpper = rangePct;
                        var color = {
                            r: Math.floor(lower.color.r * pctLower + upper.color.r * pctUpper),
                            g: Math.floor(lower.color.g * pctLower + upper.color.g * pctUpper),
                            b: Math.floor(lower.color.b * pctLower + upper.color.b * pctUpper)
                        };
                        return 'rgb(' + [color.r, color.g, color.b].join(',') + ')';
                        // or output as hex if preferred
                    }








    function hex_to_ascii(str1) {
        var hex = str1.toString();
        var str = '';
        for (var n = 0; n < hex.length; n += 2) {
            str += String.fromCharCode(parseInt(hex.substr(n, 2), 16));
        }
        return str;
    }



</script>
</html>
