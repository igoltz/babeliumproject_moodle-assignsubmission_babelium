<?php

require_once '../../../../config.php';
require_once($CFG->dirroot . '/mod/assign/submission/babelium/Logging.php');
require_once './BabeliumHelper.php';

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ){
    processStudentAudioPostRequest();
}

function processStudentAudioPostRequest(){
    Logging::logBabelium("Processing POST request");
    $audio_stream = $_POST["audiostream"];
    $audio_len =    $_POST["audiolen"];
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
    if($response == 'success' || true){
        $response = $helper->redirectAudioToBabelium(
                $audio_stream,
                $idexercise,
                $idstudent,
                $idsubtitle,
                $rolename,
                $responsehash
        );
        if($response == 'success'){
            //delete temp audio from moodle server
            $response = $helper->deleteTempAudioFile($upload_name);
        }
    }
    
    Logging::logBabelium("Processing POST request DONE");
    echo $response;
}