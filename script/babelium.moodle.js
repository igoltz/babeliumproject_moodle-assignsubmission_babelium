
//Global scope objects
var bpExercises = null;
var exerciseInfo = null;
var exerciseSubs = null;
var responseInfo = null;
var respponseSubs = null;

//Enable debugging messages
var debug = !0;

function logMessage(message){
	//IE9 and prior versions don't work well with console. Make sure it is available
    if(debug && window.console) console.log(message);
}

function init(babeliumDomain, locale, forcertmpt, exInfo, exSubs, rInfo, rSubs){

	if(exInfo && exSubs){
		exerciseInfo = exInfo;
		exerciseSubs = exSubs;
	}
	if(rInfo && rSubs){
		responseInfo = rInfo;
		responseSubs = rSubs;
	}

	//Load flash object
	flashLoader(babeliumDomain,locale,forcertmpt,"");
}

function flashLoader(babeliumDomain, locale, forcertmpt, jsCallbackObj){
	// For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection.
	var swfVersionStr = "11.1.0";

  var protocol = window.location.protocol;

	// To use express install, set to playerProductInstall.swf, otherwise the empty string.
	var xiSwfUrlStr = protocol+"//"+babeliumDomain+"/playerProductInstall.swf";
	var flashvars = {};
	flashvars.locale = locale; //Overrides the default locale that's established via system info, to the specified locale
	flashvars.forcertmpt = forcertmpt;
	flashvars.jsCallbackObj = jsCallbackObj; //Where to point the ExternalInterface.call() methods to avoid global scope methods
	var params = {};
	params.quality = "high";
	params.bgcolor = "#000000"; //Use black background
	params.allowscriptaccess = "always"; //The swf file is stored in a different domain
	params.allowfullscreen = "false";
	params.wmode = "window";
	var attributes = {};
	attributes.id = "babeliumPlayer";
	attributes.name = "babeliumPlayer";
	attributes.align = "middle";
	swfobject.embedSWF(protocol+"//"+babeliumDomain+"/babeliumPlayer.swf", "flashContent", "640", "380", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
	// JavaScript enabled so display the flashContent div in case it is not replaced with a swf object.
	swfobject.createCSS("#flashContent", "display:block;text-align:left;");
};

//This function is top-level until finding a way to route ExternalInterface.call() to object encapsulation
function onVideoPlayerInitialized(playerid){
	var bpPlayer = document.getElementById(playerid);
	if (!bpPlayer) {
		logMessage("There was a problem while loading the video player.");
		return;
	}
	logMessage("Player was successfully initialized.");
	bpExercises = new exercise();
	if(exerciseInfo && exerciseSubs){
		bpExercises.preloadPlayback(bpPlayer, exerciseInfo, exerciseSubs);
	}
	if(responseInfo && responseSubs){
		bpExercises.preloadParallel(bpPlayer, responseInfo, responseSubs);
	}
}

function exercise(){

  var instance = this;

  //Reference to player object
  this.bpPlayer = null;

  this.currentExercise=null;
  this.currentMediaData=null;
  this.primaryMediaData=null;
  this.modelMediaData=null;
  this.recordMediaData=null;
  this.currentCaptions=null;
  this.currentTimeMarkers=null;

  //this.exerciseTitle='';
  //this.exerciseSelected=!1;
  this.characterNames=null;
  this.roles=null;
  this.mediaId=0;
  this.subtitleId=0;

  this.exerciseStartedPlaying=!1;
  this.rolesReady=!1;
  this.selectedRole='';
  this.videoPlayerReady=!1;
  this.recordingAttempts=null;
  this.isRecording=!1;

  this.preloaded=!1;

  this.initialize = function(playerObj){
    if(!playerObj){
      logMessage("Can't initialize the module without a valid player object");
    }
    this.bpPlayer = playerObj;
    this.setupVideoPlayer();
    this.setRecordingButtonGroupVisibility();
  }

  this.setupVideoPlayer = function(){
    this.bpPlayer.addEventListener('onVideoPlayerError','bpExercises.onVideoPlayerError');
    this.bpPlayer.addEventListener('onVideoPlayerReady', 'bpExercises.onVideoPlayerReady');
    this.bpPlayer.addEventListener('onRecordingEnd', 'bpExercises.onRecordingEnd');
    this.bpPlayer.addEventListener('onUserDeviceAccessDenied', 'bpExercises.onUserDeviceAccessDenied');
  }

  this.onVideoPlayerReady = function(event){
    this.videoPlayerReady=!0;

    //Set 'Start recording' button's state
    $('#id_startStopRecordingBtn').prop("disabled",(!this.videoPlayerReady||!this.rolesReady));
  }

  this.onExerciseRetrieved = function(exerciseData){
    if(exerciseData){
      logMessage("Exercise data retrieved");
      this.currentExercise = exerciseData;

      /* These fields are for setting the visual components
      this.exerciseTitle = currentExercise.title;
      exerciseTitleInput.text=this.exerciseTitle;
      usernameLbl.text=this.currentExercise.userName;
      difficultyLbl.text=ExerciseRenderUtils.getLevelLabel(this.currentExercise.difficulty);
      timecreatedLbl.text=ExerciseRenderUtils.formatTimestamp(this.currentExercise.timecreated, 'medium', 'none');
      langImg.src=ExerciseRenderUtils.getFlagSource(this.currentExercise);
      exerciseDetailedData.exerciseData = this.currentExercise;
      */

      this.primaryMediaData=null;
      this.modelMediaData=null;
      if(this.currentExercise.hasOwnProperty('media')){
        if(this.currentExercise.media instanceof Array){
          var media=this.currentExercise.media;
          for(var i=0; i<media.length; i++){
            var level=parseInt(media[i].level);
            if(level==1 && !this.primaryMediaData){
              this.primaryMediaData=media[i];
              continue;
            }
            if(level==2 && !this.modelMediaData){
              this.modelMediaData=media[i];
              continue;
            }
          }
        } else if (this.currentExercise.media instanceof Object){
          this.primaryMediaData=this.currentExercise.media;
        }
      }

      this.loadSelectedMedia(primaryMediaData);
    }
  }

  this.onSubmissionRetrieved = function(submissionData){
    if(submissionData){
      this.selectedRole=submissionData.character_name;
      this.subtitleId=submissionData.fk_subtitle_id;
      if(submissionData.hasOwnProperty('leftMedia')){
        this.leftMedia=submissionData.leftMedia;
      }
      if(submissionData.hasOwnProperty('rightMedia')){
        this.rightMedia=submissionData.rightMedia;
      }
      this.fetchSubtitlesById(this.subtitleId);
    }
  }

  this.onCaptionsRetrieved = function(captionData){
    if(captionData){
      this.currentCaptions=captionData;
      if(!this.subtitleId){
        var ccollection=this.currentCaptions;
        var item=ccollection && ccollection.length ? ccollection[0] : null;
        this.subtitleId = item.subtitleId;
      }
      this.bpPlayer.setCaptions(this.currentCaptions);
    }
  }

  this.onRolesRetrieved = function(roleData){
    this.roles = null;
    this.characterNames = [];
    $('#id_roleCombo option:selected').hide();

    if(roleData){
      this.roles = roleData;
      var code=0;
      for(var role in this.roles){
        if(role != "NPC"){
          code++;
          this.characterNames.push({"code": code, "label": role});
        }
      }
    }

    var numVoices = this.characterNames.length;
    if(!numVoices){
      //Remove all children <option> from <select> control
      $('#id_startStopRecordingBtn').empty();
      this.rolesReady=!1;
    } else {
      var elem =$('#id_startStopRecordingBtn');
      $.each(this.characterNames, function(item){
        elem.append($("<option />").val(item.code).text(item.label));
      });
      this.rolesReady=!0;
      if(numVoices > 1){
        $('#id_startStopRecordingBtn').show();
      }
    }

    //Set 'Start recording' button's state
    $('#id_startStopRecordingBtn').prop("disabled",(!this.videoPlayerReady||!this.rolesReady));
  }

  this.loadSelectedMedia = function(media){
    this.recordingAttempts=null;
    this.setRecordingButtonGroupVisibility();

    this.currentMediaData=media;
    var param=this.currentMediaData;
    this.bpPlayer.loadVideoByUrl(param);

    this.rolesReady=!1;

    //Set 'Start recording' button's state
    $('#id_startStopRecordingBtn').prop("disabled",(!this.videoPlayerReady||!this.rolesReady));

    //this.exerciseSelected=true;

    var args={};
    args.mediaid=this.currentMediaData.id;

    if(!preloaded){
      //Dispatch an AJAX call to retrieve the associated captions
      //subtitleEvent get exercise subtitle lines, args)
    }
  }

  this.checkRoleSelected = function(){
    //Get the selected role name
    this.selectedRole = $('#id_roleCombo option:selected').text();
    if(this.selectedRole){
      var mform = document.forms['mform1'];
      mform.elements["recordedRole"].value = this.selectedRole;

      this.isRecording=!0;
      this.setRecordingButtonGroupVisibility();
    }
  }

  this.requestRecordingSlot = function(){
    //new exercise event request recording slot
  }

  this.prepareRecording = function(){
    this.statisticRecAttempt();
    var media={};
    media.playbackMedia=this.currentMediaData;
    media.recordMedia=this.recordMediaData;

    var useWebcam=$("input[name=recmethod]:checked").val() == 1;

    this.currentTimeMarkers=this.roles[this.selectedRole];

    this.bpPlayer.recordVideo(media, useWebcam, this.currentTimeMarkers);
  }

  this.recordMediaSlotHandler = function(){
    this.recordMediaData=null;
    if(data.recordMediaData){
      var tmp=data.recordMediaData;
      this.recordMediaData={};
      this.recordMediaData.netConnectionUrl=tmp.netConnectionUrl || null;
      this.recordMediaData.mediaUrl=tmp.mediaUrl || null;
      this.recordMediaData.maxDuration=tmp.duration || 0;

      this.prepareRecording();
    }
  }

  this.onRecordingEnd = function(event){
    this.isRecording=!1;

    if(!this.recordingAttempts){
      this.recordingAttempts=[];
    }
    this.recordingAttempts.push(this.recordMediaData);

    this.setupRecordingButtonGroupVisibility();

    var parallelmedia={};
    parallelmedia.leftMedia=this.currentMediaData;
    parallelmedia.rightMedia=this.recordMediaData;

    this.bpPlayer.disableAutoPlay();
    this.bpPlayer.loadVideoByUrl(parallelmedia, this.currentTimeMarkers);
  }

  this.onUserDeviceAccessDenied = function(event){
    this.restorePlaybackView();

    this.bpPlayer.loadVideoByUrl(this.currentMediaData);
  }

  this.onVideoPlayerError = function(event){
    this.restorePlaybackView();
  }

  this.restorePlaybackView = function(){
    this.isRecording=!1;
    this.videoPlayerReady=!1;

    this.setupRecordingButtonGroupVisibility();

    //Set 'Start recording' button's state
    $('#id_startStopRecordingBtn').prop("disabled",(!this.videoPlayerReady||!this.rolesReady));
  }

  this.setRecordingButtonGroupVisibility=function(){
    if(this.isRecording){
  		$('#id_viewRecordingBtn').hide();
  		$('#id_viewExerciseBtn').hide();
    } else {
      var ra=this.recordingAttempts ? 1 : !1;
      if(ra){
        $('#id_viewRecordingBtn').show();
  		  $('#id_viewExerciseBtn').show();
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
    collection.forEach(function (cv) {
        if (!s.hasOwnProperty(cv.exerciseRoleName)) {
        	s[cv.exerciseRoleName]=[];
        }
		s[cv.exerciseRoleName].push(cv);
    });
    return s;
  }

  //The preload functions rig the player without making additional service calls
  this.preloadPlayback=function(playerObj,exerciseData,captions){
    logMessage("Initializing playback mode with prefetched data.");
    this.preloaded=true;
    this.initialize(playerObj);
    this.onExerciseRetrieved(exerciseData);
    this.onCaptionsRetrieved(captions);
    this.onRolesRetrieved(this.separateByRole(captions));
    this.preloaded=false;
  }

  this.preloadParallel=function(playerObj,submissionData,captions){
    this.preloaded=true;
    this.initialize(playerObj);
  }

  this.onStartStopRecordingClicked = function(){
    if(this.isRecording){
      this.restorePlaybackView();
      this.bpPLayer.loadVideoByUrl(this.currentMediaData);
    } else {
      this.checkRoleSelected();
    }
  }

  this.onWatchResponse = function(event){
    var parallelmedia = {};

    //The right-side media is the last successfully recorded response
    var lastSuccessfulResponse=this.recordingAttempts[this.recordingAttempts.length-1];

    parallelmedia.leftMedia=this.currentMediaData;
    parallelmedia.rightMedia=lastSuccessfulResponse;

    this.bpPlayer.enableAutoPlay();
    this.bpPlayer.loadVideoByUrl(this.currentMediaData);
  }

  this.onWatchExercise = function(event){
    this.bpPlayer.enableAutoPlay();
    this.loadVideoByUrl(this.currentMediaData);
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

	$(document).ready(function() {
		$('#id_startStopRecordingBtn').click(function(){ instance.startStopRecordingClickHandler(); });
		$('#id_viewRecordingBtn').click(function() { instance.viewRecordingClickHandler(); });
		$('#id_viewExerciseBtn').click(function() { instance.viewExerciseClickHandler(); });
	});
}
