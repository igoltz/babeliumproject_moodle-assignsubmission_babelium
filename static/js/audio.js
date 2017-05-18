 var generateMp3 = false;
 var extension;
 var audio_context;
 var recorder;
 var audio_recorded = false;
 var is_recording = false;
 var recorderLoaded = false;

 function cstm_log(e, data) {
    log.innerHTML += "\n" + e + " " + (data || '');
 }

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

 function startRecording() {
    initRecorder();
    if(!is_recording){
        recorder && recorder.record();
        cstm_log('Recording...');
        is_recording = true;
    }
 }

 function stopRecording() {
    if(is_recording){
        is_recording = false;

        if(recorder!==undefined){
            recorder && recorder.stop();
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
        upload(url, filename, "https://babelium-static.irontec.com/upload/audio.php")
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
            document.body.innerHTML = '<div class="alert alert-danger">\
            <strong>RECORDING DISABLED!</strong>\
            For security reasons, audio recording is disabled on non HTTPS locations\
            </div>'
            + document.body.innerHTML;
        }
        try {
            // webkit shim
            window.AudioContext = window.AudioContext || window.webkitAudioContext;
            navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mediaDevices.getUserMedia;
            window.URL = window.URL || window.webkitURL;

            audio_context = new AudioContext;
            cstm_log('Audio context set up.');
            cstm_log('navigator.getUserMedia() ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
        }
        catch (e) {
            cstm_log('No web audio support in this browser!');
            sweetAlert("Oops...", "No web audio support in this browser", "error");
        }

        navigator.getUserMedia({audio: true}, startUserMedia, function(e) {
            cstm_log('No live audio input: ' + e);
            sweetAlert("Oops...", "No live audio input", "error");
        });
    }
 };

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
    var xhr=new XMLHttpRequest();
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

window.onload = function() {
    if(window.jQuery === undefined || $ === undefined){
        var script = document.createElement('script');
        document.head.appendChild(script);
        script.type = 'text/javascript';
        script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
        script.onload = initRecorder;
    }
    else{
        initRecorder();
    }
};
