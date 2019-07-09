<?php
namespace Check\Api\PiplClasses;
/**
 * Class Username
 * @package Check\Api\PiplClasses
 *
 * A username/screen-name associated with the person.

    Note that even though in many sites the username uniquely identifies one
    person it's not guarenteed, some sites allow different people to use the
    same username.
 */
class Username extends Field{

    protected $children = ['content'];

    function __construct(array $params=[])
    {
        extract($params);
        parent::__construct($params);

        // `content` is the username itself, it should be a string.
        if (!empty($content)){
            $this->content = $content;
        }
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the username is a valid username to search by.
     */
    public function is_searchable():bool {

        $st = !empty($this->content) ? $this->content : '';
        $clean = Utils::piplapi_alnum_chars($st);
        $func = function_exists("mb_strlen") ? "mb_strlen" : "strlen";
        return ($func($clean) >= 4);
    }

    /**
     * @return string
     */
    public function __toString():string {

        return $this->content;
    }
}