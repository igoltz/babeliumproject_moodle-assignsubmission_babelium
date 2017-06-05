<?php

require_once '../../../../config.php';
require_once($CFG->dirroot . '/mod/assign/submission/babelium/Logging.php');
require_once './BabeliumHelper.php';

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ){
    processStudentAudioPostRequest();
}
else{
    echo "GET request";
}

function processStudentAudioPostRequest(){
    Logging::logBabelium("Processing POST request");
    $header = 'data:audio/wav;base64,';
    $audio_stream = $_POST["audiostream"];
    //clean audio stream
    if(isset($audio_stream)){
        Logging::logBabelium("Removing base64 stream header...");
        $audio_stream = str_replace($header, '', $audio_stream);
    }
    $audio_len =    $_POST["audiolen"];
    //convert len to int
    $audio_len = intval($audio_len) - strlen($header);
    $upload_name =  $_POST["audioname"];
    
    $idexercise =  $_POST["idexercise"];
    $idmedia =  $_POST["idmedia"];
    $idstudent =  $_POST["idstudent"];
    $idsubtitle =  $_POST["idsubtitle"];
    $mediaUrl =  $_POST["mediaUrl"];
    
    $rolename =  $_POST["rolename"];
    $responsehash = $_POST["responsehash"];
    
    $helper = new BabeliumHelper();
    //save audio first on mooodle server, temp location. just in case redirection does not work
    $response = $helper->saveAudioDataResponse($audio_stream, $audio_len, $upload_name);
    if($response == 'success'){
        $response = $helper->redirectAudioToBabelium(
                $audio_stream,
                $idexercise,
                $idstudent,
                $idsubtitle,
                $rolename,
                $responsehash
        );
        $valid = isset($response) && $response['id'];
        $responseId = intval($response['id']);
        if($valid){
            //delete temp audio from moodle server
            $response = $helper->deleteTempAudioFile($upload_name);
            //link response to user
            $response = $helper->saveBabeliumResponse(
                $idexercise,
                $idmedia,
                $idstudent,
                $idsubtitle,
                $rolename,
                $responseId,
                $response
            );
        }
    }
    
    Logging::logBabelium("Processing POST request DONE");
    echo $response;
}
