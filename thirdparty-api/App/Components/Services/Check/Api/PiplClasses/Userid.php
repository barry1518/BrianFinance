<?php
namespace Check\Api\PiplClasses;
/**
 * Class Userid
 * @package Check\Api\PiplClasses
 *
 * An ID associated with a person.
 *
    The ID is a string that's used by the site to uniquely identify a person,
    it's guaranteed that in the site this string identifies exactly one person.
 */
class Userid extends Field{

    protected $children = ['content'];

    /**
     * Userid constructor.
     * @param array $params
     */
    function __construct(array $params=[]){
        extract($params);
        parent::__construct($params);

        // `content` is the ID itself, it should be a string.
        if (!empty($content)){
            $this->content = $content;
        }
    }

    /**
     * @return bool
     */
    public function is_searchable():bool {

        return (!empty($this->content)) && preg_match('/(.)@(.)/', $this->content);
    }

    /**
     * @return string
     */
    public function __toString():string {

        return $this->content;
    }
}

