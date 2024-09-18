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
 * TODO describe module prompt_handling
 *
 * @module     mod_questiongenerator/prompt_handling
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { call as getContent } from "core/ajax";
import promptModal from './prompt_modal';
export const promptHandling = (cmid) => {

    var form = $('#prompt-form');
   
    form.on('submit', function (e) {
        e.preventDefault();
        let formdata = new FormData(this);
        let prompt = formdata.get('prompt');
        // $('#qg-spinner').show();
        // $(this).find('button[type="submit"]').prop('disabled', true);
        // const modal = new promptModal();
        // modal.show();

        getContent([{
            methodname: 'mod_questiongenerator_submit_prompts',
            args: { prompt: prompt },
        }])[0].done(response => {
            console.log(response);
        }).fail(error => {
            throw new Error( error.message);
        });
    });

    
}

