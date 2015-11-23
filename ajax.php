<?php

/**
 * Process ajax requests
 *
 * @package assignsubmission_babelium
 * @copyright  2015 Inko Perurena <inko@elurnet.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require('../../../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

require_sesskey();

$action = optional_param('action', '', PARAM_ALPHANUM);
$assignmentid = required_param('assignmentid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);

$cm = \get_coursemodule_from_instance('assign', $assignmentid, 0, false, MUST_EXIST);
$context = \context_module::instance($cm->id);

$assignment = new \assign($context, null, null);

require_login($assignment->get_course(), false, $cm);

if (!$assignment->can_view_submission($userid)) {
    print_error('nopermission');
}

if($action == 'listallexercises'){
    require_capability('mod/assign:addinstance', $context);
    $response = new stdClass();
    echo json_encode($response);
    die();
}
else if($action == 'searchexercise'){
    require_capability('mod/assign:addinstance', $context);
}
