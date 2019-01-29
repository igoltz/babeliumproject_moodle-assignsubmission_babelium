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
 * Strings for component 'assignsubmission_babelium', language 'de'
 *
 * @package   assignsubmission_babelium
 * @copyright Original from 2012 Babelium Project {@link http://babeliumproject.com} modified by Elurnet Informatika Zerbitzuak S.L  {@link http://elurnet.net/es} and Irontec S.L {@link https://www.irontec.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['default']='immer eingeschaltet';
$string['default_help']='Wenn diese Option aktiviert ist, wird diese Art der Abgabe für alle neuen Aufgaben freigeschaltet werden.';
$string['enabled']='Abgabe Babelium';
$string['enabled_help']='Wenn diese Option aktiviert ist, können die Lerner Ihre Antworten auf Video und / oder Audio für Aufgaben gemäß den geplanten Übungen abgeben.';
$string['pluginname']='Aufgabe Babelium';
$string['babelium']='Aufgabe Babelium';
$string['loginfo']='Babelium IdAufnahme: {$a->geantwortet}, HashAufnahme: {$a->Antworthash}';

/*
$string['forcertmpt'] = 'RTMPT erzwingen';
$string['forcertmpt_help'] = 'Wenn diese Option markiert ist, versucht der Player eine Verbindung über RTMPT aufzubauen (gekapseltes RTMP). Ansonsten versucht der Player zunächst sich über den RTMP Port zu verbinden. (1935)';
*/

$string['serverdomain']='Server Domain';
$string['serverdomain_help']='Die Babelium Server Domain, in der beide Übungen wie Aufnahmen gespeichert werden';

/*$string['serverport']='Server-Port';
$string['serverport_help']='Die Port-Nummer, mit der Sie auf den Server zugreifen';
$string['apidomain']='Domain API';
$string['apidomain_help']='Die Domain-RPC API, mit dem Nutzer auf die  auf dem Server gespeicherten Daten von Babelium zugreifen können';
*/
$string['apiendpoint']='API Endpunkt';
$string['apiendpoint_help']='Eine Route, die zu der Domain des API gehört, und verwendet wird, um alle Anfragen zu verwalten';

$string['newapiendpoint']='API v3 Endpunkt';
$string['newapiendpoint_help']='Eine Route, die zu der Domain des API v3 gehört, und verwendet wird, um alle Anfragen zu verwalten';

$string['accesskey']='Access Key';
$string['accesskey_help']='Ein eindeutiger Schlüssel, der alle Anfragen, die der Nutzer über den Server-RPC API Babelium durchführt identifiziert';
$string['secretaccesskey']='Geheimer Access Key';
$string['secretaccesskey_help']='Der geheime Schlüssel wird verwendet, um die Identität des Nutzers bei den Anfragen zu verifizieren. Niemals ohne Verschlüsselungsmechanismus in lokalen Skripten \'zu verwenden';

$string['babeliumAvailableRecordableExercises']='Übungen die zur Verfügung stehen';
$string['babeliumAvailableRecordableExercises_help']='Wählen Sie die Übung, die  Ihren Schülern zur weiteren Beurteilung zuweisen möchten';
$string['babeliumNoExerciseAvailable']='Keine Tests verfügbar';
$string['babeliumChooseRole']='Wählen Sie eine Rolle aus';
$string['babeliumChooseSubLang']='Wählen sie eine Untertitelsprache aus';
$string['babeliumChooseRecMethod']='Wählen Sie eine Aufnahmemethode aus';
$string['babeliumMicOnly']='Nur Mikrofon';
$string['babeliumWebcamMic']='Webkamera und Mikrofon';
$string['babeliumStartRecording']='Aufnahme beginnen';
$string['babeliumStopRecording']='Aufnahme unterbrechen';
$string['babeliumViewRecording']='Aufnahme ansehen';
$string['babeliumRecordAgain']='Noch einmal aufnehmen';
$string['babeliumViewExercise']='Übung ansehen';

//Error messages
$string['babeliumErrorConfigParameters']='Working Parameter Babelium nicht hergestellt';
$string['babeliumApiErrorCode200']='Babelium Fehlermeldung 200. Fehlerhafte Antwort';
$string['babeliumApiErrorCode400']='Babelium Fehlermeldung 400. fehlerhaften Anfrage';
$string['babeliumApiErrorCode403']='Babelium Fehlermeldung 403. Berechtigungsnachweise nicht korrekt';
$string['babeliumApiErrorCode404']='Babelium Fehlermeldung 404. Methode nicht gefunden. Parameter ungültig';
$string['babeliumApiErrorCode500']='Babelium Fehlermeldung 500. Interner Serverfehler';

//todo
$string['title_not_load_exercise'] = "Could not load an exercise preview";
$string['msg_not_load_exercise'] = "An error happen while loading the exercise preview. Please contact your teacher or system administrator";
$string['video_queued'] = "Submitted video is added to processing queue, please wait...";
$string['video_processing'] = "Submitted video is being processing, please wait...";
$string['video_ready'] = "Exercise preview is ready.";

$string['connectivity_error_title'] = 'Connectivity error';
$string['connectivity_error_subtitle'] = 'Could not retrieve exercise data due to remote issue with babelium platform.';
