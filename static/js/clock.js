var formathour = 0;
var formatmin = 0;
var formatsec = 0;
var clockIdentifier;
var videoStartTime;
var timer;

function startClockCountingOn(id) {
    debug("clock.js::startClockCountingOn()");
    videoStartTime = undefined;
    if(id !== undefined){
        clockIdentifier = id;
        timer = setInterval(function(){
            startClockCounting();
        },500);
    }
}

function demo(){
    console.log("timer");
}

function stopClockCountingOn() {
    debug("clock.js::startClockCountingOn()");
    if(timer !== undefined ){
        clearInterval(timer);
    }
}

function startClockCounting(){
    debug("clock.js::startClockCounting()");
    if(clockIdentifier !== undefined){
        var today = new Date();
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
        formatsec = checkTime(s);
        setTheTime();
    }
}

function checkTime(i) {
    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
}

function setTheTime() {
    var curTime = formathour + ":" + formatmin + ":" + formatsec // Current time formatted
    clockHolder = document.getElementsByClassName(clockIdentifier);
    if(clockHolder && clockHolder[0]){
        clockHolder[0].innerText = curTime;
    }
}