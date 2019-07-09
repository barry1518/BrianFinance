<?php
namespace Check\Api\PiplClasses;
/**
 * Class Person
 * @package Check\Api\PiplClasses
 *
 * A Person object is all the data available on an individual.

    The Person object is essentially very similar in its structure to the
    Source object, the main difference is that data about an individual can
    come from multiple sources.

    The person's data comes as field objects (Name, Address, Email etc. see fields.php).
    Each type of field has its on container (note that Person is a subclass of Containers).

    For example:

    require_once dirname(__FILE__) . '/data/containers.php';
    $fields = [new Email(['address' => 'clark.kent@example.com']), new Phone(['number' => 9785550145])];
    $person = new Person(['fields' => $fields]);
    print implode(', ', $person->emails); // Outputs "clark.kent@example.com"
    print implode(', ', $person->phones); // Outputs "+1-9785550145"

    Note that a person object is used in the Search API in two ways:
    - It might come back as a result for a query (see SearchAPIResponse).
    - It's possible to build a person object with all the information you
    already have about the person you're looking for and send this object as
    the query (see SearchAPIRequest).
 */
class Person extends Containers{

    private $extended_containers = [
        'Check\Api\PiplClasses\Relationship' => 'relationships'
    ];
    public $id;
    public $search_pointer;
    public $match;
    public $inferred;
    public $relationships = [];

    /**
     * Person constructor.
     * @param array $fields
     * @param string|NULL $id
     * @param string|NULL $search_pointer
     * @param float|NULL $match
     * @param bool $inferred
     */
    function __construct(array $fields = [], string $id = NULL, string $search_pointer = NULL, float $match = NULL,bool $inferred = false){

        $this->CLASS_CONTAINER = array_merge($this->CLASS_CONTAINER, $this->extended_containers);
        parent::__construct($fields);
        $this->search_pointer = $search_pointer;
        $this->match = $match;
        $this->id = $id;
        $this->inferred = $inferred;
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the person has enough data and can be sent as a query to the API.
     */
    public function is_searchable():bool {

        $all = array_merge($this->names, $this->emails, $this->phones, $this->usernames, $this->user_ids, $this->urls);
        $searchable = array_filter($all, function($field){return $field->is_searchable();});
        $searchable_address = array_filter($this->addresses, function($field){return $field->is_sole_searchable();});
        return $searchable_address or $this->search_pointer or count($searchable) > 0;
    }

    /**
     * @return array
     *
     * An array of all the fields that are invalid and won't be used in the search.

        For example: names/usernames that are too short, emails that are
        invalid etc.
     */
    public function unsearchable_fields():array {

        $all = array_merge($this->names, $this->emails, $this->phones, $this->usernames, $this->addresses,
            $this->user_ids, $this->urls, [$this->dob]);
        $unsearchable = array_filter($all, function($field){return $field && !$field->is_searchable();});
        return $unsearchable;
    }

    /**
     * @param $params
     * @return Person
     *
     * Transform the array to a person object and return it.
     */
    public static function from_array($params):Person{

        $id = $params['@id'] ?? NULL;
        $search_pointer = $params['@search_pointer'] ?? NULL;
        $match = $params['@match'] ?? NULL;
        $inferred = $params['@inferred'] ?? false;

        $instance = new self([], $id, $search_pointer, $match, $inferred);
        $instance->add_fields($instance->fields_from_array($params));
        return $instance;
    }

    /**
     * @return array
     *
     * Return an array representation of the person.
     */
    public function to_array():array {

        $d =[];
        if (!empty($this->id)){ $d['@id'] = $this->id; }
        if (!is_null($this->match)){ $d['@match'] = $this->match; }
        if (!empty($this->search_pointer)){ $d['@search_pointer'] = $this->search_pointer; }
        if ($this->inferred){ $d['@inferred'] = $this->inferred; }
        return array_merge($d, $this->fields_to_array());
    }
}
