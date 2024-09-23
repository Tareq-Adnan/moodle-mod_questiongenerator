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
 * TODO describe file questionbank
 *
 * @package    mod_questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require('../../config.php');
 require_login();
 
 $context = context_system::instance();
 $PAGE->set_context($context);
 
 global $DB, $OUTPUT, $PAGE;
 
 // Fetch categories from the database
 $categories = $DB->get_records('qg_categories', null, '', 'id, name');
 
 // Pass categories to the template
 $templatecontext = [
     'categories' => array_values($categories),
 ];
 
 $url = new moodle_url('/mod/questiongenerator/questionbank.php');
 $PAGE->set_url($url);
 $PAGE->set_heading(get_string('questionbank', 'questiongenerator'));
 $PAGE->set_title(get_string('questionbank', 'questiongenerator'));
 
 // Custom JS and CSS
 $PAGE->requires->js_call_amd('mod_questiongenerator/questiongenerator', 'init', array($categories));
 $PAGE->requires->js_call_amd('mod_questiongenerator/questiondifficulty', 'init', array($categories));

 $PAGE->requires->css('/mod/questiongenerator/css/style.css');
 
 echo $OUTPUT->header();
 echo $OUTPUT->render_from_template('mod_questiongenerator/questiongenerator', $templatecontext);
 echo $OUTPUT->footer();
 