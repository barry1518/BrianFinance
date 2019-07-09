<?php
namespace Check\Api\PiplClasses;

/**
 * Class Source
 * @package Check\Api\PiplClasses
 * A source is a single source of data.

    Every source object is based on the URL of the
    page where the data is available, and the data itself that comes as field
    objects (Name, Address, Email etc. see fields.php).

    Each type of field has its own container (note that Source is a subclass
    of Containers).

    Sources come as results for a query and therefore they have attributes that
    indicate if and how much they match the query. They also have a validity
    timestamp available as an attribute.
 */
class Source extends Containers{

    private $extended_containers = [
        'Check\Api\PiplClasses\Relationship' => 'relationships',
        'Check\Api\PiplClasses\Tag' => 'tags'
    ];
    public $name;
    public $category;
    public $origin_url;
    public $sponsored;
    public $domain;
    public $person_id;
    public $id;
    public $premium;
    public $match;
    public $valid_since;
    public $relationships = [];
    public $tags = [];

    /**
     * Source constructor.
     * @param array $fields
     * @param float|NULL $match
     * @param string|NULL $name
     * @param string|NULL $category
     * @param string|NULL $origin_url
     * @param bool|NULL $sponsored
     * @param string|NULL $domain
     * @param string|NULL $person_id
     * @param string|NULL $id
     * @param bool|NULL $premium
     * @param \DateTime|NULL $valid_since
     */
    function __construct(array $fields = [], float $match = NULL, string $name = NULL, string $category = NULL,
                         string $origin_url = NULL,bool $sponsored = NULL, string $domain = NULL, string $person_id = NULL,
                         string $id = NULL,bool $premium = NULL, \DateTime $valid_since = NULL){

        $this->CLASS_CONTAINER = array_merge($this->CLASS_CONTAINER, $this->extended_containers);
        parent::__construct($fields);
        $this->name = $name;
        $this->category = $category;
        $this->origin_url = $origin_url;
        $this->sponsored = $sponsored;
        $this->domain = $domain;
        $this->person_id = $person_id;
        $this->id = $id;
        $this->premium = $premium;
        $this->match = $match;
        $this->valid_since = $valid_since;
    }

    /**
     * @param array $params
     * @return Source
     * Transform the dict to a record object and return the record.
     */
    public static function from_array(array $params):Source{

        $name = !empty($params['@name']) ? $params['@name'] : NULL;
        $match = !empty($params['@match']) ? $params['@match'] : NULL;
        $category = !empty($params['@category']) ? $params['@category'] : NULL;
        $origin_url = !empty($params['@origin_url']) ? $params['@origin_url'] : NULL;
        $sponsored = !empty($params['@sponsored']) ? $params['@sponsored'] : NULL;
        $domain = !empty($params['@domain']) ? $params['@domain'] : NULL;
        $person_id = !empty($params['@person_id']) ? $params['@person_id'] : NULL;
        $source_id = !empty($params['@id']) ? $params['@id'] : NULL;
        $premium = !empty($params['@premium']) ? $params['@premium'] : NULL;
        $valid_since = !empty($params['@valid_since']) ? $params['@valid_since'] : NULL;
        if (!empty($valid_since)){ $valid_since = Utils::piplapi_str_to_datetime($valid_since); }

        $instance = new self([], $match, $name, $category, $origin_url, $sponsored, $domain, $person_id,
            $source_id, $premium, $valid_since);
        $instance->add_fields($instance->fields_from_array($params));
        return $instance;
    }

    /**
     * @return array
     * Return an array representation of the record.
     */
    public function to_array():array {

        $d = [];
        if (!empty($this->valid_since)){ $d['@valid_since'] = Utils::piplapi_datetime_to_str($this->valid_since); }
        if (!empty($this->match)){ $d['@match'] = $this->match; }
        if (!empty($this->category)){ $d['@category'] = $this->category; }
        if (!empty($this->origin_url)){ $d['@origin_url'] = $this->origin_url; }
        if (!empty($this->sponsored)){ $d['@sponsored'] = $this->sponsored; }
        if (!empty($this->domain)){ $d['@domain'] = $this->domain; }
        if (!empty($this->person_id)){ $d['@person_id'] = $this->person_id; }
        if (!empty($this->id)){ $d['@source_id'] = $this->id; }
        if (!empty($this->premium)){ $d['@premium'] = $this->premium; }

        return array_merge($d, $this->fields_to_array());
    }
}
