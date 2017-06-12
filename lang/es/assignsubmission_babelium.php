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
 * Strings for component 'assignsubmission_babelium', language 'es'
 *
 * @package   assignsubmission_babelium
 * @copyright Original from 2012 Babelium Project {@link http://babeliumproject.com} modified by Elurnet Informatika Zerbitzuak S.L  {@link http://elurnet.net/es} and Irontec S.L {@link https://www.irontec.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['default'] = 'Habilitado por defecto';
$string['default_help'] = 'Si se activa la opción, este método de entrega estará habilitado por omisión para todas las tareas nuevas.';
$string['enabled'] = 'Entrega Babelium';
$string['enabled_help'] = 'Si está habilitado, los estudiantes pueden grabar respuestas en vídeo y/o audio para sus entregas siguiendo el ejercicio planteado.';
$string['pluginname'] = 'Entrega Babelium';
$string['babelium'] = 'Entrega Babelium';
$string['loginfo'] = 'Babelium IdGrabación: {$a->responseid}, HashGrabación: {$a->responsehash}';

/*
$string['forcertmpt'] = 'Forzar RTMPT';
$string['forcertmpt_help'] = 'Activar esta opción fuerza al reproductor a usar RTMPT (RTMP encapsulado) por defecto. De otra manera, el reproductor primero intenta conectarse utilizando el puerto RTMP (1935)';
*/

$string['serverdomain'] = 'Dominio del servidor';
$string['serverdomain_help'] = 'El dominio del servidor Babelium en el que se guardaran tanto los ejercicios como las grabaciones';
/*
$string['serverport'] = 'Puerto del servidor';
$string['serverport_help'] = 'El número de puerto por el que se accede al servidor';
$string['apidomain'] = 'Dominio del API';
$string['apidomain_help'] = 'El dominio del API-RPC que permite al usuario acceder a los datos almacenados en el servidor Babelium';
*/
$string['apiendpoint'] = 'Endpoint del API';
$string['apiendpoint_help'] = 'Una ruta que pertenece al dominio del API y que se usa para gestionar todas las peticiones';

$string['newapiendpoint'] = 'Endpoint del API v3';
$string['newapiendpoint_help'] = 'Una ruta que pertenece al dominio del API v3 y que se usa para gestionar todas las peticiones';

$string['accesskey'] = 'Clave de acceso';
$string['accesskey_help'] = 'Una clave única que identifica todas las peticiones que el usuario realiza al API-RPC del servidor Babelium';
$string['secretaccesskey'] = 'Clave de acceso secreta';
$string['secretaccesskey_help'] = 'La clave secreta se utiliza para validar la identidad del usuario en las peticiones. Nunca debe usarse sin algún mecanismo encriptación en scripts locales';

$string['babeliumAvailableRecordableExercises'] = 'Ejercicios Babelium';
$string['babeliumAvailableRecordableExercises_help'] = 'Escoge el ejercicio que deseas asignar a tus estudiantes para su posterior evaluación';
$string['babeliumNoExerciseAvailable'] = 'No hay ejercicios disponibles';
$string['babeliumChooseRole']='Escoge un rol';
$string['babeliumChooseSubLang']='Escoge un idioma de subtítulos';
$string['babeliumChooseRecMethod']='Escoge un método de grabación';
$string['babeliumMicOnly']='Sólo micrófono';
$string['babeliumWebcamMic']='Cámara web y micrófono';
$string['babeliumStartRecording']='Empezar a Grabar';
$string['babeliumStopRecording']='Parar Grabación';
$string['babeliumViewRecording']='Ver Grabación';
$string['babeliumRecordAgain']='Grabar de Nuevo';
$string['babeliumViewExercise']='Ver Ejercicio';

//Error messages
$string['babeliumErrorConfigParameters']='Parámetros de configuración de Babelium no establecidos';
$string['babeliumApiErrorCode200']='Babelium Error 200. Respuesta malformada';
$string['babeliumApiErrorCode400']='Babelium Error 400. Petición malformada';
$string['babeliumApiErrorCode403']='Babelium Error 403. Credenciales de autorización incorrectas';
$string['babeliumApiErrorCode404']='Babelium Error 404. Método no encontrado. Parámetros no válidos';
$string['babeliumApiErrorCode500']='Babelium Error 500. Error interno del servidor';

