<?php
namespace Check\Api\PiplClasses;
/**
 * Class Phone
 * @package Check\Api\PiplClasses
 *
 * A phone number of a person.
 */
class Phone extends Field{

    protected $attributes = ['type'];
    protected $children = ['country_code', 'number', 'extension', 'raw', 'display', 'display_international'];
    protected $types_set = ['mobile', 'home_phone', 'home_fax', 'work_phone', 'work_fax', 'pager'];

    /**
     * Phone constructor.
     * @param array $params
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        // `country_code`, `number` and `extension` should all be int/long.
        // `type` is one of Phone::$types_set.
        if (!empty($country_code)){
            $this->country_code = $country_code;
        }
        if (!empty($number)){
            $this->number = $number;
        }
        if (!empty($extension)){
            $this->extension = $extension;
        }
        if (!empty($raw)){
            $this->raw = $raw;
        }
        if (!empty($type)){
            $this->type = $type;
        }
        if (!empty($display)){
            $this->display = $display;
        }
        if (!empty($display_international)){
            $this->display_international = $display_international;
        }
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the phone is a valid phone to search by.
     */
    public function is_searchable():bool {

        return (!empty($this->raw) || (!empty($this->number) && (empty($this->country_code) || $this->country_code == 1)));
    }

    /**
     * @param $text
     * @return Phone
     */
    public static function from_text($text):Phone{

        return new Phone(['raw' => $text]);
    }
}

