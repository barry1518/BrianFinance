<?php
namespace Check\Api\PiplClasses;
use Techbanx\Service;

/**
 * Class FieldCount
 * @package Check\Api\PiplClasses
 */
class FieldCount extends Service{

    protected $attributes = [
        'addresses', 'ethnicities', 'emails', 'dobs', 'genders', 'user_ids', 'social_profiles',
        'educations', 'jobs', 'images', 'languages', 'origin_countries', 'names', 'phones',
        'relationships', 'usernames'
    ];

    /**
     * FieldCount constructor.
     * @param int|NULL $dobs
     * @param int|NULL $images
     * @param int|NULL $educations
     * @param int|NULL $addresses
     * @param int|NULL $jobs
     * @param int|NULL $genders
     * @param int|NULL $ethnicities
     * @param int|NULL $phones
     * @param int|NULL $origin_countries
     * @param int|NULL $usernames
     * @param int|NULL $languages
     * @param int|NULL $emails
     * @param int|NULL $user_ids
     * @param int|NULL $relationships
     * @param int|NULL $names
     * @param int|NULL $social_profiles
     */
    function __construct(int $dobs = NULL, int $images = NULL, int $educations = NULL, int $addresses = NULL,
                         int $jobs = NULL,int $genders = NULL, int $ethnicities = NULL, int $phones = NULL,
                         int $origin_countries = NULL,int $usernames = NULL, int $languages = NULL,
                         int $emails = NULL, int $user_ids = NULL, int $relationships = NULL,
                         int $names = NULL, int $social_profiles = NULL){

        $this->dobs = $dobs;
        $this->images = $images;
        $this->educations = $educations;
        $this->addresses = $addresses;
        $this->jobs = $jobs;
        $this->genders = $genders;
        $this->ethnicities = $ethnicities;
        $this->phones = $phones;
        $this->origin_countries = $origin_countries;
        $this->usernames = $usernames;
        $this->languages = $languages;
        $this->emails = $emails;
        $this->user_ids = $user_ids;
        $this->relationships = $relationships;
        $this->names = $names;
        $this->social_profiles = $social_profiles;
    }

    /**
     * @param array $params
     * @return FieldCount
     */
    public static function from_array(array $params):FieldCount {

        $dobs = $params['dobs'] ?? NULL;
        $images = $params['images'] ?? NULL;
        $educations = $params['educations'] ?? NULL;
        $addresses = $params['addresses'] ?? NULL;
        $jobs = $params['jobs'] ?? NULL;
        $genders = $params['genders'] ?? NULL;
        $ethnicities = $params['ethnicities'] ?? NULL;
        $phones = $params['phones'] ?? NULL;
        $origin_countries = $params['origin_countries'] ?? NULL;
        $usernames = $params['usernames'] ?? NULL;
        $languages = $params['languages'] ?? NULL;
        $emails = $params['emails'] ?? NULL;
        $user_ids = $params['user_ids'] ?? NULL;
        $relationships = $params['relationships'] ?? NULL;
        $names = $params['names'] ?? NULL;
        $social_profiles = $params['social_profiles'] ?? NULL;

        $instance = new self($dobs, $images, $educations, $addresses, $jobs,
            $genders, $ethnicities, $phones, $origin_countries,
            $usernames, $languages, $emails, $user_ids, $relationships,
            $names, $social_profiles);
        return $instance;
    }

    /**
     * @return array
     */
    public function to_array():array {

        $res = [];
        foreach($this->attributes as $attr) {
            if ($this->$attr > 0)
                $res[$attr] = $this->$attr;
        }
        return $res;
    }
}