<?php

require_once '../../../../config.php';
require_once './BabeliumHelper.php';

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ){
    processPostRequest();
}

function processPostRequest(){
    $audio_stream = $_POST["audiostream"];
    $audio_len =    $_POST["audiolen"];
    $upload_name =  $_POST["audioname"];
    
    $idexercise =  $_POST["idexercise"];
    $idstudent =  $_POST["idstudent"];
    $idsubtitle =  $_POST["idsubtitle"];
    $rolename =  $_POST["rolename"];
    
    $helper = new BabeliumHelper();
    $response = $helper->saveAudioDataResponse($audio_stream, $audio_len, $upload_name);
    if($response == 'success'){
        $response = $helper->redirectAudioToBabelium($audio_stream, $idexercise, $idstudent, $idsubtitle, $rolename);
    }
    echo $response;
}