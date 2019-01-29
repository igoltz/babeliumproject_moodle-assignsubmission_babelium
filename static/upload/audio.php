<?php

//script provisional. experimental

$save_folder = dirname(__FILE__) . "/audio";
if(! file_exists($save_folder)) {
    if(! mkdir($save_folder)) {
        die("failed to create save folder $save_folder");
    }
}
$content = $_POST["audiofile"];
$upload_name = $_POST["audioname"];
$type = "audio/x-wav";
$filename = "$save_folder/$upload_name";
$saved = 0;

    $fp = fopen($filename, 'a+');
    $result = fwrite($fp, $content) ;
    if($result === FALSE){
        echo "No se puede abrir el archivo ($filename)";
        exit;
    }
    $saved = $result ? 1 : 0;
    //var_dump($saved);
?>
