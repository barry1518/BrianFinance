<?php
namespace Check\Api\PiplClasses;
/**
 * Class Ethnicity
 * @package Check\Api\PiplClasses
 *
 * An ethnicity field.
    The content will be a string with one of the following values (based on US census definitions)
    'white', 'black', 'american_indian', 'alaska_native',
    'chinese', 'filipino', 'other_asian', 'japanese',
    'korean', 'viatnamese', 'native_hawaiian', 'guamanian',
    'chamorro', 'samoan', 'other_pacific_islander', 'other'.
 */
class Ethnicity extends Field{

    protected $children = ['content'];

    /**
     * Ethnicity constructor.
     * @param array $params
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        // `content` is the ethnicity value.

        if (!empty($content)){
            $this->content = $content;
        }
    }

    /**
     * @return string
     */
    public function __toString():string {
        return $this->content ? ucwords(str_replace("_", " ", $this->content)) : "";
    }
}
