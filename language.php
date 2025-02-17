<?php 

class Language{

    public $trans;

    public static $instance;

    public function __construct($langjson){

        $this->trans = $langjson;

        self::$instance = $this;

    }

    static function translate($key){

        $txt = self::$instance->trans[$key];

        if (strpos($txt, "%n") != false) {

            $text = str_replace("%n", "\n", $txt);

        } else {

            $text = $txt;

        }

        return $text;

    }

}

function loadLang($languageCode = false) {

    if (!$languageCode) {

        $languageCode = 'en_EN';

    }

    $langJson = json_decode(file_get_contents("languages/{$languageCode}.json"), true);

    new Language($langJson);

}