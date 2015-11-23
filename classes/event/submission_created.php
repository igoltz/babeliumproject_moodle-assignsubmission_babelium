<?php

/**
 * The assignsubmission_babelium submission_created event.
 *
 * @package    assignsubmission_babelium
 * @copyright  2015 Inko Perurena <inko@elurnet.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_babelium\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The assignsubmission_babelium submission_created event class.
 *
 * @property-read array $other {
 *      Extra information about the event.
 *
 *      - int babeliumwordcount: Word count of the online text submission.
 * }
 *
 * @package    assignsubmission_babelium
 * @since      Moodle 2.7
 * @copyright  2015 Inko Perurena <inko@elurnet.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission_created extends \mod_assign\event\submission_created {

    /**
     * Init method.
     */
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assignsubmission_file';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $descriptionstring = "The user with id '$this->userid' created a babelium submission with " .
            "the code '{$this->other['responsehash']}' in the assignment with the course module id " .
            "'$this->contextinstanceid'";
        if (!empty($this->other['groupid'])) {
            $descriptionstring .= " for the group with id '{$this->other['groupid']}'.";
        } else {
            $descriptionstring .= ".";
        }

        return $descriptionstring;
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['responsehash'])) {
            throw new \coding_exception('The \'responsehash\' value must be set in other.');
        }
    }
}
