<?php
namespace Check\Api\PiplClasses;

/**
 * Class Gender
 * @package Check\Api\PiplClasses
 */
class Gender extends Field{

    protected $children = ['content'];

    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        // `content` is the gender value - "Male"/"Female"

        if (!empty($content)){
            $this->content = $content;
        }
    }

    public function __toString():string {

        return $this->content ? ucwords($this->content) : "";
    }
}
