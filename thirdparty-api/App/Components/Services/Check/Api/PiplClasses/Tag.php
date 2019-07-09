<?php
namespace Check\Api\PiplClasses;
/**
 * Class Tag
 * @package Check\Api\PiplClasses
 *
 * A general purpose element that holds any meaningful string that's related to the person.

 * Used for holding data about the person that either couldn't be clearly classified or was
   classified as something different than the available
   data fields.
 */
class Tag extends Field{

    protected $attributes = ['classification'];
    protected $children = ['content'];

    function __construct(array $params=[]){
        extract($params);
        parent::__construct($params);

        // `content` is the tag itself, both `content` and `classification`
        // should be strings.
        if (!empty($content))
        {
            $this->content = $content;
        }
        if (!empty($classification))
        {
            $this->classification = $classification;
        }
    }

    /**
     * @return string
     */
    public function __toString():string {

        return $this->content;
    }
}

