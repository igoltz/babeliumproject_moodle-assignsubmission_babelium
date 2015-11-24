
//Global scope objects
var bpExercises = null;
var exerciseInfo = null;
var exerciseSubs = null;
var responseInfo = null;
var respponseSubs = null;

//Enable debugging messages
var debug = !1;

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
	bpExercises = new exercise2();
	if(exerciseInfo && exerciseSubs){
		bpExercises.preloadPlayback(bpPlayer, exerciseInfo, exerciseSubs);
	}
	if(responseInfo && responseSubs){
		bpExercises.preloadParallel(bpPlayer, responseInfo, responseSubs);
	}
}

function exercise2(){

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
    if(mediaData){
      this.currentExercise = mediaData;

      /* These fields are for setting the visual components
      this.exerciseTitle = currentExercise.title;
      exerciseTitleInput.text=this.exerciseTitle;
      usernameLbl.text=this.currentExercise.userName;
      difficultyLbl.text=ExerciseRenderUtils.getLevelLabel(this.currentExercise.difficulty);
      timecreatedLbl.text=ExerciseRenderUtils.formatTimestamp(this.currentExercise.timecreated, 'medium', 'none');
      langImg.src=ExerciseRenderUtils.getFlagSource(this.currentExercise);
      exerciseDetailedData.exerciseData = this.currentExercise;
      */

      this.primaryMediaData=this.modelMediaData=null;
      if(this.currentExercise.hasOwnProperty('media')){
        if(this.currentExercise instanceof Array){
          var media=this.currentExercise.media;
          for(var item in media){
            var level=parseInt(item.level);
            if(level==1 && !this.primaryMediaData){
              this.primaryMediaData=item;
              continue;
            }
            if(level==2 && !this.modelMediaData){
              this.modelMediaData=item;
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

  this.onCaptionsRetrieved = function(captionData){
    if(captionData){
      this.currentCaptions=captionData;
      var ccollection=this.currentCaptions;
      var item=ccollection && ccollection.length ? ccollection[0] : null;

      this.subtitleId = item.subtitleId;
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


}

function exercise() {

	// http://stackoverflow.com/questions/4818615/using-getjson-with-callback-within-a-javascript-object
	var instance = this;

	this.bpPlayer = null;
	this.cueManager = null;

	this.bpPlayerStates = {
		PLAY_STATE : 0,
		PLAY_BOTH_STATE : 1,
		RECORD_MIC_STATE : 2,
		RECORD_BOTH_STATE : 3
	};

	this.currentMedia = null;

	this.cueManagerReady = false;

	this.locales = [];
	this.roles = []; //Maps a role name with its role ID

	this.selectedRole = null;
	this.selectedLocale = null;
	this.recordedFilename = null;

	this.loadExerciseStatic = function(videoPlayer, mediaInfo, subtitleInfo){
		this.bpPlayer = videoPlayer;
		// bpPlayer.addEventListener('onVideoStartedPlaying','videoStartedPlayingListener');
		this.bpPlayer.addEventListener('onRecordingAborted','bpExercises.recordingAbortedListener');
		this.bpPlayer.addEventListener('onRecordingFinished','bpExercises.recordingFinishedListener');

		//onExerciseSelected part
		this.exerciseName = mediaInfo.exerciseName;
		this.exerciseTitle = mediaInfo.exerciseTitle;
		this.exerciseId = mediaInfo.exerciseId;
		this.recordedFilename = mediaInfo.responseName;
		this.selectedRole = mediaInfo.responseRole;
		this.currentMedia = mediaInfo;

		this.cueManagerReady = false;
		this.cueManager = new cuePointManager();
		for(var i in exerciseSubs) {
			this.cueManager.addCueFromSubtitleLine(subtitleInfo[i]);
		}

		this.bpPlayer.stopVideo();
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_STATE);
		if(this.recordedFilename && this.recordedFilename.length > 0){
			this.bpPlayer.responseSource(this.recordedFilename + '_merge');
		}else{
			this.bpPlayer.exerciseSource(this.exerciseName);
		}

		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.setupPlayCommands();
	};

	this.loadResponseStatic = function(videoPlayer, mediaInfo, subtitleInfo){
		this.bpPlayer = videoPlayer;
		// bpPlayer.addEventListener('onVideoStartedPlaying','videoStartedPlayingListener');
		this.bpPlayer.addEventListener('onRecordingAborted','bpExercises.recordingAbortedListener');
		this.bpPlayer.addEventListener('onRecordingFinished','bpExercises.recordingFinishedListener');

		//onExerciseSelected part
		this.exerciseName = mediaInfo.exerciseName;
		this.exerciseTitle = mediaInfo.exerciseTitle;
		this.exerciseId = mediaInfo.exerciseId;
		this.recordedFilename = mediaInfo.responseName;
		this.selectedRole = mediaInfo.responseRole;
		this.currentMedia = mediaInfo;

		this.cueManagerReady = false;
		this.cueManager = new cuePointManager();
		for(var i in subtitleInfo) {
			this.cueManager.addCueFromSubtitleLine(subtitleInfo[i]);
		}
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.setupRecordingCommands();
		this.bpPlayer.exerciseSource(this.exerciseName);
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_BOTH_STATE);
		this.bpPlayer.secondSource(this.recordedFilename);
		this.bpPlayer.seek(false);
		//Delay the call to show arrows until the metadata of the video is acquired
		this.bpPlayer.addEventListener('onMetadataRetrieved', 'bpExercises.onMetadataRetrieved');
		//this.showArrows();
	}

	this.loadExercise = function(videoPlayer, ex) {
		this.bpPlayer = videoPlayer;
		this.cueManager = new cuePointManager();
		this.setupVideoPlayer();
		this.onExerciseSelected(ex);
	};

	this.loadResponse = function(videoPlayer, resp){
		this.bpPlayer = videoPlayer;
		this.cueManager = new cuePointManager();
		this.onResponseSelected(resp);
	};

	this.setupVideoPlayer = function() {
		// bpPlayer.addEventListener('onVideoPlayerReady','videoPlayerReadyListener');
		// bpPlayer.addEventListener('onVideoStartedPlaying','videoStartedPlayingListener');
		this.bpPlayer.addEventListener('onRecordingAborted','bpExercises.recordingAbortedListener');
		this.bpPlayer.addEventListener('onRecordingFinished','bpExercises.recordingFinishedListener');
	};

	this.onExerciseSelected = function(exercise) {
		// Store selected exercise's information
		this.exerciseName = exercise.name;
		this.exerciseTitle = exercise.title;
		this.exerciseId = exercise.id;
		this.currentExercise = exercise;
		this.cueManagerReady = false;

		this.prepareExercise();
		this.resetCueManager();
	};

	this.onResponseSelected = function(response) {
		this.currentResponse = response;
		logMessage(this.currentResponse);
		this.cueManagerReady = false;
		this.resetCueManager();
		this.prepareResponse();
	};

	this.prepareExercise = function() {
		this.bpPlayer.stopVideo();
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_STATE);
		this.bpPlayer.exerciseSource(this.exerciseName);
	};

	this.prepareResponse = function() {

		this.prepareCueManagerEvaluation();
	};



	this.resetCueManager = function() {
		this.cueManager.reset();
		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
	};

	this.prepareCueManager = function() {
		//this.cueManager.setVideo(this.exerciseId);
		//this.selectedLocale = $('#localeCombo option:selected').text();
		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
	};

	this.prepareCueManagerEvaluation = function(){

		this.bpPlayer.removeEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
	};


	/**
	 * ActionScript callback
	 * It is called each time the flash object refreshes its display list (around 20 times per second on average computers with standard cpu usage)
         */
	this.enterFrameListener = function(event) {
		this.cueManager.monitorCuePoints(event);
	};

	/**
	 * Callback from another scope, use the 'instance' variable to access local properties/methods
	 */
	this.onSubtitlesRetrieved = function() {
		if(instance.currentResponse == undefined)
			instance.setupPlayCommands();
		else{
			//instance.bpPlayer.setState(instance.bpPlayerStates.PLAY_BOTH_STATE);
			instance.bpPlayer.setState(instance.bpPlayerStates.PLAY_STATE);
			instance.bpPlayer.responseSource(instance.currentResponse.file_identifier + '_merge');
			//instance.bpPlayer.secondSource(instance.currentResponse.file_identifier);
			//instance.selectedRole = instance.currentResponse.character_name;
			instance.setupPlayCommands();
			//instance.setupRecordingCommands();
			//instance.showArrows();
			instance.bpPlayer.addEventListener('onMetadataRetrieved', 'bpExercises.onMetadataRetrieved');
		}
	};

	this.onMetadataRetrieved = function(event) {
		if(this.bpPlayer.getState() == this.bpPlayerStates.PLAY_BOTH_STATE)
			this.showArrows();
	};

	this.setupPlayCommands = function() {
		var auxList = this.cueManager.getCuelist();
		if (auxList.length <= 0)
			return;

		for ( var i in auxList) {
			auxList[i].setStartCommand(new onPlaybackCuePoint(auxList[i],this.bpPlayer));
			auxList[i].setEndCommand(new onPlaybackCuePoint(null, this.bpPlayer));
		}
		this.cueManagerReady = true;

		//TODO statistical feature. To be implemented yet
		//this.videoStartedPlayingListener(null);
	};

	this.setupReplayCommands = function() {
		var auxList = this.cueManager.getCuelist();

		if (auxList.length <= 0)
			return;

		for ( var i in auxList) {
			auxList[i].setStartCommand(new onReplayRecordingCuePoint(auxList[i], this.bpPlayer));
			auxList[i].setEndCommand(new onReplayRecordingCuePoint(null, this.bpPlayer));
		}

		this.cueManagerReady = true;
	};

	this.setupRecordingCommands = function() {
		var auxList = this.cueManager.getCuelist();

		if (auxList.length <= 0)
			return;

		for ( var i in auxList) {

			if (auxList[i].role != this.selectedRole) {
				auxList[i].setStartCommand(new onRecordingOtherRoleCuePoint(auxList[i], this.bpPlayer));
				auxList[i].setEndCommand(new onPlaybackCuePoint(null, this.bpPlayer));
			} else {
				auxList[i].setStartCommand(new onRecordingSelectedRoleStartCuePoint(auxList[i], this.bpPlayer));
				auxList[i].setEndCommand(new onRecordingSelectedRoleStopCuePoint(this.bpPlayer));
			}
		}
		this.bpPlayer.seek(false);
		this.cueManagerReady = true;
	};

	/**
	 * ActionScript callback function
	 * The recording process ended successfully. Save the filename of this recording and switch to "Review Recording" state
	 */
	this.recordingFinishedListener = function(recFilename) {
		// Store last recorded response's filename
		this.recordedFilename = recFilename;


		var mform = document.forms['mform1'];
		//mform.elements["responseid"].value = responseId;
		mform.elements["responsehash"].value = recFilename;

		logMessage("Response recording ended");


		// Set the videoplayer to playback both the exercise and the
		// last response.
		this.setupRecordingCommands();
		this.bpPlayer.exerciseSource(this.exerciseName);
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_BOTH_STATE);
		this.bpPlayer.secondSource(this.recordedFilename);

		this.bpPlayer.seek(false);
		this.bpPlayer.stopVideo();

		$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStartRecording);
		//$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewExercise);
		//$('#id_viewRecordingExerciseBtn').show();
		$('#id_viewRecordingBtn').show();
		$('#id_viewExerciseBtn').show();
	};

	/**
	 * ActionScript callback function
	 * The recording process failed for some reason. Display an error and return to the "View Exercise" state
	 */
	this.recordingAbortedListener = function() {
		alert("Devices not working");
		this.recordingError();
		this.prepareExercise();

		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.setupPlayCommands();

		$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStartRecording);
		if(this.recordedFilename){
			//$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewRecording);
			//$('#id_viewRecordingExerciseBtn').show();
			$('#id_viewRecordingBtn').show();
			$('#id_viewExerciseBtn').show();
		}
	};

	this.recordingError = function() {
		this.hideArrows();
		this.bpPlayer.unattachUserDevices();
		this.bpPlayer.setState(this.bpPlayerStates.PLAY_STATE);

		this.bpPlayer.removeEventListener('onEnterFrame','bpExercises.onEnterFrameListener');
	};

	this.showArrows = function() {
		logMessage("showArrows was called")
		this.bpPlayer.arrows(true);
		this.bpPlayer.setArrows(this.cueManager.cues2rolearray(), this.selectedRole);
	};

	this.hideArrows = function() {
		this.bpPlayer.arrows(false);
		this.bpPlayer.removeArrows();
	};

	this.startStopRecordingClickHandler = function(){

		//We are currently in the middle of a recording process, pressing the button should abort the recording
		if(this.bpPlayer.getState() == this.bpPlayerStates.RECORD_MIC_STATE || this.bpPlayer.getState() == this.bpPlayerStates.RECORD_BOTH_STATE){
			this.recordingError();
			this.prepareExercise();
			this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
			this.setupPlayCommands();

			$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStartRecording);
			if(this.recordedFilename){
				//$('#id_viewRecordingExerciseBtn').val(M.str.assignsubmission_babelium.babeliumViewRecording);
				//$('#id_viewRecordingExerciseBtn').show();
				$('#id_viewRecordingBtn').show();
				$('#id_viewExerciseBtn').show();
			}
		} else {
			//Determine which role the user is going to impersonate
			this.selectedRole = $('#id_roleCombo option:selected').text();
			var mform = document.forms['mform1'];
			mform.elements["recordedRole"].value = this.selectedRole;

			//This is just in case a previous state changed the primary video of the videoplayer
			//instance.bpPlayer.exerciseSource(instance.exerciseName);

			//Prepare the cuepoints for the recording
			this.setupRecordingCommands();

			//Determine which devices the video player should attach
			if ($("input[name=recmethod]:checked").val() == 0) {
				this.bpPlayer.setState(instance.bpPlayerStates.RECORD_MIC_STATE);
			} else {
				this.bpPlayer.setState(instance.bpPlayerStates.RECORD_BOTH_STATE);
			}

			//Show the arrows for the selected role
			this.showArrows();

			$('#id_startStopRecordingBtn').val(M.str.assignsubmission_babelium.babeliumStopRecording);
			//$('#id_viewRecordingExerciseBtn').hide();
			$('#id_viewRecordingBtn').hide();
			$('#id_viewExerciseBtn').hide();

			//TODO this is dynamic data saving, we should refactor the services before using it
			//Save statistical data
			//instance.statisticRecAttempt();
		}
	};

	this.viewRecordingClickHandler = function(){
		this.setupRecordingCommands();
		this.bpPlayer.exerciseSource(instance.exerciseName);
		this.bpPlayer.setState(instance.bpPlayerStates.PLAY_BOTH_STATE);
		this.bpPlayer.secondSource(instance.recordedFilename);
		this.bpPlayer.seek(false);
		this.showArrows();
	}

	this.viewExerciseClickHandler = function(){
		this.recordingError();
		this.prepareExercise();
		this.bpPlayer.addEventListener('onEnterFrame', 'bpExercises.enterFrameListener');
		this.setupPlayCommands();
	}

	/*
	//This shouldn't be necessary because YUI JS should be linked at the end of DOM's body, but just in case
	Y.on("domready", function() {
		Y.one('#recordingEndOptions').hide();
		Y.one('#localecombo').on('change', instance.subtitleLanguageChangeHandler());
		Y.one('#startRecordingBtn').on('click', instance.startRecordingClickHandler());
		Y.one('#watchExerciseAndResponseBtn').on('click', instance.watchResponseClickHandler());
		Y.one('#recordAgainBtn').on('click', instance.recordAgainClickHandler());
		//Y.one('#abortRecordingBtn').on('click', instance.abortRecordingClickHandler());
		//Y.one('#saveResponseBtn').on('click', instance.saveResponseClickHandler());
	});
	*/

	$(document).ready(function() {
		$('#id_startStopRecordingBtn').click(function(){ instance.startStopRecordingClickHandler(); });
		$('#id_viewRecordingBtn').click(function() { instance.viewRecordingClickHandler(); });
		$('#id_viewExerciseBtn').click(function() { instance.viewExerciseClickHandler(); });
	});
}
}
