<?php
<<<<<<< HEAD



function getmicrotime()
{
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}


function gpsDecimal($deg, $min, $sec, $hem) 
{
    
   $d = $deg + ($min/60) + ($sec/36000000);
;
    return ($hem=='S' || $hem=='W') ? $d*=-1 : $d;
}

function hello($nome_immagine) {
   include('SimpleImage.php'); 
   //return 1;
   
   $PATH_TMP='/home/andrea/temporanei';
   $PATH_RICOSTRUZIONE_OUT="./univ31geo_translato_rispetto_centroide.out";
   $PATH_LIST_JPG="/home/andrea/ricostruzioni/Univ31/lista_univ31.txt";
   $PATH_CENTROIDI="./centroids_150.txt";
   $PATH_MEDIA_CAM_CENTER="./univ31geo_valore_centroide.txt";
   $PATH_INFO="/home/andrea/ricostruzioni/Univ31/univ31_translato.info";
   $PATH_DESC_ASSIGN="./bundle.desc_assignments.integer_mean.voctree.clusters.150k.bin";
   $N_CENTROIDI=150;
  
   
   /*$EXEC_JPG2PGM='/home/vinelab05/SoftwareLocalizzazione/jpeg2pgm/jpeg2pgm';
   $EXEC_FEAT="/home/vinelab05/SoftwareLocalizzazione/vsfm/bin/sift";   
   $EXEC_SIFT2KEY="/home/vinelab05/SoftwareLocalizzazione/From_SIFT_to_KEY/FromSIFTtoKEY";
   $EXEC_COMPDESCASSIGMENT="/home/vinelab05/SoftwareLocalizzazione/ACG_Localizer_v_1_2_2/build/src/compute_desc_assignments";
   $EXEC_ACGGEOLOCALIZER="/home/vinelab05/SoftwareLocalizzazione/ACG_Localizer_v_1_2_2/build/src/acg_localizer_active_search";
   $EXEC_SATTLER2CAMERA="/home/vinelab05/SoftwareLocalizzazione/from_sattler-log_to_camera_files_include_path/conv.sh";
   $EXEC_CAMERA2LATLONG="/home/vinelab05/SoftwareLocalizzazione/from_sattler_to_gps/from_sattler_to_lat_long";*/

   $EXEC_JPG2PGM='./jpeg2pgm';
   $EXEC_FEAT="./sift"; 
   $EXEC_FEAT2="./sift_multithread";   
   $EXEC_SIFT2KEY="./FromSIFTtoKEY";
   $EXEC_COMPDESCASSIGMENT="./compute_desc_assignments";
   $EXEC_ACGGEOLOCALIZER="./acg_localizer_active_search";
   $EXEC_SATTLER2CAMERA="./conv.sh";
   $EXEC_CAMERA2LATLONG="./from_sattler_to_lat_long";


   
   $scale_test=array(10,50,70);
   $tempi=array();
   
   
   $nome_immagine_originale=$nome_immagine;


  $exif = exif_read_data("$PATH_TMP/$nome_immagine_originale.jpg");

  $latitude_exif = $exif['GPSLatitude'];
  $longitude_exif = $exif['GPSLongitude'];

  $latitude_exif_ref = $exif['GPSLatitudeRef'];
  $longitude_exif_ref= $exif['GPSLongitudeRef'];



  
  $latitude_exif_dec = gpsDecimal($latitude_exif[0], $latitude_exif[1], $latitude_exif[2], $latitude_exif_ref);
  $longitude_exif_dec = gpsDecimal($longitude_exif[0], $longitude_exif[1], $longitude_exif[2], $longitude_exif_ref);



   for($i=0;$i<count($scale_test);$i++)
   {
   $image = new SimpleImage(); 
   $image->load("$PATH_TMP/$nome_immagine_originale.jpg"); 
   $image->scale("$scale_test[$i]"); 
   $image->save("$PATH_TMP/$nome_immagine_originale"."_$scale_test[$i].jpg");


   $nome_file_senza_estensione_senza_path="$nome_immagine_originale"."_$scale_test[$i]";
   $nome_file_senza_estensione=$PATH_TMP."/".$nome_file_senza_estensione_senza_path;
   $nome_file=$nome_file_senza_estensione.".jpg";
   $FILE_LIST_KEY="$PATH_TMP/listquery_$nome_file_senza_estensione_senza_path.txt";
   $FILE_LIST_SIFT="$PATH_TMP/listsift_$nome_file_senza_estensione_senza_path.txt";
   $FILE_LOG_SATTLER="$nome_file_senza_estensione"."_LOG_SATTLER.txt";
   $FILE_CAMERA="$nome_file_senza_estensione".".key.camera";

    
   exec("echo '$PATH_TMP/$nome_file_senza_estensione_senza_path.key
' > $FILE_LIST_KEY");
   exec("echo '$PATH_TMP/$nome_file_senza_estensione_senza_path.sift
' > $FILE_LIST_SIFT");
   //$x=40; 
   //$y=14;
   

   $comando="$EXEC_JPG2PGM $nome_file $nome_file_senza_estensione".".pgm";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   $tempi["calcolo_pgm"]=getmicrotime();
   exec("$comando > $PATH_TMP/out.txt 2> $PATH_TMP/error.txt");
   $tempi["calcolo_pgm"]=-$tempi["calcolo_pgm"]+getmicrotime();
   //$comando="$EXEC_FEAT $nome_file_senza_estensione".".pgm -o $nome_file_senza_estensione".".sift";

   $comando="$EXEC_FEAT $nome_file_senza_estensione".".pgm --peak-thresh=3.4 -o $PATH_TMP/%.key ;";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   $tempi["calcolo_feat_serial"]=getmicrotime();
   exec("$comando >> $PATH_TMP/out.txt 2> $PATH_TMP/error.txt");
   $tempi["calcolo_feat_serial"]=-$tempi["calcolo_feat_serial"]+getmicrotime();

   //$comando="$EXEC_FEAT2 $nome_file_senza_estensione".".pgm --peak-thresh=3.4 -o $PATH_TMP/%.sift ;";
   //exec("echo $comando >>$PATH_TMP/comandi.txt");
   //$tempi["calcolo_feat_multithread"]=getmicrotime();
   //exec("$comando 2> $PATH_TMP/error.txt");
   //$tempi["calcolo_feat_multithread"]=-$tempi["calcolo_feat_multithread"]+getmicrotime();
/*
DA ELIMINARE
   //$comando="$EXEC_SIFT2KEY $FILE_LIST_SIFT";
//echo exec("echo $comando >>$PATH_TMP/comandi.txt");
 //  echo exec("$comando >> $PATH_TMP/out.txt 2> $PATH_TMP/error.txt");

   //echo exec("mv $nome_file_senza_estensione_senza_path".".key $PATH_TMP"."/");
*/  
   $comando="$EXEC_ACGGEOLOCALIZER $FILE_LIST_KEY $PATH_RICOSTRUZIONE_OUT $N_CENTROIDI $PATH_CENTROIDI $PATH_DESC_ASSIGN 0 ".$nome_file_senza_estensione."_results.txt 200 1 1 0 > $FILE_LOG_SATTLER";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   $tempi["geolocalizzazione"]=getmicrotime();
   exec("$comando 2> $PATH_TMP/error.txt");
   $tempi["geolocalizzazione"]=-$tempi["geolocalizzazione"]+getmicrotime();
   
   $comando="$EXEC_SATTLER2CAMERA $FILE_LOG_SATTLER $FILE_LIST_KEY $PATH_TMP";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   exec("$comando 2> $PATH_TMP/error.txt");
   $comando="$EXEC_CAMERA2LATLONG $FILE_CAMERA $PATH_MEDIA_CAM_CENTER > $nome_file_senza_estensione"."_coord_final.txt";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   exec("$comando  2> $PATH_TMP/error.txt");		
   $coordinate=system("cat $nome_file_senza_estensione"."_coord_final.txt | grep 'Longitude:' | sed s/': '/=/g|sed 's/ /\&/g'");
   parse_str($coordinate);

   $ritorno["$scale_test[$i]"]["coordinate"]=array("x" => $Latitude,"y" => $Longitude);
   $ritorno["$scale_test[$i]"]["tempi"][]=$tempi;
   
  
   $dim_immagine=getimagesize($nome_file);
   $ritorno["$scale_test[$i]"] ["scala"]=$scale_test[$i];
   $ritorno["$scale_test[$i]"]["dim_immagine"][]=$dim_immagine;
   $ritorno["$scale_test[$i]"]["coordinate_exif_dec"]["latitude_exif_dec"]=$latitude_exif_dec;
   $ritorno["$scale_test[$i]"]["coordinate_exif_dec"]["longitude_exif_dec"]=$longitude_exif_dec;
   //$ritorno=1;
   }//fine for
   
return $ritorno;

} 
  $server = new SoapServer(null, 
  array('uri' => "NAMESPACE",'encoding'=>'ISO-8859-1') );
=======
function hello($immagine_codificata) {

   $PATH='/home/andrea/web/';
   $immagine_decodificata=base64_decode($immagine_codificata);
   $nome_file=md5($immagine_decodificata).".jpg";
   if (!$handle = fopen($PATH."/".$nome_file, 'w')) 
   {
    echo "impossibile aprire in scrittura ($nome_file)";
    exit;
   }
   if (fwrite($handle, $immagine_decodificata) === FALSE) 
   {
    echo "impossibile scrivere su ($nome_file)";
    exit;
   }
   fclose($handle);
   $comando="convert ".$PATH."/".$nome_file. " -monochrome ".$PATH."/BN_".$nome_file;
   exec($comando);
   $output_codificata=base64_encode(file_get_contents($PATH."/BN_".$nome_file));
   exec("rm ".$PATH."/BN_".$nome_file);
   $x=40; 
   $y=14;   


   $ritorno=array("immagine_codificata" => "$output_codificata","x" => $x,"y" => $y);
   return $ritorno;

} 
   $server = new SoapServer(null, 
      array('uri' => "NAMESPACE",'encoding'=>'ISO-8859-1')
);
>>>>>>> 6a81dc0922ca60b9700dfec0ac5284bcd74ebbdc

	
   $server->addFunction("hello"); 
   $server->handle(); 
?>
