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
 * Description of BabeliumConnector. A simple Class to connect to Babelium RPC
 *
 * @author sanguita
 */
class BabeliumConnector {

    const DEVELOPMENT_ENVIRONMENT = 'development';
    private static $environment;
    private static $config;
    private static $babeliumService;

    function __construct() {
        global $SESSION, $CFG, $BCFG;
        self::$config = $CFG;
        self::$environment = getenv("APPLICATION_ENV");
    }

   /**
    * Gets the list of recordable exercises available to this moodle instance
    * @return mixed $result
    * 		An array of exercise data if successful or false on error/when empty query results
    */
   function babeliumsubmission_get_available_exercise_list(){
        return $this->getBabeliumRemoteService()->getExerciseList();
   }

   /**
    * Retrieves the locales, subtitles, roles and data of a particular exercise
    * @param int $exerciseid
    * 		An exercise identificator
    * @return mixed $exercise
    * 		An associative array with the info, the roles, the languages and the subtitle lines of the exercise, or false on error/when empty query results
    */
   function babeliumsubmission_get_exercise_data($exerciseid,$responseid=0){
           $g = $this->getBabeliumRemoteService();

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
           $g = $this->getBabeliumRemoteService();
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
           $parameters = array(
                                   "exerciseId" => $exerciseId,
                                   "subtitleId" => $subtitleId,
                                   "characterName" => $recordedRole,
                                   "mediaUrl" => $responseName
                                   );
           return $responsedata = $this
                   ->getBabeliumRemoteService()
                   ->newServiceCall('admSaveResponse', $parameters);
   }

    public function getBabeliumRemoteService(){
        if(!isset(self::$babeliumService)){
            self::$babeliumService = new babeliumservice();
        }
        return self::$babeliumService;
    }

}
