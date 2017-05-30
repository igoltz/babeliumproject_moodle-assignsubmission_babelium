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
require_once($CFG->dirroot . '/mod/assign/submission/babelium/Logging.php');

/**
 * Description of BabeliumConnector. A simple Class to connect to Babelium RPC
 *
 * @author sanguita
 */
class BabeliumConnector {

    const DEVELOPMENT_ENVIRONMENT = 'development';
    const MINIMUM_EXERCISE_COUNT = 0;

    private static $environment;
    private static $config;
    private static $babeliumService;
    
    private $exercises      = array();
    private $exercisesMenu  = array();
    private $classattribute = array(
        'class' => 'error'
    );

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
       Logging::logBabelium("Getting available exercise list");
       $this->exercises = $this->getBabeliumRemoteService()->getExerciseList();
       return $this->getExercisesMenu();
   }
   
   function getExercisesMenu(){
       $this->exercisesMenu = array();
       if ($this->exercises && count($this->exercises) > 0) {
            foreach ($this->exercises as $exercise) {
                if(isset($exercise) && isset($exercise['id']) && isset($exercise['title'])){
                    $this->exercisesMenu[$exercise['id']] = $exercise['title'];
                }
            }
        }
        return $this->exercisesMenu;
   }

    function build_settings_form_empty($mform){
        $msg = html_writer::tag(
            'span',
            get_string('babeliumNoExerciseAvailable', 'assignsubmission_babelium'),
            $this->classattribute
        );
        $mform->addElement(
            'static',
            'noexercisemessage',
            get_string('babeliumAvailableRecordableExercises', 'assignsubmission_babelium'),
            $msg
        );
        $mform->addElement(
            'hidden',
            'noexerciseavailable',
            1
        );
    }
    
    function build_settings_form_footer($mform){
        //apply if error
        $mform->setType(
            'noexerciseavailable',
            PARAM_INT
        );
        $mform->disabledIf(
            'assignsubmission_babelium_enabled',
            'noexerciseavailable',
            'eq',
            1
        );
    }
    
    function build_settings_form_error($mform, $exception){
        //html_writer::span() shortcut function is not available in Moodle versions prior to 2.5
        //$msg = html_writer::span($e->getMessage(), 'error');
        $msg = html_writer::tag('span', $exception->getMessage(), $this->classattribute);
        $mform->addElement(
            'static',
            'assignsubmission_babelium_servererror',
            get_string('babeliumAvailableRecordableExercises', 'assignsubmission_babelium'),
            $msg
        );

        //This is a dirty hack, but it should avoid enabling Babelium submissions when server auth
        //goes wrong for some reason.
        $mform->addElement(
            'hidden',
            'noexerciseavailable',
            1
        );
    }
   
   function build_settings_form($mform, $defaultexerciseid, $version){
       $mform->addElement(
            'select',
            'assignsubmission_babelium_exerciseid',
            get_string('babeliumAvailableRecordableExercises', 'assignsubmission_babelium'),
            $this->exercisesMenu
        );

        $mform->addHelpButton(
            'assignsubmission_babelium_exerciseid',
            'babeliumAvailableRecordableExercises',
            'assignsubmission_babelium'
        );

        $mform->setDefault(
            'assignsubmission_babelium_exerciseid',
            $defaultexerciseid
        );
        //Moodle 2.5 uses a checkbox to enable different submission plugins whereas prior versions use a select
        //control. This is a dirty hack to check the version and apply a different conditional rule to each version.
        if ($version) {
            $mform->disabledIf(
                'assignsubmission_babelium_exerciseid',
                'assignsubmission_babelium_enabled',
                'eq',
                0
            );
        } 
        else {
            $mform->disabledIf(
                'assignsubmission_babelium_exerciseid',
                'assignsubmission_babelium_enabled',
                'notchecked'
            );
        }
        
        //detect if error
        //disable babelium checkbox if no exercises available found
        $value = $this->areValidExercises() ? 1 : 0;
        $mform->addElement(
            'hidden',
            'noexerciseavailable',
            $value
        );
   }

   /**
    * Retrieves the locales, subtitles, roles and data of a particular exercise
    * @param int $exerciseid
    * 		An exercise identificator
    * @return mixed $exercise
    * 		An associative array with the info, the roles, the languages and the subtitle lines of the exercise, or false on error/when empty query results
    */
   function babeliumsubmission_get_exercise_data($exerciseid,$responseid=0){
       Logging::logBabelium("Getting exercise data");
           $g = $this->getBabeliumRemoteService();
           $data = null;
           if($responseid){
                $data = $g->getResponseInformation($responseid);
                 if(!$data){
                     return null;
                 }
                $subtitleId = isset($data['subtitleId']) ? $data['subtitleId'] : 0;
                $mediaId= isset($data['mediaId']) ? $data['mediaId']: 0;
           } else {
                $data = $g->getExerciseInformation($exerciseid);
                if(!$data){
                     return null;
                 }
                $media = $data['media'];
                $subtitleId = isset($media['subtitleId']) ? $media['subtitleId'] : 0;
                $mediaId= isset($media['id']) ? $media['id']: 0;
           }
           $captions = $g->getCaptions($subtitleId,$mediaId);
           if(!$captions){
                   return null;
           }

           $exerciseRoles = $this->getExerciseRoles($captions);

           //WTF??
           //$recinfo = $g->newServiceCall('requestRecordingSlot');
           $recinfo = null;
           $returnData = $this->getResponseInfo($data, $captions, $exerciseRoles, $recinfo);
            return $returnData;
   }

   /**
    * Retrieves the locales, subtitles, roles and data of a particular response and its related exercise
    * @param int $responseid
    * @return mixed $response
    * 		An associative array with the info, the roles, the languages and the subtitle lines of the response, or false on error/when empty query results
    */
  function babeliumsubmission_get_response_data($responseid){
      Logging::logBabelium("Getting response data");
    $g = $this->getBabeliumRemoteService();
    $data = $g->getResponseInformation($responseid);
    $captions = null;
    if(isset($data) && isset($data['subtitleId'])){
      $subtitleId = $data['subtitleId'];
      $mediaId = '';
      $captions = $g->getCaptions($subtitleId, $mediaId);
    }
    if (!$captions){
      return;
    }
    else{
      $exerciseRoles = $this->getExerciseRoles($captions);
      $recinfo = null;
      return $this->getResponseInfo($data, $captions, $exerciseRoles, $recinfo);
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
   public function saveStudentExerciseOnBabelium($idstudent, $idexercise, $idsubtitle, $rolename, $responsehash, $audio_stream){
       /**
        * OLD WAY:
        * $parameters = array(
                                   "exerciseId" => $exerciseId,
                                   "subtitleId" => $subtitleId,
                                   "characterName" => $recordedRole,
                                   "mediaUrl" => $responseName
                                   );
           return $responsedata = $this
                   ->getBabeliumRemoteService()
                   ->newServiceCall('admSaveResponse', $parameters);
        */
       Logging::logBabelium("Saving student submission & audio on Babelium");
        $parameters = array(
            "studentId" => $idstudent,
            "exerciseId" => $idexercise,
            "subtitleId" => $idsubtitle,
            "characterName" => $rolename,
            "responsehash" => $responsehash,
            "audio" => $audio_stream
        );
       $g = $this->getBabeliumRemoteService();
       return $g->saveStudentExerciseOnBabelium($parameters);
   }
    
    public function getExerciseRoles($captions){
        Logging::logBabelium("Getting exercise roles");
        $exerciseRoles = array();
        if(isset($captions)){
          foreach($captions as $subline){
            $validData = isset($subline['exerciseRoleId']) && isset($subline['exerciseRoleName']);
            if($validData){
              $role = array(
                "id"=>$subline['exerciseRoleId'],
                "characterName"=>$subline['exerciseRoleName']
              );
              if(!in_array($role, $exerciseRoles)){
                $exerciseRoles[] = $role;
              }
            }
          }
        }
        return $exerciseRoles;
    }

    public function getResponseInfo($data, $captions, $exerciseRoles, $recinfo) {
        Logging::logBabelium("Getting response info");
      $validData = isset($data)
      && isset($captions)
      && isset($captions[0])
      && isset($exerciseRoles)
      && isset($exerciseRoles[0]);

      if( ! $validData ){
        //TODO add logging for failure
        return;
      }
      else if($data && $captions && $exerciseRoles && is_array($captions[0]) && is_array($exerciseRoles[0])){
        $response = array(
               "info" => $data,
               "subtitles" => $captions,
               "roles" => $exerciseRoles,
               "languages" => null,
               "recinfo" => $recinfo
        );
        return $response;
      }
      else {
        //TODO add logging for failure
        return;
      }
    }
    
    public function getBabeliumRemoteService(){
        if(!isset(self::$babeliumService)){
            self::$babeliumService = new babeliumservice();
        }
        return self::$babeliumService;
    }

    public function areValidExercises() {
        return count($this->exercisesMenu) > self::MINIMUM_EXERCISE_COUNT;
    }

}
