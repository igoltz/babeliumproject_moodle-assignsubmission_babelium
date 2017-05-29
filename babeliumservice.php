<?php

/**
 * Babelium Project open source collaborative second language oral practice - http://www.babeliumproject.com
 *
 * Copyright (c) 2013 GHyM and by respective authors (see below).
 *
 * This file is part of Babelium Project.
 *
 * Babelium Project is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Babelium Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once($CFG->dirroot . '/mod/assign/submission/babelium/Logging.php');

class babeliumservice{

    private $_curlHeaders = array();
    private $_curlHeaderHttpStatusCode;
    private $_curlHeaderHttpStatusMessage;
    private $_curlResponse;
    private $_curlOutput;

    private static $settings;
    private $logFilePath;

    function __construct() {
        Logging::logBabelium("Building babeliumservice...");
        self::$settings = $this->get_babelium_settings();
        global $CFG;
        $this->logFilePath = $CFG->dataroot."/babelium.log";
    }
    
    //@deprecated
    /**
     * Parses the output of cURL. The headers found in this output are stored in the $_curlHeaders class property.
     * The response object, which should be a JSON object, is stored in the $_curlResponse class property.
     * @param string $output
     * 		A string that contains the output of the last cURL execution
     * @throws moodle_exception
     * 		The response did not contain the required JSON object
     */
    private function parseCurlOutput($output){
            $this->_curlResponse = null;
            foreach(preg_split("/(\r?\n)/", $output, 0) as $line){
                    if(!empty($line)){
                            if(preg_match('/(^{.+)/', $line, $matches)){
                                    $this->_curlResponse = $matches[1];
                            } else {
                                    $this->_curlHeaders[] = $line;
                            }
                    }
            }
            $this->parseResponseHeaders();
    }

    //@deprecated
    /**
     * Searches for a HTTP status code in the response headers. If no headers are returned
     * or the status code is different to 200 it throws an error
     * @throws moodle_exception
     * 		The response had no headers or the status code is not 200
     */
    private function parseResponseHeaders(){
            $this->_curlHeaderHttpStatusCode = 500;
            $this->_curlHeaderHttpStatusMessage = 'Internal server error';
            if($this->_curlHeaders && is_array($this->_curlHeaders) && count($this->_curlHeaders) > 0){
                    foreach($this->_curlHeaders as $h){
                            if(preg_match("/^HTTP\/\d.\d (\d+)(.*)/",$h,$matches)){
                                    $this->_curlHeaderHttpStatusCode = trim($matches[1]);
                                    $this->_curlHeaderHttpStatusMessage = trim($matches[2]);
                                    break;
                            }
                    }
            }
    }
    
    //@deprecated
    /**
     * Makes API requests using cURL and a signed header
     * @param String $method
     * 		The API method to which the request is going to be made
     * @param mixed $parameters
     * 		The parameters of the API request can be either an associative array or simple data types
     * @return mixed $result
     * 		An array of data when the API request was successful, false on error.
     * 		Make sure you use the identical operator to check the response (!==FALSE) some response may be 0
     * @throws moodle_exception
     * 		The required configuration parameters of the API are not set
     */
    public function newServiceCall($method,$parameters = null){

            global $USER, $CFG;
            /*$config_fields = array('babelium_babeliumWebDomain',
                                   'babelium_babeliumWebPort',
                                   'babelium_babeliumApiDomain',
                                   'babelium_babeliumApiEndPoint',
                                   'babelium_babeliumApiAccessKey',
                                   'babelium_babeliumApiSecretAccessKey');

            if(!$BCFG || !is_object($BCFG)){
                    $this->display_error('babeliumErrorConfigParameters');
            }
            foreach($config_fields as $cfield){
                    if(!isset($BCFG->$cfield) || empty($BCFG->$cfield)){
                            $this->display_error('babeliumErrorConfigParameters');
                    }
                    if(($cfield == 'babelium_babeliumApiAccessKey' && strlen($BCFG->$cfield)!=20 ) ||
                       ($cfield == 'babelium_babeliumApiSecretAccessKey' && strlen($BCFG->$cfield)!=40)){
                            $this->display_error('babeliumErrorConfigParameters');
                    }
            }
            */
            foreach(self::$settings as $prop=>$value){
                    if(empty($value)){
                            $this->display_error('babeliumErrorConfigParameters');
                    }
                    if(($prop == 'babelium_babeliumApiAccessKey' && strlen($value)!=20) ||
                       ($prop == 'babelium_babeliumApiSecretAccessKey' && strlen($value)!=40)){
                            $this->display_error('babeliumErrorConfigParameters');
                    }
            }

            $commProtocol = 'http://';

            $request = array();
            $request['method'] = $method;
            if($parameters != null && is_array($parameters) && count($parameters) > 0){
                    $request['parameters'] = $parameters;
            }

            //Date timestamp formated following one of the standards allowed for HTTP 1.1 date headers (DATE_RFC1123)
            $date = date("D, d M Y H:i:s O");
            if (getenv("APPLICATION_ENV") == "development"){
                //auth bypass while development
                $fake_host = "babelium-dev.irontec.com";
                $referer = str_replace($_SERVER['HTTP_HOST'], $fake_host, $_SERVER['HTTP_REFERER']);
                $pieces = parse_url($referer);
                $originhost = $fake_host;
                $origin = $pieces['scheme'] . "://" . $originhost;
                $request['header']['date'] = $date;
                $signature = "BMP ".self::$settings->babelium_babeliumApiAccessKey.":".$this->generateAuthorization($method, $date, $originhost, self::$settings->babelium_babeliumApiSecretAccessKey);
                $request['header']['authorization'] = $signature;
            }
            else{
                //default
                $referer = $_SERVER['HTTP_REFERER'];
                $pieces = parse_url($referer);
                $originhost = $_SERVER['HTTP_HOST'];
                $origin = $pieces['scheme'] . "://" . $originhost;

                $request['header']['date'] = $date;
                $signature = "BMP ".self::$settings->babelium_babeliumApiAccessKey.":".$this->generateAuthorization($method, $date, $originhost, self::$settings->babelium_babeliumApiSecretAccessKey);
                $request['header']['authorization'] = $signature;
            }

            //See this workaround if the query parameters are written with &amp; : http://es.php.net/manual/es/function.http-build-query.php#102324
            $request = http_build_query($request,'', '&');

            $query_string = $this->get_query_string($method);

            //Check if proxy (if used) should be bypassed for this url
            $proxybypass = function_exists('is_proxybypass') ? is_proxybypass($query_string) : false;

            //Prepare the cURL request
            if (!$ch = curl_init($query_string)) {
                    debugging('Can not init curl.');
                    return false;
            }

            //curl_setopt($ch, CURLOPT_URL, $query_string);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_REFERER, $referer);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Origin: $origin"));

            //Check for proxy configuration
            if (!empty($CFG->proxyhost) and !$proxybypass) {
                    // SOCKS supported in PHP5 only
                    if (!empty($CFG->proxytype) and ($CFG->proxytype == 'SOCKS5')) {
                            if (defined('CURLPROXY_SOCKS5')) {
                                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                            } else {
                                    curl_close($ch);
                                    debugging("SOCKS5 proxy is not supported in PHP4.", DEBUG_ALL);
                                    return false;
                            }
                    }

                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, false);

                    if (empty($CFG->proxyport)) {
                            curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost);
                    } else {
                            curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
                    }

                    if (!empty($CFG->proxyuser) and !empty($CFG->proxypassword)) {
                            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $CFG->proxyuser.':'.$CFG->proxypassword);
                            if (defined('CURLOPT_PROXYAUTH')) {
                                    // any proxy authentication if PHP 5.1
                                    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC | CURLAUTH_NTLM);
                            }
                    }
            }

            $result = curl_exec($ch);
            curl_close($ch);

            //Parses the response output to separate the headers from the body of the response
            $this->parseCurlOutput($result);

            //Add the service call to the activity log to better track down possible problems
            $this->activity_log($USER->id,$USER->username, "service_call",$query_string, $method, is_array($parameters)?implode('&',$parameters):$parameters, $date, $_SERVER['HTTP_HOST'], $signature, $this->_curlHeaderHttpStatusCode, $this->_curlHeaderHttpStatusMessage);

            //If the body of the response is empty or the HTTP status code is not 200 display an error message
            if(!$this->_curlResponse || $this->_curlHeaderHttpStatusCode != 200)
                    $this->display_error('babeliumApiErrorCode'.$this->_curlHeaderHttpStatusCode);

            $result = json_decode($this->_curlResponse,true);
            if($result['status'] == 'success' && $result['response'])
                    $result = $result['response'];
            else
                    $result = false;
            return $result;
    }

    /**
     * Get all the configuration parameters needed to communicate with the external Babelium server
     * @return stdClass - An array of properties (keys) and their values (values)
     */
    private function get_babelium_settings(){
        if(!isset(self::$settings)){
                Logging::logBabelium("Reading babelium plugin user configuration...");
                $bcfg = new stdClass();
                $bcfg->babelium_babeliumWebDomain = get_config('assignsubmission_babelium','serverdomain');
                //$bcfg->babelium_babeliumWebPort = get_config('assignsubmission_babelium','serverport');//removed
                //$bcfg->babelium_babeliumApiDomain = get_config('assignsubmission_babelium','apidomain');
                //$bcfg->babelium_babeliumApiEndPoint = get_config('assignsubmission_babelium','apiendpoint');
                $bcfg->babelium_new_api_endpoint = get_config('assignsubmission_babelium','newapiendpoint');
                $bcfg->babelium_babeliumApiAccessKey = get_config('assignsubmission_babelium','accesskey');
                $bcfg->babelium_babeliumApiSecretAccessKey = get_config('assignsubmission_babelium','secretaccesskey');
                self::$settings = $bcfg;
        }
        return self::$settings;
    }

    public function make_request($headers, $request_url, $post_params = null){
        //Prepare the cURL request
        $ch = curl_init($request_url);
        if (!$ch) {
                debugging('Can not init curl.');
                return false;
        }

        $referer = "";
        $origin="";
        
       $curl_data = array(
            CURLOPT_URL => $request_url,
            CURLOPT_HTTPHEADER  => $headers,
            CURLOPT_REFERER => $referer,
            //CURLOPT_VERBOSE => 1,
            //CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER  =>true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        );
       
       if(isset($post_params)){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
       }

        curl_setopt_array(
                $ch,
                $curl_data
        );
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $result;
    }

    public function get_query_string($method){
        Logging::logBabelium("Getting query string...");
        $commProtocol = 'http://';
        $web_domain = self::$settings->babelium_babeliumWebDomain;
        $api_domain = $web_domain;
        $api_endpoint = self::$settings->babelium_new_api_endpoint;
        $api_url = $commProtocol . $api_domain . $api_endpoint;
        $query_string = $api_url . '/' . $method;
        Logging::logBabelium("Query string is: ".$query_string);
        return $query_string;
    }

    public function build_headers(){
        Logging::logBabelium("Building request headers...");
        return array(
            'Access-Key:'.self::$settings->babelium_babeliumApiAccessKey,
            'Secret-Access:'.self::$settings->babelium_babeliumApiSecretAccessKey
        );
    }

    public function getExerciseList() {
        $headers = $this->build_headers();
        $request_url = self::$settings->babelium_babeliumWebDomain.self::$settings->babelium_new_api_endpoint."/exercises";
        return $this->makeApiV3Request($request_url, $headers);
    }
    
    public function getExerciseInformation($exerciseId) {
        $headers = $this->build_headers();
        $request_url = self::$settings->babelium_babeliumWebDomain.self::$settings->babelium_new_api_endpoint."/exercises/".$exerciseId;
        return $this->makeApiV3Request($request_url, $headers);
    }
    
    public function getResponseInformation($responseId) {
        $headers = $this->build_headers();
        $request_url = self::$settings->babelium_babeliumWebDomain.self::$settings->babelium_new_api_endpoint."/response/".$responseId;
        return $this->makeApiV3Request($request_url, $headers);
    }
    
    public function getCaptions($subtitleId, $mediaId) {
        $headers = $this->build_headers();
        $request_url = self::$settings->babelium_babeliumWebDomain.self::$settings->babelium_new_api_endpoint."/sub-titles/".$subtitleId;
        return $this->makeApiV3Request($request_url, $headers);
    }
    
    
    public function saveStudentExerciseOnBabelium($params) {
        $headers = $this->build_headers();
        $request_url = self::$settings->babelium_babeliumWebDomain.self::$settings->babelium_new_api_endpoint."/response";
        return $this->makeApiV3Request($request_url, $headers, $params);
    }
    
    public function saveStudentAudioOnBabelium($params) {
        $headers = $this->build_headers();
        $request_url = self::$settings->babelium_babeliumWebDomain.self::$settings->babelium_new_api_endpoint."/response";
        return $this->makeApiV3Request($request_url, $headers, $params);
    }
    
    private function makeApiV3Request($request_url, $headers, $params = null){
        Logging::logBabelium("Requesting ".$request_url);
        //Check if proxy (if used) should be bypassed for this url
        $proxybypass = function_exists('is_proxybypass') ? is_proxybypass($request_url) : false;
        $result = $this->make_request($headers, $request_url, $params);
        //Parses the response output to separate the headers from the body of the response
        //$this->parseCurlOutput($result);
        return $this->decodeJsonResponse($result);
    }

    /**
     * Makes a unique signature for each API request
     * @param String $method
     * 		The API method to which the request is going to be made
     * @param String $date
     * 		The current date of the calling server formatted following the RFC1123 specification
     * @return String $signature
     * 		The authorization header of the request
     */
    private function generateAuthorization($method, $date, $origin, $skey){
            $stringtosign = utf8_encode($method . "\n" . $date . "\n" . $origin);
            $digest = hash_hmac("sha256", $stringtosign, /*$this->setting->babelium_babeliumApiSecretAccessKey*/ $skey, false);
            $signature = base64_encode($digest);
            return $signature;
    }

    /**
     * A small function for displaying the API errors. This is done via a function for compatibility issues between moodle 1.9 and moodle 2.x
     * @param String $errorCode
     * 		An error string that identifies the cause of the problem
     * @throws moodle_exception
     * 		A moodle exception is thrown if we are working with moodle 2.x. Otherways the older error() function is used
     */
    private function display_error($errorCode){
        if(!$errorCode)
            return;
        Logging::logBabelium("Error detected with code ".$errorCode);
        //We are in moodle 2.x
        if(class_exists("moodle_exception")){
                throw new moodle_exception($errorCode,'assignsubmission_babelium');
        } else {
                error(get_string($errorCode,'assignsubmission_babelium'));
        }
    }

    private function activity_log($user_id=0, $user_name='', $action='', $query_string='', $req_method='', $req_params='', $req_hdate='', $req_hdomain='', $req_hsignature='', $resp_http_status=0, $info=''){
            $calling_ip = getremoteaddr(); //getremoteaddr() is a moodle's function
            $time_now = time();
            $message = $time_now ."\t".$calling_ip."\t".$user_id."\t".$user_name."\t".$action."\t".$req_hdate."\t".$req_hdomain."\t".$req_hsignature."\t".$resp_http_status."\t".$query_string."\t".$req_method."\t".$req_params."\t".$info;
            error_log($message."\n",3, $this->logFilePath);
    }

    public function decodeJsonResponse($result) {
        Logging::logBabelium("Decoding JSON answer: ".$result);
        $result = json_decode($result,true);
        if (count($result)>0) {
            return $result;
        }
        else {
            return false;
        }
    }
}
