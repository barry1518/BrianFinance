<?php
namespace Check\Api\PiplClasses;

/**
 * Class OriginCountry
 * @package Check\Api\PiplClasses
 *
 * An origin country of the person.
 */
class OriginCountry extends Field{

    protected $children = ['country'];

    /**
     * OriginCountry constructor.
     * @param array $params
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        // `country` is a two letter country code.

        if (!empty($country)){
            $this->country = $country;
        }
    }

    /**
     * @return string
     */
    public function __toString():string {

        if (!empty($this->country)){
            $uppedcoutnry = strtoupper($this->country);
            return array_key_exists($uppedcoutnry, Utils::$piplapi_countries) ?
                Utils::$piplapi_countries[$uppedcoutnry] : NULL;
        }
        return "";
    }
}