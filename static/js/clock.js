var formathour = 0;
var formatmin = 0;
var formatsec = 0;
var clockIdentifier;
var videoStartTime;
var videoCurTime = 0;
var videoDuration = 0;
var timer;

function startClockCountingOn(id) {
    debug("clock.js::startClockCountingOn()");
    
    var video = document.getElementById('submission_video');
	if(videoDuration === 0){
		videoDuration = video.duration;
		setTheTime();
	}
	videoStartTime = undefined;
    if(id !== undefined){
        clockIdentifier = id;
        timer = setInterval(function(){
            startClockCounting(video);
        },500);
    }
}

function demo(){
    console.log("timer");
}

function stopClockCountingOn() {
    debug("clock.js::startClockCountingOn()");
    if(timer !== undefined ){
    	console.log('Clear Clock interval');
        clearInterval(timer);
    }
}

function startClockCounting(video){
    debug("clock.js::startClockCounting()");
    if(clockIdentifier !== undefined){
        /*var today = new Date();
        if(videoStartTime===undefined){
            videoStartTime = today;
        }
        today = today - videoStartTime;
        today = new Date(today);
        var h = today.getHours();
        h = h - 1;
        var m = today.getMinutes();
        var s = today.getSeconds();
        formathour = checkTime(h);
        formatmin = checkTime(m);
        formatsec = checkTime(s);*/
    	videoCurTime = video.currentTime;
        setTheTime();
    }
}

function checkTime(i) {
    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
}

function setTheTime() {
	//var curTime = formatmin + ":" + formatsec;   // Current time formatted
    var curTime = formatClockDigits(parseInt(videoCurTime / 60, 10)) + ":" + formatClockDigits(Math.round(videoCurTime % 60)) + ' / ' +  formatClockDigits(parseInt(videoDuration / 60, 10)) + ":" + formatClockDigits(Math.round(videoDuration % 60));
    clockHolder = document.getElementsByClassName(clockIdentifier);
    if(clockHolder && clockHolder[0]){
       clockHolder[0].innerText = curTime;
    }
}

function formatClockDigits(val){
	return ("0" + val).slice(-2);
}

function updateVideoTimer(){
	var video = document.getElementById('submission_video');
	if(video !== null && videoDuration === 0){
		console.log(video.duration);
		videoDuration = video.duration;
		setTheTime();
	}
}