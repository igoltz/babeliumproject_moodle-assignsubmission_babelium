var formathour = 0;
var formatmin = 0;
var formatsec = 0;

function myclock() {
    var date = new Date(); // Get the current date/time
    var hour = 0; // Save hours
    var min = 0; // Save minutes
    var sec = 0; // Save seconds
    formathour = 0; // Format hours
    formatmin = 0; // Format minutes
    formatsec = 0; // Format seconds
    timeout = setTimeout(myclock, 1000);
}

function format(x) {
    if (x < 10) x = "0" + x;
    return x;
}

var c;

$("#startRecord").click(function() {
    var started = is_recording;
    if (!started) {
        c = setInterval(setTheTime, 1000);
        started = true; //redundant
    }
    else {
        started = false; //redundant
        clearInterval(c);
     }
});

function setTheTime() {
    myclock();
    var curTime = formathour + ":" + formatmin + ":" + formatsec // Current time formatted
    $("#clock").prop('value', curTime);
}