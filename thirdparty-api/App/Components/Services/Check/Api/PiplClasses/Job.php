<?php
namespace Check\Api\PiplClasses;

/**
 * Class Job
 * @package Check\Api\PiplClasses
 *
 * Job information of a person.
 */
class Job extends Field{

    protected $children = ['title', 'organization', 'industry', 'date_range', 'display'];

    /**
     * Job constructor.
     * @param array $params
     *
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        // `title`, `organization`, `industry`, should all be strings.
        // `date_range` is A DateRange object (DateRange),
        // that's the time the person held this job.

        if (!empty($title)){
            $this->title = $title;
        }
        if (!empty($organization)){
            $this->organization = $organization;
        }
        if (!empty($industry)){
            $this->industry = $industry;
        }
        if (!empty($display)){
            $this->display = $display;
        }
        if (!empty($date_range)){
            $this->date_range = $date_range;
        }
    }
}

