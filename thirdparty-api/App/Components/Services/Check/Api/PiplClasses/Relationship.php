<?php
namespace Check\Api\PiplClasses;
/**
 * Class Relationship
 * @package Check\Api\PiplClasses
 *
 * Name of another person related to this person.
 */
class Relationship extends Containers{

    protected $types_set = ['friend', 'family', 'work', 'other'];

    public $type;
    public $subtype;
    public $valid_since;
    public $inferred;

    /**
     * Relationship constructor.
     * @param array $fields
     * @param string|NULL $type
     * @param string|NULL $subtype
     * @param \DateTime|NULL $valid_since
     * @param bool|NULL $inferred
     */
    function __construct(array $fields = [], string $type = NULL, string $subtype = NULL, \DateTime $valid_since = NULL,
                         bool $inferred = NULL){

        parent::__construct($fields);
        $this->type = $type;
        $this->subtype = $subtype;
        $this->valid_since = $valid_since;
        $this->inferred = $inferred;
    }

    /**
     * @param $class_name
     * @param $params
     * @return Relationship
     *
     * Transform the array to a person object and return it.
     */
    public static function from_array($class_name, array $params):Relationship {

        $type = $params['@type'] ?? NULL;
        $subtype = $params['@subtype'] ?? NULL;
        $valid_since = $params['@valid_since'] ?? NULL;
        $inferred = $params['@inferred'] ?? NULL;

        $instance = new self([], $type, $subtype, $valid_since, $inferred);
        $instance->add_fields($instance->fields_from_array($params));
        return $instance;
    }

    /**
     * @return string
     */
    public function __toString():string {

        return count($this->names) > 0 && $this->names[0]->first ? $this->names[0]->first : "";
    }

    /**
     * @return array
     *
     * eturn an array representation of the person.
     */
    public function to_array():array {

        $d = [];
        if (!empty($this->valid_since)){ $d['@valid_since'] = $this->valid_since; }
        if (!empty($this->inferred)){ $d['@inferred'] = $this->inferred; }
        if (!empty($this->type)){ $d['@type'] = $this->type; }
        if (!empty($this->subtype)){ $d['@subtype'] = $this->subtype; }

        return array_merge($d, $this->fields_to_array());
    }
}

