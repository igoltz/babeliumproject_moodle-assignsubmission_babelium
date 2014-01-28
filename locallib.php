<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the definition for the library class for babelium submission plugin
 *
 * @package   assignsubmission_babelium
 * @copyright 2013 Babelium Project {@link http://babeliumproject.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * File area for babelium submission assignment
 */
define('ASSIGNSUBMISSION_BABELIUM_FILEAREA', 'submissions_babelium');

/** Include babelium helper classes **/
require_once($CFG->dirroot . '/mod/assign/submission/babelium/babeliumlib.php');

class assign_submission_babelium extends assign_submission_plugin {

    /**
     * Get the name of the babelium submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('babelium', 'assignsubmission_babelium');
    }

    /**
     * Get babelium submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_babelium_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_babelium', array('submission'=>$submissionid));
    }

    /**
     * Get LTI user role archetype for the provided context
     *
     * @param stdClass $user
     * @param int $cmid
     * @param int $courseid
     * @return mixed $roles
     */
    private function get_user_role($user, $cmid, $courseid){
        $roles = array();

        if (empty($cmid)) {
            //If no cmid is passed, check if the user is a teacher in the course
            $coursecontext = context_course::instance($courseid);

            if (has_capability('moodle/course:manageactivities', $coursecontext)) {
                array_push($roles, 'Instructor');
            } else {
                array_push($roles, 'Learner');
            }
        } else {
            $context = context_module::instance($cmid);

            //The only assign module capability teachers and non-editing teachers have
            if (has_capability('mod/assign:grade', $context)) {
                array_push($roles, 'Instructor');
            } else {
                array_push($roles, 'Learner');
            }
        }

        if (is_siteadmin($user)) {
            array_push($roles, 'Administrator');
        }

        return join(',', $roles);
    }

    /**
     * Build a 'fake' lis header until we support IMS-LTI in our tool provider.
     * 
     * @param stdClass $instance
     * @param stdClass $course
     */
    private function build_lis_request($instance, $course){
        global $USER, $CFG;

        if (empty($instance->cmid)) {
            $instance->cmid = 0;
        }

        $role = $this->get_user_role($USER, $instance->cmid, $course->id);

        //Basic data
        $requestparams = array(
            'resource_link_id' => isset($instance->id) ? $instance->id : '',
            'resource_link_title' => isset($instance->name) ? $instance->name : '',
            'resource_link_description' => isset($instance->intro) ? $instance->intro : '',
            'user_id' => $USER->id,
            'roles' => $role,
            'context_id' => $course->id,
            'context_label' => $course->shortname,
            'context_title' => $course->fullname,
            'launch_presentation_locale' => current_language()
        );

        //Additional data
        $requestparams['lis_person_name_given'] =  $USER->firstname;
        $requestparams['lis_person_name_family'] =  $USER->lastname;
        $requestparams['lis_person_name_full'] =  $USER->firstname." ".$USER->lastname;
        $requestparams['lis_person_contact_email_primary'] = $USER->email;
    
        return $requestparams;
    }

    /**
     * Get the default setting for babelium submission plugin. 
     * This form is displayed in the Submission settings area when creating a new assignment
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        $defaultexerciseid = $this->get_config('exerciseid') > 0 ? $this->get_config('exerciseid') : 0;  	

        $exercises = array();
        $exercisesMenu = array();
        $classattribute = array('class' => 'error');
        
        try {
            $lisdata = $this->build_lis_request($this->assignment, $COURSE);
            $exercises = babeliumsubmission_get_available_exercise_list($lisdata);

            if($exercises && count($exercises) > 0){
                foreach ($exercises as $exercise) {
                    $exercisesMenu[$exercise['id']] = $exercise['title'];
                }
            }
            if(count($exercisesMenu)>0){
                $mform->addElement('select', 'assignsubmission_babelium_exerciseid', get_string('babeliumAvailableRecordableExercises','assignsubmission_babelium'), $exercisesMenu);
                $mform->addHelpButton('assignsubmission_babelium_exerciseid', 'babeliumAvailableRecordableExercises', 'assignsubmission_babelium');
                $mform->setDefault('assignsubmission_babelium_exerciseid', $defaultexerciseid);
                
                //Moodle 2.5 uses a checkbox to enable different submission plugins whereas prior versions use a select
                //control. This is a dirty hack to check the version and apply a different conditional rule to each version.
                if($CFG->version < 2013051400){
                    $mform->disabledIf('assignsubmission_babelium_exerciseid', 'assignsubmission_babelium_enabled', 'eq', 0);
                }else{
                    $mform->disabledIf('assignsubmission_babelium_exerciseid', 'assignsubmission_babelium_enabled', 'notchecked');
                }
                $mform->addElement('hidden', 'noexerciseavailable', 0);
            } else {
            	$msg = html_writer::tag('span', get_string('babeliumNoExerciseAvailable', 'assignsubmission_babelium'), $classattribute);
                $mform->addElement('static',
                                   'noexercisemessage',
                                   get_string('babeliumAvailableRecordableExercises', 'assignsubmission_babelium'),
                                   $msg);
                $mform->addElement('hidden', 'noexerciseavailable', 1);
            }
        } catch (Exception $e) {
            //html_writer::span() shortcut function is not available in Moodle versions prior to 2.5
            //$msg = html_writer::span($e->getMessage(), 'error');
            $msg = html_writer::tag('span', $e->getMessage(), $classattribute);
            $mform->addElement('static',
                               'assignsubmission_babelium_servererror',
                               get_string('babeliumAvailableRecordableExercises', 'assignsubmission_babelium'),
                               $msg);

            //This is a dirty hack, but it should avoid enabling Babelium submissions when server auth
            //goes wrong for some reason.
            $mform->addElement('hidden', 'noexerciseavailable', 1);
        }
        $mform->setType('noexerciseavailable', PARAM_INT);
        $mform->disabledIf('assignsubmission_babelium_enabled', 'noexerciseavailable', 'eq', 1);
    }

    /**
     * Save the settings for babelium submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        if (isset($data->assignsubmission_babelium_exerciseid)) {
            $this->set_config('exerciseid', $data->assignsubmission_babelium_exerciseid);
        }
        return true;
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $COURSE;
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
                $data->responseid = $babeliumsubmission->responseid;
                $data->responsehash = $babeliumsubmission->responsehash;
            }

        }
        $lisdata = $this->build_lis_request($this->assignment, $COURSE);
        $exercise_data = !empty($data->responsehash) ?
            babeliumsubmission_get_response_data($lisdata, $data->responseid) : babeliumsubmission_get_exercise_data($lisdata, $exerciseid);
        if(!$exercise_data)
            throw new dml_exception("Error while retrieving Babelium external data");

        $this->get_babelium_form_elements($mform, array($data, 
                                          $exercise_data['info'], 
                                          $exercise_data['roles'], 
                                          $exercise_data['languages'], 
                                          $exercise_data['subtitles']));
        return true;
    }

    /**
     * Adds Babelium related elements to the submission form
     *  
     * @param MoodleQuickForm $mform
     * @param mixed $formdata 
     */
    public function get_babelium_form_elements(MoodleQuickForm $mform, $formdata){
        global $PAGE;
        $PAGE->requires->string_for_js('babeliumViewRecording', 'assignsubmission_babelium');
        $PAGE->requires->string_for_js('babeliumViewExercise', 'assignsubmission_babelium');
        $PAGE->requires->string_for_js('babeliumStartRecording', 'assignsubmission_babelium');
        $PAGE->requires->string_for_js('babeliumStopRecording', 'assignsubmission_babelium');

        list($data, $exinfo, $exroles, $exlangs, $exsubs) = $formdata;

        $roleMenu = array();
        if($exroles && count($exroles) > 0){
            foreach ($exroles as $exrole) {
                if(isset($exrole['characterName']) && $exrole['characterName'] != "NPC"){
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
        //$mform->addRule('responsehash','You cannot save the assignment without recording something','required');
        $mform->addRule('responsehash', get_string('required'), 'required', null, 'client');

        //The role selected in the combobox the last time the user pushed the 'Start Recording' button 
        $mform->addElement('hidden', 'recordedRole');
        $mform->setType('recordedRole', PARAM_TEXT);

        $mform->addElement('hidden', 'subtitleId', $exsubs[0]['subtitleId']);
        $mform->setType('subtitleId', PARAM_RAW);
        $mform->addElement('hidden', 'exerciseDuration', $exinfo['duration']);
        $mform->setType('exerciseDuration', PARAM_RAW);

        //Returns a string with all the html and script tags needed to init the babelium widget
        $html_content = babeliumsubmission_html_output(!empty($data->responsehash),$exinfo,$exsubs);

        $mform->addElement('html',$html_content);
        $mform->addElement('select', 'roleCombo', get_string('babeliumChooseRole', 'assignsubmission_babelium'), $roleMenu);

        //TODO Currently, we only allow one language for the subtitles so this element is not needed for now
        //$mform->addElement('select', 'localeCombo', get_string('babeliumChooseSubLang', 'assignment_babelium'), $localeMenu);

        $recmethods=array();
        $recmethods[] = $mform->createElement('radio', 'recmethod','none', get_string('babeliumMicOnly','assignsubmission_babelium'), 0);
        $recmethods[] = $mform->createElement('radio', 'recmethod','none', get_string('babeliumWebcamMic','assignsubmission_babelium'), 1);
        $mform->addGroup($recmethods, 'radioar', get_string('babeliumChooseRecMethod','assignsubmission_babelium'), array(' '), false);

        //TODO check how the help dynamic popups retrieve their texts to apply the same principle to the label of these two buttons
        $babeliumactions=array();
        $babeliumactions[] = $mform->createElement('button', 'startStopRecordingBtn', 
                get_string('babeliumStartRecording','assignsubmission_babelium'));
        $babeliumactions[] = $mform->createElement('button', 'viewRecordingBtn', 
                get_string('babeliumViewRecording', 'assignsubmission_babelium'), 
                empty($data->responsehash) ? 'style="display:none;"' : null);
        $babeliumactions[] = $mform->createElement('button', 'viewExerciseBtn', 
                get_string('babeliumViewExercise', 'assignsubmission_babelium'), 
                empty($data->responsehash) ? 'style="display:none;"' : null);
        $mform->addGroup($babeliumactions, 'babeliumActions', '', '', false);
    }

    /**
     * Save the video-recording hash
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB, $COURSE;

        $babeliumsubmission = $this->get_babelium_submission($submission->id);

        //plagiarism code event trigger when files are uploaded
        $eventdata = new stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->assignment->get_course_module()->id;
        $eventdata->itemid = $submission->id;
        $eventdata->courseid = $this->assignment->get_course()->id;
        $eventdata->userid = $USER->id;
        $eventdata->content = $data->responsehash;
        events_trigger('assessable_content_uploaded', $eventdata);

        $lisdata = $this->build_lis_request($this->assignment, $COURSE);
        if ($babeliumsubmission) {
            if($babeliumsubmission->responsehash != $data->responsehash){
                $responsedata = babeliumsubmission_save_response_data($lisdata,
                                                                      $this->get_config('exerciseid'), 
                                                                      $data->exerciseDuration, 
                                                                      $data->subtitleId, 
                                                                      $data->recordedRole, 
                                                                      $data->responsehash);
                if(!$responsedata)
                    throw new moodle_exception('babeliumErrorSavingResponse','assignsubmission_babelium');
                $babeliumsubmission->responseid = $responsedata['responseId'];
            } else {
                $babeliumsubmission->responseid = $data->responseid;
            }
            $babeliumsubmission->responsehash = $data->responsehash;

            return $DB->update_record('assignsubmission_babelium', $babeliumsubmission);
        } else {
            $babeliumsubmission = new stdClass();
            $babeliumsubmission->responsehash = $data->responsehash;

            $responsedata = babeliumsubmission_save_response_data($lisdata,
                                                                  $this->get_config('exerciseid'), 
                                                                  $data->exerciseDuration, 
                                                                  $data->subtitleId, 
                                                                  $data->recordedRole, 
                                                                  $data->responsehash);
            if(!$responsedata)
                throw new moodle_exception('babeliumErrorSavingResponse','assignsubmission_babelium');
            $babeliumsubmission->responseid = $responsedata['responseId'];


            $babeliumsubmission->submission = $submission->id;
            $babeliumsubmission->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignsubmission_babelium', $babeliumsubmission) > 0;
        }
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user) {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id, 
                                     'assignsubmission_babelium', 
                                     ASSIGNSUBMISSION_BABELIUM_FILEAREA, 
                                     $submission->id, "timemodified", false);

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
    public function view_summary(stdClass $submission, & $showviewlink) {
        $babeliumsubmission = $this->get_babelium_submission($submission->id);
        // always show the view link
        $showviewlink = true;

        if ($babeliumsubmission) {
            $output = '<div class="no-overflow">';
            $thumbnailpath = 'http://'.get_config('assignsubmission_babelium','serverdomain').
                             '/resources/images/thumbs/'.$babeliumsubmission->responsehash.'/default.jpg';
            $thumbnail = '<img src="'.$thumbnailpath.'" alt="'.get_string('babelium','assignsubmission_babelium').'" border="0" height="45" width="60"/>';
            $output .= $thumbnail;
            $output .= '</div>';
            return $output;
        }
        return '';
    }

    /**
     * Display the saved video-response in the view table
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        global $CFG, $COURSE;
        $result = '';
        $babeliumsubmission = $this->get_babelium_submission($submission->id);
        if ($babeliumsubmission) {
            $result = '<div class="no-overflow">';
            $babeliumcontent = '';
            $lisdata = $this->build_lis_request($this->assignment, $COURSE);
            $response_data = babeliumsubmission_get_response_data($lisdata, $babeliumsubmission->responseid);
            if($response_data)
                $babeliumcontent = babeliumsubmission_html_output(0,$response_data['info'],$response_data['subtitles']);
            $result .= $babeliumcontent;
            $result .= '</div>';
        }

        return $result;
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type
     * @param int $version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        if ($type == 'babelium' && $version >= 2011112900) {
            return true;
        }
        return false;
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
    public function upgrade_settings(context $oldcontext,stdClass $oldassignment, & $log) {
        if ($oldassignment->assignmenttype == 'babelium') {
            $this->set_config('exerciseid', $oldassignment->var1);
            return true;
        }
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
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $submission, & $log) {
        global $DB;

        $babeliumsubmission = new stdClass();
        $babeliumsubmission->responseid = $oldsubmission->data1;
        $babeliumsubmission->responsehash = $oldsubmission->data2;

        $babeliumsubmission->submission = $submission->id;
        $babeliumsubmission->assignment = $this->assignment->get_instance()->id;

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

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // will throw exception on failure
        $DB->delete_records('assignsubmission_babelium', array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Formatting for log info
     *
     * @param stdClass $submission The submission
     *
     * @return string
     */
    public function format_for_log(stdClass $submission) {

        // format the info for each submission plugin add_to_log
        $babeliumsubmission = $this->get_babelium_submission($submission->id);
        $babeliumloginfo = '';
        $babeliumloginfo .= get_string('loginfo', 'assignsubmission_babelium', 
                array('responseid'=>$babeliumsubmission->responseid, 'responsehash'=>$babeliumsubmission->responsehash));

        return $babeliumloginfo;
    }

    /**
     * Return true if no response was submitted
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission) {
        $babeliumsubmission = $this->get_babelium_submission($submission->id);

        return empty($babeliumsubmission->responsehash);
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNSUBMISSION_BABELIUM_FILEAREA=>$this->get_name());
    }
}
