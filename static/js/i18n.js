/* 
 * language file
 */
var default_lang = "es";

var en = {
    "swal_msg_http_title":"Recording disabled",
    "swal_msg_http_body": "For security reasons, audio recording is disabled on non HTTPS websites",
    "recording_log" : "Recording...",
    "recording_stopped_log" : "Stopped recording.",
    "recording_link_log" : "Creating audio link...",
    "swal_msg_allow_micro_title" : "Allow microphone",
    "swal_msg_allow_micro_body" : "Please, allow microphone access before recording.",
    "swal_msg_cancel_btn" : "Cancel",
    "swal_msg_allow_btn" : "Allow",
    "swal_record_first_title" : "Babelium recorder",
    "swal_record_first_body" : "You have to start a record first."
};

var es = {
    "swal_msg_http_title":"Grabación deshabilitada",
    "swal_msg_http_body": "Por razones de seguridad, no se permite grabar en sitios sin HTTPS",
    "recording_log" : "Grabando...",
    "recording_stopped_log" : "Grabación finalizada",
    "recording_link_log" : "Creando link al fichero de audio...",
    "swal_msg_allow_micro_title" : "Permitir micrófono",
    "swal_msg_allow_micro_body" : "Por favor, permita acceso al microfono antes de grabar.",
    "swal_msg_cancel_btn" : "Cancelar",
    "swal_msg_allow_btn" : "Permitir",
    "swal_record_first_title" : "Grabador Babelium",
    "swal_record_first_body" : "Tienes que realizar una grabación."
};

var de = {
    "swal_msg_http_title":"Recording disabled",
    "swal_msg_http_body": "For security reasons, audio recording is disabled on non HTTPS websites",
    "recording_log" : "Recording...",
    "recording_stopped_log" : "Stopped recording.",
    "recording_link_log" : "Creating audio link...",
    "swal_msg_allow_micro_title" : "Allow microphone",
    "swal_msg_allow_micro_body" : "Please, allow microphone access before recording.",
    "swal_msg_cancel_btn" : "Cancel",
    "swal_msg_allow_btn" : "Allow",
    "swal_record_first_title" : "Babelium recorder",
    "swal_record_first_body" : "You have to start a record first."
    
};

var eu = {
    "swal_msg_http_title":"Grabazioa debekatuta",
    "swal_msg_http_body": "Zure segurtazunagatik, soinu grabazioak debekatuta daude ez HTTPS web orrietan",
    "recording_log" : "Grabatzen...",
    "recording_stopped_log" : "Grabazioa amaituta",
    "recording_link_log" : "Audio fitxeroaren link-a egiten...",
    "swal_msg_allow_micro_title" : "Mikrofonoa onartu",
    "swal_msg_allow_micro_body" : "Mesedez, baimena eman mikrofonoa erabiltzeko grabatu baino lehen.",
    "swal_msg_cancel_btn" : "Ezeztatu",
    "swal_msg_allow_btn" : "Baimena eman",
    "swal_record_first_title" : "Babelium grabatzailea",
    "swal_record_first_body" : "Grabazioa egin behar duzu."
};

var languages = {
    "en":en, //English
    "es":es, //Spanish
    "de":de, //Deutsch
    "eu":eu, //Basque
};

function getString(key){
    if(lang===null || lang===undefined){
        lang = default_lang; //Default language
    }
    
    lang = "es"; //for debug
    
    var languageData = languages[lang];
    if(key!==null && key!==undefined){
        var stringData = languageData[key];
        if(stringData){
            return stringData;
        }
    }
    return "";
}