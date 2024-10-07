<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_questiongenerator configuration form.
 *
 * @package     mod_questiongenerator
 * @copyright   2024 Tarekul Islam <tarekul.islam@brainstation-23.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
use core_grades\component_gradeitems;
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/questiongenerator/lib.php');
/**
 * Module instance settings form.
 *
 * @package     mod_questiongenerator
 * @copyright   2024 Tarekul Islam <tarekul.islam@brainstation-23.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_questiongenerator_mod_form extends moodleform_mod {


    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $COURSE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('questiongeneratorname', 'mod_questiongenerator'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $itemnumber = 0;
        $component = "mod_questiongenerator";
        $gradefieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');
        $isupdate = !empty($this->_cm);
        $gradeoptions = [
        'isupdate' => $isupdate,
        'currentgrade' => false,
        'hasgrades' => false,
        'canrescale' => $this->_features->canrescale,
        'useratings' => $this->_features->rating,
        ];
        $mform->addElement('header', 'modstandardgrade', get_string('gradenoun'));

        // If supports grades and grades arent being handled via ratings.
        if ($isupdate) {
            $gradeitem = grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $this->_cm->modname,
            'iteminstance' => $this->_cm->instance,
            'itemnumber' => 0,
            'courseid' => $COURSE->id,
            ]);
            if ($gradeitem) {
                $gradeoptions['currentgrade'] = $gradeitem->grademax;
                $gradeoptions['currentgradetype'] = $gradeitem->gradetype;
                $gradeoptions['currentscaleid'] = $gradeitem->scaleid;
                $gradeoptions['hasgrades'] = $gradeitem->has_grades();
            }
        }
        $mform->addElement('modgrade', $gradefieldname, get_string('gradenoun'), $gradeoptions);
        $mform->addHelpButton($gradefieldname, 'modgrade', 'grades');
        $mform->setDefault($gradefieldname, $CFG->gradepointdefault);

        // Grading method.
        $mform->addElement(
        'select',
        'grademethod',
        get_string('grademethod', 'mod_questiongenerator'),
        questiongenerator_get_grading_options()
        );

        // $this->standard_grading_section_elements();
        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }
}
