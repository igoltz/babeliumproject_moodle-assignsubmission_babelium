function loadVideo(videoId, subtitleId) {
    debug("babelium.core.js::loadVideo()");
    var videoStr = "\
    <video id='submission_video' style='width:100%' poster='"+getPosterUrl(videoId)+"' controls crossorigin='anonymous'>\
        <source src='"+getMP4video(videoId)+"' type='video/mp4'>\
        <source src='"+getWEBMvideo(videoId)+"' type='video/webm'>\
        <track kind='captions' label='"+getSubtitlesLangCaption(subtitleId)+"' src='"+getSubtitlesURL(subtitleId)+"' srclang='"+getSubtitlesLang(subtitleId)+"' default>\
        video not supported\
    </video>";
    //append video element to div
    $('.videocontent').html(videoStr);
}


function isHTMLdescription(description) {
    debug("babelium.core.js::isHTMLdescription()");
    return description.indexOf("><") !== -1;
}


function getSubtitlesURL(subtitleId){
    debug("babelium.core.js::getSubtitlesURL()");
    return host+"/sub-titles/"+subtitleId+".vtt";
}

function getSubtitlesLang(subtitleId) {
    debug("babelium.core.js::getSubtitlesLang()");
    return "en";
}

function getSubtitlesLangCaption(subtitleId) {
    debug("babelium.core.js::getSubtitlesLangCaption()");
    return "English captions";
}

function getPosterUrl(videoId) {
    debug("babelium.core.js::getPosterUrl()");
    hasMedia = exinfo!==undefined && exinfo.media !== undefined;
    if(hasMedia && exinfo.media.thumbnail!==undefined){
        return exinfo.media.thumbnail;
    }
    else{
        return "//babelium-dev.irontec.com/static/_temp/novideo.jpg";
    }
}

function getMP4video(videoId) {
    debug("babelium.core.js::getMP4video()");
    var hasMedia = exinfo!==undefined;
    if(hasMedia){
        var isExercise = !exinfo.exerciseId;
        if(isExercise){
            //exercise
            hasMedia = exinfo.media !== undefined;
            if(hasMedia && exinfo.media.mp4Url!==undefined){
                return exinfo.media.mp4Url;
            }
        }
        else{
            //response
            hasMedia = exinfo.mp4Url !== undefined;
            if(hasMedia){
                return exinfo.mp4Url;
            }
        }
    }
    //default video url for not found
    return "//babelium-dev.irontec.com/static/_temp/video.mp4";
}

function getWEBMvideo(videoId) {
    debug("babelium.core.js::getWEBMvideo()");
    hasMedia = exinfo!==undefined && exinfo.media !== undefined;
    if(hasMedia && exinfo.media.webpUrl!==undefined){
        return exinfo.media.webpUrl;
    }
    else{
        return "//babelium-dev.irontec.com/static/_temp/video.webm";
    }
}

function debug(data) {
    if(debug_enabled){
        console.log(data);
    }
}
