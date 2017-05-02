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

class babeliumservice{

	private $_curlHeaders = array();
	private $_curlHeaderHttpStatusCode;
	private $_curlHeaderHttpStatusMessage;
	private $_curlResponse;
	private $_curlOutput;
        
        private $settings;
        
        function __construct() {
            $this->settings = $this->get_babelium_settings();
        }

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

	/**
	 * Get all the configuration parameters needed to communicate with the external Babelium server
	 * @return stdClass - An array of properties (keys) and their values (values)
	 */
	private function get_babelium_settings(){
		$bcfg = new stdClass();
		$bcfg->babelium_babeliumWebDomain = get_config('assignsubmission_babelium','serverdomain');
		$bcfg->babelium_babeliumWebPort = get_config('assignsubmission_babelium','serverport');
		$bcfg->babelium_babeliumApiDomain = get_config('assignsubmission_babelium','apidomain');
		$bcfg->babelium_babeliumApiEndPoint = get_config('assignsubmission_babelium','apiendpoint');
		$bcfg->babelium_babeliumApiAccessKey = get_config('assignsubmission_babelium','accesskey');
		$bcfg->babelium_babeliumApiSecretAccessKey = get_config('assignsubmission_babelium','secretaccesskey');
		return $bcfg;
	}


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

		global $USER;
		/*$config_fields = array('babelium_babeliumWebDomain',
				       'babelium_babeliumWebPort',
				       'babelium_babeliumApiDomain',
				       'babelium_babeliumApiEndPoint',
				       'babelium_babeliumApiAccessKey',
				       'babelium_babeliumApiSecretAccessKey');
		
		if(!$this->setting || !is_object($this->setting)){
			$this->display_error('babeliumErrorConfigParameters');
		}
		foreach($config_fields as $cfield){
			if(!isset($this->setting->$cfield) || empty($this->setting->$cfield)){
				$this->display_error('babeliumErrorConfigParameters');
			}
			if(($cfield == 'babelium_babeliumApiAccessKey' && strlen($this->setting->$cfield)!=20 ) || 
			   ($cfield == 'babelium_babeliumApiSecretAccessKey' && strlen($this->setting->$cfield)!=40)){
				$this->display_error('babeliumErrorConfigParameters');
			}
		}
		*/
		$request = $this->build_request($method, $parameters);
		$query_string = $this->get_query_string($method);

		//Check if proxy (if used) should be bypassed for this url
		$proxybypass = function_exists('is_proxybypass') ? is_proxybypass($query_string) : false;
		
		$result = $this->make_request($request, $query_string, null);
		/*
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
		}*/
		
		//Parses the response output to separate the headers from the body of the response
		$this->parseCurlOutput($result);
		
		//Add the service call to the activity log to better track down possible problems
		$this->activity_log($USER->id,$USER->username, "service_call",$query_string, $method, is_array($parameters)?implode('&',$parameters):$parameters, $date, $_SERVER['HTTP_HOST'], $signature, $this->_curlHeaderHttpStatusCode, $this->_curlHeaderHttpStatusMessage);
		
		//If the body of the response is empty or the HTTP status code is not 200 display an error message
		if(!$this->_curlResponse || $this->_curlHeaderHttpStatusCode != 200)
			$this->display_error('babeliumApiErrorCode'.$this->_curlHeaderHttpStatusCode);
		
		return $this->decodeJsonResponse();
	}
        
        public function make_request($request, $query, $type){
            //Prepare the cURL request
                $ch = curl_init($query);
		if (!$ch) {
			debugging('Can not init curl.');
			return false;
		}
		
		//curl_setopt($ch, CURLOPT_URL, $query_string);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                if($type!=null){
                    curl_setopt($ch, CURLOPT_POST, 1);
                }		
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if(isset($referer)){
                    curl_setopt($ch, CURLOPT_REFERER, $referer);
                }
                if(isset($origin)){
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Origin: $origin"));
                }
                $result = curl_exec($ch);
		curl_close($ch);
                
                return $result;
        }
        
        public function get_query_string($method){
            $commProtocol = 'http://';
            $web_domain = $this->settings->babelium_babeliumWebDomain;
            $api_domain = $this->settings->babelium_babeliumApiDomain;
            $api_endpoint = $this->settings->belium_babeliumApiEndPoint;
            $api_url = $commProtocol . $api_domain . '/' . $api_endpoint;
            $query_string = $api_url . '?' . $method;
            return $query_string;
        }


        public function build_request($method, $parameters){
            foreach($this->settings as $prop=>$value){
                if(empty($value)){
                        $this->display_error('babeliumErrorConfigParameters');
                }
                if(($prop == 'babelium_babeliumApiAccessKey' && strlen($value)!=20) ||
                   ($prop == 'babelium_babeliumApiSecretAccessKey' && strlen($value)!=40)){
                        $this->display_error('babeliumErrorConfigParameters');
                }
            }
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
                $signature = "BMP ".$this->settings->babelium_babeliumApiAccessKey.":".$this->generateAuthorization($method, $date, $originhost, $this->settings->babelium_babeliumApiSecretAccessKey);
                $request['header']['Access-Key'] = $signature;
                $request['header']['authorization'] = "nVaWxwn2i1a03OJdSNQs";
                $request['header']['Secret-Access'] = "_QH(`M!]p.SXJv^NVksTEjECWxMQvM}U,ixv0.zx";
            }
            else{
                //default
                $referer = $_SERVER['HTTP_REFERER'];
                $pieces = parse_url($referer);
                $originhost = $_SERVER['HTTP_HOST']; 
                $origin = $pieces['scheme'] . "://" . $originhost;

                $request['header']['date'] = $date;
                $signature = "BMP ".$this->settings->babelium_babeliumApiAccessKey.":".$this->generateAuthorization($method, $date, $originhost, $this->settings->babelium_babeliumApiSecretAccessKey);
                $request['header']['authorization'] = $signature;
            }

            //See this workaround if the query parameters are written with &amp; : http://es.php.net/manual/es/function.http-build-query.php#102324
            $request = http_build_query($request,'', '&');
            return $request;
        }
        
        public function getExerciseList() {
            $settings = $this->get_babelium_settings();
            $request = $this->build_request($settings, $method, $parameters);
            $query_string = "https://babelium-server-dev.irontec.com/api/v3/exercises";
            //Check if proxy (if used) should be bypassed for this url
            $proxybypass = function_exists('is_proxybypass') ? is_proxybypass($query_string) : false;
            $result = $this->make_request($request, $query_string, null);
            //Parses the response output to separate the headers from the body of the response
            $this->parseCurlOutput($result);
            return $this->decodeJsonResponse();
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
		
		//We are in moodle 2.x
		if(class_exists("moodle_exception")){
			throw new moodle_exception($errorCode,'assignsubmission_babelium');
		} else {
			error(get_string($errorCode,'assignsubmission_babelium'));
		}
	}
	
	private function activity_log($user_id=0, $user_name='', $action='', $query_string='', $req_method='', $req_params='', $req_hdate='', $req_hdomain='', $req_hsignature='', $resp_http_status=0, $info=''){
		global $CFG;
		
		$log_file_path = $CFG->dataroot."/babelium.log";
		
		$calling_ip = getremoteaddr(); //getremoteaddr() is a moodle's function
		$time_now = time();
		
		$message = $time_now ."\t".$calling_ip."\t".$user_id."\t".$user_name."\t".$action."\t".$req_hdate."\t".$req_hdomain."\t".$req_hsignature."\t".$resp_http_status."\t".$query_string."\t".$req_method."\t".$req_params."\t".$info;
		
		
		error_log($message."\n",3,$log_file_path);
	}

    public function decodeJsonResponse() {
        $result = json_decode($this->_curlResponse,true);
        if ($result['status'] == 'success' && $result['response']) {
            $result = $result['response'];
        }
        else {
            $result = false;
        }
        return $result;
    }

}
