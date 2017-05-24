 var generateMp3 = false;
 var extension;
 var audio_context;
 var recorder;
 var audio_recorded = false;
 var is_recording = false;
 var recorderLoaded = false;
 var progressBar;
 var showProgressDialog = true;

 function cstm_log(e, data) {
    log.innerHTML += "\n" + e + " " + (data || '');
 }

 function startRecording() {
    //check if secure origin
    if (location.protocol === 'http:') {
        swal("Recording disabled", "For security reasons, audio recording is disabled on non HTTPS websites" ,"error");
    }
    else{
        if(!is_recording){
            if(recorder !== undefined){
                recorder.record();
                recorder && recorder.stop();
                recorder && recorder.record();
                cstm_log('Recording...');
                is_recording = true;
            }
        }
    }
 }

 function stopRecording() {
    if(is_recording){
        is_recording = false;
        if(recorder!==undefined){
            recorder && recorder.stop();
            autoStopVideo();
            cstm_log('Stopped recording.');
            cstm_log('Creating audio link...');
            // create WAV download link using audio data blob
            createDownloadLink();
            recorder.clear();
        }
    }
    else{
        //shoe error
        sweetAlert("Babelium recorder", "You have to start a record first", "error");
    }
    setStatus("Audio recording controls");
 }

 function createDownloadLink() {
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
        //make post
        upload(url, filename, "https://babelium-dev.irontec.com/static/upload/audio.php")
    });
 }

function initRecorder() {
    if(!recorderLoaded){
        if(generateMp3){
            extension = ".mp3";
        }
        else{
            extension = ".wav";
        }
        //check if secure origin
        if (location.protocol === 'http:') {
            // page is in-secure
            document.body.innerHTML = '\
            <div class="alert alert-danger">\
            <strong>RECORDING DISABLED!</strong>\
            For security reasons, audio recording is disabled on non HTTPS locations\
            </div>'
            + document.body.innerHTML;
        }
        try {
            // webkit shim
            window.AudioContext = window.AudioContext || window.webkitAudioContext;
            navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.mediaDevices.getUserMedia;
            window.URL = window.URL || window.webkitURL;

            audio_context = new AudioContext;
            cstm_log('Audio context set up.');
            cstm_log('navigator.getUserMedia() ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
        }
        catch (e) {
            cstm_log('No web audio support in this browser!');
            sweetAlert("Oops...", "No web audio support in this browser", "error");
        }

        navigator.getUserMedia(
            {audio: true},
            startUserMedia,
            function(e) {
                cstm_log('No live audio input: ' + e);
                sweetAlert("Oops...", "No live audio input", "error");
            }
        );
    }
 };

  function startUserMedia(stream) {
    var input = audio_context.createMediaStreamSource(stream);
    cstm_log('Media stream created.');
    // Uncomment if you want the audio to feedback directly
    //input.connect(audio_context.destination);
    //cstm_log('Input connected to audio context destination.');

    //custom recorder settings
    config = {
        sampleRate : 48000, // 48kbps = 48000 sample rate in bits,
        numChannels: 1,
        bufferLen: 1024
    };
    cstm_log('Setting recorder configuration...');

    recorder = new Recorder(input, config);
    cstm_log('Recorder initialised.');
    recorderLoaded = true;
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
              text: "<h2>Your audio has been successfully recorded.<h2>\
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
