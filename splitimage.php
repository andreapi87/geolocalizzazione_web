<?php
function split_image($basename,$path_in,$path_out,$n_pezzi_orizzontali,$n_pezzi_verticali)
{
   $dim_immagine=getimagesize("$path_in/$basename.jpg");
   $width=$dim_immagine[0]/$n_pezzi_orizzontali;   
   $height=$dim_immagine[1]/$n_pezzi_verticali; 
   $source = @imagecreatefromjpeg( "$path_in/$basename.jpg" );

   $source_width = imagesx( $source );
   $source_height = imagesy( $source );
   $i=0;
   for( $col = 0; $col < $source_width / $width; $col++)
   {
    for( $row = 0; $row < $source_height / $height; $row++)
    {
	$nuovo_nome=sprintf("$basename"."_%02d_%02d", $col, $row );
        $fn = sprintf( "$path_out/$nuovo_nome.jpg", $col, $row );

        //echo( "$fn<br>" );

        $im = @imagecreatetruecolor( $width, $height );
        imagecopyresized( $im, $source, 0, 0,
            $col * $width, $row * $height, $width, $height,
            $width, $height );
        imagejpeg( $im, $fn );
        imagedestroy( $im );
	$basename_file_senza_estensioni_senza_path[$i]=$nuovo_nome;
	$i++;
   }
  }
   return $basename_file_senza_estensioni_senza_path;
 //echo "split image\n";
}




?>
