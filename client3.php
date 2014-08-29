<?php # HelloClient.php
# Copyright (c) 2005 by Dr. Herong Yang
#
<<<<<<< HEAD

$allowedTypes = array( IMAGETYPE_JPEG);
$detectedType = exif_imagetype($_FILES["immagine"]['tmp_name']);
$error = !in_array($detectedType, $allowedTypes);

if ($_FILES["immagine"]["error"] > 0 ) 
=======
if ($_FILES["immagine"]["error"] > 0) 
>>>>>>> 6a81dc0922ca60b9700dfec0ac5284bcd74ebbdc
{
  echo "Errore: " . $_FILES["immagine"]["error"] . "<br>";
  exit;
}
<<<<<<< HEAD
else
{
$allowedTypes = array( IMAGETYPE_JPEG);
$detectedType = exif_imagetype($_FILES["immagine"]['tmp_name']);
$error = !in_array($detectedType, $allowedTypes);
if($error)
{
  echo "Errore:  tipo file non consentito! (solo jpeg/jpg! che mi vuoi mandare?!?!)<br>";
  exit;
}

}



=======
>>>>>>> 6a81dc0922ca60b9700dfec0ac5284bcd74ebbdc

$client = new SoapClient(null, array(
      'location' => "http://localhost/server3.php",
      'uri'      => "NAMESPACE",
      'encoding'=>'ISO-8859-1',
      'trace'    => 1 )

);
<<<<<<< HEAD
   $PATH_TMP='/home/andrea/temporanei';
   //$return = $client->__soapCall("hello",array("world"));

   $codifica=md5_file($_FILES["immagine"]["tmp_name"]);
   move_uploaded_file($_FILES["immagine"]["tmp_name"],$PATH_TMP."/"."$codifica".".jpg");
   
   $ritorno = $client->hello($codifica);
   
//header("Content-type: image/png"); // or whatever 
//print base64_decode($ritorno);
//var_dump($ritorno);

echo '
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <script type="text/javascript" 
src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">
      function initialize() {
';

 $indice=0;
  foreach($ritorno as $scala)
  {
	
   $x=$scala["coordinate"]["x"];
   if($x=="nan")
    $x=0;
   $y=$scala["coordinate"]["y"];
   if($y=="nan")
    $y=0;
   echo "var latlng_$indice = new google.maps.LatLng(".$x.",".$y.");
        ";
   echo "var latlng_exif_$indice = new google.maps.LatLng(".$scala["coordinate_exif_dec"]["latitude_exif_dec"].",".$scala["coordinate_exif_dec"]["longitude_exif_dec"].");
        ";
   echo  "  var options_$indice = { zoom: 18,
                  center: latlng_$indice,               
                }; 
	 ";

   echo "var map_$indice = new google.maps.Map(document.getElementById('map_canvas_$indice'), options_$indice);	
	 var myMarker_$indice = new google.maps.Marker({ position: latlng_$indice, map: map_$indice, title:'tu secondo me sei qui!'});
	 var myMarker_exif_$indice = new google.maps.Marker({ position: latlng_exif_$indice, map: map_$indice, title:'secondo il tuo gps invece sei qui!', icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'});
	";



   $indice++; 
  }
  
echo '}
     </script>
  </head>
  <body onload="initialize()">'."\n";

   $indice=0;
   foreach($ritorno as $scala)
   {
   	echo "info:<br>\n";
   	//print_r($ritorno["dim_immagine"]);
	echo "Risoluzione: ". $scala["dim_immagine"]['0']['0']." X ". $scala["dim_immagine"]['0']['1']." (".$scala["scala"]."% dell'originale)<br>\n";
   	echo "<br>\n";
  

   echo "tempi:<br>\n";
   echo '<pre>'; print_r($scala["tempi"]['0']); echo '</pre>';
   
   echo "totale: ". array_sum($scala["tempi"]['0']) ." secondi <br>\n";

   $x=$scala["coordinate"]["x"];
   $y=$scala["coordinate"]["y"];

   $lat_exif=$scala["coordinate_exif_dec"]["latitude_exif_dec"];
   $long_exif=$scala["coordinate_exif_dec"]["longitude_exif_dec"];
 /*  echo 'immagine trasformata in b\n:<br>
<img src="data:image/png;base64,'.$ritorno["immagine_codificata"].' " height="250" width="250" />';*/
   echo "<br>\n coordinate ricevute: lat: ".$x."; long: ".$y."<br>\n";
   echo "<br>\n coordinate exif:     lat: ".$lat_exif."; long: ".$long_exif."<br>\n";
   echo '<div align="left" style="width:100%"><table cellpadding="0" cellspacing="2">';
   echo '<tbody><tr><td>';
   echo "<div id='map_canvas_$indice' style='width:300px; height:300px;'></div>\n";
   echo '<td><img style="width:300px; height:300px;" src="'.'temporanei'.'/'.$codifica.'_'.$scala["scala"].'.jpg">';
   echo "</td>
</tr>
</tbody>
</table>
</div>";
   $indice++;
   }//fine for

   echo "\n  </body> \n
</html> ";


=======

   //$return = $client->__soapCall("hello",array("world"));
   $data=base64_encode(file_get_contents($_FILES["immagine"]["tmp_name"]));//file_get_contents('/home/andrea/Immagini/logo.jpg'));
   $ritorno = $client->hello($data);
   
	//header("Content-type: image/png"); // or whatever 
	//print base64_decode($ritorno);
//var_dump($ritorno);
   $x=$ritorno["x"];
   $y=$ritorno["y"];
   echo 'immagine trasformata in b\n:<br>
<img src="data:image/png;base64,'.$ritorno["immagine_codificata"].' " height="250" width="250" />';
   echo '<br>coordinate ricevute: lat '.$x.'; long '.$y.'<br>';

//echo '<img src="http://maps.google.com/maps/api/staticmap?center='.$x.','.$y.'&zoom=8&size=400x300&sensor=true" style="width: 400px; height: 400px;" />'

echo '
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
 
<title>Google Maps API v3: esempio base</title>
 
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
 
<script type="text/javascript">
var initialize = function() {
 
  // fornisce latitudine e longitudine
   var latlng = new google.maps.LatLng('.$x.','.$y.');	  
   var options = { zoom: 5,
                  center: latlng,               
                };      


   var map = new google.maps.Map(document.getElementById("map"), options);
   var marker = new google.maps.Marker({ position: latlng,
                                      map: map,
                                      title: "tu sei qui!"});
 }
 
window.onload = initialize;
</script>
 
</head>
<body style="margin:0; padding:0;">
<div id="map" style="width:25%; height:25%"></div>
</body>
</html>
';

>>>>>>> 6a81dc0922ca60b9700dfec0ac5284bcd74ebbdc
?>
