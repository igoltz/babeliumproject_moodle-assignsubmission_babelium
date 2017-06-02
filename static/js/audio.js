 var generateMp3 = false;
 var extension;
 var audio_context;
 var recorder;
 var audio_recorded = false;
 var is_recording = false;
 var recorderLoaded = false;
 var showProgressDialog = true;
 var lastRecordedAudio = [];
 var recording_permission_granted = false;
 function cstm_log(e, data) {
    log.innerHTML += "\n" + e + " " + (data || '');
 }

 function startRecording() {
    if(recording_permission_granted){
        //check if secure origin
        if (location.protocol === 'http:') {
            swal("Recording disabled", "For security reasons, audio recording is disabled on non HTTPS websites" ,"error");
            autoStopVideo();
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
    else{
        swal({
            title: "Allow microphone",
            text: "Please, allow microphone access before recording",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Allow",
            closeOnConfirm: true
            },
            function(){
                checkPermissions('microphone');
            }
        );
    }
 }

 function checkPermissions(permissionName, descriptor){
    if(!recording_permission_granted){
        initRecorder();
        //request required permissions
        //mic only for now
        navigator.permissions.query(descriptor || {
            name: permissionName
        })
        .then(function(result) {
        if (result.state == 'granted') {
            recording_permission_granted = true;
        } else if (result.state == 'prompt') {
            recording_permission_granted = false;
        } else if (result.state == 'denied') {
            recording_permission_granted = false;
        }
        result.onchange = function() {
            console.log("permission changed");
        };
    });

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

