//dynamically load video data and subtitle with previous check

var key = "1234";
var secret = "abcd";
var host = "//babelium-server-dev.irontec.com/api/v3";
var contentServerUrl = "//babelium-server-dev.irontec.com/";

window.onload = function() {
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
}

function loadSubtitles(id){
    var onSuccess = function(response){
        console.log("Success: "+response);
    };
    var onError = function(response){
        console.log("Error: "+response);
    };
    rpc("GET", getSubtitlesURL(id), onSuccess, onError);
}

function loadVideo(videoId, subtitleId) {
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

function loadExerciseDescription(description) {
    if(isHTMLdescription(description)){
        //warning: possible XSS injection if value of description is not correctly sanitized
        $('.exdescription').html(description);
    }
    else{
        $('.exdescription').text(description);
    }
}

function isHTMLdescription(description) {
    return description.indexOf("><") !== -1;
}

function rpc(method, url, onSuccess, onError){
    // Request with custom header
    jQuery.ajax(
        {
            url: url,
            /*headers:
            {
                'access-key': key,
                'secret': secret
            },*/
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


function getSubtitlesURL(subtitleId){
    return host+"/sub-titles/"+subtitleId+".vtt";
}

function getSubtitlesLang(subtitleId) {
    return "en";
}

function getSubtitlesLangCaption(subtitleId) {
    return "English captions";
}

function getPosterUrl(videoId) {
    hasMedia = exinfo!==undefined && exinfo.media !== undefined;
    if(hasMedia && exinfo.media.thumbnail!==undefined){
        return exinfo.media.thumbnail;
    }
    else{
        return "//babelium-dev.irontec.com/static/_temp/novideo.jpg";
    }
}

function getMP4video(videoId) {
    hasMedia = exinfo!==undefined && exinfo.media !== undefined;
    if(hasMedia && exinfo.media.mp4Url!==undefined){
        return exinfo.media.mp4Url;
    }
    else{
        return "//babelium-dev.irontec.com/static/_temp/video.mp4";
    }
}

function getWEBMvideo(videoId) {
    hasMedia = exinfo!==undefined && exinfo.media !== undefined;
    if(hasMedia && exinfo.media.webpUrl!==undefined){
        return exinfo.media.webpUrl;
    }
    else{
        return "//babelium-dev.irontec.com/static/_temp/video.webm";
    }
}

function onVideoPlay(){
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
    autoStopVideo();
    if(is_recording){
        stopRecording();
    }
}

function autoStopVideo() {
    var video = document.getElementById('submission_video');
    if(video !== undefined && video !== null){
        video.pause();
    }
}

function setStatus(text){
    var status = document.getElementById('status_text');
    if(status !== undefined && status !== null && text !== null && text !== undefined){
        status.textContent = text;
    }
}



function upload(blob, filename, url) {
    //first, self download the file from blob
    var xhr=new XMLHttpRequest();
    xhr.onload=function(e) {
        if(this.readyState === 4) {
            //upload received data as blob/raw to server
            //then, upload downloaded file to server
            send(filename, e.target.responseText, url);
        }
    };
    xhr.open("GET",blob,true);
    xhr.send();
}

function send(filename, data, url){
    if(showProgressDialog){
        //show success message
        swal(
            {
              title: "Recording finished",
              html: true,
              text: "<h2>Your audio has been successfully recorded.</h2>\
              <p>Please wait while uploading...</p>\
              <div id='bar_container' style='margin: 20px;width: 400px;height: 8px;'></div>\
              ",
              type: "info",
              showCancelButton: false,
              closeOnConfirm: true,
              showLoaderOnConfirm: true
            }
        );

        //create progress bar
        var progressBar = new ProgressBar.Line(bar_container, {
          strokeWidth: 4,
          easing: 'easeInOut',
          duration: 1400,
          color: '#FFEA82',
          trailColor: '#eee',
          trailWidth: 1,
          svgStyle: {width: '100%', height: '100%'}
        });
        progressBar.set(1);

        $.ajax({
            xhr: function() {
                var xhr = new XMLHttpRequest();
                // Upload progress
                xhr.upload.addEventListener("progress", function(evt){
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        console.log(percentComplete);
                        if(progressBar!==undefined){
                            //update progress bar
                            progressBar.set(percentComplete);
                        }
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
            url: "/",
            data: {},
            success: function(data){
                // Do something success-ish
                swal("Upload finished", "File successfully uploaded", "success");
            }
        });
    }
    else{
        var xhr = new XMLHttpRequest();
        xhr.onload=function(e) {
            if(this.readyState === 4) {
                console.log("Server returned: ",e.target.responseText);
            }
        };
        var fd=new FormData();
        fd.append("audioname", filename);
        fd.append("audiofile",data);
        xhr.open("POST",url,true);
        xhr.send(fd);
    }
}
