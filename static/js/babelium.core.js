//dynamically load video data and subtitle with previous check

var key = "1234";
var secret = "abcd";
var host = "//babelium-server-dev.irontec.com/api/v3";
var contentServerUrl = "//babelium-server-dev.irontec.com/";
var debug_enabled = true;

var audioPostUrl = "//babelium-dev.irontec.com/mod/assign/submission/babelium/post.php"
window.onload = function() {
    debug("babelium.core.js::onload()");
    if(window.jQuery === undefined || $ === undefined){
        var script = document.createElement('script');
        document.head.appendChild(script);
        script.type = 'text/javascript';
        script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
        script.onload = start;
    }
    else{
        start();
    }
};

function start(){
    debug("babelium.core.js::start()");
    //init recorder
    initRecorder();
    //load subtitles
    var subtitleId = exsubs[0].subtitleId;
    loadSubtitles(subtitleId);
    //load video
    loadVideo(exinfo.id, subtitleId);
    loadExerciseDescription(exinfo.description);
    //set listeners
    var video = document.getElementById('submission_video');
    if(video!==undefined){
        video.addEventListener('ended', onVideoEnded, false);
    }
    overwriteFormControl();
}

function overwriteFormControl() {
    debug("babelium.core.js::overwriteFormControl()");
    var buttonId = 'id_submitbutton';
    var submission = document.getElementById(buttonId);
    if(submission !== undefined){
        submission.addEventListener("click", function(event){
            onSubmissionDoneListener(event);
        });
    }

    //cancel button
    var buttonId = 'id_cancel';
    var submission = document.getElementById(buttonId);
    if(submission !== undefined){
        submission.addEventListener("click", function(event){
            onSubmissionCancelledListener(event);
        });
    }
}

function onSubmissionCancelledListener(event) {
    event.preventDefault();
    var url = window.location.href;
    url = url.replace("editsubmission", "view");
    window.location.href = url;
}

function loadSubtitles(id){
    debug("babelium.core.js::loadSubtitles()");
    var onSuccess = function(response, ajaxOptions, thrownError){
        console.log("Success: "+response);
    };
    var onError = function(response, ajaxOptions, thrownError){
        console.log("Error: "+response);
    };
    rpc("GET", getSubtitlesURL(id), onSuccess, onError);
}



function loadExerciseDescription(description) {
    debug("babelium.core.js::loadExerciseDescription()");
    if(isHTMLdescription(description)){
        //warning: possible XSS injection if value of description is not correctly sanitized
        $('.exdescription').html(description);
    }
    else{
        $('.exdescription').text(description);
    }
}

function rpc(method, url, onSuccess, onError){
    debug("babelium.core.js::rpc()");
    // Request with custom header
    jQuery.ajax(
        {
            type: method,
            url: url,
            success: function(xhr, ajaxOptions, thrownError){
                if(onSuccess !== undefined){
                    onSuccess(xhr.responseText);
                }
            },
            error: function(xhr, ajaxOptions, thrownError){
                if(onError !== undefined){
                    onError(xhr.responseText);
                }
            }
        }
    );
}

function onVideoPlay(){
    debug("babelium.core.js::onVideoPlay()");
    var video = document.getElementById('submission_video');
    if(video !== undefined && video !== null){
        console.log("playing video...");
        //start video at the beginning
        video.currentTime = 0;
        //play video
        video.play();
        console.log("recording...");
        startRecording();
        setStatus("Recording...");
    }
}

function onVideoEnded() {
    debug("babelium.core.js::onVideoEnded()");
    autoStopVideo();
    if(is_recording){
        stopRecording();
    }
}

function autoStopVideo() {
    debug("babelium.core.js::autoStopVideo()");
    var video = document.getElementById('submission_video');
    if(video !== undefined && video !== null){
        video.pause();
    }
}

function setStatus(text){
    debug("babelium.core.js::setStatus()");
    var status = document.getElementById('status_text');
    if(status !== undefined && status !== null && text !== null && text !== undefined){
        status.textContent = text;
    }
}

 function createDownloadLink() {
    debug("babelium.core.js::createDownloadLink()");
    recorder && recorder.exportWAV(function(blob) {
        var url = URL.createObjectURL(blob);
        console.log(url);
        var li = document.createElement('li');
        var au = document.createElement('audio');
        var hf = document.createElement('a');

        au.controls = true;
        au.src = url;
        hf.href = url;
        var filename = new Date().toISOString() +  extension;
        hf.download = filename;
        hf.innerHTML = hf.download;
        li.appendChild(au);
        li.appendChild(hf);
        recordingslist.appendChild(li);
        if(url!==undefined){
            getRecordedAudioStream(url, onAudioStreamReceived);
        }
    });
 }

function getRecordedAudioStream(blob, callback) {
    debug("babelium.core.js::getRecordedAudioStream()");
    //first, self download the file from blob
    var xhr=new XMLHttpRequest();
    xhr.onload = function(e) {
        if(this.readyState === 4) {
            if(callback!==undefined){
                callback(e.target.responseText);
            }
        }
    };
    xhr.open("GET", blob, true);
    xhr.send();
}

function onAudioStreamReceived(audioStream) {
    debug("babelium.core.js::onAudioStreamReceived()");
    //for now, save downloaded audio stream as global variable
    if(audioStream !== undefined){
        lastRecordedAudio = audioStream;
    }
}

function onSubmissionDoneListener(event) {
    debug("babelium.core.js::onSubmissionDoneListener()");

    event.preventDefault();
    //1 make ajax call to moodle middleware
    var onSuccess = function(data, textStatus, xhr){
        //2 audio post was ok. send data to moodle using its form
        var formid = 'mform1';
        var sumbissionForm = document.getElementById(formid);
        if(sumbissionForm !== undefined){
            sumbissionForm.elements["recordedRole"].value = getRecordedRole();
            sumbissionForm.elements["responsehash"].value = getResponseHash();
            //sumbissionForm.submit();
        }
    };
    var onError = function(data, textStatus, xhr){
        //1 show error popup with server returned message
        swal("Error", data, "error");
    };
    sendAudioDataToMiddleWare(audioPostUrl, onSuccess, onError);
}

function getRecordedRole() {
    return $bjq('#id_roleCombo option:selected').text();
}

function getResponseHash() {
    return exinfo.media.mp4Url;
}

function sendAudioDataToMiddleWare(audioPostUrl, onSuccess, onError){
    debug("babelium.core.js::sendAudioDataToMiddleWare()");
    if(showProgressDialog){
        //show success message
        swal(
            {
              title: "Recording finished",
              html: true,
              text: "<h3>Your audio has been successfully recorded.</h3>\
              <p>Please wait while uploading...</p>\
              <div id='bar_container' style='margin: 20px;width: 400px;height: 8px;'>\
                  <progress id='progress_bar' value='0' max='100'>\
                    <span>0</span>% uploaded\
                  </progress>\
              </div>\
              ",
              type: "info",
              showCancelButton: false,
              closeOnConfirm: true,
              showLoaderOnConfirm: true,
              showConfirmButton: false
            }
        );

        //define additional middleware data
        var fd = new FormData();
        var b = exinfo.media.mp4Url;
        var baseUrl = b.substring(0, b.lastIndexOf('/')+1 );
        var timestamp = new Date().getTime();
        var newMediaUrl = baseUrl + "resp-"+timestamp+".flv";

        //convert last audio data to base64
        if(lastRecordedAudio!== undefined && lastRecordedAudio.length > 0){
            lastRecordedAudio = btoa(lastRecordedAudio);
        }
        fd.append("audiostream", lastRecordedAudio);
        fd.append("audiolen",   lastRecordedAudio.length);
        fd.append("audioname",  timestamp);

        fd.append("idexercise", exinfo.id);
        fd.append("idmedia",  exinfo.media.id);
        fd.append("idsubtitle", exinfo.media.subtitleId);
        fd.append("mediaUrl",   newMediaUrl);

        fd.append("rolename",   getRecordedRole());
        fd.append("responsehash",   getResponseHash());

        var method01 = 3;
        if(method01 == 1){
            var request = new XMLHttpRequest();
            request.open("POST", audioPostUrl);
            request.send(fd);
        }
        else if(method01 == 2){
            debug("babelium.core.js::demo get request to moodle()");
            var onSuccess = function(response, ajaxOptions, thrownError){
                console.log("demo request response: "+response);
            };
            var onError = function(response, ajaxOptions, thrownError){
                console.log("demo request error response: "+response);
            };
            rpc("POST", audioPostUrl, onSuccess, onError);

        }
        else{
            $.ajax({
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    // Upload progress
                    xhr.upload.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
                            console.log(percentComplete);
                            updateUploadDialogProgressBar(percentComplete);
                        }
                   }, false);

                   // Download progress
                   xhr.addEventListener("progress", function(evt){
                       if (evt.lengthComputable) {
                           var percentComplete = evt.loaded / evt.total;
                           // Do something with download progress
                           console.log(percentComplete);
                       }
                   }, false);

                   return xhr;
                },
                type: 'POST',
                url: audioPostUrl,
                data: fd,
                processData: false,  // tell jQuery not to process the data
                contentType: false,   // tell jQuery not to set contentType
                success: function(data, textStatus, xhr){
                    //show success dialog. and execute callback when click on button
                    swal({
                      title: "Upload finished",
                      text: "File successfully uploaded",
                      type: "success",
                      showCancelButton: false
                    },
                    function(){
                        if( onSuccess !== undefined ){
                            onSuccess(data, textStatus, xhr);
                        }
                    });
                },
                error: function(data, textStatus, xhr){
                    //execute error callback
                    if( onError !== undefined ){
                        onError(data, textStatus, xhr);
                    }
                }
            });
        }
    }
    else{
        var xhr = new XMLHttpRequest();
        xhr.open("POST", audioPostUrl, true);
        xhr.send(fd);
    }
}
