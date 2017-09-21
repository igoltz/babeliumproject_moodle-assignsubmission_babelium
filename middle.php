<?php

/**
 * Babelium Project open source collaborative second language oral practice - http://www.babeliumproject.com
 *
 * Copyright (c) 2013 GHyM and by respective authors (see below).
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

/**
 * This file defines the server side data submission listener
 *
 * @package   assignsubmission_babelium
 * @copyright Original from 2012 Babelium Project {@link http://babeliumproject.com} modified by Elurnet Informatika Zerbitzuak S.L  {@link http://elurnet.net/es} and Irontec S.L {@link https://www.irontec.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



if(getenv("APPLICATION_ENV") == 'development'){
    require_once '/var/www/babelium-moodle-local/config.php';
}else{
    require_once '../../../../config.php';
}

require_once($CFG->dirroot . '/mod/assign/submission/babelium/Logging.php');

require_once './BabeliumHelper.php';

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ){
    processPOSTRequest();
}
else if ( $_SERVER['REQUEST_METHOD'] == 'GET' ){
    processGETRequest();
}

function processPOSTRequest(){
    $methodName = $_POST["name"];
    $data = $_POST["data"];
    echo "{}";
}

function processGETRequest(){
    $methodName = $_GET["name"];
    $data = $_GET["data"];
    if($methodName == "exerciseinfo"){
        if(is_numeric($data)){
            //get exercise information and return as json
            $helper = new BabeliumHelper();
            $data = $helper->getExerciseDataOnly($data);
            echo json_encode($data);
        }
    }
    else{
        echo "{}";
    }
}