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
 * TODO describe module quiz_handle
 *
 * @module     mod_questiongenerator/quiz_handle
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
export const quizHandling = async (cmid) => { 
    let currentTab = 0;
    var next = document.getElementById("nextBtn");
    var prev = document.getElementById("prevBtn");
    var form = document.getElementById("quizForm");
    showTab(currentTab);

    function showTab(n) {
        let x = document.getElementsByClassName("step");
        x[n].style.display = "block";
        let progress = (n / (x.length - 1)) * 100;
        document.querySelector(".progress-bar").style.width = progress + "%";
        document.querySelector(".progress-bar").setAttribute("aria-valuenow", progress);
        document.getElementById("prevBtn").style.display = n == 0 ? "none" : "inline";
        document.getElementById("nextBtn").innerHTML = n == x.length - 1 ? "Submit" : "Next";
        if (n == x.length-1 ) {
            document.getElementById("nextBtn").setAttribute("type", "submit");
        } else {
            document.getElementById("nextBtn").setAttribute("type", "button");
        }
    }
        next.addEventListener("click", function () {
        nextPrev(1);
    });
    prev.addEventListener("click", function () {
        nextPrev(-1);
    });

    function nextPrev(n) {
        let x = document.getElementsByClassName("step");
        if (n == 1 && !validateForm()) return false;
        x[currentTab].style.display = "none";
        currentTab += n;
        if (currentTab >= x.length) {
            // document.getElementById("quizForm").submit();
        }else {
            showTab(currentTab);
        }
        
    }

    $(document).on('submit','#quizForm', (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        console.log(formData);
       
    })

    function validateForm() {
        let valid = false;
        let x = document.getElementsByClassName("step");
        let y = x[currentTab].getElementsByTagName("input");
        for (var i = 0; i < y.length; i++) {
            if (y[i].type === "radio" && y[i].checked) {
                valid = true;
                break;
            }
        }
        return valid;
    }
}