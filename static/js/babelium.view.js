//Global scope objects
var $bjq = jQuery.noConflict();
var host = CONSTANTS.babelium_host_api;
var contentServerUrl = CONSTANTS.babelium_host;
var audioPostUrl = CONSTANTS.babelium_audio_api;
var debug_enabled = location.protocol === CONSTANTS.http;
var babelium_server_data = "";
var no_value = -1;
var is_babelium_view = true;
var subtitle_file_data;
var cuePointList = [];

var mode;
function initView(exec_mode) {
    debug("babelium.view.js::initView()");
    mode = exec_mode;

    if($ === undefined && jQuery!==undefined){
        debug("Reseeting $ value as jQuery");
        $ = jQuery;
    }

    //load subtitles
    var subtitleId = exsubs[0].subtitleId;
    loadSubtitles(subtitleId);
    //load video
    loadVideo(exinfo.id, subtitleId, "edited");
    loadExerciseDescription(exinfo.description);
}

function loadSubtitles(id) {
    debug("babelium.view.js::loadSubtitles()");
    var onSuccess = function(response, ajaxOptions, thrownError) {
        console.log("Success: " + response);
        subtitle_file_data = response;
    };
    var onError = function(response, ajaxOptions, thrownError) {
        console.log("Error: " + response);
    };
    rpc("GET", getSubtitlesURL(id), onSuccess, onError);
}

function loadExerciseDescription(description) {
    debug("babelium.view.js::loadExerciseDescription()");
    if (isHTMLdescription(description)) {
        //warning: possible XSS injection if value of description is not correctly sanitized
        $('.exdescription').html(description);
    } else {
        $('.exdescription').text(description);
    }
}

function rpc(method, url, onSuccess, onError) {
    debug("babelium.view.js::rpc()");
    // Request with custom header
    jQuery.ajax({
        type: method,
        url: url,
        success: function(xhr, ajaxOptions, thrownError) {
            if (onSuccess !== undefined) {
                var data = xhr !== undefined ? xhr : xhr.responseText;
                onSuccess(data);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if (onError !== undefined) {
                var data = xhr !== undefined ? xhr : xhr.responseText;
                onError(data);
            }
        }
    });
}

function sync_rpc(method, url, onSuccess, onError){
    debug("babelium.view.js::sync_rpc()");
    // Request with custom header
    return jQuery.ajax({
        type: method,
        url: url,
        async: false
    }).responseText;
}

function showLoading(value){
    debug("babelium.view.js::showLoading()");
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
    debug("babelium.view.js::translate()");
    setButtonsText();
    setTitle();
    setlogs();
}

function setButtonsText(){
    debug("babelium.view.js::setButtonsText()");
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
    debug("babelium.view.js::setTitle()");
    var title = document.getElementsByClassName("exercise-desc-title")[0];
    if(title){
        title.innerHTML = getString("exercise_title");
    }
}

function setlogs(){
    debug("babelium.view.js::setlogs()");
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
    debug("babelium.view.js::show()");
    //hide loader
    showLoading(false);
    //show container
    var container = document.getElementsByClassName("babelium-container")[0];
    if(container){
        container.style.display = 'block';
    }
}

function setStatus(text) {
    debug("babelium.view.js::setStatus()");
    var status = document.getElementById('status_text');
    if (status !== undefined && text !== undefined && status !== null && text !== null) {
        status.textContent = text;
    }
}

function setStatusColor(color) {
    debug("babelium.view.js::setStatusColor()");
    var status = document.getElementById('status_text');
    if (status !== undefined && color !== undefined && status !== null && color !== null) {
        status.style.color = color;
    }
}

function setCounterColor(color){
    debug("babelium.view.js::setCounterColor()");
    var counter = document.getElementsByClassName('clock')[0];
    if (counter !== undefined && color !== undefined && counter !== null && color !== null) {
        counter.style.color = color;
    }
}