//dynamically load video data and subtitle with previous check

var key = "1234";
var secret = "abcd";
var host = "http://babelium-server-dev.irontec.com/api/v3";

window.onload = function() {
    if(window.jQuery === undefined ){
        var script = document.createElement('script'); 
        document.head.appendChild(script);  
        script.type = 'text/javascript';
        script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
        script.onload = start;
    }
    else{
        start();
    }
};

function start(){
    var subtitleId = 2220;
    loadSubtitles(subtitleId);
}

function loadSubtitles(id){
    var onSuccess = function(response){
        alert("subtitle request done [OK]");
        alert(response);
    };
    var onError = function(response){
        alert("subtitle request done [FAILED]");
        alert(response);
    };
    rpc("GET", "/sub-titles/"+id, onSuccess, onError);
}

function rpc(method, url, onSuccess, onError){
    // Request with custom header
    jQuery.ajax(
        {
            url: host+url,
            headers:
            {
                'access-key': key,
                'secret': secret
            },
            success: function(xhr, ajaxOptions, thrownError){
                if(onSuccess !== undefined){
                    onSuccess(xhr.responseText);
                }
            },
            error: function(xhr, ajaxOptions, thrownError){
                if(onError !== undefined){
                    onError(xhr.responseText);
                }
            }
        }
    );
}
