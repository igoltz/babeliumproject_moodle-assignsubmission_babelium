<?php

/**
 * Babelium Project open source collaborative second language oral practice - http://www.babeliumproject.com
 *
 * Copyright (c) 2012 GHyM and by respective authors (see below).
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

require_once($CFG->dirroot . '/mod/assign/submission/babelium/babeliumservice.php');

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
		$html_content.='<h2 id="babelium-exercise-title">'.$info['title'].'</h2>';
	}
    $html_content.="<iframe onload='javascript:(function(o){o.style.height=(o.contentWindow.document.body.scrollHeight*0.6)+\"px\";}(this));' style='height:100px;width:100%;border:none;overflow:hidden;' src='//babelium-dev.irontec.com/iframe/upload.html'></iframe>";

    //HTML5 video player example

    /*
    $html_content.='<h1>Reproduccion de video sin Flash</h1>';
    $html_content.="<script src='//babelium-static.irontec.com/plyr/dist/plyr.js' language='javascript'></script>";
    $html_content.="<link rel='stylesheet' href='//babelium-static.irontec.com/plyr/dist/plyr.css'>";
    $html_content.='<iframe width="560" height="315" src="https://www.youtube.com/embed/8SpASXsPwl0" frameborder="0" allowfullscreen></iframe>';
    $html_content.='<video style="width:100%" poster="//babelium-static.irontec.com/_temp/poster.jpg" controls crossorigin="anonymous">
  <source src="//babelium-static.irontec.com/_temp/video.mp4" type="video/mp4">
  <source src="//babelium-static.irontec.com/_temp/video.webm" type="video/webm">
  <!-- Captions are optional -->
  <track kind="captions" label="English captions" src="//babelium-static.irontec.com/_temp/subs.vtt" srclang="en" default>
    </video>';
    */


	$html_content.='<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js" language="javascript"></script>';
	$html_content.='<script src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/script/babelium.moodle.js" language="javascript"></script>';
	$html_content.='<script type="text/javascript"> var $bjq = jQuery.noConflict(); </script>';


	//disable SWF integration

	/*
	$html_content.='<script src="'. $CFG->wwwroot .'/mod/assign/submission/babelium/script/swfobject.js" language="javascript"></script>';
	$html_content.='<div id="flashContent">
			 <p>To view this page ensure that Adobe Flash Player version 11.1.0 or greater is installed. </p>
			 <script type="text/javascript">
				var pageHost = ((document.location.protocol == "https:") ? "https://" : "http://");
				document.write("<a href=\'"+pageHost+"://www.adobe.com/go/getflashplayer\'><img src=\'"
						+ pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif\' alt=\'Get Adobe Flash player\' /></a>" );
			</script>
			</div>
			<noscript><p>Either scripts and active content are not permitted to run or Adobe Flash Player version 11.1.0 or greater is not installed.</p></noscript>';
    */
	$domain = get_config('assignsubmission_babelium','serverdomain');
	$forcertmpt = get_config('assignsubmission_babelium','forcertmpt');
	$lang = current_language();

	$html_content .= '<script language="javascript" type="text/javascript">
						init("'.$domain.'", "'.$lang.'", "'.$forcertmpt.'", '.$exinfo.', '.$exsubs.', '. $rsinfo .', '. $rssubs .', '. $recinfo .');
					  </script>';
	return $html_content;
}

/**
 * Gets the list of recordable exercises available to this moodle instance
 * @return mixed $result
 * 		An array of exercise data if successful or false on error/when empty query results
 */
function babeliumsubmission_get_available_exercise_list(){
	$g = new babeliumservice();
	$result = $g->newServiceCall('getRecordableExercises');
	return $result;
}

/**
 * Retrieves the locales, subtitles, roles and data of a particular exercise
 * @param int $exerciseid
 * 		An exercise identificator
 * @return mixed $exercise
 * 		An associative array with the info, the roles, the languages and the subtitle lines of the exercise, or false on error/when empty query results
 */
function babeliumsubmission_get_exercise_data($exerciseid,$responseid=0){
	$g = new babeliumservice();

	if($responseid){
		$data = $g->newServiceCall('getResponseById', array("responseId"=>$responseid));
	} else {
		$data = $g->newServiceCall('getExerciseById', array("id"=>$exerciseid));
	}
	if(!$data){
		return null;
	}

	$subtitleId = isset($data['subtitleId']) ? $data['subtitleId'] : 0;
	$mediaId= isset($data['media']) ? $data['media']['id'] : 0;
	$captions = $g->newServiceCall('getSubtitleLines', array("id" => $subtitleId, "mediaid" => $mediaId));

	if(!$captions){
		return null;
	}

	$exerciseRoles = array();
	foreach($captions as $subline){
		$role = array("id"=>$subline['exerciseRoleId'], "characterName"=>$subline['exerciseRoleName']);
		if(!in_array($role,$exerciseRoles))
			$exerciseRoles[] = $role;
	}

	$recordInfo = $g->newServiceCall('requestRecordingSlot');

	if($data && $exerciseRoles && $captions && is_array($exerciseRoles[0]) && is_array($captions[0])){
		$exercise = array(
			"info" => $data,
			"roles" => $exerciseRoles,
			"subtitles" => $captions,
			"languages" => null,
			"recinfo" => $recordInfo
		);
		return $exercise;
	} else {
		//TODO add some kind of log function here so that the admin knows what happened: add_to_log() maybe
		return;
	}
}

/**
 * Retrieves the locales, subtitles, roles and data of a particular response and its related exercise
 * @param int $responseid
 * @return mixed $response
 * 		An associative array with the info, the roles, the languages and the subtitle lines of the response, or false on error/when empty query results
 */
function babeliumsubmission_get_response_data($responseid){
	$g = new babeliumservice();
	$data = $g->newServiceCall('getResponseById', array("responseId"=>$responseid));
	$captions = $g->newServiceCall('getSubtitleLines', array("id"=>$data['subtitleId'],"mediaid"=>''));

	if (!$captions)
		return;

	$exerciseRoles = array();
	foreach($captions as $subline){
		$role = array("id"=>$subline['exerciseRoleId'], "characterName"=>$subline['exerciseRoleName']);
		if(!in_array($role,$exerciseRoles))
			$exerciseRoles[] = $role;
	}

	if($data && $captions && $exerciseRoles && is_array($captions[0]) && is_array($exerciseRoles[0])){
		$response = array(
			"info" => $data,
			"subtitles" => $captions,
			"roles" => $exerciseRoles,
			"languages" => null,
			"recinfo" => null
		);
		return $response;
	} else {
		//TODO add logging for failure
		return;
	}
}

/**
 * Saves the data of a new response recorded using the plugin
 * @param int $exerciseId
 * 		An exercise identificator
 * @param int $exerciseDuration
 * 		The duration of the exercise in seconds
 * @param int $subtitleId
 * 		The identificator of the subtitles that were used on the recording process
 * @param String $recordedRole
 * 		The character name that was impersonated in the recording process
 * @param String $responseName
 * 		The hash name of the recording file
 * @return mixed $responseData
 * 		Array with information about the newly saved response, or false on error
 */
function babeliumsubmission_save_response_data($exerciseId, $subtitleId, $recordedRole, $responseName){
	$g = new babeliumservice();
	$parameters = array(
				"exerciseId" => $exerciseId,
				"subtitleId" => $subtitleId,
				"characterName" => $recordedRole,
				"mediaUrl" => $responseName
				);
	return $responsedata = $g->newServiceCall('admSaveResponse', $parameters);
}
