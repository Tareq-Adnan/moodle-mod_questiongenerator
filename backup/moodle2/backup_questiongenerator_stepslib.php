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
 * Backup steps for mod_questiongenerator are defined here.
 *
 * @package     mod_questiongenerator
 * @category    backup
 * @copyright   2024 Tarekul Islam <tarekul.islam@brainstation-23.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// More information about the backup process: {@link https://docs.moodle.org/dev/Backup_API}.
// More information about the restore process: {@link https://docs.moodle.org/dev/Restore_API}.

/**
 * Define the complete structure for backup, with file and id annotations.
 */
class backup_questiongenerator_activity_structure_step extends backup_activity_structure_step {

    /**
     * Method define_structure
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        // Define the main element describing the questiongenerator instance.
        $questiongenerator = new backup_nested_element('questiongenerator', ['id'], [
            'course', 'name', 'intro', 'introformat', 'legacyfiles', 'legacyfileslast',
            'display', 'displayoptions', 'revision', 'timemodified',
        ]);

        // Build the tree.

        // Define sources.
        $questiongenerator->set_source_table('questiongenerator', ['id' => backup::VAR_ACTIVITYID]);
            // Define id annotations.

            // Define file annotations.
        $questiongenerator->annotate_files('mod_questiongenerator', 'intro', null);
            // Return the root element (questiongenerator), wrapped into standard activity structure.
            return $this->prepare_activity_structure($questiongenerator);
    }
}
