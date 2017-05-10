<?php

/**
* BabeliumHelper class that contains BabeliumPlugin Business Logic
* @author sanguita
*/
class BabeliumHelper
{
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

}
