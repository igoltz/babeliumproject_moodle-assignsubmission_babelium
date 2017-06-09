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
    if(isset($audio_len)){
        $audio_len = intval($audio_len) - strlen($header);
    }

    $upload_name =  $_POST["audioname"];
    if(isset($upload_name)){
        $upload_name = trim($upload_name);
    }

    $idexercise =  $_POST["idexercise"];
    $idmedia =  $_POST["idmedia"];
    $idstudent =  $_POST["idstudent"];
    $idsubtitle =  $_POST["idsubtitle"];

    $mediaUrl =  $_POST["mediaUrl"];
    if(isset($mediaUrl)){
        $mediaUrl = trim($mediaUrl);
    }

    $rolename =  $_POST["rolename"];
    if(isset($rolename)){
        $rolename = trim($rolename);
    }

    $responsehash = $_POST["responsehash"];
    if(isset($responsehash)){
        $responsehash = trim($responsehash);
    }

    //make security checks
    //audio_len = numeric value
    //upload_name = numeric_value (current is unix timestamp)
    //ids = numeric
    //mediaUrl, rolename and responseHash unchecked
    $passed = true;
    $passed &= is_numeric($audio_len);
    $passed &= is_numeric($upload_name); //preg_match('\d+', $upload_name);
    $passed &= is_numeric($idexercise);
    $passed &= is_numeric($idmedia);
    $passed &= is_numeric($idstudent);
    $passed &= is_numeric($idsubtitle);

    $response = "";
    if($passed){
        $response = execute_post_request(
            $audio_stream,
            $audio_len,
            $upload_name,
            $idexercise,
            $idstudent,
            $idsubtitle,
            $rolename,
            $responsehash
        );
    }
    else{
        $response = throw_security_error();
    }
    echo $response;
}

function throw_security_error(){
    return '{"error":"could no validate all input fields"}';
}

function execute_post_request($audio_stream, $audio_len, $upload_name, $idexercise, $idstudent, $idsubtitle, $rolename, $responsehash){
    $helper = new BabeliumHelper();
    //save audio first on mooodle server, temp location. just in case redirection does not work
    $response = $helper->saveAudioDataResponse($audio_stream, $audio_len, $upload_name);
    if($response == 'success'){
        $babeliumServerResponse = $helper->redirectAudioToBabelium(
                $audio_stream,
                $idexercise,
                $idstudent,
                $idsubtitle,
                $rolename,
                $responsehash
        );
        $valid = isset($babeliumServerResponse) && $babeliumServerResponse['id'];
        if($valid){
            //delete temp audio from moodle server
            $response = $helper->deleteTempAudioFile($upload_name);
        }
        //return babelium server api response json as base64 string back to the client
        return base64_encode(json_encode($babeliumServerResponse));
    }
    else{
        Logging::logBabelium("Processing POST request DONE");
        return $response;
    }
}