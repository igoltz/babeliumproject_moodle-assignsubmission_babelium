//Global scope objects
var $bjq = jQuery.noConflict();
var host = "//babelium-server-dev.irontec.com/api/v3";
var contentServerUrl = "//babelium-server-dev.irontec.com/";
var audioPostUrl = "//babelium-dev.irontec.com/mod/assign/submission/babelium/post.php";
var debug_enabled = location.protocol === 'http:';
var babelium_server_data = "";
var no_value = -1;

function initView() {
    //load subtitles
    var subtitleId = exsubs[0].subtitleId;
    loadSubtitles(subtitleId);
    //load video
    loadVideo(exinfo.id, subtitleId);
    loadExerciseDescription(exinfo.description);
}

function loadSubtitles(id) {
    debug("babelium.core.js::loadSubtitles()");
    var onSuccess = function(response, ajaxOptions, thrownError) {
        console.log("Success: " + response);
    };
    var onError = function(response, ajaxOptions, thrownError) {
        console.log("Error: " + response);
    };
    rpc("GET", getSubtitlesURL(id), onSuccess, onError);
}

function loadExerciseDescription(description) {
    debug("babelium.core.js::loadExerciseDescription()");
    if (isHTMLdescription(description)) {
        //warning: possible XSS injection if value of description is not correctly sanitized
        $('.exdescription').html(description);
    } else {
        $('.exdescription').text(description);
    }
}

function rpc(method, url, onSuccess, onError) {
    debug("babelium.core.js::rpc()");
    // Request with custom header
    jQuery.ajax({
        type: method,
        url: url,
        success: function(xhr, ajaxOptions, thrownError) {
            if (onSuccess !== undefined) {
                onSuccess(xhr.responseText);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            if (onError !== undefined) {
                onError(xhr.responseText);
            }
        }
    });
}