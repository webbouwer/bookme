<?php

require 'config/config.php';
require_once 'ics.php';


$events = hello_bookme_import_ics($icsurl);
$events_array = array();

if ($events) {
    foreach ($events as $event) {
        $start = date('c', strtotime($event['DTSTART;TZID=Europe/Amsterdam']));
        $end = date('c', strtotime($event['DTEND;TZID=Europe/Amsterdam']));
        $events_array[] = array(
            'title' => $event['SUMMARY'],
            'start' => $start,
            'end' => $end
        );
    }
}

// Initialize array for available slots
$available_slots = array();

// Set start date to 2 days from now
$start_date = new DateTime();
$start_date->modify('+2 days');

// Set end date to 6 months from start
$end_date = clone $start_date;
$end_date->modify('+6 months');

// Loop through each day
while ($start_date <= $end_date) {
    // Skip Sundays (7 is Sunday)
    if ($start_date->format('N') != 7) {
        // Define the two time slots for this day
        $slot1_start = $start_date->format('Y-m-d') . ' 10:30:00';
        $slot1_end = $start_date->format('Y-m-d') . ' 12:30:00';
        $slot2_start = $start_date->format('Y-m-d') . ' 13:30:00';
        $slot2_end = $start_date->format('Y-m-d') . ' 15:30:00';

        // Check if slot 1 is available
        $slot1_available = true;
        foreach ($events_array as $event) {
            if (strtotime($event['start']) < strtotime($slot1_end) && 
                strtotime($event['end']) > strtotime($slot1_start)) {
                $slot1_available = false;
                break;
            }
        }

        // Check if slot 2 is available
        $slot2_available = true;
        foreach ($events_array as $event) {
            if (strtotime($event['start']) < strtotime($slot2_end) && 
                strtotime($event['end']) > strtotime($slot2_start)) {
                $slot2_available = false;
                break;
            }
        }

        // Add available slots to array
        if ($slot1_available) {
            $available_slots[] = array(
                'title' => 'Ochtend',
                'description' => 'Ochtend',
                'start' => date('c', strtotime($slot1_start)),
                'className'=> 'available',
                'end' => date('c', strtotime($slot1_end)),
            );
        }

        if ($slot2_available) {
            $available_slots[] = array(
                'title' => 'Middag',
                'description' => 'Middag',
                'start' => date('c', strtotime($slot2_start)),
                'className'=> 'available',
                'end' => date('c', strtotime($slot2_end)),
            );
        }
    } 
    $start_date->modify('+1 day');
}

$events = $available_slots;
$events_json = json_encode($events);

function hasAvailableSlot($date, $events) {
    foreach ($events as $event) {
        if (strpos($event['start'], $date) === 0) {
            return true;
        }
    }
    return false; 
}

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
    <h1>Afspraak Aanvragen</h1>
    <div id="formcontainer">
        <div id="stepNavigator">
            <span class="stepNav active" data-step="1">Stap 1: Selecteer Datum</span>
            <span class="stepNav" data-step="2">Stap 2: Selecteer Tijdslot</span>
            <span class="stepNav" data-step="3">Stap 3: Vul Gegevens In</span>
            <span class="stepNav" data-step="4">Stap 4: Controleer Aanvraag</span>
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
            <h2>Vul Uw Gegevens In</h2>
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
                    <label for="city">Stad:</label>
                    <input type="text" id="city" name="city" required>
                </div>
                <div class="datafield">
                    <label for="size">Grootte:</label>
                    <input type="text" id="size" name="size" required>
                </div>
                <div class="datafield">
                    <label for="info">Aanvullende Informatie:</label>
                    <textarea id="info" name="info"></textarea>
                </div>
                <div class="datafield">
                    <button type="button" id="backToStep2">ander moment</button>
                    <button type="button" id="toStep4">aanvraag controleren</button>
                </div>
            </form>
        </div>
        <div id="step4" class="step">
            <h2>Controleer Uw Aanvraag</h2>
            <div id="review"></div>
            <button id="backToStep3">aanpassen</button>
            <button id="submitBooking">Verzenden</button>
        </div>
        <div id="step5" class="step">
            <h2>Bedankt voor Uw Aanvraag!</h2>
            <p id="confirmation">Uw aanvraag is ontvangen. Wij nemen spoedig contact met u op.</p>
            <!-- <button id="backToStep1">Boek een ander tijdslot</button> --> 
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            var calendarEl = document.getElementById('bookingcalendar');
            var selectedDate, selectedSlot, formData;

            var events = <?php echo $events_json; ?>;
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
                    $('#review').html(`
                        <p>Datum: ${selectedDate}</p>
                        <p>Tijdslot: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                        ${formData.map(field => `<p>${field.name.charAt(0).toUpperCase() + field.name.slice(1)}: ${field.value}</p>`).join('')}
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
                    info: formData.find(field => field.name === 'info').value,
                    date: selectedDate,
                    slot: {
                        title: selectedSlot.title,
                        start: selectedSlot.start,
                        end: selectedSlot.end
                    }
                }, function(response) {
                    $('#confirmation').html(`
                        <p>Bedankt voor uw aanvraag!</p>
                        <p>E-mail: ${formData.find(field => field.name === 'email').value}</p>
                        <p>Telefoon: ${formData.find(field => field.name === 'telephone').value}</p>
                        <p>Datum: ${selectedDate}</p>
                        <p>Tijdslot: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
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