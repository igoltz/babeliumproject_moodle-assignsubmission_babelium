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
require_once($CFG->dirroot . '/mod/assign/submission/babelium/BabeliumConnector.php');

/**
 * BabeliumHelper class that contains BabeliumPlugin Business Logic
 *
 * @package   assignsubmission_babelium
 * @copyright Original from 2012 Babelium Project {@link http://babeliumproject.com} modified by Elurnet Informatika Zerbitzuak S.L  {@link http://elurnet.net/es} and Irontec S.L {@link https://www.irontec.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class BabeliumHelper
{
    const DEVELOPMENT_ENVIRONMENT = 'development';

    private static $environment;
    private static $config;
    private static $rootPath;

    const PRACTICE_MODE = 0;
    const REVIEW_MODE = 1;

    const MIN_EXERCISE_ID = 0;
    const DEFAULT_EXERCISE_ID = 0;
    const MIN_EXERCISE_NUMBER = 0;

    const DEFAULT_SUBMISSION_ID = 0;

    const ASSIGNSUBMISSION_BABELIUM = 'assignsubmission_babelium';

    //keys
    const KEY_EXERCISE_ID = 'exerciseid';
    const KEY_ASSIGNMENT = 'assignment';
    const KEY_CONFIG_BABELIUM_NAME = 'babelium';

    const JSON_ID = 'id';
    const JSON_TITLE = 'title';

    const EMPTY_STRING = '';
    const SUBMISSION_TYPE = "babelium";
    const REQUEST_TYPE = "submission";
    const MIN_MOODLE_VERSION = 2011112900;

    /**
    * Available babelium exercise list
    **/
    private $exercises = array();

    /**
    * Key-pair list for holding <select> content
    **/
    private $exercisesMenu  = array();
    private $classattribute = array('class' => 'error');

    private $exerciseid;
    private $data;
    private $exercise_data;
    private $submission;

    function __construct() {
        global $CFG;
        self::$config = $CFG;
        self::$environment = getenv("APPLICATION_ENV");
        self::$rootPath = $CFG->wwwroot;
    }

    public function getDefaultExerciseId($plugin){
        $id = $plugin->get_config(self::KEY_EXERCISE_ID);
        return $id > self::MIN_EXERCISE_ID ? $id : self::DEFAULT_EXERCISE_ID;
    }

    public function areValidExercises(){
        return isset($this->exercises) && count($this->exercises) > self::MIN_EXERCISE_NUMBER;
    }

    public function getAvailableExercises(){
            $this->exercises = babeliumsubmission_get_available_exercise_list();
            return $this->exercises;
    }

    public function createExerciseList(){
            foreach ($this->exercises as $exercise) {
                $hasValidData = isset($exercise) && isset($exercise[self::JSON_ID]) && $exercise[self::JSON_TITLE];
                if($hasValidData){
                    $this->exercisesMenu[$exercise[self::JSON_ID]] = $exercise[self::JSON_TITLE];
                }
            }
    }

    public function isValidExercise($data){
            if(isset($data)){
                return isset($data->assignsubmission_babelium_exerciseid);
            }
            return false;
    }

    public function saveThisExercise($plugin, $data){
        $hasValidData = isset($data) && isset($data->assignsubmission_babelium_exerciseid);
            if($hasValidData){
                $plugin->set_config(
                        self::KEY_EXERCISE_ID,
                        $data->assignsubmission_babelium_exerciseid
                );
            }
    }

    public function saveSubmissionConfiguration($plugin, $data){
        $hasValidData = isset($data) && isset($data->assignsubmission_babelium_exerciseid);
        if($hasValidData){
            $plugin->set_config(self::KEY_EXERCISE_ID, $data->assignsubmission_babelium_exerciseid);
        }
        return true;
    }

    public function getSubmissionId($submission){
            $this->submission = isset($submission) ? $submission->id : self::DEFAULT_SUBMISSION_ID;
            return $this->submission;
    }

    public function isExerciseIdAvailable($plugin){
            $this->exerciseid = $plugin->get_config(self::KEY_EXERCISE_ID);
            return $this->exerciseid <= self::MIN_EXERCISE_ID;
    }

    public function checkReceivedData($data){
        if(isset($data)){
            $this->data = $data;
            if (!isset($this->data->responseid)) {
                $this->data->responseid = self::EMPTY_STRING;
            }

            if (!isset($this->data->responsehash)) {
                $this->data->responsehash = self::EMPTY_STRING;
            }
        }
        return $this->data;
    }

    public function populateWithBabeliumData($plugin, $submission){
        Logging::logBabelium("Populating with babelium data");
        if(isset($submission)){
            $babeliumsubmission = $plugin->get_babelium_submission($submission->id);
            if ($babeliumsubmission) {
                $this->data->responseid   = $babeliumsubmission->responseid;
                $this->data->responsehash = $babeliumsubmission->responsehash;
            }
        }
        return $this->data;
    }

    public function getExerciseData($data){
        Logging::logBabelium("Getting exercise data...");
        //original code
        //$exercise_data = !empty($data->responsehash) ? babeliumsubmission_get_exercise_data(0, $data->responseid) : babeliumsubmission_get_exercise_data($exerciseid);
        if( !empty($data->responsehash) ){
            $defaultExerciseId = 0;
            $this->exercise_data = babeliumsubmission_get_exercise_data($defaultExerciseId, $data->responseid);
        }
        else{
            $this->exercise_data = babeliumsubmission_get_exercise_data($this->exerciseid);
        }
        return $this->exercise_data;
    }

    public function hasExerciseData(){
            return isset($this->exercise_data);
    }

    public function getFormData($plugin, $mform){
        Logging::logBabelium("Getting moodle form data...");
        $plugin->get_babelium_form_elements($mform,
            array(
                $this->data,
                $this->exercise_data['info'],
                $this->exercise_data['roles'],
                $this->exercise_data['languages'],
                $this->exercise_data['subtitles'],
                $this->exercise_data['recinfo']
            )
        );
    }

    public function copySubmission($plugin, $source, $dest){
        // Copy the assignsubmission_babelium record.
        global $DB;
        $hasValidData = isset($source) && isset($dest);
        if($hasValidData){
            $babeliumsubmission = $plugin->get_babelium_submission($source->id);
            if ($babeliumsubmission) {
                unset($babeliumsubmission->id);
                $babeliumsubmission->submission = $dest->id;
                $DB->insert_record(self::ASSIGNSUBMISSION_BABELIUM, $babeliumsubmission);
            }
        }
    }

    /**
    * Return true if no response was submitted
    * @param stdClass $submission
    */
    public function isEmptySubmission($plugin, $submission){
        Logging::logBabelium("Checking for EMPTY submission...");
        $hasValidData = isset($submission);
        if($hasValidData){
            $babeliumsubmission = $plugin->get_babelium_submission($submission->id);
            $empty = empty($babeliumsubmission->responsehash);
            Logging::logBabelium("Submission was empty? : ".$empty);
            return $empty;
        }
        Logging::logBabelium("Submission was empty");
        return true;
    }

    public function deleteInstance($plugin){
        global $DB;
        Logging::logBabelium("Deleting instance...");
        // will throw exception on failure
        $DB->delete_records(
            self::ASSIGNSUBMISSION_BABELIUM,
            array(
                self::KEY_ASSIGNMENT => $plugin->assignment->get_instance()->id
            )
        );
        Logging::logBabelium("Instance deleted DONE!");
    }

    public function get_plugin_name() {
        Logging::logBabelium("Getting plugin name...");
        $name = get_string(self::KEY_CONFIG_BABELIUM_NAME, self::ASSIGNSUBMISSION_BABELIUM);
        Logging::logBabelium("Plugin name is: ".$name);
        return $name;
    }

    public function getBabeliumSubmission($submissionid) {
        global $DB;
        Logging::logBabelium("Getting babelium sumbission with ID ".$submissionid);
        $hasValidData = isset($submissionid);
        if($hasValidData){
            return $DB->get_record(
                    self::ASSIGNSUBMISSION_BABELIUM,
                    array(
                        self::REQUEST_TYPE => $submissionid
                    )
            );
        }
    }

    public function check_file_code($submissioncode) {
        global $OUTPUT;

        if (!$submissioncode || empty($submissioncode)) {
            $errormsg = get_string('responsehashnotset', self::ASSIGNSUBMISSION_BABELIUM);
            return $OUTPUT->error_text($errormsg);
        }
        return null;
    }

    public function copy_submission($sourcesubmission, $destsubmission){
        global $DB;
        $hasValidData = isset($sourcesubmission) && isset($destsubmission);
        if($hasValidData){
            // Copy the assignsubmission_babelium record.
            $babeliumsubmission = $this->get_babelium_submission($sourcesubmission->id);
            if ($babeliumsubmission) {
                unset($babeliumsubmission->id);
                $babeliumsubmission->submission = $destsubmission->id;
                $DB->insert_record(self::ASSIGNSUBMISSION_BABELIUM, $babeliumsubmission);
            }
            return true;
        }
        return false;
    }

    public function format_for_log($submission) {
        // format the info for each submission plugin add_to_log
        $babeliumsubmission = $this->getBabeliumSubmission($submission->id);
        $babeliumloginfo    = '';
        $babeliumloginfo .= get_string('loginfo', self::ASSIGNSUBMISSION_BABELIUM, array(
            'responseid' => $babeliumsubmission->responseid,
            'responsehash' => $babeliumsubmission->responsehash
        ));

        return $babeliumloginfo;
    }

    public function upgradeSubmission($plugin, $oldcontext, $oldassignment, $oldsubmission, $submission, $log) {
        global $DB;

        $babeliumsubmission               = new stdClass();
        $babeliumsubmission->responseid   = $oldsubmission->data1;
        $babeliumsubmission->responsehash = $oldsubmission->data2;

        $babeliumsubmission->submission = $submission->id;
        $babeliumsubmission->assignment = $plugin->assignment->get_instance()->id;

        if ($babeliumsubmission->responseid === null) {
            $babeliumsubmission->responseid = 0;
        }

        if ($babeliumsubmission->responsehash === null) {
            $onlinetextsubmission->responsehash = '';
        }

        if (!$DB->insert_record(self::ASSIGNSUBMISSION_BABELIUM, $babeliumsubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'mod_assign', $submission->userid);
            return false;
        }
        return true;
    }

    public function upgradeSettings($oldcontext, $oldassignment, $log) {
        if ($oldassignment->assignmenttype == self::SUBMISSION_TYPE) {
            $this->set_config('exerciseid', $oldassignment->var1);
            return true;
        }
    }

    /**
    * Returns html code for displaying the babelium widget with the provided information
    *
    * @param int $mode
    *	States whether we should return the widget in play ($mode = 0) or review ($mode = 1) status
    * @param array $info
    *	Information about the exercise/response the widget is going to display
    * @param array $subs
    *	The subtitles & roles this exercise/response is going to use
    * @return String $html_content
    *	An html snippet that loads the babelium player and its related scripts
    */
   function babeliumsubmission_html_exercise_done_view_output($info, $subs, $rmedia){
        Logging::logBabelium("Rendering HTML5 data with babelium information...");
       global $SESSION, $CFG, $BCFG;

       $content_path = $this->getSumbissionViewHTMLPath();

       $exinfo = '""';
       $exsubs = '""';
       $rsinfo = '""';
       $rssubs = '""';
       $recinfo = '""';

        $rsinfo = json_encode($info);
        $rssubs = json_encode($subs);

        if($rmedia){
            $recinfo = json_encode($rmedia);
        }

       $html_content = '';
       $html_content = $this->generateStatusAlert($html_content, $info);
       if(isset($info['title'])){
               $html_content.='<h2 id="babelium-exercise-title" class="centered">'.$info['title'].'</h2>';
       }
       $html_content.= file_get_contents($content_path, FILE_USE_INCLUDE_PATH);
        //load jquery just in case
       $html_content.='<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>'.PHP_EOL;

       $domain = get_config(self::ASSIGNSUBMISSION_BABELIUM,'serverdomain');
       $lang = current_language();

        $html_content.='<script
              src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/js/babelium.view.js"
              language="javascript">
          </script>'.PHP_EOL;

       $html_content .= '<script language="javascript" type="text/javascript">
                               var domain = "'.$domain.'";
                               var lang = "'.$lang.'";
                               var exerciseinfo = '.$exinfo.';
                               var exercisesubs = '.$exsubs.';
                               var rsinfo = '.$rsinfo.';
                               var responsesubs = '.$rssubs.';
                               var recinfo = '.$recinfo.';
                               var exinfo = exerciseinfo || rsinfo;
                               var exsubs = exercisesubs || responsesubs;
                               window.onload = function() {
                                debug("built-in::onload()");
                                if(window.jQuery === undefined || $ === undefined){
                                    var script = document.createElement("script");
                                    document.head.appendChild(script);
                                    script.type = "text/javascript";
                                    script.src = "//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js";
                                    script.onload = initView;
                                }
                                else{
                                    initView();
                                }
                            };
                         </script>'.PHP_EOL;

       $html_content.='<script
                     src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/dist/sweetalert/sweetalert.min.js"
                     language="javascript">
                 </script>'.PHP_EOL;
       
        $html_content.='<script
                     src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/js/video.loader.js"
                     language="javascript">
                 </script>'.PHP_EOL;

       Logging::logBabelium("Injecting ". strlen($html_content)." data bytes into babelium submission");
       return $html_content;
   }

   /**
    * Returns html code for displaying the babelium widget with the provided information
    *
    * @param int $mode
    *	States whether we should return the widget in play ($mode = 0) or review ($mode = 1) status
    * @param array $info
    *	Information about the exercise/response the widget is going to display
    * @param array $subs
    *	The subtitles & roles this exercise/response is going to use
    * @return String $html_content
    *	An html snippet that loads the babelium player and its related scripts
    */
   function babeliumsubmission_html_exercise_todo_view_output($mode, $info, $subs, $rmedia){
        Logging::logBabelium("Rendering HTML5 data with babelium information...");
       global $SESSION, $CFG, $BCFG;

       $content_path = $this->getSumbissionUploadHTMLPath();

       $exinfo = '""';
       $exsubs = '""';
       $rsinfo = '""';
       $rssubs = '""';
       $recinfo = '""';

        if($mode == self::REVIEW_MODE){
            $rsinfo = json_encode($info);
            $rssubs = json_encode($subs);
        }
        else if($mode == self::PRACTICE_MODE){
            $exinfo = json_encode($info);
            $exsubs = json_encode($subs);
        }

        if($rmedia){
            $recinfo = json_encode($rmedia);
        }

       $html_content = '';
       $html_content = $this->generateStatusAlert($html_content, $info);

       if(isset($info['title'])){
               $html_content.='<h2 id="babelium-exercise-title" class="centered">'.$info['title'].'</h2>';
       }
       $html_content.= file_get_contents($content_path, FILE_USE_INCLUDE_PATH);
        //load jquery just in case
       $html_content.='<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>'.PHP_EOL;

       $domain = get_config(self::ASSIGNSUBMISSION_BABELIUM,'serverdomain');
       $lang = current_language();

       $html_content.='<script
                           src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/js/audio.js"
                           language="javascript">
                       </script>'.PHP_EOL;

       $html_content.='<script
                     src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/js/babelium.view.js"
                     language="javascript">
                 </script>'.PHP_EOL;

        $html_content.='<script
                     src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/js/video.loader.js"
                     language="javascript">
                 </script>'.PHP_EOL;

       $html_content.='<script
                           src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/js/babelium.core.js"
                           language="javascript">
                       </script>'.PHP_EOL;

        $html_content.='<script async defer
                        src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/static/dist/progressbar/progressbar.min.js"
                        language="javascript">
                        </script>'.PHP_EOL;

       $html_content .= '<script language="javascript" type="text/javascript">
                               var domain = "'.$domain.'";
                               var lang = "'.$lang.'";
                               var exinfo = '.$exinfo.';
                               var exsubs = '.$exsubs.';
                               var rsinfo = '.$rsinfo.';
                               var rssubs = '.$rssubs.';
                               var recinfo = '.$recinfo.';
                               init(exinfo, exsubs, rsinfo, rssubs, recinfo);
                         </script>'.PHP_EOL;
       Logging::logBabelium("Injecting ". strlen($html_content)." data bytes into babelium submission");
       return $html_content;
   }

    public function getSumbissionUploadHTMLPath() {
        Logging::logBabelium("Loading submission upload template from local file");
         if($this->isDevelopment()){
             $content_path = self::$rootPath.'/mod/assign/submission/babelium/iframe/upload.body.html';
         }
         else{
             $content_path = '/var/www/babelium-moodle/moodle32/iframe/upload.body.html';
         }
         Logging::logBabelium("Injecting template data from file: ".$content_path);
         return $content_path;
     }

     public function getSumbissionViewHTMLPath() {
        Logging::logBabelium("Loading submission view template from local file");
         if($this->isDevelopment()){
             $content_path = self::$rootPath.'/mod/assign/submission/babelium/iframe/view.body.html';
         }
         else{
             $content_path = '/var/www/babelium-moodle/moodle32/iframe/view.body.html';
         }
         Logging::logBabelium("Injecting template data from file: ".$content_path);
         return $content_path;
     }

    public function isDevelopment() {
        $env = self::$environment == self::DEVELOPMENT_ENVIRONMENT;
        Logging::logBabelium("Is development environment? : ".$env);
        return $env;
    }

    public function canUpgrade($type, $version) {
        Logging::logBabelium("Is elegible for upgrading?");
        if(isset($type) && isset($version)){
            $valid = ($type == self::SUBMISSION_TYPE && $version >= self::MIN_MOODLE_VERSION);
            Logging::logBabelium("Valid to be upgraded: ".$valid);
        }
        Logging::logBabelium("No valid to be upgraded");
        return false;
    }

    public function displaySubmittedExercise($plugin, $submission){
        Logging::logBabelium("Displaying HTML5 video response");
        $result = "";
        if(isset($submission)){
            $babeliumsubmission = $this->getBabeliumSubmission($submission->id);
            if ($babeliumsubmission) {
                $result          = '<div class="no-overflow">';
                $babeliumcontent = '';
                $responseid = $babeliumsubmission->responseid;
                $response_data = $plugin->getBabeliumConnector()->babeliumsubmission_get_response_data($responseid);
                $hasValidData = isset($response_data);
                if ($hasValidData){
                    $babeliumcontent = $this->babeliumsubmission_html_exercise_done_view_output($response_data['info'], $response_data['subtitles'], null);
                }
                else{
                    $babeliumcontent = $this->babeliumsubmission_html_output_error($submission);
                }
                $result .= $babeliumcontent;
                $result .= '</div>';
            }
        }
        Logging::logBabelium("Injecting ". strlen($result)." data bytes into babelium video display");
        return $result;
    }

    public function deleteTempAudioFile($upload_name) {
        Logging::logBabelium("Deleting audio file: ".$upload_name);
        $dataDir = self::$config->dataroot."/audiofiles";
        if($this->folder_exist($dataDir)){
            $filename = $dataDir."/".$upload_name;
            unlink($filename);
            Logging::logBabelium("audio file ".$filename." was deleted");
            return "success";
        }
        else{
            return "audiofiles folder does not exists";
        }
    }

    public function saveAudioDataResponse($audio_stream, $audio_len, $upload_name){
        Logging::logBabelium("Saving audio stream on Moodle server");
        global $CFG;
        //check destination file
        $dataDir = $CFG->dataroot."/audiofiles";
        if(!$this->folder_exist($dataDir)){
            Logging::logBabelium("Creating directory 'audiofiles'...");
            mkdir($dataDir);
            chmod($dataDir, 0777);
        }
        //check length first
        $len = strlen($audio_stream);
        if($len != $audio_len){
            Logging::logBabelium("ERROR: invalid audio length was detected. Declared length was ".$audio_len." and detected length is ".$len);
            return "Declared audio source length does not match with received audio length";
        }
        else{
            //save the audio in temp dir.
            $type = "audio/x-wav";
            $filename = $dataDir."/".$upload_name;
            $saved = 0;
            $fp = fopen($filename, 'a+');
            //convert audio stream to byte array from base64
            $decoded = base64_decode($audio_stream);
            $result = fwrite($fp, $decoded) ;
            if($result === FALSE){
                Logging::logBabelium("fwrite() returned false so an error happen when writing file on disk, please check permissions");
                echo "An error was detected while writing the file";
                die();
            }
            $saved = $result ? 1 : 0;
            if($saved){
                Logging::logBabelium("File was successfully saved!");
                return "success";
            }
            else{
                Logging::logBabelium("Saving file on disk failed!");
                return "failed";
            }
        }
    }

    /**
    * Checks if dir exist and return canonicalized absolute pathname (sort version)
    * @param string $folder the path being checked.
    * @return mixed returns the canonicalized absolute pathname on success otherwise FALSE is returned
    */
   private function folder_exist($folder){
       // Get canonicalized absolute pathname
       $path = realpath($folder);
       // If it exist, check if it's a directory
       $result = ($path !== false AND is_dir($path)) ? $path : false;
       return $result;
   }

   public function redirectAudioToBabelium($audio_stream, $idexercise, $idstudent, $idsubtitle, $rolename, $responsehash){
       Logging::logBabelium("Redirecting user audio stream to babelium server");
       $connector = new BabeliumConnector();
       //save student response on babelium
       return $connector->saveStudentExerciseOnBabelium(
            $idstudent,
            $idexercise,
            $idsubtitle,
            $rolename,
            $responsehash,
            $audio_stream
        );
   }

    public function babeliumsubmission_html_output_error($submission) {
        Logging::logBabelium("Generating error HTML for failed submission preview");
        $html_content = '';
        $html_content.='<h2>Could not load an exercise preview</h2>';
        $html_content.='<p>An error happen while loading the exercise preview. Please contact your teacher or system administrator</p>';
        $showDetails = false;
        if($showDetails){
            //capture var dump
            ob_start();
            var_dump($submission);
            $result = ob_get_clean();
            $html_content.="<pre>".$result."</pre>";
        }
        return $html_content;
    }

    /**
     *  Does not work properly. View issue #13 on git
     * @param type $submission
     * @param boolean $showviewlink
     * @return string
     */
    public function viewSummary($submission, $showviewlink) {
        $babeliumsubmission = $this->getBabeliumSubmission($submission->id);
        // always show the view link
        $showviewlink = true;
        $output = '';
        if ($babeliumsubmission) {
            $output   = '<div class="no-overflow">';
            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

            $recordedMediaUrl  = $babeliumsubmission->responsehash;
            $index = '/media';
            $last_offset = -4;
            $recordedMediaCode = substr($recordedMediaUrl, strpos($recordedMediaUrl, $index) + strlen($index), $last_offset);

            $thumbnailpath = $protocol
                    . get_config('assignsubmission_babelium', 'serverdomain')
                    . '/resources/images/thumbs/'
                    . $recordedMediaCode
                    . '/default.jpg';

            $thumbnail     = '<img src="'
                    . $thumbnailpath
                    . '" alt="'
                    . get_string('babelium', 'assignsubmission_babelium')
                    . '" border="0" height="45" width="60"/>';

            $output .= $thumbnail;
            $output .= '</div>';
        }
        return $output;
    }

    public function generateStatusAlert($html_content, $info) {
        //add video status if needed
       if( isset($info['isProcessed']) && $info['isProcessed']==0 ){
           //conversion not started
            $html_content.='<div class="alert alert-info alert-block fade in" role="alert">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                Submitted video is added to processing queue, please wait...
                            </div>';
       }
       else if( isset($info['isProcessed']) && $info['isProcessed']==1 ){
           //conversion started
           if( isset($info['isConverted']) && $info['isConverted']==0 ){
               //conversion started but not finished
               $html_content.='<div class="alert alert-info alert-block fade in" role="alert">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    Submitted video is being processing, please wait...
                                </div>';
           }
           else{
               //conversion finished
               $html_content.='<div class="alert alert-info alert-block fade in" role="alert">
                                    <button type="button" class="close" data-dismiss="alert">×</button>
                                    Exercise preview is ready.
                               </div>';
           }
       }
       return $html_content;
    }

}
