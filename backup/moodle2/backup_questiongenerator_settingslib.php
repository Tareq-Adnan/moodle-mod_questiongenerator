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
 * Plugin custom settings are defined here.
 *
 * @package     mod_questiongenerator
 * @category    backup
 * @copyright   2024 Tarekul Islam <tarekul.islam@brainstation-23.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * backup_questiongenerator_settingslib
 */
class backup_questiongenerator_settingslib extends backup_activity_task {

    // Define particular settings for the backup process.
    /**
     * Method define_my_settings
     *
     * @return void
     */
    protected function define_my_settings() {
        // Example setting: no specific settings for this activity.
    }

    // Define additional steps for the backup process.
    /**
     * Method define_my_steps
     *
     * @return void
     */
    protected function define_my_steps() {
        $this->add_step(new backup_questiongenerator_activity_structure_step('questiongenerator_structure',
                        'questiongenerator.xml'));
    }
}
