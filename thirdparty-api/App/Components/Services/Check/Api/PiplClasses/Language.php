<?php
namespace Check\Api\PiplClasses;
/**
 * Class Language
 * @package Check\Api\PiplClasses
 *
 * A language the person is familiar with.
 */
class Language extends Field{

    protected $children = ['language', "region", "display"];

    /**
     * Language constructor.
     * @param array $params
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        // `language` is the language code itself. For example "en"
        // `region` is the language region. For example "US"
        // `display` is a display value. For example "en_US"

        if (!empty($language)){
            $this->language = $language;
        }
        if (!empty($display)){
            $this->display = $display;
        }
        if (!empty($region)){
            $this->region = $region;
        }
    }
}


