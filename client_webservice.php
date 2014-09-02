<?php 

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

function distanza($lat1,$lon1,$lat2,$lon2)
{
   $R = 6371; // km
   $f1 = deg2rad($lat1);
   $f2 = deg2rad($lat2);
   $df = deg2rad($lat2-$lat1);
   $dl = deg2rad($lon2-$lon1);

   $a = sin($df/2) * sin($df/2) +
        cos($f1) * cos($f2) *
        sin($dl/2) * sin($dl/2);
   $c = 2 * atan2(sqrt($a), sqrt(1-$a));

   $d = $R * $c;
   return $d*1000;
}



function mmmr($array, $output = 'mean'){
    if(!is_array($array)){
        return FALSE;
    }else{
        switch($output){
            case 'mean':
                $count = count($array);
                $sum = array_sum($array);
                $total = $sum / $count;
            break;
            case 'median':
                rsort($array);
                $middle = round(count($array) / 2);
                $total = $array[$middle-1];
            break;
            case 'mode':
                $v = array_count_values($array);
                arsort($v);
                foreach($v as $k => $v){$total = $k; break;}
            break;
            case 'range':
                sort($array);
                $sml = $array[0];
                rsort($array);
                $lrg = $array[0];
                $total = $lrg - $sml;
            break;
        }
        return $total;
    }
}

function indice_mediano($array)
{
  $originale=$array;
  rsort($array);
  $middle = round(count($array) / 2);
  $total = $array[$middle-1];
  return array_search($total,$originale);
}





function gpsDecimal($deg, $min, $sec, $hem) 
{
    
   $d = $deg + ($min/60) + ($sec/36000000);
   return ($hem=='S' || $hem=='W') ? $d*=-1 : $d;

}

include('splitimage.php'); 
include('SimpleImage.php'); 


$allowedTypes = array(IMAGETYPE_JPEG);
$detectedType = exif_imagetype($_FILES["immagine"]['tmp_name']);
$error = !in_array($detectedType, $allowedTypes);

if ($_FILES["immagine"]["error"] > 0 ) 
{
  echo "Errore: " . $_FILES["immagine"]["error"] . "<br>";
  exit;
}
else
{
if($error)
{
  echo "Errore:  tipo file non consentito! (solo jpeg/jpg! che mi vuoi mandare?!?!)<br>";
  exit;
}

}



   $PATH_TMP='temporanei';
   $codifica=md5_file($_FILES["immagine"]["tmp_name"]);
   $basename_immagine=$codifica;

   move_uploaded_file($_FILES["immagine"]["tmp_name"],$PATH_TMP."/"."$basename_immagine".".jpg");
   

   
  ini_set("default_socket_timeout", 180	); 
   $client = new SoapClient
	(null, array(
      'location' => "http://localhost/server_webservice.php",
      'uri'      => "NAMESPACE",
      'encoding'=>'ISO-8859-1',
      'trace'    => 1 )
	);



   $tempi=array();
   
   
   


   $exif = exif_read_data("$PATH_TMP/$basename_immagine.jpg");


   $latitude_exif = $exif['GPSLatitude'];
   $longitude_exif = $exif['GPSLongitude'];

   $latitude_exif_ref = $exif['GPSLatitudeRef'];
   $longitude_exif_ref= $exif['GPSLongitudeRef'];

  
   $latitude_exif_dec = gpsDecimal($latitude_exif[0], $latitude_exif[1], $latitude_exif[2], $latitude_exif_ref);
   $longitude_exif_dec = gpsDecimal($longitude_exif[0], $longitude_exif[1], $longitude_exif[2], $longitude_exif_ref);


   $n_pezzi_orizzontali=1;
   $n_pezzi_verticali=1;
   //TAGLIA IN PEZZI
   $basename_immagini=split_image($basename_immagine,$PATH_TMP,$PATH_TMP,$n_pezzi_orizzontali,$n_pezzi_verticali);
   $indice_max=0;
   $indice_min=0;
   
   $scala=10;
  
   while($scala<100)
{
   for($i=0;$i<$n_pezzi_orizzontali*$n_pezzi_verticali;$i++)
   {
    //RIDIMENSIONA
    $image = new SimpleImage(); 
    $image->load("$PATH_TMP/$basename_immagine.jpg"); //"$PATH_TMP/$basename_immagini[$i].jpg"
    $image->scale($scala); 
    $image->save("$PATH_TMP/$basename_immagini[$i].jpg");
    //SFOCATURA
    //$image = new Imagick("$PATH_TMP/$basename_immagini[$i].jpg");
    //$image->blurImage(1,1);
    //file_put_contents("$PATH_TMP/$basename_immagini[$i].jpg", $image); 
    //CALCOLA SIFT  
    $ris_feat[$i]=$client->calcola_sift($basename_immagini[$i],$PATH_TMP,$PATH_TMP);
    $numero_feat[$i]=$ris_feat[$i]["numero_feat"];
    if($numero_feat[$i]>$indice_max)
     $indice_max=$i;

     if($numero_feat[$i]<$indice_min)
     $indice_min=$i;
   }

   $i_mediano=indice_mediano($numero_feat);
   
   for($i=0;$i<$n_pezzi_orizzontali*$n_pezzi_verticali;$i++)
   {
    $indice_da_calcolare=$i;
//print_r($i_mediano);
//echo " index: $indice_da_calcolare <br> ";
    $risultati[$i]["risultati_acg"]=$client->acg_localizer($basename_immagini[$indice_da_calcolare],$PATH_TMP);
if(!($risultati[$i]["risultati_acg"]["x"]=="nan" || $risultati[$i]["risultati_acg"]["x"]=="-nan" || $risultati[$i]["risultati_acg"]["y"]=="nan" || $risultati[$i]["risultati_acg"]["y"]=="-nan"))
    	$scala=200;
    $risultati[$i]["tempi"]=array_merge($risultati[$i]["risultati_acg"]["tempi"],$ris_feat[$indice_da_calcolare]["tempi"]); 
    $risultati[$i]["numero_feat"]=$numero_feat[$indice_da_calcolare];
    
    $dim_immagine=getimagesize("$PATH_TMP/$basename_immagini[$indice_da_calcolare].jpg");
    $risultati[$i]["dim_immagine"]=$dim_immagine;
    $risultati[$i]["coordinate_exif_dec"]["latitude_exif_dec"]=$latitude_exif_dec;
    $risultati[$i]["coordinate_exif_dec"]["longitude_exif_dec"]=$longitude_exif_dec;
    
   }
$scala=$scala+10;
}
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
  
   //POSIZIONO I MARCATORI E CALCOLO LA MEDIA
   $indice=0;
   $medio=array("x" => 0,"y" => 0);
   $validi=0;
   for($i=0;$i<$n_pezzi_orizzontali*$n_pezzi_verticali;$i++)
   {
	
    $x=$risultati[$i]["risultati_acg"]["x"];
    if($x=="nan" || $x=="-nan")
     $x=0;
    $y=$risultati[$i]["risultati_acg"]["y"];
    if($y== "nan" || $y=="-nan")
     $y=0;
     echo "var latlng_$indice = new google.maps.LatLng(".$x.",".$y.");
        ";
    echo "var latlng_exif_$indice = new google.maps.LatLng(".$latitude_exif_dec.",".$longitude_exif_dec.");
        ";
    echo  "  var options_$indice = { zoom: 18,
                   center: latlng_$indice,               
                 }; 
	 ";

    echo "var map_$indice = new google.maps.Map(document.getElementById('map_canvas_$indice'), options_$indice);	
	 var myMarker_$indice = new google.maps.Marker({ position: latlng_$indice, map: map_$indice, title:'tu secondo me sei qui!'});
	 var myMarker_exif_$indice = new google.maps.Marker({ position: latlng_exif_$indice, map: map_$indice, title:'secondo il tuo gps invece sei qui!', icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'});
	";
    
    if($x!="nan" && $x!="-nan" && $y!= "nan" && $y!="-nan" && $x!=0 && $y!=0)
    {
    $medio["x"]=$medio["x"]+$x;
    $medio["y"]=$medio["y"]+$y;
    $validi++;
    }
    $indice++; 
   }
   
   //SE LA MEDIA E' POSSIBILE GENERO IL MARCATORE PER LA MEDIA 
   if($validi)
   {
    $medio["x"]=$medio["x"]/$validi;
    $medio["y"]=$medio["y"]/$validi;

    echo "var latlng_MEDIO = new google.maps.LatLng(".$medio["x"].",".$medio["y"].");
        ";
    echo "var latlng_exif_MEDIO = new google.maps.LatLng(".$latitude_exif_dec.",".$longitude_exif_dec.");
	";

    echo  "  var options_MEDIO = { zoom: 18,
                  center: latlng_MEDIO,               
                }; 
	 ";
    echo "var map_MEDIO = new google.maps.Map(document.getElementById('map_canvas_MEDIO'), options_MEDIO);	
	 var myMarker_MEDIO = new google.maps.Marker({ position: latlng_MEDIO, map: map_MEDIO, title:'in media stai qui!'});
	 var myMarker_exif_MEDIO = new google.maps.Marker({ position: latlng_exif_MEDIO, map: map_MEDIO, title:'secondo il tuo gps invece sei qui!', icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'});
	 var myMarker_exif_MEDIO = new google.maps.Marker({ position: latlng_exif_MEDIO, map: map_MEDIO, title:'secondo il tuo gps invece sei qui!', icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'});
	";
   }
   else $medio=0;
  
   echo '}
     </script>
  </head>
  <body onload="initialize()">'."\n";

   //STAMPO I RISULTATI
   $indice=0;
   for($i=0;$i<$n_pezzi_orizzontali*$n_pezzi_verticali;$i++)
   {
   	echo "info:<br>\n";
   	
	echo "Risoluzione: ". $risultati[$i]["dim_immagine"]['0']." X ". $risultati[$i]["dim_immagine"]['1']."<br>\n";
   	echo "<br>\n";
  

   	echo "tempi:<br>\n";
   	echo '<pre>'; print_r($risultati[$i]["tempi"]); echo '</pre>';
   
   	echo "totale: ". array_sum($risultati[$i]["tempi"]) ." secondi <br>\n";

   	$x=$risultati[$i]["risultati_acg"]["x"];
   	$y=$risultati[$i]["risultati_acg"]["y"];
   	echo "numero feat: ".$risultati[$i]["numero_feat"]."<br>\n";
   	$lat_exif=$risultati[$i]["coordinate_exif_dec"]["latitude_exif_dec"];
   	$long_exif=$risultati[$i]["coordinate_exif_dec"]["longitude_exif_dec"];
   	echo "<br>\n coordinate ricevute: lat: ".$x."; long: ".$y."<br>\n";
   	echo "<br>\n coordinate exif:     lat: ".$lat_exif."; long: ".$long_exif."<br>\n";
   	$d=distanza($x,$y,$lat_exif,$long_exif);
   	echo "distanza (in metri): ".$d."<br>\n";
   	echo '<div align="left" style="width:100%"><table cellpadding="0" cellspacing="2">';
   	echo '<tbody><tr><td>';
   	echo "<div id='map_canvas_$indice' style='width:300px; height:300px;'></div>\n";
   	echo '<td><img style="width:300px; height:300px;" src="'.'temporanei'.'/'.$basename_immagini[$i].'.jpg">';
   	echo "</td>
</tr>
</tbody>
</table>
</div>";
   	$indice++;
   }

   echo "\n  </body> \n
</html> ";

//SE E' RIUSCITO A CALCOLARE IL PUNTO MEDIO VISUALIZZA LA CARTINA
if($medio)
{

   echo "<br>\n coordinate MEDIO: lat: ".$medio["x"]."; long: ".$medio["y"]."<br>\n";
   echo "<br>\n coordinate exif:     lat: ".$lat_exif."; long: ".$long_exif."<br>\n";
   $d=distanza($medio["x"],$medio["y"],$lat_exif,$long_exif);
   echo "distanza (in metri): ".$d."<br>\n";
   echo '<div align="left" style="width:100%"><table cellpadding="0" cellspacing="2">';
   echo '<tbody><tr><td>';
   echo "<div id='map_canvas_MEDIO' style='width:300px; height:300px;'></div>\n";
   echo '<td><img style="width:300px; height:300px;" src="'.'temporanei'.'/'.$basename_immagine.'.jpg">';
   echo "</td>
</tr>
</tbody>
</table>
</div>";
}

?>
