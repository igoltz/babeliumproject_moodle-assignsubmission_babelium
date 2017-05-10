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
    const KEY_ASSIGNMENT_RECORD = 'assignsubmission_babelium';

    const JSON_ID = 'id';
    const JSON_TITLE = 'title';

    const EMPTY_STRING = '';

    /**
    * Available babelium exercise list
    **/
    private $exercises = array();

    /**
    * Key-pair list for holding <select> content
    **/
    private $exercisesMenu  = array();
    private $classattribute = array(
        'class' => 'error'
    );

    private $exerciseid;
    private $data;
    private $exercise_data;
    private $submission;

    function __construct() {
        global $SESSION, $CFG, $BCFG;
        self::$config = $CFG;
        self::$environment = getenv("APPLICATION_ENV");
        self::$rootPath = $CFG->wwwroot;
    }

    public function getDefaultExerciseId($plugin){
        $id = $plugin->get_config(KEY_EXERCISE_ID);
        return $id > MIN_EXERCISE_ID ? $id : DEFAULT_EXERCISE_ID;
    }

    public function areValidExercises(){
            $exercises && count($exercises) > MIN_EXERCISE_NUMBER;
    }

    public function getAvailableExercises(){
            $exercises = babeliumsubmission_get_available_exercise_list();
    }

    public function createExerciseList(){
            foreach ($exercises as $exercise) {
                $exercisesMenu[$exercise[JSON_ID]] = $exercise[JSON_TITLE];
            }
    }

    public function isValidExercise($data){
            return isset($data->assignsubmission_babelium_exerciseid);
    }

    public function saveThisExercise($plugin, $data){
            $plugin->set_config(
                    KEY_EXERCISE_ID,
                    $data->assignsubmission_babelium_exerciseid
            );
    }
    
    public function saveSubmissionConfiguration($plugin, $data){
        if (isset($data->assignsubmission_babelium_exerciseid)) {
            $plugin->set_config('exerciseid', $data->assignsubmission_babelium_exerciseid);
        }
        return true;
    }

    public function getSubmissionId($submission){
            $this->submission = $submission ? $submission->id : DEFAULT_SUBMISSION_ID;
    }

    public function isExerciseIdAvailable($plugin){
            $exerciseid = $plugin->get_config(KEY_EXERCISE_ID);
            return $this->exerciseid <= MIN_EXERCISE_ID;
    }

    public function checkReceivedData($data){
        $this->data = $data;
        if (!isset($this->data->responseid)) {
            $this->data->responseid = EMPTY_STRING;
        }

        if (!isset($this->data->responsehash)) {
            $this->data->responsehash = EMPTY_STRING;
        }
        return $this->data;
    }

    public function populateWithBabeliumData($plugin, $submission){
        $babeliumsubmission = $plugin->get_babelium_submission($submission->id);
        if ($babeliumsubmission) {
            $this->data->responseid   = $babeliumsubmission->responseid;
            $this->data->responsehash = $babeliumsubmission->responsehash;
        }
        return $this->data;
    }

    public function getExerciseData($data){
        //original code
        //$exercise_data = !empty($data->responsehash) ? babeliumsubmission_get_exercise_data(0, $data->responseid) : babeliumsubmission_get_exercise_data($exerciseid);
        if( !empty($data->responsehash) ){
            $this->exercise_data = babeliumsubmission_get_exercise_data(0, $data->responseid);
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
        $plugin->get_babelium_form_elements($mform, array(
            $data,
            $exercise_data['info'],
            $exercise_data['roles'],
            $exercise_data['languages'],
            $exercise_data['subtitles'],
            $exercise_data['recinfo']
        ));
    }

    public function copySubmission($plugin, $source, $dest){
        // Copy the assignsubmission_babelium record.
        global $DB;
        $babeliumsubmission = $plugin->get_babelium_submission($sourcesubmission->id);
        if ($babeliumsubmission) {
            unset($babeliumsubmission->id);
            $babeliumsubmission->submission = $destsubmission->id;
            $DB->insert_record(KEY_ASSIGNMENT_RECORD, $babeliumsubmission);
        }
    }

    /**
    * Return true if no response was submitted
    * @param stdClass $submission
    */
    public function isEmptySubmission($plugin, $submission){
        $babeliumsubmission = $plugin->get_babelium_submission($submission->id);
        return empty($babeliumsubmission->responsehash);
    }

    public function deleteInstance($plugin){
        global $DB;
        // will throw exception on failure
        $DB->delete_records(
            ASSIGNSUBMISSION_BABELIUM,
            array(
                'assignment' => $plugin->assignment->get_instance()->id
            )
        );
    }

    public function get_plugin_name() {
        return get_string('babelium', 'assignsubmission_babelium');
    }

    public function getBabeliumSumbission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_babelium', array(
            'submission' => $submissionid
        ));
    }

    public function check_file_code($submissioncode) {
        global $OUTPUT;

        if (!$submissioncode || empty($submissioncode)) {
            $errormsg = get_string('responsehashnotset', 'assignsubmission_babelium');
            return $OUTPUT->error_text($errormsg);
        }
        return null;
    }
        
    public function copy_submission($sourcesubmission, $destsubmission){
        global $DB;

        // Copy the assignsubmission_babelium record.
        $babeliumsubmission = $this->get_babelium_submission($sourcesubmission->id);
        if ($babeliumsubmission) {
            unset($babeliumsubmission->id);
            $babeliumsubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_babelium', $babeliumsubmission);
        }
        return true;
    }

    public function format_for_log($submission) {
        // format the info for each submission plugin add_to_log
        $babeliumsubmission = $this->getBabeliumSumbission($submission->id);
        $babeliumloginfo    = '';
        $babeliumloginfo .= get_string('loginfo', 'assignsubmission_babelium', array(
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

        if (!$DB->insert_record('assignsubmission_babelium', $babeliumsubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'mod_assign', $submission->userid);
            return false;
        }
        return true;
    }

    public function upgradeSettings($oldcontext, $oldassignment, $log) {
        if ($oldassignment->assignmenttype == 'babelium') {
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

       if($mode){
               $rsinfo = json_encode($info);
               $rssubs = json_encode($subs);
       } else {
               $exinfo = json_encode($info);
               $exsubs = json_encode($subs);
       }

       if($rmedia)
               $recinfo = json_encode($rmedia);

       $html_content = '';
       if(isset($info['title'])){
               $html_content.='<h2 id="babelium-exercise-title" class="centered">'.$info['title'].'</h2>';
       }
       $html_content.= file_get_contents($content_path, FILE_USE_INCLUDE_PATH);

           //load jquery just in case
       $html_content.='<!-- jquery -->
                       <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
                       <script>window.jQuery || document.write("<script src="jquery.js">\x3C/script>")</script>'.PHP_EOL;

       $domain = get_config('assignsubmission_babelium','serverdomain');
       $lang = current_language();

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

       if(getenv("APPLICATION_ENV") !== 'development'){
           $html_content.='<script
                               src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/script/babelium.moodle.js"
                               language="javascript">
                           </script>'.PHP_EOL;
           $html_content.='<script
                               src="//babelium-static.irontec.com/js/babelium.core.js"
                               language="javascript">
                           </script>'.PHP_EOL;
       }
       else{
           $html_content.='<script
                               src="http://197.0.11.3/mod/assign/submission/babelium/script/babelium.moodle.js"
                               language="javascript">
                           </script>'.PHP_EOL;
           $html_content.='<script
                               src="http://197.0.11.3/mod/assign/submission/babelium/static/js/babelium.core.js"
                               language="javascript">
                           </script>'.PHP_EOL;
       }
       return $html_content;
   }
   
    public function getSumbissionHTMLPath() {
         if($this->isDevelopment()){
             $content_path = self::rootPath  .'/mod/assign/submission/babelium/iframe/upload.body.html';
         }
         else{
             $content_path = '/var/www/html/babelium-plugin-shortcut/iframe/upload.body.html';
         }
         return $content_path;
     }

    public function isDevelopment() {
        return self::$environment == DEVELOPMENT_ENVIRONMENT;
    }

}
