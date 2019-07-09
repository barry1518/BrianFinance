<?php
namespace Check\Api\PiplClasses;
/**
 * Class Url
 * @package Check\Api\PiplClasses
 * A URL that's related to a person. Can either be a source of data about the person, or a URL otherwise related to the person.
 */
class Url extends Field{

    protected $attributes = ['category', 'sponsored', 'source_id', 'name', 'domain'];
    protected $children = ['url'];

    function __construct(array $params=[]){
        extract($params);
        parent::__construct($params);
        if (!empty($url)){
            $this->url = $url;
        }
        if (!empty($category)){
            $this->category = $category;
        }
        if (!empty($source_id)){
            $this->source_id = $source_id;
        }
        if (!empty($name)){
            $this->name = $name;
        }
        if (!empty($domain)){
            $this->domain = $domain;
        }
        if (!empty($sponsored)){
            $this->sponsored = $sponsored;
        }
    }

    /**
     * @return bool
     * A bool value that indicates whether the URL is a valid URL.
     */
    public function is_valid_url():bool {

        return (!empty($this->url) && Utils::piplapi_is_valid_url($this->url));
    }

    /**
     * @return bool
     */
    public function is_searchable():bool {

        return (!empty($this->url));
    }

    /**
     * @return string
     */
    public function __toString():string {

        return $this->url ? $this->url : $this->name;
    }
}

