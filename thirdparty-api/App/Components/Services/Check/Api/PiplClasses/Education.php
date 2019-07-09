<?php
namespace Check\Api\PiplClasses;
/**
 * Class Education
 * @package Check\Api\PiplClasses
 *
 * Education information of a person.
 */

class Education extends Field{

    protected $children = ['degree', 'school', 'date_range', 'display'];

    /**
     * Education constructor.
     * @param array $params
     *
     * `degree` and `school` should both be strings.
     * `date_range` is A DateRange object (DateRange), that's the time the person was studying.
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        if (!empty($degree)){
            $this->degree = $degree;
        }
        if (!empty($school)){
            $this->school = $school;
        }
        if (!empty($date_range)){
            $this->date_range = $date_range;
        }
        if (!empty($display)){
            $this->display = $display;
        }
    }
}

