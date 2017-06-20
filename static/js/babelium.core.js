window.onload = function() {
    debug("babelium.core.js::onload()");
    if (window.jQuery === undefined || $ === undefined) {
        var script = document.createElement('script');
        document.head.appendChild(script);
        script.type = 'text/javascript';
        script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
        script.onload = start;
    } else {
        start();
    }
};

function start() {
    debug("babelium.core.js::start()");
    showLoading(true);
    //init recorder
    initRecorder();
    initView();
    overwriteFormControl();
    //translate text to user lang
    translate();
    show();
}

function showLoading(value){
    var babelium_loading_row = document.getElementsByClassName("babelium_loading_row")[0];
    if(babelium_loading_row){
        //make sure it is shown
        if(value === true){
            babelium_loading_row.style.display = 'block';
        }
        else{
            babelium_loading_row.style.display = 'none';
        }
    }
}

function translate(){
    setStatus(getString('submission_recording_controls'));
    setButtonsText();
    setTitle();
    setlogs();
}

function setButtonsText(){
    var record = document.getElementsByClassName("startRecord")[0];
    if(record){
        record.innerHTML = getString("record");
    }
    var stop = document.getElementsByClassName("stopRecord")[0];
    if(stop){
        stop.innerHTML = getString("stop");
    }
}

function setTitle(){
    var title = document.getElementsByClassName("exercise-desc-title")[0];
    if(title){
        title.innerHTML = getString("exercise_title");
    }
}

function setlogs(){
    var recording_list_title = document.getElementsByClassName("recording_list_title")[0];
    if(recording_list_title){
        recording_list_title.innerHTML = getString("recording_list_title");
    }
    var recording_log_title = document.getElementsByClassName("recording_log_title")[0];  
    if(recording_log_title){
        recording_log_title.innerHTML = getString("recording_log_title");
    }
}

function show(){
    //hide loader
    showLoading(false);
    //show container
    var container = document.getElementsByClassName("babelium-container")[0];
    if(container){
        container.style.display = 'block';
    }
}

function overwriteFormControl() {
    debug("babelium.core.js::overwriteFormControl()");
    var buttonId = 'id_submitbutton';
    var submission = document.getElementById(buttonId);
    if (submission !== undefined) {
        submission.addEventListener("click", function(event) {
            if (audio_recorded || debug_enabled) {
                onSubmissionDoneListener(event);
            } else {
                event.preventDefault();
                sweetAlert(
                    getString("swal_no_record_submission_title"),
                    getString("swal_no_record_submission_body"),
                    "warning"
                );
            }
        });
    }

    //cancel button
    var buttonId = 'id_cancel';
    var submission = document.getElementById(buttonId);
    if (submission !== undefined) {
        submission.addEventListener("click", function(event) {
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

function onVideoPlay() {
    debug("babelium.core.js::onVideoPlay()");
    var video = document.getElementById('submission_video');
    if (video !== undefined && video !== null) {
        if (recorderLoaded) {
            console.log("playing video...");
            //start video at the beginning
            video.currentTime = 0;
            //play video
            video.play();
            console.log("recording...");
            startRecording();
            setStatus(getString("recording_status"));
        } else {
            cstm_log(getString("recorder_no_loaded_log"));
            startRecording();
        }
    }
}

function setStatus(text) {
    debug("babelium.core.js::setStatus()");
    var status = document.getElementById('status_text');
    if (status !== undefined && status !== null && text !== null && text !== undefined) {
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
        var filename = new Date().toISOString() + extension;
        hf.download = filename;
        hf.innerHTML = hf.download;
        li.appendChild(au);
        li.appendChild(hf);
        recordingslist.appendChild(li);
        if (url !== undefined) {
            getRecordedAudioStream(blob, url, onAudioStreamReceived);
        }
    });
}

function getRecordedAudioStream(blob, bloburl, callback) {
    debug("babelium.core.js::getRecordedAudioStream()");
    //using FileReader API
    var reader = new FileReader();
    reader.onloadend = function() {
        base64data = reader.result;
        if (callback !== undefined) {
            callback(base64data);
        }
    }
    if (blob !== undefined) {
        reader.readAsDataURL(blob);
    } else {
        debug("No blob audio file detected");
    }
}

function onAudioStreamReceived(audioStream) {
    debug("babelium.core.js::onAudioStreamReceived()");
    //for now, save downloaded audio stream as global variable
    if (audioStream !== undefined) {
        lastRecordedAudio = audioStream;
    }
}

function onSubmissionDoneListener(event) {
    debug("babelium.core.js::onSubmissionDoneListener()");
    event.preventDefault();
    if (debug_enabled) {
        //2 audio post was ok. send data to moodle using its form
        var formid = 'mform1';
        var sumbissionForm = document.getElementById(formid);
        if (sumbissionForm !== undefined) {
            sumbissionForm.elements["recordedRole"].value = getRecordedRole();
            sumbissionForm.elements["responsehash"].value = getResponseHash();
            sumbissionForm.elements["payload"].value = "ew0KCSJpZCI6IDk5OQ0KfQ=="; // {"id": 999} as base 64
            sumbissionForm.submit();
        }
    } else {
        //1 make ajax call to moodle middleware
        var onSuccess = function(data, textStatus, xhr) {
            //2 audio post was ok. send data to moodle using its form
            var formid = 'mform1';
            var sumbissionForm = document.getElementById(formid);
            if (sumbissionForm !== undefined) {
                sumbissionForm.elements["recordedRole"].value = getRecordedRole();
                sumbissionForm.elements["responsehash"].value = getResponseHash();
                sumbissionForm.elements["payload"].value = getResponseData();
                sumbissionForm.submit();
            }
        };
        var onError = function(data, textStatus, xhr) {
            //1 show error popup with server returned message
            swal("Error", data, "error");
        };
        sendAudioDataToMiddleWare(audioPostUrl, onSuccess, onError);
    }
}

function getRecordedRole() {
    return $bjq('#id_roleCombo option:selected').text();
}

function getResponseHash() {
    if (exinfo.media) {
        return exinfo.media.mp4Url ? exinfo.media.mp4Url : "";
    } else {
        return exinfo.mp4Url ? exinfo.mp4Url : "";
    }
}

function getResponseData() {
    return babelium_server_data;
}

function getSubtitleId() {
    if (exinfo.media) {
        return exinfo.media.subtitleId ? exinfo.media.subtitleId : no_value;
    } else {
        return exinfo.subtitleId ? exinfo.subtitleId : no_value;
    }
}

function getExerciseId() {
    if (exinfo.exerciseId) {
        //response mode
        return exinfo.exerciseId ? exinfo.exerciseId : no_value;
    } else {
        //exercise mode
        return exinfo.id ? exinfo.id : no_value;
    }
}

function sendAudioDataToMiddleWare(audioPostUrl, onSuccess, onError) {
    debug("babelium.core.js::sendAudioDataToMiddleWare()");
    if (showProgressDialog) {
        //show success message
        swal({
            title: getString("swal_audio_recorded_title"),
            html: true,
            text: "<h3>"+getString("swal_audio_recorded_h3")+"</h3>\
              <p>"+getString("swal_audio_recorded_p")+"</p>\
              <div id='bar_container' style='margin: 20px;width: 400px;height: 8px;'>\
                  <progress id='progress_bar' value='0' max='100'>\
                    <span>0</span>% "
                    +getString("dialog_uploaded_ratio")+
                  "</progress>\
              </div>\
              ",
            type: "info",
            showCancelButton: false,
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
            showConfirmButton: false
        });

        //define additional middleware data
        var fd = new FormData();
        var b = getResponseHash();
        var baseUrl = b.substring(0, b.lastIndexOf('/') + 1);
        var timestamp = new Date().getTime();
        var newMediaUrl = baseUrl + "resp-" + timestamp + ".flv";

        if (lastRecordedAudio !== undefined && lastRecordedAudio.length > 0) {
            fd.append("audiostream", lastRecordedAudio);
            fd.append("audiolen", lastRecordedAudio.length);
        } else {
            fd.append("audiostream", []);
            fd.append("audiolen", 0);
        }
        fd.append("audioname", timestamp);

        fd.append("idexercise", getExerciseId());
        fd.append("idsubtitle", getSubtitleId());
        fd.append("mediaUrl", newMediaUrl);
        fd.append("idstudent", -1);


        fd.append("rolename", getRecordedRole());
        fd.append("responsehash", getResponseHash());

        var method01 = 1;
        if (method01 === 3) {
            var request = new XMLHttpRequest();
            request.open("POST", audioPostUrl);
            request.send(fd);
        } else if (method01 === 2) {
            debug("babelium.core.js::demo get request to moodle()");
            var onSuccess = function(response, ajaxOptions, thrownError) {
                console.log("demo request response: " + response);
            };
            var onError = function(response, ajaxOptions, thrownError) {
                console.log("demo request error response: " + response);
            };
            rpc("POST", audioPostUrl, onSuccess, onError);

        } else {
            $.ajax({
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    // Upload progress
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
                            console.log(percentComplete);
                            updateUploadDialogProgressBar(percentComplete);
                        }
                    }, false);

                    // Download progress
                    xhr.addEventListener("progress", function(evt) {
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
                processData: false, // tell jQuery not to process the data
                contentType: false, // tell jQuery not to set contentType
                success: function(data, textStatus, xhr) {
                    //show success dialog. and execute callback when click on button
                    swal({
                            title: getString("swal_file_uploaded_title"),
                            text: getString("swal_file_uploaded_body"),
                            type: "success",
                            showCancelButton: false
                        },
                        function() {
                            if (onSuccess !== undefined) {
                                babelium_server_data = data;
                                onSuccess(data, textStatus, xhr);
                            }
                        });
                },
                error: function(data, textStatus, xhr) {
                    //execute error callback
                    if (onError !== undefined) {
                        onError(data, textStatus, xhr);
                    }
                }
            });
        }
    } else {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", audioPostUrl, true);
        xhr.send(fd);
    }
}