<?php
namespace Check\Api\PiplClasses;
/**
 * Class Address
 * @package Check\Api\PiplClasses
 *
 * An address of a person.
 */
class Address extends Field{

    protected $attributes = ['type'];
    protected $children = ['country', 'state', 'city', 'po_box', 'zip_code', 'street', 'house', 'apartment', 'raw', 'display'];
    protected $types_set = ['home', 'work', 'old'];

    /**
     * Address constructor.
     * @param array $params
     *
        `country`, `state`, `city`, `po_box`, `zip_code`, `street`, `house`, `apartment`,
        `raw`, `type`, should all be strings.

        `country` and `state` are country code (like "US") and state code
        (like "NY"), note that the full value is available as
        address.country_full and address.state_full.

        `raw` is an unparsed address like "123 Marina Blvd, San Francisco,
        California, US", usefull when you want to search by address and don't
        want to work hard to parse it.
        Note that in response data there's never address.raw, the addresses in
        the response are always parsed, this is only for querying with
        an unparsed address.

        `type` is one of Address::$types_set.
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        if (!empty($country)){
            $this->country = $country;
        }
        if (!empty($state)){
            $this->state = $state;
        }
        if (!empty($city)){
            $this->city = $city;
        }
        if (!empty($po_box)){
            $this->po_box = $po_box;
        }
        if (!empty($zip_code)){
            $this->zip_code = $zip_code;
        }
        if (!empty($street)){
            $this->street = $street;
        }
        if (!empty($house)){
            $this->house = $house;
        }
        if (!empty($apartment)){
            $this->apartment = $apartment;
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
    }

    /**
     * @return bool
     */
    public function is_sole_searchable():bool {

        return (!empty($this->raw) or (!empty($this->city) and !empty($this->street) and !empty($this->house)));
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the address is a valid address to search by.
     */
    public function is_searchable():bool {

        return (!empty($this->raw) || !empty($this->city) || !empty($this->state) || !empty($this->country));
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the object's country is a valid country code.
     */
    public function is_valid_country():bool {

        return (!empty($this->country) &&
            array_key_exists(strtoupper($this->country), Utils::$piplapi_countries));
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the object's state is a valid state code.
     */
    public function is_valid_state():bool {

        return ($this->is_valid_country() &&
            array_key_exists(strtoupper($this->country), Utils::$piplapi_states) &&
            !empty($this->state) &&
            array_key_exists(strtoupper($this->state), Utils::$piplapi_states[strtoupper($this->country)]));

    }

    /**
     * @return null|string
     * the full name of the object's country.

        $address = new Address(['country' => 'FR']);
        print $address->country; // Outputs "FR"
        print $address->country_full(); // Outputs "France"
     */
    public function country_full():?string  {

        if (!empty($this->country)){
            $uppedcoutnry = strtoupper($this->country);

            return array_key_exists($uppedcoutnry, Utils::$piplapi_countries) ?
                Utils::$piplapi_countries[$uppedcoutnry] :
                NULL;
        }
        return NULL;
    }

    /**
     * @return string
     *
     * The full name of the object's state.

         $address = new Address(['country' => 'US', 'state' => 'CO']);
         print $address->state; // Outputs "CO"
         print $address->state_full(); // Outputs "Colorado"
     */
    public function state_full():string {

        if ($this->is_valid_state()){
            return Utils::$piplapi_states[strtoupper($this->country)][strtoupper($this->state)];
        }
        return NULL;
    }
}