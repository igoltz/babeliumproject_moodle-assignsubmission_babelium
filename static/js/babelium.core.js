var toogle_changed = false;

/* CLOCK VARIABLES BEGIN */

var formathour = 0;
var formatmin = 0;
var formatsec = 0;
var clockIdentifier;
var videoStartTime;
var timer;

/* CLOCK VARIABLES END */

/* CANVAS VARAIBLES BEGIN */

var block_height; //block height is canvas height
var can;
var ctx;
var cue_indicator_width = 2; //2 pixel width for cue indicator
var cue_point_list_ready = false;

var cue_block_outside_color = "red"
var cue_block_inside_color = "green"
var cue_block_passed_color = "blue";
var cue_indicator_color = "white" //cue indicator color
var canvas_background_color = 'rgba(0, 0 ,0, .7)'; //bg color canvas

/* CANVAS VARIABLES END */

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
    showRecordingMode(false);
    //init recorder
    initRecorder();
    var mode = "submission_mode";
    initView(mode);
    initToogle();
    initCanvas();
    overwriteFormControl();
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
    debug("babelium.core.js::onSubmissionCancelledListener()");
    event.preventDefault();
    var url = window.location.href;
    url = url.replace("editsubmission", "view");
    //redirect to exercise view page
    window.location.href = url;
}

function onRecordingButtonPress() {
    debug("babelium.core.js::onRecordingButtonPress()");
    if(!is_recording){
        var video = document.getElementById('submission_video');
        if (video !== undefined && video !== null) {
            if (recorderLoaded) {
                //recording is enabled. play video
                //start video at the beginning
                video.currentTime = 0;
                //play video
                video.play();
                //start recording
            }
            //call recording with status flag
            startRecording(recorderLoaded);
        }
    }
}

function createDownloadLink() {
    debug("babelium.core.js::createDownloadLink()");
    recorder && recorder.exportWAV(function(blob) {
        var url = URL.createObjectURL(blob);
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
    debug("babelium.core.js::getRecordedRole()");
    return $bjq('#id_roleCombo option:selected').text();
}

function getResponseHash() {
    debug("babelium.core.js::getResponseHash()");
    if (exinfo.media) {
        return exinfo.media.mp4Url ? exinfo.media.mp4Url : "";
    } else {
        return exinfo.mp4Url ? exinfo.mp4Url : "";
    }
}

function getResponseData() {
    debug("babelium.core.js::getResponseData()");
    return babelium_server_data;
}

function getSubtitleId() {
    debug("babelium.core.js::getSubtitleId()");
    if (exinfo.media) {
        return exinfo.media.subtitleId ? exinfo.media.subtitleId : no_value;
    } else {
        return exinfo.subtitleId ? exinfo.subtitleId : no_value;
    }
}

function getExerciseId() {
    debug("babelium.core.js::getExerciseId()");
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

/**
    BEGIN TOOGLE CONTROL
**/


function initToogle() {
    debug("babelium.core.js::initToogle()");
    var onVideoToogleChange = function () {
        toogle_changed = !toogle_changed;
        console.log("Toogle status: "+toogle_changed);
        setToogleText(toogle_changed);
        if(toogle_changed){
            //view original video
            onToogleGoesToTrueState();
        }
        else{
            //view edited video
            onToogleGoesToFalseState();
        }
    };

    var toogleElement = document.getElementsByClassName('video-toogle')[0];
    if(toogleElement !== undefined ){
        toogleElement.addEventListener('click', onVideoToogleChange );
        setToogleText(toogle_changed);
    }

    var toogleElementBlock = document.getElementsByClassName("video-toogle-container")[0];
    var blockStatusShow = exinfo!==undefined && exinfo.exerciseId!==undefined;
    //hide toogle if no response
    if(toogleElementBlock!==undefined){
        var visibility = blockStatusShow ? "inherit" : "hidden";
        var display = blockStatusShow ? "" : "none";
        //css visibility
        toogleElementBlock.style.visibility = visibility;
        //css display
        toogleElementBlock.style.display = display;
    }
}

function setToogleText(toogleStatus){
    debug("babelium.core.js::setToogleText()");
    var clsnameLeft = "video-toogle-text-left";
    var toogleTextLeft = document.getElementsByClassName(clsnameLeft)[0];

    var clsnameRight = "video-toogle-text-right";
    var toogleTextRight = document.getElementsByClassName(clsnameRight)[0];

    var focus_color = "#2196f3";
    var no_focus_color = "#3a3a3a";

    //reset text
    if(toogleTextLeft!==undefined){
        toogleTextLeft.innerHTML = getString("view_edited_video");
    }
    if(toogleTextRight!==undefined){
        toogleTextRight.innerHTML = getString("view_original_video");
    }

    //update status
    if(toogleStatus){
        if(toogleTextLeft!==undefined){
            toogleTextLeft.style.fontWeight = "inherit";
            toogleTextLeft.style.color = no_focus_color;
        }
        if(toogleTextRight!==undefined){
            toogleTextRight.style.fontWeight = "Bold";
            toogleTextRight.style.color = focus_color;
        }
    }
    else{
        if(toogleTextLeft!==undefined){
            toogleTextLeft.style.fontWeight = "Bold";
            toogleTextLeft.style.color = focus_color;
        }
        if(toogleTextRight!==undefined){
            toogleTextRight.style.fontWeight = "inherit";
            toogleTextRight.style.color = no_focus_color;
        }
    }
}

function onToogleGoesToTrueState(){
    debug("babelium.core.js::onToogleGoesToTrueState()");
    //load edited video
    if(exinfo!==undefined){
        injectVideoFromId(exinfo.exerciseId, exinfo.subtitleId, "change_to_original");
    }
}

function onToogleGoesToFalseState(){
    debug("babelium.core.js::onToogleGoesToFalseState()");
    //load original video
    if(exinfo!==undefined){
        injectVideoFromId(exinfo.id, exinfo.subtitleId, "edited");
    }
}

/**
    END TOOGLE CONTROL
**/

function showRecordingMode(isRecording){
    debug("babelium.core.js::showRecordingMode()");
    //get recording logo
    var image = document.getElementsByClassName('recording-image')[0];
    if(image !== undefined && image !== null){
        //show or hide recording image
        image.style.display = isRecording ? "inherit" : "none";
    }
    //set recording color
    var color = isRecording ? "red" : "black";
    setStatusColor(color);
    setCounterColor(color);
    if(isRecording){
        //update text
        setStatus(getString('recording_status'));
        //start counter
        startClockCountingOn('clock');
        //update log
        cstm_log(
            getString('recording_log')
        );
    }
    else{
        //update text
        setStatus(getString('submission_recording_controls'));
        //stop counter
        stopClockCountingOn();
        //start counter
        if(audio_recorded){
            cstm_log(
                getString('recording_stopped_log')
            );
            cstm_log(
                getString('recording_link_log')
            );
        }
    }
}

////////////////////////////
// BEGIN CANVAS FUNCTIONS //
////////////////////////////

function initCanvas(){
    debug("babelium.core.js::initCanvas()");
    can = document.getElementsByClassName('cuepointsCanvas')[0];
    if(can!==undefined){
        ctx = can.getContext('2d');
        if(ctx !== undefined ){
            block_height = can.height;
            window.requestAnimationFrame(updateCueInfo);
        }
    }
}

function parseCuePointList(){
    debug("babelium.core.js::parseCuePointList()");
    var video = document.getElementById('submission_video');

    if(subtitle_file_data!==undefined && video!==null && video!==undefined){
        //parse subtitle file
        var parser = new WebVTT.Parser(window, WebVTT.StringDecoder());

        parser.onregion = function(region) {
            console.log(region);
        };
        parser.oncue = function(cue) {
            //read each cue and get times
            //cue.endTime & cue.startTime
            //create cue block
            if( isUserCuePoint(cue) ){
                var x = convertCueTimeToPixelPosition(video, cue.startTime);
                var l = convertCueTimeToPixelPosition(video, cue.endTime - cue.startTime);
                var point = {
                    startX: x,
                    startY: 0,
                    width: l,
                    outside_color: cue_block_outside_color,
                    inside_color: cue_block_inside_color,
                    passed_color: cue_block_passed_color
                };
                cuePointList.push(point);
            }
            //for debug
            debug(point);
        };
        parser.onflush = function() {
            cue_point_list_ready = true;
            paintCuePoints(video);
            console.log("Flushed");
        };
        parser.onparsingerror = function(e) {
            console.log(e);
        };

        parser.parse(subtitle_file_data);
        //Indicates that no more data is expected and will force the parser to parse any unparsed data that it may have.
        //Will also trigger onflush.
        parser.flush();
    }
}

function isUserCuePoint(cue) {
    if(cue === undefined){
        return true; //paint cue by default
    }
    return cue.text.indexOf("<v Yourself>") !== -1; //paint cue if is for user
}

function updateCueInfo(){
    var video = document.getElementById('submission_video');
    //clear canvas
    ctx.clearRect(0, 0, can.width, can.height);
    //set background color
    can.style.backgroundColor = canvas_background_color;

    if(cue_point_list_ready && video!==undefined && video!==null){
        //draw cue blocks
        paintCuePoints(video);
    }

    //draw cursor (user timer arrow indicator)
    var position = convertTimeToPixelPosition(video);
    updateIndicatorPosition(position);

    //request new animation frame
    window.requestAnimationFrame(updateCueInfo);
}

function paintCuePoints(video){
    //draw cuepoints
    if(cuePointList!==undefined){
        for (var i = 0; i < cuePointList.length; i++) {
            var point = cuePointList[i];
            canvas_rect(ctx, point.startX, point.startY, point.width, block_height, calculateBlockColor(point, video));
        }
    }
}

function calculateBlockColor(point, video){
    //convert time to pixel
    if(video!==undefined){
        currentVideoTime = convertTimeToPixelPosition(video);
        if( currentVideoTime < point.startX){
            //cursor is outside of the block
            return point.outside_color;
        }
        else if(currentVideoTime >= point.startX && currentVideoTime <= (point.startX + point.width)){
            //cursor is inside the block
            return point.inside_color;
        }
        else if(currentVideoTime > (point.startX + point.width)){
            //cursor has passed the block
            return point.passed_color;
        }
        else{
            //default color
            return point.outside_color;
        }
    }
    else{
        return point.outside_color;
    }
}

function updateIndicatorPosition(position_x) {
    var fromx = position_x; //top left
    var tox = position_x;
    var fromy = 0;
    var toy = block_height; //bottom
    canvas_arrow(ctx, fromx, fromy, tox, toy, cue_indicator_width, cue_indicator_color);
}

function canvas_arrow(context, fromx, fromy, tox, toy, lineWidth, lineColor){
    context.beginPath();
    context.moveTo(fromx, fromy);
    context.lineTo(tox, toy);
    if(lineWidth > 1){
        context.lineWidth = lineWidth;
    }
    if(lineColor !== undefined){
        context.strokeStyle = lineColor;
    }
    context.stroke();
}

function canvas_rect(ctx, a, b, c, d, color) {
    //a, b --> upper left point coordinates
    //c, d --> width, height
    if(ctx!==undefined){
        //set fill style
        ctx.fillStyle = color;
        //filñ
        ctx.fillRect(a, b, c, d);
    }
}

function convertTimeToPixelPosition(video) {
    if(video!==null && video!==undefined){
        if( video.duration === 0 ){
            return 0;
        }
        else{
            return ( video.currentTime / video.duration ) * can.width;
        }
    }
    else{
        return 0;
    }
}

function convertCueTimeToPixelPosition(video, time) {
    if(video!==null && video!==undefined){
        if( video.duration === 0 ){
            return 0;
        }
        else{
            return ( time / video.duration ) * can.width;
        }
    }
    else{
        return 0;
    }
}

//////////////////////////
// END CANVAS FUNCTIONS //
//////////////////////////