<?php

class ICS {
    private $ics_content;
    public function __construct($ics_content) {
        $this->ics_content = $ics_content;
    }
    public function events() {
        $lines = explode("\n", $this->ics_content);
        $events = array();
        $event = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === 'BEGIN:VEVENT') {
                $event = array();
            } elseif ($line === 'END:VEVENT') {
                $events[] = $event;
            } else {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $event[$key] = $value;
                }
            }
        }
        return $events; 
    }
}

function hello_bookme_import_ics($ics_url) {
    if (empty($ics_url)) {
        return 'No ICS URL provided.';
    }
    // Fetch the ICS file content
    $ics_content = @file_get_contents($ics_url);
    if ($ics_content === false) {
        return 'Unable to fetch ICS file. Please check the URL and try again.';
    }
    // Parse the ICS file
    $ics = new ICS($ics_content);
    $events = $ics->events();
    return $events;
}

