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
import { call as SubmitForm } from 'core/ajax'
import $ from 'jquery';
export const quizHandling = async (cmid, quizid, marks) => {
    let currentTab = 0;
    var next = document.getElementById("nextBtn");
    var prev = document.getElementById("prevBtn");
    var form = document.getElementById("quizForm");
    showTab(currentTab);


    function showTab(n) {
        let x = document.getElementsByClassName("step");
        $('#formSpinner').hide();
        x[n].style.display = "block";
        let progress = (n / (x.length - 1)) * 100;
        document.querySelector(".progress-bar").style.width = progress + "%";
        document.querySelector(".progress-bar").setAttribute("aria-valuenow", progress);
        document.getElementById("prevBtn").style.display = n == 0 ? "none" : "inline";
        document.getElementById("nextBtn").innerHTML = n == x.length - 1 ? "Submit" : "Next";
        // if (n == x.length) {
        //     document.getElementById("nextBtn").setAttribute("type", "submit");
        // } else {
        //     document.getElementById("nextBtn").setAttribute("type", "button");
        // }
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
            document.getElementById("nextBtn").setAttribute("type", "submit");
        } else {
            showTab(currentTab);
        }

    }

    $(document).on('submit', '#quizForm', (event) => {
        event.preventDefault();
        next.innerHTML = `<span class="spinner-border text-light" role="status" style='height:1.5rem !important; width:1.5rem !important;vertical-align:middle !important'>
                            </span>`;
       $('#nextBtn').prop('disabled', true);

      
        let formDataAssocArray = [];
        $('#quizForm input[type="radio"]:checked').each(function() {
            const questionId = $(this).attr('name').split('_')[1]; 
            const answerIndex = $(this).val(); 
            const refValue = $(this).data('ref'); 
            const type = $(this).data('type')
    
            // Push the relevant data to the array
            formDataAssocArray.push({
                answer: answerIndex,
                question: questionId,
                ref: refValue,
                type:type.toLowerCase()
            });
        });

      
        SubmitForm([{
            methodname: 'mod_questiongenerator_end_quiz',
            args: {
                quiz_data: formDataAssocArray,
                cmid: cmid,
                quizid: parseInt(quizid),
                marks: marks,
            },
        }])[0].done(response => {
            console.log(response);
            if (response.status) {
                $('#quizContainer').html(createThankYouMessage(response.correct_ans,response.wrong,response.total,response.rawmark));
                setInterval(() => {
                    $('#redirect-text').append('.');
                }, 1000);
                setTimeout(function () {
                    window.location.href = response.redirect;
                }, 5000);
            } else {
                $('#quizContainer').html('<p class="text-danger text-center bold">Something went wrong! Please try again later.</p> <small id=\'redirect-text\'></small>');
                setInterval(() => {
                    $('#redirect-text').append('.');
                }, 1000);
                setTimeout(function () {
                    window.location.href = response.redirect;
                }, 5000);
            }
        }).fail(error => {
            console.error('Error:', error.message);

        });


    })

    function createThankYouMessage(correct,wrong,mark,totalmark) {

        return `
        <div class="success-checkmark">
            <div class="check-icon">
                <span class="icon-line line-tip"></span>
                <span class="icon-line line-long"></span>
                <div class="icon-circle"></div>
                <div class="icon-fix"></div>
            </div>
        </div>
        <div class='text-center'>
        <h4 style="color: #008304;">Quiz Finished!</h4>
       <div class="alert alert-secondary d-flex justify-content-between align-items-center p-3">
    <div class='mx-auto'>
        <p class="mb-1">
            <span class='text-success'>You got <strong>${mark}</strong> out of <strong>${totalmark}</strong> marks.</span>
        </p>
        <p class="mb-1">
            <span><strong class=' text-success'>Correct:</strong> ${correct}, <strong class=' text-warning'>Wrong:</strong> ${wrong}</span>
        </p>
        <a class='text-center' style='font-weight:400' href='grade.php?id=${cmid}'> See in Details</a>
    </div>
    <div>
        <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
    </div>
</div>
<small id='redirect-text' class="text-muted d-block text-center mt-3">Will be redirecting to course page</small>

    `;
    }

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