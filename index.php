<?php
/* 
 * This script is a simplyfied booking form that allows users to select a date and time slot for an appointment.
 * It checks the availability of time slots based on events from an ICS file and displays them in a calendar view.
 * Users can select a date and time slot, enter their details, review the booking, and submit the form.
 * The form data is sent via AJAX to a PHP script that sends an email with the booking details.
 */

require 'config/config.php';
require 'calendar.php';
$bookme = new calendarAvailabillity($icsurl);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afspraken Aanvragen</title>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <link rel="stylesheet" href="booking.css">
</head>
<body>
    <div id="bookmebox">
        <h1>Afspraak Aanvragen</h1>
        <div id="formcontainer">
            <div id="stepNavigator">
                <span class="stepNav active" data-step="1">Stap 1: Datum & tijd</span>
                <span class="stepNav" data-step="2">Stap 2: Tijdslot</span>
                <span class="stepNav" data-step="3">Stap 3: Gegevens</span>
                <span class="stepNav" data-step="4">Stap 4: Controleer</span>
                <span class="stepNav" data-step="5">Stap 5: Bevestiging</span>
            </div>
            <div id="step1" class="step active">
                <div id="bookingcalendar"></div>
            </div>
            <div id="step2" class="step">
                <h2>Selecteer een Tijdslot</h2>
                <div id="timeslots"></div>
                <button id="backToStep1Step2">andere datum</button>
            </div>
            <div id="step3" class="step">
                <h2>Vul jou gegevens in</h2>
                <p style="font-size:0.8em;">Ingevulde gegevens worden alleen via email verstuurd en alleen gebruikt voor contact met jou en voor de voorbereiding van de afspraak.</p>
                <form id="bookingForm">
                    <div class="datafield">
                        <label for="name">Naam:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="datafield">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="datafield">
                        <label for="telephone">Telefoon:</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    <div class="datafield">
                        <label for="city">Plaats:</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="datafield">
                        <label for="size">Kledingmaat:</label>
                        <select id="size" name="size" required>
                            <option value="32">32</option>
                            <option value="34">34</option>
                            <option value="36">36</option>
                            <option value="38" selected>38</option>
                            <option value="40">40</option>
                            <option value="42">42</option>
                            <option value="plussize">plussize</option>
                        </select>
                    </div>
                    <div class="datafield">
                        <label for="bezoekers">Hoeveel mensen neem je mee?</label> 
                        <select id="bezoekers" name="bezoekers" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="datafield">
                        <label for="info">Aanvullende informatie:</label>
                        <textarea id="info" name="info" placeholder="bv. dat je zwanger bent of beperkingen hebt waar wij rekening mee kunnen houden. Ook als je liever wat eerder of later zou willen komen kun je dat hier aangeven"></textarea>
                    </div>
                    <div class="datafield">
                        <button type="button" id="backToStep2">ander moment</button>
                        <button type="button" id="toStep4">aanvraag controleren</button> 
                    </div>
                    
                </form>
            </div>
            <div id="step4" class="step">
                <h2>Controleer de aanvraag</h2>
                <div id="review"></div>
                <button id="backToStep3">aanpassen</button>
                <button id="submitBooking">Verzenden</button>
            </div>
            <div id="step5" class="step">
                <h2>Bedankt voor de aanvraag!</h2>
                <p id="confirmation">De aanvraag is ontvangen, wij nemen spoedig contact op.</p>
                <!-- <button id="backToStep1">Boek een ander tijdslot</button> --> 
            </div>
        </div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            var calendarEl = document.getElementById('bookingcalendar');
            var selectedDate, selectedSlot, formData;
            var events = <?php $bookme->getSlots(); //echo $events_json; ?>;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'UTC',
                initialView: 'dayGridMonth',
                locale: 'nl',
                height: 'auto',
                allDaySlot: false,
                displayEventTime: true,
                displayEventEnd: true,
                events: events,
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'today'
                },
                footerToolbar: {
                    left: 'dayGridMonth,listWeek',
                    center: '',
                    right: 'prev,next'
                },
                dateClick: function(info) {
                    if (hasAvailableSlot(info.dateStr)) {
                        selectedDate = info.dateStr;
                        selectedSlot = false;
                        $('#step1').removeClass('active');
                        $('#step2').addClass('active');
                        updateStepNavigator(2);
                        loadTimeSlots(selectedDate);
                    }
                },
                eventClick: function(info) {
                    selectedDate = info.event.startStr.split('T')[0];
                    selectedSlot = {
                        title: info.event.title,
                        start: info.event.startStr,
                        end: info.event.endStr
                    };
                    $('#step1').removeClass('active');
                    $('#step2').addClass('active');
                    updateStepNavigator(2);
                    loadTimeSlots(selectedDate);
                },
                dayRender: function(info) {
                    if (!hasAvailableSlot(info.dateStr)) {
                        $(info.el).addClass('fc-disabled');
                    }
                },
                windowResize: function(view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('listWeek');
                    } else {
                        calendar.changeView('dayGridMonth');
                    }
                }
            });
            calendar.render();
            // Initial view check for responsive display
            if (window.innerWidth < 768) {
                calendar.changeView('listWeek');
            }
            // Check if there are clickable days or timeslots in the current view
            function hasClickableDaysOrSlots() {
                var view = calendar.view;
                var hasClickable = false;
                var currentDate = view.activeStart;
                while (currentDate < view.activeEnd) {
                    if (hasAvailableSlot(currentDate.toISOString().split('T')[0])) {
                        hasClickable = true;
                        break;
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                return hasClickable;
            }
            function checkAndMoveToNext() {
                if (!hasClickableDaysOrSlots()) {
                    calendar.next();
                    setTimeout(checkAndMoveToNext, 500); // Check again after moving to the next period
                }
            }
            checkAndMoveToNext(); // Initial check and move if necessary
            // Check and move to next if no clickable events on window resize
            $(window).resize(function() {
                checkAndMoveToNext();
            });
            function loadTimeSlots(date) {
                $('#timeslots').empty();
                var dutchDate = new Date(date).toLocaleDateString('nl-NL', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                $('#timeslots').append('<h3>' + dutchDate + '</h3><div class="timeslotbar"></div>');
                events.forEach(function(slot) {
                    if (slot.start.startsWith(date)) {
                        var button = $('<button>')
                            .text(slot.title + ' (' + slot.start.split('T')[1].substring(0, 5) + ' - ' + slot.end.split('T')[1].substring(0, 5) + ')')
                            .data('slot', slot)
                            .click(function() {
                                selectedSlot = $(this).data('slot');
                                $('#bookingForm .slotinfo').remove();
                                $('#bookingForm').prepend('<div class="slotinfo">' + dutchDate + ' - ' + selectedSlot.title + ' (' + selectedSlot.start.split('T')[1].substring(0, 5) + ' - ' + selectedSlot.end.split('T')[1].substring(0, 5) + ')</div>');
                                $('#timeslots button').removeClass('selected');
                                $(this).addClass('selected');
                                $('#step2').removeClass('active');
                                $('#step3').addClass('active');
                                updateStepNavigator(3);
                            });
                        if (selectedSlot && (selectedSlot.start.split('T')[1].substring(0, 5) === slot.start.split('T')[1].substring(0, 5))) {
                            button.addClass('selected');
                        }
                        $('#timeslots').find('.timeslotbar').append(button);
                    }
                });
            }
            function hasAvailableSlot(date) {
                return events.some(function(event) {
                    return event.start.startsWith(date);
                });
            }
            function updateStepNavigator(step) {
                $('.stepNav').removeClass('done active');
                $('.stepNav').each(function(index) {
                    if (index + 1 < step) {
                        $(this).addClass('done');
                    } else if (index + 1 === step) {
                        $(this).addClass('active');
                    }
                });
            }
            $('.stepNav').click(function() {
                var step = $(this).data('step');
                if ($(this).hasClass('done') || $(this).hasClass('active')) {
                    $('.step').removeClass('active');
                    $('#step' + step).addClass('active');
                    updateStepNavigator(step);
                }
            });
            $('#backToStep1Step2').click(function() {
                $('#step2').removeClass('active');
                $('#step1').addClass('active');
                updateStepNavigator(1);
            });
            $('#backToStep2').click(function() {
                $('#step3').removeClass('active');
                $('#step2').addClass('active');
                updateStepNavigator(2);
            });
            $('#toStep4').click(function() {
                var valid = true;
                $('#bookingForm input[required], #bookingForm textarea[required]').each(function() {
                    if (!this.value) {
                        $(this).addClass('error');
                        valid = false;
                    } else {
                        $(this).removeClass('error');
                    }
                });
                // Additional validation for specific fields
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                var telephonePattern = /^[0-9\-\+\s\(\)]+$/;
                if (!emailPattern.test($('#email').val())) {
                    $('#email').addClass('error');
                    valid = false;
                } else {
                    $('#email').removeClass('error');
                }
                if (!telephonePattern.test($('#telephone').val())) {
                    $('#telephone').addClass('error');
                    valid = false;
                } else {
                    $('#telephone').removeClass('error');
                }
                if (valid) {
                    formData = $('#bookingForm').serializeArray();
                    let infotext = formData.find(field => field.name === 'info').value;
                    if( infotext == '' ){
                         infotext = 'n.v.t.';
                    }
                    $('#review').html(`
                        <p>Datum: ${selectedDate}</p>
                        <p>Tijdslot: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                        <p>Naam: ${formData.find(field => field.name === 'name').value}</p>
                        <p>E-mail: ${formData.find(field => field.name === 'email').value}</p>
                        <p>Telefoon: ${formData.find(field => field.name === 'telephone').value}</p>
                        <p>Plaats: ${formData.find(field => field.name === 'city').value}</p>
                        <p>Kledingmaat: ${formData.find(field => field.name === 'size').value}</p>
                        <p>Aantal bezoekers: ${formData.find(field => field.name === 'bezoekers').value}</p>
                        <p>Aanvullende Informatie: ${infotext}</p>
                    `);
                    $('#step3').removeClass('active');
                    $('#step4').addClass('active');
                    updateStepNavigator(4);
                }
            });
            $('#backToStep3').click(function() {
                $('#step4').removeClass('active');
                $('#step3').addClass('active');
                updateStepNavigator(3);
            });
            $('#submitBooking').click(function() {
                $.post('send_email.php', {
                    name: formData.find(field => field.name === 'name').value,
                    email: formData.find(field => field.name === 'email').value,
                    telephone: formData.find(field => field.name === 'telephone').value,
                    city: formData.find(field => field.name === 'city').value,
                    size: formData.find(field => field.name === 'size').value,
                    bezoekers: formData.find(field => field.name === 'bezoekers').value,
                    info: formData.find(field => field.name === 'info').value,
                    date: selectedDate,
                    slot: {
                        title: selectedSlot.title,
                        start: selectedSlot.start,
                        end: selectedSlot.end
                    }
                }, function(response) {
                    $('#confirmation').html(`
                        <p>Naam: ${formData.find(field => field.name === 'name').value}</p>
                        <p>E-mail: ${formData.find(field => field.name === 'email').value}</p>
                        <p>Telefoon: ${formData.find(field => field.name === 'telephone').value}</p>
                        <p>Datum: ${selectedDate}</p>
                        <p>Tijdslot: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                        <p>Aantal bezoekers: ${formData.find(field => field.name === 'bezoekers').value}</p>
                    `);
                    $('#step4').removeClass('active');
                    $('#step5').addClass('active');
                    updateStepNavigator(5);
                });
            });
            $('#backToStep1').click(function() {
                $('#step5').removeClass('active');
                $('#step1').addClass('active');
                updateStepNavigator(1); 
            });
        });
    </script>
</body>
</html>