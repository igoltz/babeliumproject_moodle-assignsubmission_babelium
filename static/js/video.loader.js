var noPosterImageUrl = CONSTANTS.no_poster_image_url;
var posterImage = noPosterImageUrl;

/** CACHE **/

var posterURL = undefined;
var originalVideoURL = undefined;
var editedVideoURL = undefined;

/** CACHE **/

function loadVideo(videoId, subtitleId, type) {
    debug("video.loader.js::loadVideo()");
    downloadPosterImage(videoId, subtitleId, type);
}


function isHTMLdescription(description) {
    debug("video.loader.js::isHTMLdescription()");
    return description.indexOf(CONSTANTS.html_indicator) !== CONSTANTS.html_mark_found;
}


function getSubtitlesURL(subtitleId) {
    debug("video.loader.js::getSubtitlesURL()");
    return host + CONSTANTS.subtitles_api_path + subtitleId + CONSTANTS.subtitles_extension;
}

function getSubtitlesLang(subtitleId) {
    debug("video.loader.js::getSubtitlesLang()");
    return CONSTANTS.default_subtitle_lang;
}

function getSubtitlesLangCaption(subtitleId) {
    debug("video.loader.js::getSubtitlesLangCaption()");
    return CONSTANTS.default_subtitle_caption;
}

function getPosterUrl(videoId) {
    debug("video.loader.js::getPosterUrl()");
    if(posterURL === undefined){
        var hasMedia = exinfo !== undefined;
        if (hasMedia) {
            var isExercise = !exinfo.exerciseId;
            if (isExercise) {
                //exercise
                hasMedia = exinfo.media !== undefined;
                if (hasMedia && exinfo.media.thumbnail !== undefined) {
                    posterURL = exinfo.media.thumbnail;
                }
            } else {
                //response
                hasMedia = exinfo.thumbnail !== undefined;
                if (hasMedia) {
                    posterURL = exinfo.thumbnail;
                }
            }
        }
        else{
            //default video url for not found
            posterURL = noPosterImageUrl;
        }
    }
    return posterURL;
}

function downloadPosterImage(videoId, subtitleId, type) {
    debug("video.loader.js::downloadPosterImage()");
    var expectedPosterUrl = getPosterUrl(videoId);
    var onSuccess = function(response, ajaxOptions, thrownError) {
        debug("Poster image is valid");
        posterImage = expectedPosterUrl;
        injectVideoFromId(videoId, subtitleId, type);
    };
    var onError = function(response, ajaxOptions, thrownError) {
        debug("Poster image is not valid");
        posterImage = noPosterImageUrl;
        injectVideoFromId(videoId, subtitleId, type);
    };
    rpc("GET", expectedPosterUrl, onSuccess, onError);
}

function injectVideoFromId(videoId, subtitleId, type) {
    debug("video.loader.js::injectVideoFromId()");
    var videoUrl = getMP4video(videoId, type);
    var videoWebmUrl = getWEBMvideo(videoId, type);
    var subtitlesUrl = getSubtitlesURL(subtitleId);
    var subcaption = getSubtitlesLangCaption(subtitleId);
    var sublang = getSubtitlesLang(subtitleId);
    injectVideo(posterImage, videoUrl,videoWebmUrl, subtitlesUrl, sublang, subcaption);
}

function injectVideo(posterImage, videoUrl, videoWebmUrl, subtitlesUrl, sublang, subcaption) {
    debug("video.loader.js::injectVideo()");
    var videoStr = "\
    <video id='submission_video' style='width:100%' poster='" + posterImage + "' controls crossorigin='anonymous'>\
        <source src='" + videoUrl + "' type='video/mp4'>\
        <source src='" + videoWebmUrl + "' type='video/webm'>\
        <track kind='captions' label='" + subcaption + "' src='" + subtitlesUrl + "' srclang='" + sublang + "' default>\
        video not supported\
    </video>";
    //append video element to div
    var videocontent = document.getElementsByClassName("videocontent")[0];
    if(videocontent!==undefined){
        videocontent.innerHTML = videoStr;
    }

    //set listeners
    var video = document.getElementById('submission_video');
    if (video !== undefined) {
        //on video ended event
        video.addEventListener('ended', onVideoEnded, false);
        //on load metadata event
        if(mode == 'submission_mode'){
            video.addEventListener('loadedmetadata', function() {
                debug("video metadata loaded! parse subtitles now");
                parseCuePointList();
            });
        }
    }
    //translate text to user lang
    translate();
    show();
}

function onVideoEnded() {
    debug("babelium.core.js::onVideoEnded()");
    try{
        autoStopVideo();
        if(isGraderView()){
            debug("No need to stop recording on grader view");
        }
        else{
            if (is_recording) {
                stopRecording();
            }
        }
    }
    catch(err){
        debug("Error on video ended event: "+err.message);
    }
}

function isGraderView() {
    debug("video.loader.js::isGraderView()");
    return window.location.href.indexOf(CONSTANTS.grader_view_mark) !== CONSTANTS.grader_view_mark_found;
}

function autoStopVideo() {
    debug("babelium.core.js::autoStopVideo()");
    var video = document.getElementById('submission_video');
    if (video !== undefined && video !== null) {
        video.pause();
        video.currentTime = CONSTANTS.reset_video_time;
    }
}

function getMP4video(videoId, type) {
    debug("video.loader.js::getMP4video()");
    if(type === "edited"){
        if(editedVideoURL === undefined){
            var hasMedia = exinfo !== undefined;
            if (hasMedia) {
                var isExercise = !exinfo.exerciseId;
                if (isExercise) {
                    //exercise
                    hasMedia = exinfo.media !== undefined;
                    if (hasMedia && exinfo.media.mp4Url !== undefined) {
                        editedVideoURL = exinfo.media.mp4Url;
                    }
                } else {
                    //response
                    hasMedia = exinfo.mp4Url !== undefined;
                    if (hasMedia) {
                        editedVideoURL = exinfo.mp4Url;
                    }
                }
            }
        }
        return editedVideoURL;
    }
    else if( type === "change_to_original"){
        //get exerciseId, get exercise info and return original video url
        if(originalVideoURL === undefined){
            var hasMedia = exinfo !== undefined;
            if (hasMedia) {
                var hasExerciseId = exinfo.exerciseId;
                if(hasExerciseId!==undefined){
                    var exerciseUrl = CONSTANTS.exercise_info_api_path_via_middle + "?name=exerciseinfo&data=" + exinfo.exerciseId;
                    var responseAjaxData = sync_rpc("GET", exerciseUrl);
                    if(responseAjaxData !== undefined){
                        responseAjaxData = JSON.parse(responseAjaxData);
                        if (responseAjaxData.media !== undefined && responseAjaxData.media.mp4Url !== undefined) {
                            originalVideoURL = responseAjaxData.media.mp4Url;
                        }
                    }
                }
            }
        }
        return originalVideoURL;
    }
    //default video url for not found
    originalVideoURL = CONSTANTS.default_video_mp4_url;
    return originalVideoURL;
}

function getWEBMvideo(videoId, type) {
    debug("video.loader.js::getWEBMvideo()");
    hasMedia = exinfo !== undefined && exinfo.media !== undefined;
    if (hasMedia && exinfo.media.webpUrl !== undefined) {
        return exinfo.media.webpUrl;
    } else {
        return CONSTANTS.default_video_webm_url;
    }
}

function debug(data) {
    if (debug_enabled) {
        console.log(data);
    }
}
