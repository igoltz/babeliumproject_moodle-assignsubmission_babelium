<?php

/**
 * The assignsubmission_babelium assessable uploaded event.
 *
 * @package    assignsubmission_babelium
 * @copyright  2015 Inko Perurena <inko@elurnet.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_babelium\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The assignsubmission_babelium assessable uploaded event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string format: content format.
 * }
 *
 * @package    assignsubmission_babelium
 * @since      Moodle 2.6
 * @copyright  2015 Inko Perurena <inko@elurnet.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessable_uploaded extends \core\event\assessable_uploaded {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has saved a babelium submission with id '$this->objectid' " .
               "in the assignment activity with the course module id '$this->contextinstanceid'.";
    }

    /**
     * Legacy event data if get_legacy_eventname() is not empty.
     *
     * @return stdClass
     */
    protected function get_legacy_eventdata() {
        $eventdata = new \stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->contextinstanceid;
        $eventdata->itemid = $this->objectid;
        $eventdata->courseid = $this->courseid;
        $eventdata->userid = $this->userid;

        return $eventdata;
    }

    /**
     * Return the legacy event name.
     *
     * @return string
     */
    public static function get_legacy_eventname() {
        return 'assessable_content_uploaded';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventassessableuploaded', 'assignsubmission_babelium');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/assign/view.php', array('id' => $this->contextinstanceid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assign_submission';
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception when validation fails.
     * @return void
     */
    protected function validate_data() {
        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}
