<?php
namespace Check\Api\PiplClasses;

use Techbanx\Service;

/**
 * Class Field
 * @package Check\Api\PiplClasses
 * Base class of all data fields, made only for inheritance.
 */
abstract class Field extends Service {

    protected $attributes = [];
    protected $children = [];
    protected $types_set = [];

    protected $internal_params = [];

    /**
     * Field constructor.
     * @param array $params
     * `valid_since` is a DateTime object, it's the first time Pipl's crawlers found this data on the page.
       `inferred` is a boolean indicating whether this field includes inferred data.
     */
    function __construct(array $params=[]) {

        extract($params);
        if (!empty($valid_since)){
            $this->valid_since =  $valid_since;
        }
        if (!empty($inferred)){
            $this->inferred =  $inferred;
        }
        // API v5
        if (!empty($last_seen)){
            $this->last_seen =  $last_seen;
        }
        if (!empty($current)){
            $this->current =  $current;
        }

    }

    /**
     * @param string $name
     * @param $val
     */
    public function __set(string $name, $val){

        if (in_array($name, $this->attributes) ||
            in_array($name, $this->children) ||
            ($name == 'valid_since') ||
            ($name == 'last_seen') ||
            ($name == 'current') ||
            ($name == 'inferred')){
                if ($name == 'type'){
                    $this->validate_type($val);
                }
                $this->internal_params[$name] = $val;
            }
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name) {

        if (in_array($name, $this->attributes) ||
            in_array($name, $this->children) ||
            ($name == 'valid_since') ||
            ($name == 'inferred') ||
            ($name == 'current') ||
            ($name == 'last_seen')){
                if (array_key_exists($name, $this->internal_params))
                {
                    return $this->internal_params[$name];
                }
            }
        return NULL;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name):bool {

        return ((in_array($name, $this->attributes) ||
            in_array($name, $this->children) ||
            ($name == 'valid_since') || ($name == "inferred") || ($name == 'current') || ($name == "last_seen")) &&
            array_key_exists($name, $this->internal_params));
    }

    /**
     * @param string $name
     */
    public function __unset(string $name){

        if (in_array($name, $this->attributes) ||
            in_array($name, $this->children) ||
            ($name == 'valid_since') || ($name == "inferred") || ($name == 'current') || ($name == "last_seen")
        ){
            unset($this->internal_params[$name]);
        }
    }

    /**
     * @return array|null|string
     */
    public function __toString(){

        return isset($this->display) ? $this->display : "";
    }

    /**
     * @return string
     *
     * Return a string representation of the object.
     */
    public function get_representation():string {

        $allattrs = array_merge($this->attributes, $this->children);
        array_push($allattrs, "valid_since");

        $allattrsvalues = array_map([$this, 'internal_mapcb_buildattrslist'], $allattrs);

        // $allattrsvalues is now a multidimensional array
        $args = array_reduce($allattrsvalues, [$this, 'internal_reducecb_buildattrslist']);
        $args = substr_replace($args, "", -2);

        return get_class($this) . '(' . $args . ')';
    }

    /**
     * @param string $attr
     * @return array|null
     */
    private function internal_mapcb_buildattrslist(string $attr):?array {

        if (isset($this->internal_params[$attr])){
            return [$attr => $this->internal_params[$attr]];
        }
        else {
            return NULL;
        }
    }

    /**
     * @param string $res
     * @param array $x
     * @return string
     */
    private function internal_reducecb_buildattrslist(string $res, array $x):string {

        if (is_array($x) && count($x) > 0){
            $keys = array_keys($x);
            if (isset($x[$keys[0]])){
                $val = $x[$keys[0]];

                if ($val instanceof \DateTime){
                    $val = Utils::piplapi_datetime_to_str($val);
                }
                else if (is_array($val)){
                    $val = '[' . implode(', ', $val) . ']';
                }
                else{
                    $val = (string)$val;
                }

                $newval = $keys[0] . '=' . $val . ', ';
                // This is a bit messy, but gets around the weird fact that array_reduce
                // can only accept an initial integer.
                if (empty($res)){
                    $res = $newval;
                }
                else{
                    $res .= $newval;
                }
            }
        }
        return $res;
    }

    /**
     * @param string $type
     *
     * Take an string `type` and raise an InvalidArgumentException if it's not
        a valid type for the object.

        A valid type for a field is a value from the types_set attribute of
        that field's class.
     */
    public function validate_type(string $type){

        if (!empty($type) && !in_array($type, $this->types_set)){
            throw new \InvalidArgumentException('Invalid type for ' . get_class($this) . ' ' . $type);
        }
    }

    /**
     * @param string $clsname
     * @param array $d
     * @return object
     *
     * Transform the dict to a field object and return the field.
     */
    public static function from_array(string $clsname, array $d):object {

        $newdict = [];

        foreach ($d as $key => $val){
            if (Utils::piplapi_string_startswith($key, '@')){
                $key = substr($key, 1);
            }

            if ($key == 'last_seen'){
                $val = Utils::piplapi_str_to_datetime($val);
            }

            if ($key == 'valid_since'){
                $val = Utils::piplapi_str_to_datetime($val);
            }

            if ($key == 'date_range'){
                // DateRange has its own from_array implementation
                $val = DateRange::from_array($val);
            }

            $newdict[$key] = $val;
        }

        return new $clsname($newdict);
    }

    /**
     * @param string $attr
     * @return array
     */
    private function internal_mapcb_attrsarr(string $attr):array {

        return [$attr => '@'];
    }

    /**
     * @param string $attr
     * @return array
     */
    private function internal_mapcb_childrenarr(string $attr):array {

        return [$attr => ''];
    }

    /**
     * @return array
     *
     * Return a dict representation of the field.
     */
    public function to_array():array {

        $d = [];
        if (!empty($this->valid_since)){
            $d['@valid_since'] = Utils::piplapi_datetime_to_str($this->valid_since);
        }
        if (!empty($this->last_seen)){
            $d['@last_seen'] = Utils::piplapi_datetime_to_str($this->last_seen);
        }
        $newattr = array_map([$this, "internal_mapcb_attrsarr"], $this->attributes);
        $newchild = array_map([$this, "internal_mapcb_childrenarr"], $this->children);

        // $newattr and $newchild are multidimensionals- this is used to iterate over them
        // we first merge the two arrays and then create an iterator that flattens them
        $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator(array_merge($newattr, $newchild)));

        foreach ($it as $key => $prefix){
            if (array_key_exists($key, $this->internal_params)){
                $value = $this->internal_params[$key];

                if (isset($value) && is_object($value) && method_exists($value, 'to_array')){
                    $value = $value->to_array();
                }

                if (isset($value)){
                    $d[$prefix . $key] = $value;
                }
            }
        }

        return $d;
    }

    /**
     * @return bool
     */
    public function is_searchable():bool {

        return true;
    }
}