//Global scope objects
var bpExercises = null;
var exerciseInfo = null;
var exerciseSubs = null;
var recordInfo = null;
var responseInfo = null;
var respponseSubs = null;

var $bjq = jQuery.noConflict();

//Enable debugging messages
var debug = !1;

function logMessage(message) {
    //IE9 and prior versions don't work well with console. Make sure it is available
    if (debug && window.console) console.log(message);
}

function init(babeliumDomain, locale, forcertmpt, exInfo, exSubs, rInfo, rSubs, recInfo) {

    if (exInfo && exSubs) {
        exerciseInfo = exInfo;
        exerciseSubs = exSubs;
        recordInfo = recInfo;
    }
    if (rInfo && rSubs) {
        responseInfo = rInfo;
        responseSubs = rSubs;
        recordInfo = recInfo;
    }
}

//This function is top-level until finding a way to route ExternalInterface.call() to object encapsulation
function onVideoPlayerInitialized(playerid) {
    var bpPlayer = document.getElementById(playerid);
    if (!bpPlayer) {
        logMessage("There was a problem while loading the video player.");
        return;
    }
    logMessage("Player was successfully initialized.");
    bpExercises = new exercise();
    if (exerciseInfo && exerciseSubs) {
        bpExercises.preloadAddEdit(bpPlayer, exerciseInfo, exerciseSubs, recordInfo);
    }
    if (responseInfo && responseSubs) {
        bpExercises.preloadView(bpPlayer, responseInfo, responseSubs);
    }
}

function exercise() {

    var instance = this;

    //Reference to player object
    this.bpPlayer = null;

    //These fields hold the data of the preloaded calls
    this.preloaded = !1;
    this.pData = null;
    this.pRecordData = null;
    this.pCaptions = null;

    this.currentExercise = null;
    this.currentMediaData = null;
    this.primaryMediaData = null;
    this.recordMediaData = null;
    this.currentCaptions = null;
    this.currentTimeMarkers = null;

    //this.exerciseTitle='';
    //this.exerciseSelected=!1;
    this.characterNames = null;
    this.roles = null;
    this.mediaId = 0;
    this.subtitleId = 0;

    this.exerciseStartedPlaying = !1;
    this.rolesReady = !1;
    this.selectedRole = '';
    this.videoPlayerReady = !1;
    this.recordingAttempts = null;
    this.isRecording = !1;

    // Set the player element in the closure and init the scope
    this.initialize = function(playerObj) {
        if (!playerObj) {
            logMessage("Can't initialize the module without a valid player object");
        }
        this.bpPlayer = playerObj;
        this.setupVideoPlayer();
        this.setRecordingButtonGroupVisibility();
    }

    this.setupStartStopRecordButton = function(value) {
        var elem = $bjq('#id_startStopRecordingBtn');
        var label = value ? M.str.assignsubmission_babelium.babeliumStopRecording : M.str.assignsubmission_babelium.babeliumStartRecording;
        elem.val(label);
    }

    this.setupVideoPlayer = function() {
        this.bpPlayer.addEventListener('onVideoPlayerError', 'bpExercises.onVideoPlayerError');
        this.bpPlayer.addEventListener('onVideoPlayerReady', 'bpExercises.onVideoPlayerReady');
        this.bpPlayer.addEventListener('onRecordingEnd', 'bpExercises.onRecordingEnd');
        this.bpPlayer.addEventListener('onUserDeviceAccessDenied', 'bpExercises.onUserDeviceAccessDenied');
    }

    this.onVideoPlayerReady = function(event) {
        this.videoPlayerReady = !0;

        //Set 'Start recording' button's state
        $bjq('#id_startStopRecordingBtn').prop("disabled", (!this.videoPlayerReady || !this.rolesReady));
    }

    this.onExerciseRetrieved = function(exerciseData) {
        if (exerciseData) {
            logMessage("Exercise data retrieved");
            this.currentExercise = exerciseData;

            this.primaryMediaData = null;
            if (this.currentExercise.hasOwnProperty('media')) {
                this.primaryMediaData = this.currentExercise.media;
                this.loadSelectedMedia(this.primaryMediaData);
            }
            if (this.currentExercise.hasOwnProperty('leftMedia')) {
                this.primaryMediaData = this.currentExercise.leftMedia;
                this.currentMediaData = this.primaryMediaData;
                this.recordMediaData = this.currentExercise.rightMedia;
                this.selectedRole = this.currentExercise.selectedRole;
                this.onCaptionsRetrieved(this.pCaptions);
                this.onRolesRetrieved(this.separateByRole(this.pCaptions));

                this.recordingAttempts = [];
                this.recordingAttempts.push(this.recordMediaData);
                this.recordMediaData.recordedRole = exerciseData.selectedRole;
                this.onWatchResponse(null);
            }
        }
    }

    this.onSubmissionRetrieved = function(submissionData, captions) {
        if (submissionData) {
            this.selectedRole = submissionData.selectedRole;
            this.subtitleId = submissionData.subtitleId;
            if (submissionData.hasOwnProperty('leftMedia')) {
                this.currentMediaData = submissionData.leftMedia;
            }
            if (submissionData.hasOwnProperty('rightMedia')) {
                this.recordMediaData = submissionData.rightMedia;
            }
            this.currentCaptions = captions;
            this.roles = this.separateByRole(captions);
            this.currentTimeMarkers = this.roles[this.selectedRole];

            this.bpPlayer.setCaptions(this.currentCaptions);

            var parallelmedia = {};
            parallelmedia.leftMedia = this.currentMediaData;
            parallelmedia.rightMedia = this.recordMediaData;

            this.bpPlayer.loadVideoByUrl(parallelmedia, this.currentTimeMarkers);
        }
    }

    this.onCaptionsRetrieved = function(captionData) {
        if (captionData) {
            this.currentCaptions = captionData;
            if (!this.subtitleId) {
                var ccollection = this.currentCaptions;
                var item = ccollection && ccollection.length ? ccollection[0] : null;
                this.subtitleId = item.subtitleId;
            }
            logMessage("Captions: " + this.currentCaptions);
            this.bpPlayer.setCaptions(this.currentCaptions);
        }
    }

    this.onRolesRetrieved = function(roleData) {
        this.roles = null;
        this.characterNames = [];

        //Grab the <select> element of the roles
        var elem = $bjq('#id_roleCombo');
        //Start out hidden
        elem.hide();

        if (roleData) {
            this.roles = roleData;
            var code = 0;
            logMessage(this.roles.constructor);
            for (var role in this.roles) {
                if (role != "NPC") {
                    code++;
                    this.characterNames.push({
                        "code": code,
                        "label": role
                    });
                }
            }
        }
        logMessage("Voices: " + this.characterNames.length);
        var numVoices = this.characterNames.length;

        //Remove all children <option> from the <select> control
        elem.find('option').remove().end();

        if (!numVoices) {
            this.rolesReady = !1;
        } else {
            $bjq.each(this.characterNames, function(index, value) {
                elem.append($bjq("<option />").val(value.code).text(value.label));
            });
            elem.val($bjq("#id_roleCombo option:first").val());
            this.rolesReady = !0;
            if (numVoices > 1) {
                elem.show();
            }
        }

        //Set 'Start recording' button's state
        $bjq('#id_startStopRecordingBtn').prop("disabled", (!this.videoPlayerReady || !this.rolesReady));

        if (this.preload) {
            this.preload = !1; //preloading steps are over
        }
    }

    this.loadSelectedMedia = function(media) {
        this.recordingAttempts = null;
        this.setRecordingButtonGroupVisibility();

        this.currentMediaData = media;
        var param = this.currentMediaData;
        this.bpPlayer.loadVideoByUrl(param);

        this.rolesReady = !1;

        //Set 'Start recording' button's state
        $bjq('#id_startStopRecordingBtn').prop("disabled", (!this.videoPlayerReady || !this.rolesReady));

        //this.exerciseSelected=true;

        if (!this.preloaded) {
            var args = {};
            args.mediaid = this.currentMediaData.id;
            //Dispatch an AJAX call to retrieve the associated captions
            //subtitleEvent get exercise subtitle lines, args)
        } else {
            this.onCaptionsRetrieved(this.pCaptions);
            this.onRolesRetrieved(this.separateByRole(this.pCaptions));
        }
    }

    this.checkRoleSelected = function() {
        //Get the selected role name
        this.selectedRole = $bjq('#id_roleCombo option:selected').text();
        if (this.selectedRole) {
            this.isRecording = !0;
            this.setupStartStopRecordButton(this.isRecording);
            this.setRecordingButtonGroupVisibility();
        }
    }

    this.requestRecordingSlot = function() {
        if (this.preloaded && this.pRecordData) {
            logMessage(this.pRecordData);
            var iurl = this.pRecordData.mediaUrl;
            logMessage("Incoming record url: " + iurl);
            var mediadir = iurl.substring(0, iurl.lastIndexOf('/') + 1);
            logMessage("Reported media directory: " + mediadir);

            var prefix = 'resp-';
            var timestamp = new Date().getTime();

            var attemptHash = prefix + timestamp + '.flv';
            var mediaUrl = mediadir + attemptHash;

            this.pRecordData.mediaUrl = mediaUrl;

            this.recordMediaSlotHandler(this.pRecordData);
        } else {
            //new exercise event request recording slot
        }
    }

    this.prepareRecording = function() {
        //Disabled for now
        //this.statisticRecAttempt();
        var media = {};
        media.playbackMedia = this.currentMediaData;
        media.recordMedia = this.recordMediaData;

        var useWebcam = $bjq("input[name=recmethod]:checked").val() == 1;

        this.currentTimeMarkers = this.roles[this.selectedRole];

        this.bpPlayer.recordVideo(media, useWebcam, this.currentTimeMarkers);
    }

    this.recordMediaSlotHandler = function(recordMediaData) {
        if (this.preloaded) {
            this.preloaded = false;
        }
        this.recordMediaData = null;
        if (recordMediaData) {
            var tmp = recordMediaData;
            this.recordMediaData = {};
            this.recordMediaData.netConnectionUrl = tmp.netConnectionUrl || null;
            this.recordMediaData.mediaUrl = tmp.mediaUrl || null;
            this.recordMediaData.maxDuration = tmp.duration || 0;

            this.prepareRecording();
        }
    }

    this.onRecordingEnd = function(event) {
        logMessage("Recording ended");
        this.isRecording = !1;
        this.setupStartStopRecordButton(this.isRecording);

        //Recording ended successfully, set the recorded role
        var mform = document.forms['mform1'];
        mform.elements["recordedRole"].value = this.selectedRole;
        mform.elements["responsehash"].value = this.recordMediaData.mediaUrl;

        this.recordMediaData.recordedRole = this.selectedRole;

        if (!this.recordingAttempts) {
            this.recordingAttempts = [];
        }
        this.recordingAttempts.push(this.recordMediaData);

        this.setRecordingButtonGroupVisibility();

        var parallelmedia = {};
        parallelmedia.leftMedia = this.currentMediaData;
        parallelmedia.rightMedia = this.recordMediaData;

        this.bpPlayer.disableAutoPlay();
        this.bpPlayer.loadVideoByUrl(parallelmedia, this.currentTimeMarkers);
    }

    this.onUserDeviceAccessDenied = function(event) {
        this.restorePlaybackView();

        this.bpPlayer.loadVideoByUrl(this.currentMediaData);
    }

    this.onVideoPlayerError = function(event) {
        this.restorePlaybackView();
    }

    this.restorePlaybackView = function() {
        this.isRecording = !1;
        this.setupStartStopRecordButton(this.isRecording);
        this.videoPlayerReady = !1;

        this.setRecordingButtonGroupVisibility();

        //Set 'Start recording' button's state
        $bjq('#id_startStopRecordingBtn').prop("disabled", (!this.videoPlayerReady || !this.rolesReady));
    }

    this.setRecordingButtonGroupVisibility = function() {
        if (this.isRecording) {
            $bjq('#id_viewRecordingBtn').hide();
            $bjq('#id_viewExerciseBtn').hide();
        } else {
            var ra = this.recordingAttempts ? 1 : !1;
            if (ra) {
                $bjq('#id_viewRecordingBtn').show();
                $bjq('#id_viewExerciseBtn').show();
            }
        }
    }

    // Helper function to separate captions by role tags
    this.separateByRole = function(collection) {
        'use strict';
        if (!collection) {
            return null;
        }
        var s = {};
        collection.forEach(function(cv) {
            if (!s.hasOwnProperty(cv.exerciseRoleName)) {
                s[cv.exerciseRoleName] = [];
            }
            s[cv.exerciseRoleName].push(cv);
        });
        return s;
    }

    //The preload functions rig the player without making additional service calls
    this.preloadAddEdit = function(playerObj, exerciseData, captions, recordData) {
        logMessage("Initializing interactive mode with prefetched data.");

        this.pData = this.pCaptions = this.pRecordData = null;

        this.preloaded = true;
        this.initialize(playerObj);

        this.pData = exerciseData;
        this.pCaptions = captions;
        if (recordData) {
            this.pRecordData = recordData;
        }
        this.onExerciseRetrieved(this.pData);
    }

    this.preloadView = function(playerObj, submissionData, captions) {
        logMessage("Initializing review mode with prefetched data.");

        this.pData = this.pCaptions = null;

        //In place on this.initialize()
        this.bpPlayer = playerObj;

        this.onSubmissionRetrieved(submissionData, captions);
    }

    this.onStartStopRecordingClicked = function() {
        if (this.isRecording) {
            this.restorePlaybackView();
            this.bpPlayer.loadVideoByUrl(this.currentMediaData);
        } else {
            this.preloaded = true;
            this.checkRoleSelected();
            this.requestRecordingSlot();
        }
    }

    this.onWatchResponse = function(event) {
        var parallelmedia = {};

        //The right-side media is the last successfully recorded response
        var lastSuccessfulResponse = this.recordingAttempts[this.recordingAttempts.length - 1];
        this.currentTimeMarkers = this.roles[lastSuccessfulResponse.recordedRole];

        parallelmedia.leftMedia = this.currentMediaData;
        parallelmedia.rightMedia = lastSuccessfulResponse;

        this.bpPlayer.enableAutoPlay();
        this.bpPlayer.loadVideoByUrl(parallelmedia, this.currentTimeMarkers);
    }

    this.onWatchExercise = function(event) {
        this.bpPlayer.enableAutoPlay();
        this.bpPlayer.loadVideoByUrl(this.currentMediaData);
    }

    /*
    this.saveResponseClickHandler=function(event){
      var responseData={};
      repsonseData.exerciseId=this.currentExercise.id;
      responseData.mediaId=this.currentMediaData.id;
      responseData.characterName=this.selectedRole;
      responseData.recordMedia=this.recordingAttempts[this.recordingAttempts.length-1];

      //new response event responseData
    }

    this.responseSuccessfullySavedRetrieved = function(value){
      if(this.initialized){
        if(data.savedResponseId){
          this.bpPlayer.resetComponent();
          this.bpPlayer.enableAutoPlay();

          if(this.modelMediaData && (this.modelMediaData != this.currentMediaData)){
            //prompt user if she wants to view the model media
          } else {
            //redirect to browse section
          }
        }
      }
    }

    this.statisticRecAttempt = function(){
      var params={};
      params.responseAttempt=true;
      params.exerciseId=this.currentExercise.id;
      params.subtitlesAreUsed=this.bpPlayer.areCaptionsDisplayed();
      params.subtitleId=this.subtitleId;
      params.exerciseRoleName=this.selectedRole;

      //Dispatch event UserVideoHistoryEvent.stat_attempt_response, params
    }

    this.statisticRecSave = function(value){
      if(data.savedResponseId){
        var params={};
        params.responseAttempt=false;
        params.responseId=data.savedResponseId;
        params.exerciseId=this.currentExercise.id;
        params.subtitlesAreUsed=this.bpPlayer.areCaptionsDisplayed();
        params.subtitleId=this.subtitleId;
        params.exerciseRoleName=this.selectedRole;

        //Dispatch event UserVideoHistory.stat_save_response, params
      }
    }
    */

    $bjq(document).ready(function() {
        $bjq('#id_startStopRecordingBtn').click(function() {
            instance.onStartStopRecordingClicked();
        });
        $bjq('#id_viewRecordingBtn').click(function() {
            instance.onWatchResponse();
        });
        $bjq('#id_viewExerciseBtn').click(function() {
            instance.onWatchExercise();
        });
    });
}