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
 * Strings for component 'assignsubmission_babelium', language 'eu'
 *
 * @package   assignsubmission_babelium
 * @copyright Original from 2012 Babelium Project {@link http://babeliumproject.com} modified by Elurnet Informatika Zerbitzuak S.L  {@link http://elurnet.net/es} and Irontec S.L {@link https://www.irontec.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['default'] = 'Berez gaituta';
$string['default_help'] = 'Ezarriz gero, bidalketa-metodo hau berez gaituko da zeregin berri guztietan';
$string['enabled'] = 'Babelium bidalketa';
$string['enabled_help'] = 'Ezarriz gero, ikasleek beraien bidalketan bideo edo/eta audio erantzunak grabatu ditzakete proposatutako ariketa jarraituz.';
$string['pluginname'] = 'Babelium bidalketa';
$string['babelium'] = 'Babelium bidalketa';
$string['loginfo'] = 'Babelium GrabaketaId: {$a->responseid}, GrabaketaHash: {$a->responsehash}';

/*
$string['forcertmpt'] = 'RTMPT Derrigortu';
$string['forcertmpt_help'] = 'Aukera hau gaitzeak erreproduzigailua defektuz RTMPT (kapsulatutako RMTP) erabiltzera derrigortzen du. Bestela, erreproduzigailua lehendabizi RTMP portua erabiliz konektatzen saiatzen da (1935)';
*/

$string['serverdomain'] = 'Zerbitzari domeinua';
$string['serverdomain_help'] = 'Ariketak zein grabazioak gordetzeko erabiliko den Babelium zerbitzariaren domeinua';
/*
$string['serverport'] = 'Zerbitzari portua';
$string['serverport_help'] = 'Zerbitzarira atzitzeko erabiltzen den portu zenbakia';
$string['apidomain'] = 'API domeinua';
$string['apidomain_help'] = 'Erabiltzaileari Babelium zerbitzarian dauden datuak atzitzea ahalbidetzen duen RPC-APIaren domeinua';
*/
$string['apiendpoint'] = 'APIaren endpointa';
$string['apiendpoint_help'] = 'Eskaera guztiak kudeatzeko erabiltzen den APIaren domeinuaren barneko helbidea';

$string['newapiendpoint'] = 'API v3aren endpointa';
$string['newapiendpoint_help'] = 'Eskaera guztiak kudeatzeko erabiltzen den API v3aren domeinuaren barneko helbidea';

$string['accesskey'] = 'Atzipen gakoa';
$string['accesskey_help'] = 'Erabiltzaileak Babelium zerbitzariaren aurka RPC-APIaren bitartez egiten dituen eskaera guztiak identifikatzen dituen gako unibokoa';
$string['secretaccesskey'] = 'Atzipen gako sekretua';
$string['secretaccesskey_help'] = 'Eskaeretan erabiltzailearen identitatea balioztatzeko erabiltzen da gako sekretua. Ez da inoiz skript lokaletan enkriptatu gabe erabili behar';

$string['babeliumAvailableRecordableExercises'] = 'Babelium ariketak';
$string['babeliumAvailableRecordableExercises_help'] = 'Aukera ezazu zure ikasleei geroago ebaluatuak izateko esleituko diezun ariketa';
$string['babeliumNoExerciseAvailable'] = 'Ez dago ariketa eskuragarririk';
$string['babeliumChooseRole']='Rol bat aukeratu';
$string['babeliumChooseSubLang']='Azpitituluen hizkuntza aukeratu';
$string['babeliumChooseRecMethod']='Grabaketa metodoa aukeratu';
$string['babeliumMicOnly']='Mikrofonoa soilik';
$string['babeliumWebcamMic']='Web-kamera eta mikrofonoa';
$string['babeliumStartRecording']='Grabatzen Hasi';
$string['babeliumStopRecording']='Grabaketa Eten';
$string['babeliumViewRecording']='Grabaketa Ikusi';
$string['babeliumRecordAgain']='Berriz Grabatu';
$string['babeliumViewExercise']='Ariketa Ikusi';

//Error messages
$string['babeliumErrorConfigParameters']='Babeliumeko konfigurazio parametroak ez daude ezarrita';
$string['babeliumApiErrorCode200']='Babelium 200 Errorea. Erantzun gaizki eraikia';
$string['babeliumApiErrorCode400']='Babelium 400 Errorea. Eskaera gaizki eraikia';
$string['babeliumApiErrorCode403']='Babelium 403 Errorea. Autorizazio kredentzial desegokia';
$string['babeliumApiErrorCode404']='Babelium 404 Errorea. Metodoa ezezaguna. Baliogabeko parametroak';
$string['babeliumApiErrorCode500']='Babelium 500 Errorea. Zerbitzariko barne errorea';

$string['title_not_load_exercise'] = "Ariketaren aurreikuspena ezin da kargatu";
$string['msg_not_load_exercise'] = "Errore bat gertatu da ariketa-aurrebista kargatzean. Mesedez, zure irakaslearekin edo sistemako administratzailearekin jarri harremanetan.";
$string['video_queued'] = "Bidalitako bideoa bizpahiru minututan prozezatuko da, mesedez itxaron ...";
$string['video_processing'] = "Bidalitako bideoa prozesatzen ari da, mesedez itxaron ...";
$string['video_ready'] = "Ariketaren aurreikuspena prest dago";

$string['connectivity_error_title'] = 'Konexio errorea';
$string['connectivity_error_subtitle'] = 'Ezinezkoa izan da ariketaren datuak jasotzea Babelium plataformaren arazo batengatik';