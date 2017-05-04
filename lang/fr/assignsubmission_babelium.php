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
 * Strings for component 'assignsubmission_babelium', language 'fr'
 *
 * @package   assignsubmission_babelium
 * @copyright 2012 Babelium Project {@link http://babeliumproject.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 $string['default'] = 'Activé par défaut';
 $string['default_help'] = 'Si cette option est activée, la méthode de dépôt sera activée par défaut pour toutes les nouvelles tâches.';
 $string['enabled'] = 'Dépôt Babelium';
 $string['enabled_help'] = 'Si activé, les étudiants peuvent enregistrer des réponses en vidéo et/ou audio pour leurs dépôts.';
 $string['pluginname'] = 'Dépôt Babelium';
 $string['babelium'] = 'Dépôt Babelium';
 $string['loginfo'] = 'Enregistrement ResponseID: {$a->responseid}, ResponseHash: {$a->responsehash}';

/*
 $string['forcertmpt'] = 'Force RTMPT';
 $string['forcertmpt_help'] = 'Activer cette option force le reproducteur à utiliser RTMPT (RTMP encapsulé) par défaut. Autrement,le reproducteur essaie d\'abord de se connecter en utilisant le port RTMP (1935)';
*/

 $string['serverdomain'] = 'Domaine du serveur';
 $string['serverdomain_help'] = 'Domaine du serveur Babelium sur lequel sont conservés les exercices et les enregistrements';
 /*
 $string['serverport'] = 'Port du serveur';
 $string['serverport_help'] = 'Numéro de port par lequel on accède au serveur';
 $string['apidomain'] = 'Domaine de l\'API';
 $string['apidomain_help'] = 'Domaine de l\'API-RPC permettant à l\'usager l\'accès aux données entreposées sur le serveur Babelium';
 */

 $string['apiendpoint'] = 'Endpoint de l\'API';
 $string['apiendpoint_help'] = 'Chemin appartenant au domaine de l\'API utilisée pour gérer toutes les demandes';

 $string['new_apiendpoint'] = 'Endpoint de l\'API v3';
 $string['new_apiendpoint_help'] = 'Chemin appartenant au domaine de l\'API v3 utilisée pour gérer toutes les demandes';

 $string['accesskey'] = 'Code d\'accès';
 $string['accesskey_help'] = 'Code unique identifiant toutes les demandes que l\'usager fait à l\'API-RPC sur le serveur Babelium';
 $string['secretaccesskey'] = 'Code d\'accès secret';
 $string['secretaccesskey_help'] = 'Code secret utilisé pour valider l\'identité de l\'usager pour les demandes. Ne jamais utiliser sans  cryptage pour les scripts locaux';

 $string['babeliumAvailableRecordableExercises'] = 'Exercices Babelium';
 $string['babeliumAvailableRecordableExercises_help'] = 'Choisissez l\'exercice que vous souhaitez donner à vos étudiants pour une évaluation postérieure';
 $string['babeliumNoExerciseAvailable'] = 'Aucun exercice disponible';
 $string['babeliumChooseRole']='Choisissez un rôle';
 $string['babeliumChooseSubLang']='Choisissez une langue pour les sous-titres';
 $string['babeliumChooseRecMethod']='Choisissez une méthode d\'enregistrement';
 $string['babeliumMicOnly']='Seulement microphone';
 $string['babeliumWebcamMic']='Caméra web et microphone';
 $string['babeliumStartRecording']='Commencer l\'enregistrement';
 $string['babeliumStopRecording']='Arrêter l\'enregistrement';
 $string['babeliumViewRecording']='Voir l\'enregistrement';
 $string['babeliumRecordAgain']='Recommencer l\'enregistrement';
 $string['babeliumViewExercise']='Voir exercice';

 //Error messages
 $string['babeliumErrorConfigParameters']='Paramètres de configuration de Babelium non établis';
 $string['babeliumApiErrorCode200']='Babelium Error 200. Réponse malformée';
 $string['babeliumApiErrorCode400']='Babelium Error 400. Demande malformée';
 $string['babeliumApiErrorCode403']='Babelium Error 403. Documents d\'autorisation incorrects';
 $string['babeliumApiErrorCode404']='Babelium Error 404. Méthode non trouvée. Paramètres non valides';
 $string['babeliumApiErrorCode500']='Babelium Error 500. Erreur interne du serveur';
