<?php
namespace Check\Api\PiplClasses;
use Techbanx\Service;

/**
 * Class AvailableData
 * @package Check\Api\PiplClasses
 */
class AvailableData extends Service{
    function __construct(array $basic = NULL, array $premium = NULL){
        $this->basic = $basic ? FieldCount::from_array($basic) : NULL;
        $this->premium = $premium ? FieldCount::from_array($premium) : NULL;
    }

    /**
     * @param array $params
     * @return AvailableData
     */
    public static function from_array(array $params):AvailableData {
        $basic = $params['basic'] ?? NULL;
        $premium = $params['premium'] ?? NULL;
        $instance = new self($basic, $premium);
        return $instance;
    }

    /**
     * @return array
     */
    public function to_array():array {
        $res = [];
        if ($this->basic != NULL)
            $res['basic'] = $this->basic->to_array();
        if ($this->premium != NULL)
            $res['premium'] = $this->premium->to_array();
        return $res;
    }
}
