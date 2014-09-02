<?php


class myGlobals {   
//static $PATH_TMP='/home/andrea/temporanei';
static $PATH_RICOSTRUZIONE_OUT="./univ31geo_translato_rispetto_centroide.out";
static $PATH_LIST_JPG="/home/andrea/ricostruzioni/Univ31/lista_univ31.txt";
static $PATH_CENTROIDI="./centroids_150.txt";
static $PATH_MEDIA_CAM_CENTER="./univ31geo_valore_centroide.txt";
static $PATH_INFO="/home/andrea/ricostruzioni/Univ31/univ31_translato.info";
static $PATH_DESC_ASSIGN="./bundle.desc_assignments.integer_mean.voctree.clusters.150k.bin";
static $N_CENTROIDI=150;
   
/*$EXEC_JPG2PGM='/home/vinelab05/SoftwareLocalizzazione/jpeg2pgm/jpeg2pgm';
   $EXEC_FEAT="/home/vinelab05/SoftwareLocalizzazione/vsfm/bin/sift";   
   $EXEC_SIFT2KEY="/home/vinelab05/SoftwareLocalizzazione/From_SIFT_to_KEY/FromSIFTtoKEY";
   $EXEC_COMPDESCASSIGMENT="/home/vinelab05/SoftwareLocalizzazione/ACG_Localizer_v_1_2_2/build/src/compute_desc_assignments";
   $EXEC_ACGGEOLOCALIZER="/home/vinelab05/SoftwareLocalizzazione/ACG_Localizer_v_1_2_2/build/src/acg_localizer_active_search";
   $EXEC_SATTLER2CAMERA="/home/vinelab05/SoftwareLocalizzazione/from_sattler-log_to_camera_files_include_path/conv.sh";
   $EXEC_CAMERA2LATLONG="/home/vinelab05/SoftwareLocalizzazione/from_sattler_to_gps/from_sattler_to_lat_long";*/

static $EXEC_JPG2PGM='./jpeg2pgm';
static $EXEC_JPG2PGM2='convert';
static $EXEC_FEAT="./sift"; 
static $EXEC_FEAT2="./sift2";   
static $EXEC_SIFT2KEY="./FromSIFTtoKEY";
static $EXEC_COMPDESCASSIGMENT="./compute_desc_assignments";
static $EXEC_ACGGEOLOCALIZER="./acg_localizer_active_search";
static $EXEC_SATTLER2CAMERA="./conv.sh";
static $EXEC_CAMERA2LATLONG="./from_sattler_to_lat_long";
}

function getmicrotime()
{
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}

function calcola_sift($basename_senza_estensione_senza_path,$PATH_TMP,$path_out)
{
   $fullname_senza_estensione=$PATH_TMP."/".$basename_senza_estensione_senza_path;
   $comando=myGlobals::$EXEC_JPG2PGM2." $fullname_senza_estensione".".jpg $fullname_senza_estensione".".pgm";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   $tempi["calcolo_pgm"]=getmicrotime();
   exec("$comando > $PATH_TMP/out.txt 2> $PATH_TMP/error.txt");
   $tempi["calcolo_pgm"]=-$tempi["calcolo_pgm"]+getmicrotime();
   //$comando="$EXEC_FEAT $fullname_senza_estensione".".pgm -o $fullname_senza_estensione".".sift";
//VSFM: --peak-thresh=3.4
   $comando=myGlobals::$EXEC_FEAT." $fullname_senza_estensione".".pgm --peak-thresh=3.4 -o $path_out/%.key ;";
//$comando=myGlobals::$EXEC_FEAT." $fullname_senza_estensione".".pgm --peak-thresh=3.4 -o temporanei/%.sift> $path_out/$basename_senza_estensione_senza_path.key";
   //$comando=myGlobals::$EXEC_FEAT2." $fullname_senza_estensione".".jpg $path_out/$basename_senza_estensione_senza_path.key ;";
   exec("echo '$comando' >>$PATH_TMP/comandi.txt");
   $tempi["calcolo_feat_serial"]=getmicrotime();
   exec("$comando 2> $PATH_TMP/error.txt ;");
   $tempi["calcolo_feat_serial"]=-$tempi["calcolo_feat_serial"]+getmicrotime();
   $numero_feat=exec("head -n1 $path_out/$basename_senza_estensione_senza_path.key |cut -d' ' -f1");


   return array("tempi" => $tempi, "numero_feat" => $numero_feat);
}

function acg_localizer($basename_sift_senza_estensione_senza_path,$PATH_TMP)
{  
   $basename_senza_estensione_senza_path=$basename_sift_senza_estensione_senza_path;
   $fullname_senza_estensione=$PATH_TMP."/".$basename_sift_senza_estensione_senza_path;
   $FILE_LIST_KEY="$PATH_TMP/listquery_$basename_sift_senza_estensione_senza_path.txt";
   $FILE_LIST_SIFT="$PATH_TMP/listsift_$basename_sift_senza_estensione_senza_path.txt";
   $FILE_LOG_SATTLER="$fullname_senza_estensione"."_LOG_SATTLER.txt";
   $FILE_CAMERA="$fullname_senza_estensione".".key.camera";


   exec("echo '$fullname_senza_estensione.key
' > $FILE_LIST_KEY");
   exec("echo '$fullname_senza_estensione.sift
' > $FILE_LIST_SIFT");
   $comando=myGlobals::$EXEC_ACGGEOLOCALIZER." $FILE_LIST_KEY ".myGlobals::$PATH_RICOSTRUZIONE_OUT." ".myGlobals::$N_CENTROIDI." ".myGlobals::$PATH_CENTROIDI." ".myGlobals::$PATH_DESC_ASSIGN." 0 $fullname_senza_estensione"."_results.txt 200 1 1 0 > $FILE_LOG_SATTLER";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   $tempi["geolocalizzazione"]=getmicrotime();
   exec("$comando 2> $PATH_TMP/error.txt");
   $tempi["geolocalizzazione"]=-$tempi["geolocalizzazione"]+getmicrotime();
   
   $comando=myGlobals::$EXEC_SATTLER2CAMERA." $FILE_LOG_SATTLER $FILE_LIST_KEY $PATH_TMP";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   exec("$comando 2> $PATH_TMP/error.txt");
   $comando=myGlobals::$EXEC_CAMERA2LATLONG." $FILE_CAMERA ".myGlobals::$PATH_MEDIA_CAM_CENTER." > $fullname_senza_estensione"."_coord_final.txt";
   exec("echo $comando >>$PATH_TMP/comandi.txt");
   exec("$comando  2> $PATH_TMP/error.txt");		
   $coordinate=system("cat $fullname_senza_estensione"."_coord_final.txt | grep 'Longitude:' | sed s/': '/=/g|sed 's/ /\&/g'");
   parse_str($coordinate);
   $rit=array("x" => $Latitude,"y" => $Longitude, "tempi" => $tempi);
   return $rit;

}



function getacglocalizer($base_name_immagine,$PATH_TMP)
{
   include('SimpleImage.php'); 
   //return 1;


   $base_name_senza_estensione_senza_path=$base_name_immagine;
   $fullname_senza_estensione=$PATH_TMP."/".$base_name_senza_estensione_senza_path;
   $fullname_file_jpg=$fullname_senza_estensione.".jpg";
  
   $ris_sift=calcola_sift($base_name_senza_estensione_senza_path,$PATH_TMP,$PATH_TMP);
   
   //$comando="$EXEC_FEAT2 $fullname_senza_estensione".".pgm --peak-thresh=3.4 -o $PATH_TMP/%.sift ;";
   //exec("echo $comando >>$PATH_TMP/comandi.txt");
   //$tempi["calcolo_feat_multithread"]=getmicrotime();
   //exec("$comando 2> $PATH_TMP/error.txt");
   //$tempi["calcolo_feat_multithread"]=-$tempi["calcolo_feat_multithread"]+getmicrotime();
/*
DA ELIMINARE
   //$comando="$EXEC_SIFT2KEY $FILE_LIST_SIFT";
//echo exec("echo $comando >>$PATH_TMP/comandi.txt");
 //  echo exec("$comando >> $PATH_TMP/out.txt 2> $PATH_TMP/error.txt");

   //echo exec("mv $base_name_senza_estensione_senza_path".".key $PATH_TMP"."/");
*/  

   $acg_res=acg_localizer($base_name_senza_estensione_senza_path,$PATH_TMP);
   $ritorno["x"]=$acg_res["x"];
   $ritorno["y"]=$acg_res["y"];   
   $ritorno["tempi"]=array_merge($acg_res["tempi"],$ris_sift["tempi"]);   

   return $ritorno;


}


  $server = new SoapServer(null, 
  array('uri' => "NAMESPACE",'encoding'=>'ISO-8859-1') );


   $server->addFunction("calcola_sift"); 
   $server->addFunction("acg_localizer"); 	
   $server->addFunction("getacglocalizer"); 
   $server->handle(); 
?>
