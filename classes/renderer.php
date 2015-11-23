<?php

/**
 * This file contains the definition for the library class for babelium renderer
 *
 * @package   assignsubmission_babelium
 * @copyright 2015 Inko Perurena <inko@elurnet.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the babelium submission plugin.
 *
 * @package assignsubmission_babelium
 * @copyright 2015 Inko Perurena <inko@elurnet.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignfeedback_editpdf_renderer extends plugin_renderer_base {

  /**
   * Render the babelium widget in the submission form.
   *
   * @param assignsubmission_babelium_widget $widget - Renderable widget containing assignment and user.
   * @return string
   */
  public function render_assignsubmission_babelium_widget(assignsubmission_babelium_widget $widget) {
    global $CFG;

    $html = '';

    //Tell the user this module requires Javascript to work
    $html .= html_writer::div(get_string('jsrequired', 'assignsubmission_babelium'), 'hiddenifjs');

    html_writer::tag('h2',$widget->title,array('id'=>'babelium-exercise-title'));
    html_writer::div('flashContent');
    html_writer::tag('p',$text);
    html_writer::script('var pageHost = ((document.location.protocol == "https:") ? "https://" : "http://"); 
    document.write("<a href=\'http://www.adobe.com/go/getflashplayer\'><img src=\'"
        + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif\' alt=\'Get Adobe Flash player\' /></a>" ); ');
    html_writer::script(null,$swfobjecturl);

    $this->page->requires->yui_module('moodle-assignsubmission_babelium-service',
                                      'M.assignsubmission_babelium.service.init',
                                      $initparams);
    $this->page->requires->strings_for_js(array(
      'viewrecording',
      'viewexercise',
      'startrecording',
      'stoprecording'
    ), 'assignsubmission_babelium');

    return $html;
  }
}
