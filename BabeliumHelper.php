<?php

/**
* BabeliumHelper class that contains BabeliumPlugin Business Logic
* @author sanguita
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
            return $this->exercise_data;
    }

    public function getFormData($plugin, $mform){
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
        $hasValidData = isset($submission);
        if($hasValidData){
            $babeliumsubmission = $plugin->get_babelium_submission($submission->id);
            return empty($babeliumsubmission->responsehash);
        }
        return true;
    }

    public function deleteInstance($plugin){
        global $DB;
        // will throw exception on failure
        $DB->delete_records(
            self::ASSIGNSUBMISSION_BABELIUM,
            array(
                self::KEY_ASSIGNMENT => $plugin->assignment->get_instance()->id
            )
        );
    }

    public function get_plugin_name() {
        return get_string(self::KEY_CONFIG_BABELIUM_NAME, self::ASSIGNSUBMISSION_BABELIUM);
    }

    public function getBabeliumSubmission($submissionid) {
        global $DB;
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
   function babeliumsubmission_html_output($mode, $info, $subs, $rmedia){

       global $SESSION, $CFG, $BCFG;

       $content_path = $this->getSumbissionHTMLPath();

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
       if(isset($info['title'])){
               $html_content.='<h2 id="babelium-exercise-title" class="centered">'.$info['title'].'</h2>';
       }
       $html_content.= file_get_contents($content_path, FILE_USE_INCLUDE_PATH);
        //load jquery just in case
       $html_content.='<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>'.PHP_EOL;

       $domain = get_config(self::ASSIGNSUBMISSION_BABELIUM,'serverdomain');
       $lang = current_language();

       if(getenv("APPLICATION_ENV") !== 'development'){
           $html_content.='<script
                               src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/script/babelium.moodle.js"
                               language="javascript">
                           </script>'.PHP_EOL;
           $html_content.='<script
                               src="//babelium-dev.irontec.com/static/js/babelium.core.js"
                               language="javascript">
                           </script>'.PHP_EOL;
       }
       else{
           $html_content.='<script
                               src="http://192.168.1.13/mod/assign/submission/babelium/script/babelium.moodle.js"
                               language="javascript">
                           </script>'.PHP_EOL;
           $html_content.='<script
                               src="http://192.168.1.13/mod/assign/submission/babelium/static/js/babelium.core.js"
                               language="javascript">
                           </script>'.PHP_EOL;
       }

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
       return $html_content;
   }

    public function getSumbissionHTMLPath() {
         if($this->isDevelopment()){
             $content_path = self::$rootPath.'/mod/assign/submission/babelium/iframe/upload.body.html';
         }
         else{
             $content_path = '/var/www/babelium-moodle/moodle32/iframe/upload.body.html';
         }
         return $content_path;
     }

    public function isDevelopment() {
        return self::$environment == self::DEVELOPMENT_ENVIRONMENT;
    }

    public function canUpgrade($type, $version) {
        if(isset($type) && isset($version)){
            return ($type == self::SUBMISSION_TYPE && $version >= self::MIN_MOODLE_VERSION);
        }
        return false;
    }

    public function displayVideoResponse($plugin, $submission){
        $result = "";
        if(isset($submission)){
            $babeliumsubmission = $this->getBabeliumSubmission($submission->id);
            if ($babeliumsubmission) {
                $result          = '<div class="no-overflow">';
                $babeliumcontent = '';
                $responseid = $babeliumsubmission->responseid;
                $response_data = $plugin->getBabeliumConnector()->babeliumsubmission_get_response_data($responseid);
                if (isset($response_data)){
                    $babeliumcontent = $this->babeliumsubmission_html_output(self::REVIEW_MODE, $response_data['info'], $response_data['subtitles'], null);
                }
                $result .= $babeliumcontent;
                $result .= '</div>';
            }
        }
        return $result;
    }
}