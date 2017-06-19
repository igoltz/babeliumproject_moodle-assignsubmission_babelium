 var generateMp3 = false;
 var extension;
 var audio_context;
 var recorder;
 var is_recording = false;
 var recorderLoaded = false;
 var showProgressDialog = true;
 var lastRecordedAudio = [];
 var recording_permission_granted = false;
 var audio_recorded = false;
 var is_page_insecure_msg_shown = false;

 function cstm_log(e, data) {
     log.innerHTML += "\n" + e + " " + (data || '');
 }

 function startRecording() {
     if (recording_permission_granted) {
         //check if secure origin
         if (location.protocol === 'http:') {
            swal(
                getString("swal_msg_http_title"),
                getString("swal_msg_http_body"),
                "error"
            );
             autoStopVideo();
         } else {
             if (!is_recording) {
                 if (recorder !== undefined) {
                     audio_recorded = false;
                     recorder.record();
                     recorder && recorder.stop();
                     recorder && recorder.record();
                     cstm_log(getString('recording_log'));
                     is_recording = true;
                 }
             }
         }
     } else {
         autoStopVideo();
         swal({
                 title: getString("swal_msg_allow_micro_title"),
                 text: getString("swal_msg_allow_micro_body"),
                 type: "warning",
                 showCancelButton: true,
                 confirmButtonText: getString("swal_msg_allow_btn"),
                 cancelButtonText: getString("swal_msg_cancel_btn"),
                 closeOnConfirm: true
             },
             function() {
                 checkPermissions('microphone');
             }
         );
     }
 }

 function checkPermissions(permissionName, descriptor) {
     if (!recording_permission_granted) {
         initRecorder();
         //request required permissions
         //mic only for now
         try {
             navigator.permissions.query(descriptor || {
                     name: permissionName
                 })
                 .then(function(result) {
                     if (result.state === 'granted') {
                         recording_permission_granted = true;
                     } else if (result.state === 'prompt') {
                         recording_permission_granted = false;
                     } else if (result.state === 'denied') {
                         recording_permission_granted = false;
                     }
                     result.onchange = function() {
                         console.log("permission changed");
                     };
                 });
         } catch (err) {
             console.log(err);
         }
     }
 }

 function stopRecording() {
     if (is_recording) {
         is_recording = false;
         if (recorder !== undefined) {
             recorder && recorder.stop();
             autoStopVideo();
             cstm_log(getString('recording_stopped_log'));
             cstm_log(getString('recording_link_log'));
             // create WAV download link using audio data blob
             createDownloadLink();
             recorder.clear();
             audio_recorded = true;
         }
     } else {
         //shoe error
         sweetAlert(getString('swal_record_first_title'), getString("swal_record_first_body"), "error");
     }
     setStatus(getString('submission_recording_controls')); 
 }

 function initRecorder() {
     if (!recorderLoaded) {
         if (generateMp3) {
             extension = ".mp3";
         } else {
             extension = ".wav";
         }
         //check if secure origin
         if (location.protocol === 'http:') {
             // page is in-secure
             if (!is_page_insecure_msg_shown) {
                 document.body.innerHTML = '\
                <div class="alert alert-danger">\
                <strong>'+getString("no_https_web_title")+'</strong>'
                         +getString("swal_msg_http_body")+
                '</div>' +
                     document.body.innerHTML;
                 is_page_insecure_msg_shown = true;
             }
         }
         try {
             // webkit shim
             window.AudioContext = window.AudioContext || window.webkitAudioContext;
             navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.mediaDevices.getUserMedia;
             window.URL = window.URL || window.webkitURL;

             audio_context = new AudioContext;
             cstm_log('Audio context set up.');
             cstm_log('navigator.getUserMedia() ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
         } catch (e) {
            cstm_log(
                    getString("swal_noudio_support_body")
                    );
            sweetAlert(
                getString("swal_oops_title"),
                getString("swal_noudio_support_body"),
                "error"
            );
         }

         navigator.getUserMedia({
                 audio: true
             },
             startUserMedia,
             function(e) {
                cstm_log(
                    getString("swal_no_live_audio")
                    + e
                );
                sweetAlert(
                    getString("swal_oops_title"),
                    getString("swal_no_live_audio"),
                    "error"
                );
             }
         );
     }
 };

 function startUserMedia(stream) {
     //detecting mics
     var mics = stream.getAudioTracks().length;
     if (mics > 0) {
         console.log("Audio tracks detected: " + mics);
         var input = audio_context.createMediaStreamSource(stream);
         cstm_log(getString("recorder_media_log"));
         // Uncomment if you want the audio to feedback directly
         //input.connect(audio_context.destination);
         //cstm_log('Input connected to audio context destination.');

         //custom recorder settings
         config = {
             sampleRate: 48000, // 48kbps = 48000 sample rate in bits,
             numChannels: 1,
             bufferLen: 1024
         };
         cstm_log(getString('recorder_config_log'));

         recorder = new Recorder(input, config);
         cstm_log(getString("recorder_init_log"));
         recorderLoaded = true;
         recording_permission_granted = true;
     } else {
         sweetAlert(
            getString("swal_oops_title"),
            getString("swal_no_mics_body"),
            "error"
         );
     }
 }