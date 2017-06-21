var noPosterImageUrl = "//babelium-dev.irontec.com/static/_temp/novideo.jpg";
var posterImage = noPosterImageUrl;

function loadVideo(videoId, subtitleId, type) {
    debug("video.loader.js::loadVideo()");
    downloadPosterImage(videoId, subtitleId, type);
}


function isHTMLdescription(description) {
    debug("video.loader.js::isHTMLdescription()");
    return description.indexOf("><") !== -1;
}


function getSubtitlesURL(subtitleId) {
    debug("video.loader.js::getSubtitlesURL()");
    return host + "/sub-titles/" + subtitleId + ".vtt";
}

function getSubtitlesLang(subtitleId) {
    debug("video.loader.js::getSubtitlesLang()");
    return "en";
}

function getSubtitlesLangCaption(subtitleId) {
    debug("video.loader.js::getSubtitlesLangCaption()");
    return "English captions";
}

function getPosterUrl(videoId) {
    debug("video.loader.js::getPosterUrl()");
    var hasMedia = exinfo !== undefined;
    if (hasMedia) {
        var isExercise = !exinfo.exerciseId;
        if (isExercise) {
            //exercise
            hasMedia = exinfo.media !== undefined;
            if (hasMedia && exinfo.media.thumbnail !== undefined) {
                return exinfo.media.thumbnail;
            }
        } else {
            //response
            hasMedia = exinfo.thumbnail !== undefined;
            if (hasMedia) {
                return exinfo.thumbnail;
            }
        }
    }
    //default video url for not found
    return noPosterImageUrl;
}

function downloadPosterImage(videoId, subtitleId, type) {
    var expectedPosterUrl = getPosterUrl(videoId);
    debug("video.loader.js::downloadPosterImage()");
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
    var videoUrl = getMP4video(videoId, type);
    var videoWebmUrl = getWEBMvideo(videoId, type);
    var subtitlesUrl = getSubtitlesURL(subtitleId);
    var subcaption = getSubtitlesLangCaption(subtitleId);
    var sublang = getSubtitlesLang(subtitleId);
    injectVideo(posterImage, videoUrl,videoWebmUrl, subtitlesUrl, sublang, subcaption);
}

function injectVideo(posterImage, videoUrl, videoWebmUrl, subtitlesUrl, sublang, subcaption) {
    var videoStr = "\
    <video id='submission_video' style='width:100%' poster='" + posterImage + "' controls crossorigin='anonymous'>\
        <source src='" + videoUrl + "' type='video/mp4'>\
        <source src='" + videoWebmUrl + "' type='video/webm'>\
        <track kind='captions' label='" + subcaption + "' src='" + subtitlesUrl + "' srclang='" + sublang + "' default>\
        video not supported\
    </video>";
    //append video element to div
    $('.videocontent').html(videoStr);

    //set listeners
    var video = document.getElementById('submission_video');
    if (video !== undefined) {
        video.addEventListener('ended', onVideoEnded, false);
    }
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
    return window.location.href.indexOf("&action=grader&") != -1;
}

function autoStopVideo() {
    debug("babelium.core.js::autoStopVideo()");
    var video = document.getElementById('submission_video');
    if (video !== undefined && video !== null) {
        video.pause();
        video.currentTime = 0;
    }
}

function getMP4video(videoId, type) {
    debug("video.loader.js::getMP4video()");
    if(type === "edited"){
        var hasMedia = exinfo !== undefined;
        if (hasMedia) {
            var isExercise = !exinfo.exerciseId;
            if (isExercise) {
                //exercise
                hasMedia = exinfo.media !== undefined;
                if (hasMedia && exinfo.media.mp4Url !== undefined) {
                    return exinfo.media.mp4Url;
                }
            } else {
                //response
                hasMedia = exinfo.mp4Url !== undefined;
                if (hasMedia) {
                    return exinfo.mp4Url;
                }
            }
        }
    }
    else if( type === "original"){
        var hasMedia = exinfo !== undefined;
        if (hasMedia) {
            var isExercise = !exinfo.exerciseId;
            if (isExercise) {
                //exercise
                hasMedia = exinfo.media !== undefined;
                if (hasMedia && exinfo.media.mp4Url !== undefined) {
                    return exinfo.media.mp4Url;
                }
            } else {
                //response
                hasMedia = exinfo.mp4Url !== undefined;
                if (hasMedia) {
                    return exinfo.mp4Url;
                }
            }
        }
    }
    //default video url for not found
    return "//babelium-dev.irontec.com/static/_temp/video.mp4";
}

function getWEBMvideo(videoId, type) {
    debug("video.loader.js::getWEBMvideo()");
    hasMedia = exinfo !== undefined && exinfo.media !== undefined;
    if (hasMedia && exinfo.media.webpUrl !== undefined) {
        return exinfo.media.webpUrl;
    } else {
        return "//babelium-dev.irontec.com/static/_temp/video.webm";
    }
}

function debug(data) {
    if (debug_enabled) {
        console.log(data);
    }
}