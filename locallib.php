<?php

/**
 * This file contains the definition for the library class for babelium submission plugin
 *
 * @package   assignsubmission_babelium
 * @copyright 2015 Babelium Project {@link http://babeliumproject.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * File area for babelium submission assignment
 */
define('ASSIGNSUBMISSION_BABELIUM_FILEAREA', 'submissions_babelium');

/** Include babelium helper classes **/
require_once($CFG->dirroot . '/mod/assign/submission/babelium/Logging.php');
require_once($CFG->dirroot . '/mod/assign/submission/babelium/BabeliumConnector.php');
require_once($CFG->dirroot . '/mod/assign/submission/babelium/BabeliumHelper.php');

class assign_submission_babelium extends assign_submission_plugin
{
    const PRACTICE_MODE = 0;
    const REVIEW_MODE = 1;
    const NO_RESPONSE_ID_VALID = -1;

    private static $helper;
    private static $babeliumConnector;

        /**
     * Get the name of the babelium submission plugin
     * @return string
     */
    public function get_name()
    {
        Logging::logBabelium("Getting plugin name");
        return $this->getBabeliumHelper()->get_plugin_name();
    }

    /**
     * Get babelium submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    public function get_babelium_submission($submissionid)
    {
        Logging::logBabelium("Getting submission from DB...");
        return $this->getBabeliumHelper()->getBabeliumSubmission($submissionid);
    }

    /**
     * Get the default setting for babelium submission plugin.
     * This form is displayed in the Submission settings area when creating a new assignment
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform){
        Logging::logBabelium("Loading new submission babelium form...");
        global $CFG, $COURSE;
        $defaultexerciseid = $this->getBabeliumHelper()->getDefaultExerciseId($this);
        try {
            $this->getBabeliumConnector()->babeliumsubmission_get_available_exercise_list();
            if ($this->getBabeliumConnector()->areValidExercises()) {
                $version = $CFG->version < 2013051400;
                $this->getBabeliumConnector()->build_settings_form($mform, $defaultexerciseid, $version);
            } else {
                $this->getBabeliumConnector()->build_settings_form_empty($mform);
            }
        }
        catch (Exception $e) {
            $this->getBabeliumConnector()->build_settings_form_error($mform, $e);
        }
        $this->getBabeliumConnector()->build_settings_form_footer($mform);
    }

    /**
     * Save the settings for babelium submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data)
    {
        Logging::logBabelium("Saving settings for babelium submission plugin...");
        return $this->getBabeliumHelper()->saveSubmissionConfiguration($this, $data);
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data)
    {
        Logging::logBabelium("Getting form elements, submission form");
        if (($exerciseid = $this->get_config('exerciseid')) <= 0) {
            return false;
        }
        $submissionid = $submission ? $submission->id : 0;

        if (!isset($data->responseid)) {
            $data->responseid = '';
        }
        if (!isset($data->responsehash)) {
            $data->responsehash = '';
        }

        if ($submission) {
            $babeliumsubmission = $this->get_babelium_submission($submission->id);
            if ($babeliumsubmission) {
                $data->responseid   = $babeliumsubmission->responseid;
                $data->responsehash = $babeliumsubmission->responsehash;
            }

        }

        $exercise_data = null;
        if(!empty($data->responsehash)){
            $exercise_data = $this->getBabeliumConnector()->babeliumsubmission_get_exercise_data(0, $data->responseid);
        }
        else{
            $exercise_data = $this->getBabeliumConnector()->babeliumsubmission_get_exercise_data($exerciseid);
        }
        if (!$exercise_data)
            throw new dml_exception("Error while retrieving Babelium external data. No data returned");

        //error_log(print_r($exercise_data,true),3,"/tmp/error.log");

        $this->get_babelium_form_elements($mform, array(
            $data,
            $exercise_data['info'],
            $exercise_data['roles'],
            $exercise_data['languages'],
            $exercise_data['subtitles'],
            $exercise_data['recinfo']
        ));
        return true;
    }

    /**
     * Adds Babelium related elements to the submission form
     *
     * @param MoodleQuickForm $mform
     * @param mixed $formdata
     */
    public function get_babelium_form_elements(MoodleQuickForm $mform, $formdata)
    {
        global $PAGE;
        $PAGE->requires->jquery();
        
        list($data, $exinfo, $exroles, $exlangs, $exsubs, $recinfo) = $formdata;

        $roleMenu = array();
        if ($exroles && count($exroles) > 0) {
            foreach ($exroles as $exrole) {
                if (isset($exrole['characterName']) && $exrole['characterName'] != "NPC") {
                    $roleMenu[$exrole['characterName']] = $exrole['characterName'];
                }
            }
        }

        // hidden params
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'edit');
        $mform->setType('edit', PARAM_INT);

        $mform->addElement('hidden', 'responseid');
        $mform->setType('responseid', PARAM_RAW);

        $mform->addElement('hidden', 'responsehash');
        $mform->setType('responsehash', PARAM_TEXT);
        
        $mform->addElement('hidden', 'payload');
        $mform->setType('payload', PARAM_TEXT);
        //$mform->addRule('responsehash','You cannot save the assignment without recording something','required');
        //$mform->addRule('responsehash', get_string('required'), 'required', null, 'server');

        //The role selected in the combobox the last time the user pushed the 'Start Recording' button
        $mform->addElement('hidden', 'recordedRole');
        $mform->setType('recordedRole', PARAM_TEXT);

        $mform->addElement('hidden', 'subtitleId', $exsubs[0]['subtitleId']);
        $mform->setType('subtitleId', PARAM_RAW);

        if (isset($exinfo['duration'])) {
            $mform->addElement('hidden', 'exerciseDuration', $exinfo['duration']);
            $mform->setType('exerciseDuration', PARAM_RAW);
        }

        //error_log(print_r($recinfo,true),3,"/tmp/error.log");

        //Returns a string with all the html and script tags needed to init the babelium widget
        $html_content = $this->getBabeliumHelper()->babeliumsubmission_html_exercise_todo_view_output(self::PRACTICE_MODE, $exinfo, $exsubs, $recinfo);

        $mform->addElement('html', $html_content);
        $mform->addElement('select', 'roleCombo', get_string('babeliumChooseRole', 'assignsubmission_babelium'), $roleMenu);

        //TODO Currently, we only allow one language for the subtitles so this element is not needed for now
        //$mform->addElement('select', 'localeCombo', get_string('babeliumChooseSubLang', 'assignment_babelium'), $localeMenu);

        /* No need to give them an option. just audio
         * $recmethods   = array();
        $recmethods[] = $mform->createElement('radio', 'recmethod', 'none', get_string('babeliumMicOnly', 'assignsubmission_babelium'), 0);
        $recmethods[] = $mform->createElement('radio', 'recmethod', 'none', get_string('babeliumWebcamMic', 'assignsubmission_babelium'), 1);
        $mform->addGroup($recmethods, 'radioar', get_string('babeliumChooseRecMethod', 'assignsubmission_babelium'), array(
            ' '
        ), false);*/
    }

    /**
     * Save the video-recording hash
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data)
    {
        Logging::logBabelium("Saving user response for submission...");
        global $USER, $DB;

        // File storage options should go here if needed
        $babeliumsubmission = $this->get_babelium_submission($submission->id);

        // Check that the responsehash is set before submitting anything
        $codenotset = $this->check_file_code($data->responsehash);
        if ($codenotset) {
            $this->set_error($codenotset);
            return false;
        }
        
        //check if we have a response
        if(!isset($data->payload)){
            throw new moodle_exception('babeliumErrorSavingResponse', 'assignsubmission_babelium');
        }
        $responsedata = base64_decode($data->payload);
        $responsedata = json_decode($responsedata);
        
        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'content' => trim($data->responsehash),
                'pathnamehashes' => array()
            )
        );
        
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        $event = \assignsubmission_babelium\event\assessable_uploaded::create($params);
        $event->trigger();

        $groupname = null;
        $groupid   = 0;
        //Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array(
                'id' => $submission->groupid
            ), '*', MUST_EXIST);
            $groupid   = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        // Unset the objectid and other fields from params for use in submission events.
        unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'responsehash' => $data->responsehash,
            'groupid' => $groupid,
            'groupname' => $groupname
        );
        if ($babeliumsubmission) {
            if ($babeliumsubmission->responsehash != $data->responsehash) {
                /*$responsedata = $connector->saveStudentExerciseOnBabelium(
                        $this->get_config('exerciseid'),
                        $data->subtitleId,
                        $data->recordedRole,
                        $data->responsehash
                );*/
                $babeliumsubmission->responseid = $responsedata->id;
            } else {
                $babeliumsubmission->responseid = $data->responseid;
            }
            $babeliumsubmission->responsehash = $data->responsehash;
            $params['objectid']               = $babeliumsubmission->id;
            $updatestatus                     = $DB->update_record('assignsubmission_babelium', $babeliumsubmission);
            $event                            = \assignsubmission_babelium\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else {
            $babeliumsubmission               = new stdClass();
            $babeliumsubmission->responsehash = $data->responsehash;

            /*$responsedata = $connector->saveStudentExerciseOnBabelium(
                    $this->get_config('exerciseid'),
                    $data->subtitleId,
                    $data->recordedRole,
                    $data->responsehash
            );*/
            $babeliumsubmission->responseid = $responsedata->id; //$responsedata['responseId'];
            $babeliumsubmission->submission = $submission->id;
            $babeliumsubmission->assignment = $this->assignment->get_instance()->id;
            $babeliumsubmission->id         = $DB->insert_record('assignsubmission_babelium', $babeliumsubmission);
            $params['objectid']             = $babeliumsubmission->id;
            $event                          = \assignsubmission_babelium\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $babeliumsubmission->id > 0;
        }
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user)
    {
        $result = array();
        $fs     = get_file_storage();

        $files = $fs->get_area_files(
                    $this->assignment->get_context()->id,
                    'assignsubmission_babelium',
                    ASSIGNSUBMISSION_BABELIUM_FILEAREA,
                    $submission->id,
                    "timemodified",
                    false
                );

        foreach ($files as $file) {
            $result[$file->get_filename()] = $file;
        }
        return $result;
    }

    /**
     * Display the list of files  in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of files is long
     * @return string
     */
    public function view_summary(stdClass $submission, &$showviewlink)
    {
        Logging::logBabelium("Showing HTML5 summary view of submission");
        return $this->getBabeliumHelper()->viewSummary($submission, $showviewlink);
    }

    /**
     * Display the saved video-response in the view table
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission)
    {
        Logging::logBabelium("Displaying view...");
        return $this->getBabeliumHelper()->displaySubmittedExercise($this, $submission);
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type
     * @param int $version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version)
    {
        return $this->getBabeliumHelper()->canUpgrade($type, $version);
    }


    /**
     * Upgrade the settings from the old assignment
     * to the new plugin based one
     *
     * @param context $oldcontext - the old assignment context
     * @param stdClass $oldassignment - the old assignment data record
     * @param string $log record log events here
     * @return bool Was it a success? (false will trigger rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, &$log)
    {
        Logging::logBabelium("Upgrading settings..");
        return $this->getBabeliumHelper()->upgradeSettings($oldcontext, $oldassignment, $log);
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @param context $oldcontext The context of the old assignment
     * @param stdClass $oldassignment The data record for the old oldassignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $submission, &$log)
    {
        Logging::logBabelium("Upgrading submission...");
        return $this->getBabeliumHelper()->upgradeSubmission(
                $this,
                $oldcontext,
                $oldassignment,
                $oldsubmission,
                $submission,
                $log
        );
    }

    /**
     * Formatting for log info
     *
     * @param stdClass $submission The submission
     *
     * @return string
     */
    public function format_for_log(stdClass $submission)
    {
        return $this->getBabeliumHelper()->format_for_log($submission);
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance()
    {
        Logging::logBabelium("Deleting instance...");
        return $this->getBabeliumHelper()->deleteInstance($this);
    }

    /**
     * Return true if no response was submitted
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission)
    {
        Logging::logBabelium("Checking for empty submission...");
        return $this->getBabeliumHelper()->isEmptySubmission($this, $submission);
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas()
    {
        Logging::logBabelium("Getting file areas...");
        return array(
            ASSIGNSUBMISSION_BABELIUM_FILEAREA => $this->get_name()
        );
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission)
    {
        Logging::logBabelium("Copying submission...");
        return $this->getBabeliumHelper()->copy_submission($sourcesubmission, $destsubmission);
    }

    public function check_file_code($submissioncode)
    {
        Logging::logBabelium("Checking file code...");
        return $this->getBabeliumHelper()->check_file_code($submissioncode);
    }

    public function getBabeliumHelper() {
        if(!isset(self::$helper)){
            self::$helper = new BabeliumHelper();
        }
        return self::$helper;
    }

    public function getBabeliumConnector() {
        if(!isset(self::$babeliumConnector)){
            self::$babeliumConnector = new BabeliumConnector();
        }
        return self::$babeliumConnector;
    }

}