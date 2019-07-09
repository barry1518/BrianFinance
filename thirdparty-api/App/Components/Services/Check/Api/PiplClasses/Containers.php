<?php
namespace Check\Api\PiplClasses;
use Techbanx\Service;

/**
 * Class Containers
 * @package Check\Api\PiplClasses
 *
 * The base class of Record and Person, made only for inheritance.
 */
class Containers extends Service{

    public $names = [];
    public $addresses = [];
    public $phones = [];
    public $emails = [];
    public $jobs = [];
    public $ethnicities = [];
    public $origin_countries = [];
    public $languages = [];
    public $educations = [];
    public $images = [];
    public $usernames = [];
    public $user_ids = [];
    public $urls = [];
    public $dob;
    public $gender;

    protected $CLASS_CONTAINER = [
        'Check\Api\PiplClasses\Name' => 'names',
        'Check\Api\PiplClasses\Address' => 'addresses',
        'Check\Api\PiplClasses\Phone' => 'phones',
        'Check\Api\PiplClasses\Email' => 'emails',
        'Check\Api\PiplClasses\Job' => 'jobs',
        'Check\Api\PiplClasses\Ethnicity' => 'ethnicities',
        'Check\Api\PiplClasses\OriginCountry' => 'origin_countries',
        'Check\Api\PiplClasses\Language' => 'languages',
        'Check\Api\PiplClasses\Education' => 'educations',
        'Check\Api\PiplClasses\Image' => 'images',
        'Check\Api\PiplClasses\Username' => 'usernames',
        'Check\Api\PiplClasses\Userid' => 'user_ids',
        'Check\Api\PiplClasses\Url' => 'urls'
    ];

    protected $singular_fields = [
        'Check\Api\PiplClasses\Dob' => 'dob',
        'Check\Api\PiplClasses\Gender' => 'gender',
    ];

    /**
     * Containers constructor.
     * @param array $fields
     * `fields` is an array of field objects from fields.php.
     */
    function __construct(array $fields=[]){

        $this->add_fields($fields);
    }

    /**
     * @param array $fields
     * Add the fields to their corresponding container. `fields` is an array of field objects from fields.php
     */
    public function add_fields(array $fields){

        if (empty($fields))
        {
            return;
        }

        foreach ($fields as $field)
        {
            $cls = is_object($field) ? get_class($field) : NULL;
            if (array_key_exists($cls, $this->CLASS_CONTAINER))
            {
                $container = $this->CLASS_CONTAINER[$cls];
                $this->{$container}[] = $field;
            } elseif(array_key_exists($cls, $this->singular_fields)) {
                $this->{$this->singular_fields[$cls]} = $field;
            } else {
                $type = empty($cls) ? gettype($field) : $cls;
                throw new \InvalidArgumentException('Object of type ' . $type . ' is an invalid field');
            }
        }
    }

    /**
     * @return array
     * An array with all the fields contained in this object.
     */
    public function all_fields():array {

        $allfields = [];
        foreach (array_values($this->CLASS_CONTAINER) as $val){
            $allfields = array_merge($allfields, $this->{$val});
        }
        foreach (array_values($this->singular_fields) as $val){
            if($this->{$val}) {
                $allfields[] = $this->{$val};
            }
        }
        return $allfields;
    }

    /**
     * @param array $d
     * @return array
     * Load the fields from the dict, return an array with all the fields.
     */
    public function fields_from_array(array $d):array {

        $fields = [];
        foreach (array_keys($this->CLASS_CONTAINER) as $field_cls){
            $container = $this->CLASS_CONTAINER[$field_cls];
            if (array_key_exists($container, $d)) {
                $field_array = $d[$container];
                foreach ($field_array as $x) {
                    $from_array_func = method_exists($field_cls, 'from_array') ? [$field_cls, 'from_array'] : ['Check\Api\PiplClasses\Field', 'from_array'];
                    
                    //error_log('field_cls='. $field_cls);
                    //error_log('from_array_func='. print_r($from_array_func,true));
                    $fields[] = call_user_func($from_array_func, $field_cls, $x);
                }
            }
        }
        foreach (array_keys($this->singular_fields) as $field_cls){
            $container = $this->singular_fields[$field_cls];
            if (array_key_exists($container, $d)) {
                $field_array = $d[$container];
                $from_array_func = method_exists($field_cls, 'from_array') ? [$field_cls, 'from_array'] : ['Check\Api\PiplClasses\Field', 'from_array'];
                $fields[] = call_user_func($from_array_func, $field_cls, $field_array);
            }
        }
        return $fields;
    }

    /**
     * @return array
     * Transform the object to an array and return it.
     */
    public function fields_to_array():array {

        $d = [];
        foreach (array_values($this->CLASS_CONTAINER) as $container){
            $fields = $this->{$container};
            if (!empty($fields)){
                $all_fields = [];
                foreach($fields as $field) {
                    $all_fields[] = $field->to_array();
                }
                if (count($all_fields) > 0){
                    $d[$container] = $all_fields;
                }
            }
        }
        foreach (array_values($this->singular_fields) as $container){
            $field = $this->{$container};
            if (!empty($field)){
                $d[$container] =  $field->to_array();
            }
        }
        return $d;
    }

    /**
     * @return mixed
     */
    function jsonSerialize(){

        return $this->to_array();
    }
}

