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
 * This file contains the version information for the babelium submission plugin
 *
 * @package    assignsubmission_babelium
 * @copyright  2013 Babelium Project {@link http://babeliumproject.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2013072900;
$plugin->release   = '0.9.7 (Build: 2013072900)';

// Moodle 2.4 changed the header of the function get_files() 
// of mod/assign/assignmentplugin.php  after its initial release to fix 
// an issue. The Babelium plugin requires you to use a Moodle 2.4 version
// newer than this change to avoid strict check warnings that could arise
// from checking this function's header. See Moodle commits: 
//    f6d09222a2ec0208a280558171da1affd226ff52 (change in the function header)
//    322af447f5f0fa1cb83ea80e322e7b26bddecdc1 (weekly release after the change)
$plugin->requires  = 2012120300.05;

$plugin->component = 'assignsubmission_babelium';
$plugin->cron      = 0;
$plugin->maturity  = MATURITY_BETA;
