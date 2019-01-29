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
 * Strings for component 'assignsubmission_babelium', language 'en'
 *
 * @package   assignsubmission_babelium
 * @copyright Original from 2012 Babelium Project {@link http://babeliumproject.com} modified by Elurnet Informatika Zerbitzuak S.L  {@link http://elurnet.net/es} and Irontec S.L {@link https://www.irontec.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, this submission method will be enabled by default for all new assignments.';
$string['enabled'] = 'Babelium submissions';
$string['enabled_help'] = 'If enabled, students are able to save video-responses as their submission.';
$string['pluginname'] = 'Babelium submissions';
$string['babelium'] = 'Babelium submissions';
$string['loginfo'] = 'Submission responseID: {$a->responseid}, responseHash: {$a->responsehash}';

/*
$string['forcertmpt'] = 'Force RTMPT';
$string['forcertmpt_help'] = 'Enabling this option forces the player to use RTMPT (encapsulated RTMP) by default. Otherwise, the player first attempts to connect using the RTMP port (1935)';
*/

$string['serverdomain'] = 'Babelium server domain';
$string['serverdomain_help'] = 'The domain of the Babelium server in which the resources are going to be stored';

/*$string['serverport'] = 'Babelium server port';
$string['serverport_help'] = 'Babelium server port';
$string['apidomain'] = 'Babelium API domain';
$string['apidomain_help'] = 'The domain of the RPC-API that allows us to access the data stored in the Babelium server';
*/

$string['apiendpoint'] = 'Babelium API endpoint';
$string['apiendpoint_help'] = 'The place inside the RPC-API domain that is used to manage all the calls';

$string['newapiendpoint'] = 'Babelium API v3 endpoint';
$string['newapiendpoint_help'] = 'The place inside the RPC-API domain that is used to manage all the calls';

$string['accesskey'] = 'Babelium access key';
$string['accesskey_help'] = 'A unique key that identifies all your RPC-API requests against the Babelium server';
$string['secretaccesskey'] = 'Babelium secret access key';
$string['secretaccesskey_help'] = 'The secret key that is used to validate your identity. Should never be put in the clear on useragent scripts';

$string['babeliumAvailableRecordableExercises'] = 'Babelium exercises';
$string['babeliumAvailableRecordableExercises_help'] = 'Choose the exercise you wish to assign to your students for later evaluation';
$string['babeliumNoExerciseAvailable'] = 'No exercises available';
$string['babeliumChooseRole']='Choose a role';
$string['babeliumChooseSubLang']='Choose subtitle language';
$string['babeliumChooseRecMethod']='Choose recording method';
$string['babeliumMicOnly']='Microphone only';
$string['babeliumWebcamMic']='Webcam and microphone';
$string['babeliumStartRecording']='Start Recording';
$string['babeliumStopRecording']='Stop Recording';
$string['babeliumViewRecording']='View Recording';
$string['babeliumRecordAgain']='Record Again';
$string['babeliumViewExercise']='View Exercise';
$string['responsehashnotset']='You are attempting to submit an empty submission. Please review your submission and try again.';

//Error messages
$string['babeliumErrorConfigParameters']='Babelium configuration parameters not set';
$string['babeliumApiErrorCode200']='Babelium Error 200. Malformed response';
$string['babeliumApiErrorCode400']='Babelium Error 400. Malformed request';
$string['babeliumApiErrorCode403']='Babelium Error 403. Wrong authorization credentials';
$string['babeliumApiErrorCode404']='Babelium Error 404. Method not found. Invalid parameters';
$string['babeliumApiErrorCode500']='Babelium Error 500. Internal server error';

$string['title_not_load_exercise'] = "Could not load an exercise preview";
$string['msg_not_load_exercise'] = "An error happen while loading the exercise preview. Please contact your teacher or system administrator";

$string['video_queued'] = "Submitted video is added to processing queue, please wait...";
$string['video_processing'] = "Submitted video is being processing, please wait...";
$string['video_ready'] = "Exercise preview is ready.";

$string['connectivity_error_title'] = 'Connectivity error';
$string['connectivity_error_subtitle'] = 'Could not retrieve exercise data due to remote issue with babelium platform.';