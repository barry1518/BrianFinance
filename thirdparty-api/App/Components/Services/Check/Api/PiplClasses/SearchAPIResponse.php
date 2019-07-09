<?php
/**
 * @package Check\Api\PiplClasses
 * @author Techbanx (Yuan He)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party

 PHP wrapper for easily making calls to Pipl's Search API.

 Pipl's Search API allows you to query with the information you have about
 a person (his name, address, email, phone, username and more) and in response
 get all the data available on him on the web.

 The classes contained in this module are:
 - SearchAPIRequest -- Build your request and send it.
 - SearchAPIResponse -- Holds the response from the API in case it contains data.
 - SearchAPIError -- An exception raised when the API response is an error.

 The classes are based on the person data-model that's implemented here in containers.php
*/
namespace Check\Api\PiplClasses;

use Techbanx\Service;

/**
 * Class SearchAPIResponse
 * @package Check\Api\PiplClasses
 *
 * A response comprises the three things returned as a result to your query:
- a person (Person) that is the data object
representing all the information available for the person you were
looking for.
this object will only be returned when our identity-resolution engine is
convinced that the information is of the person represented by your query.
obviously, if the query was for "John Smith" there's no way for our
identity-resolution engine to know which of the hundreds of thousands of
people named John Smith you were referring to, therefore you can expect
that the response will not contain a person object.
on the other hand, if you search by a unique identifier such as email or
a combination of identifiers that only lead to one person, such as
"Clark Kent from Smallville, KS, US", you can expect to get
a response containing a single person object.

- a list of possible persons (Person). If our identity-resolution
engine did not find a definite match, you can use this list to further
drill down using the persons' search_pointer field.

- a list of sources (Source) that fully/partially
match the person from your query, if the query was for "Clark Kent from
Kansas US" the response might also contain sources of "Clark Kent
from US" (without Kansas), if you need to differentiate between sources
with full match to the query and partial match or if you want to get a
score on how likely is that source to be related to the person you are
searching please refer to the source's "match" field.

the response also contains the query as it was interpreted by Pipl. This
part is useful for verification and debugging, if some query parameters
were invalid you can see in response.query that they were ignored, you can
also see how the name/address from your query were parsed in case you
passed raw_name/raw_address in the query.
 */
class SearchAPIResponse extends Service{
    public $query;
    public $person;
    public $sources;
    public $possible_persons;
    public $warnings;
    public $http_status_code;
    public $visible_sources;
    public $available_sources;
    public $available_data;
    public $search_id;
    public $match_requirements;
    public $source_category_requirements;
    public $persons_count;
    public $qps_allotted;
    public $qps_current;
    public $quota_allotted;
    public $quota_current;
    public $quota_reset;
    public $raw_json;

    /**
     * SearchAPIResponse constructor.
     * @param int $http_status_code
     * @param \Check\Api\PiplClasses\Person $query
     * @param int $visible_sources
     * @param int $available_sources
     * @param string $search_id
     * @param array $warnings
     * @param \Check\Api\PiplClasses\Person $person
     * @param array $possible_persons
     * @param array $sources
     * @param \Check\Api\PiplClasses\AvailableData|NULL $available_data
     * @param string|NULL $match_requirements
     * @param string|NULL $source_category_requirements
     * @param int|NULL $persons_count
     * @param int|NULL $qps_allotted
     * @param int|NULL $qps_current
     * @param int|NULL $quota_allotted
     * @param int|NULL $quota_current
     * @param \DateTime|NULL $quota_reset
     */
    public function __construct(int $http_status_code, Person $query, int $visible_sources, int $available_sources,
                                string $search_id, array $warnings, Person $person, array $possible_persons,
                                array $sources, AvailableData $available_data = NULL,string $match_requirements = NULL,
                                string $source_category_requirements = NULL, int $persons_count = NULL,
                                int $qps_allotted = NULL, int $qps_current = NULL, int $quota_allotted = NULL,
                                int $quota_current = NULL,\DateTime $quota_reset = NULL){

        $this->http_status_code = $http_status_code;
        $this->visible_sources = $visible_sources;
        $this->available_sources = $available_sources;
        $this->search_id = $search_id;
        $this->query = $query;
        $this->person = $person;
        $this->match_requirements = $match_requirements;
        $this->source_category_requirements = $source_category_requirements;
        $this->possible_persons = !empty($possible_persons) ? $possible_persons : [];
        $this->sources = !empty($sources) ? $sources : [];
        $this->warnings = !empty($warnings) ? $warnings : [];
        $this->available_data = !empty($available_data) ? $available_data : [];
        $this->persons_count = !empty($persons_count) ? $persons_count : (!empty($person) ? 1 : count($this->possible_persons));
        $this->qps_allotted = $qps_allotted;
        $this->qps_current = $qps_current;
        $this->quota_allotted = $quota_allotted;
        $this->quota_current = $quota_current;
        $this->quota_reset = $quota_reset;

        // raw json
        $this->raw_json = NULL;
    }

    /**
     * @param callable $key_function
     * @return array
     *
     * Return an array with the sources grouped by the key returned by `key_function`.

        `key_function` takes a source and returns the value from the source to
        group by (see examples in the group_sources_by_* methods below).

        The return value is an array, a key in this array is a key returned by
        `key_function` and the value is a list of all the sources with this key.
     */
    public function group_sources(callable $key_function):array {
        $new_groups = [];
        foreach ($this->sources as $rec) {
            $grp = $key_function($rec);
            $new_groups[$grp][] = $rec;
        }
        return $new_groups;
    }

    /**
     * @return array
     *
     * Return the sources grouped by the domain they came from.
        The return value is an array, a key in this array is a domain
        and the value is a list of all the sources with this domain.
     */
    public function group_sources_by_domain():array {
        $key_function = function ($x){return $x->domain;};
        return $this->group_sources($key_function);
    }

    /**
     * @return array
     *
     * Return the sources grouped by their category.
        The return value is an array, a key in this array is a category
        and the value is a list of all the sources with this category.
     */
    public function group_sources_by_category():array {
        $key_function = function($x){return $x->category;};
        return $this->group_sources($key_function);
    }

    /**
     * @return array
     * Return the sources grouped by their query_person_match attribute.
    The return value is an array, a key in this array is a query_person_match
    float and the value is a list of all the sources with this
    query_person_match value.
     */
    public function group_sources_by_match():array {
        $key_function = function ($x){return $x->match;};
        return $this->group_sources($key_function);
    }

    /**
     * @return array
     */
    public function to_array():array {
        // Return a dict representation of the response.
        $d = [];

        if (!empty($this->http_status_code)) {
            $d['@http_status_code'] = $this->http_status_code;
        }
        if (!empty($this->visible_sources)) {
            $d['@visible_sources'] = $this->visible_sources;
        }
        if (!empty($this->available_sources)) {
            $d['@available_sources'] = $this->available_sources;
        }
        if (!empty($this->search_id)) {
            $d['@search_id'] = $this->search_id;
        }
        if (!empty($this->persons_count)) {
            $d['@persons_count'] = $this->persons_count;
        }

        if (!empty($this->warnings)) {
            $d['warnings'] = $this->warnings;
        }
        if (!empty($this->query)) {
            $d['query'] = $this->query->to_array();
        }
        if (!empty($this->person)) {
            $d['person'] = $this->person->to_array();
        }
        if (!empty($this->possible_persons)) {
            $d['possible_persons'] = [];
            foreach ($this->possible_persons as $possible_person) {
                $d['possible_persons'][] = $possible_person->to_array();
            }
        }
        if (!empty($this->sources)) {
            $d['sources'] = [];
            foreach ($this->sources as $source) {
                $d['sources'][] = $source->to_array();
            }
        }

        if (!empty($this->available_data)) {
            $d['available_data'] = $this->available_data->to_array();
        }

        if (!empty($this->match_requirements)) {
            $d['match_requirements'] = $this->match_requirements;
        }

        return $d;
    }

    /**
     * @param $d
     * @param array $headers
     * @return SearchAPIResponse
     */
    public static function from_array($d, $headers = []):SearchAPIResponse {
        // Transform the array to a response object and return the response.
        $warnings = $d['warnings'] ?? [];
        $query = NULL;
        if (!empty($d['query'])) {
            $query = Person::from_array($d['query']);
        }

        $person = NULL;
        if (!empty($d['person'])) {
            $person = Person::from_array($d['person']);
        }

        $sources = [];
        if (array_key_exists("sources", $d) && count($d['sources']) > 0) {
            foreach ($d["sources"] as $source) {
                $sources[] = Source::from_array($source);
            }
        }

        $possible_persons = [];
        if (array_key_exists("possible_persons", $d) && count($d['possible_persons']) > 0) {
            foreach ($d["possible_persons"] as $possible_person) {
                $possible_persons[] = Person::from_array($possible_person);
            }
        }

        // Handle headers
        $qps_allotted = !empty($headers['x-apikey-qps-allotted']) ? intval($headers['x-apikey-qps-allotted']) : null;
        $qps_current = !empty($headers['x-apikey-qps-current']) ? intval($headers['x-apikey-qps-current']) : null;
        $quota_allotted = !empty($headers['x-apikey-quota-allotted']) ? intval($headers['x-apikey-quota-allotted']) : null;
        $quota_current = !empty($headers['x-apikey-quota-current']) ? intval($headers['x-apikey-quota-current']) : null;
        $quota_reset = !empty($headers['x-quota-reset']) ?
            \DateTime::createFromFormat(Utils::PIPLAPI_DATE_QUOTA_RESET, $headers['x-quota-reset']) : null;

        // API V5 - New attributes

        $available_data = NULL;
        if (!empty($d['available_data'])) {
            $available_data = AvailableData::from_array($d['available_data']);
        }

        $match_requirements = NULL;
        if (!empty($d['match_requirements'])) {
            $match_requirements = $d['match_requirements'];
        }

        $source_category_requirements = NULL;
        if (!empty($d['source_category_requirements'])) {
            $source_category_requirements = $d['source_category_requirements'];
        }

        $persons_count = NULL;
        if (!empty($d['@persons_count'])) {
            $persons_count = $d['@persons_count'];
        }

        $response = new SearchAPIResponse($d["@http_status_code"], $query, $d["@visible_sources"],
            $d["@available_sources"], $d["@search_id"], $warnings, $person, $possible_persons, $sources,
            $available_data, $match_requirements, $source_category_requirements, $persons_count,
            $qps_allotted, $qps_current, $quota_allotted, $quota_current, $quota_reset);

        //error_log('vvvv'.print_r($response->person->names[0]->display,true));
        return $response;

    }

    /**
     * @return Name
     */
    public function name():Name {
        return ($this->person && count($this->person->names) > 0) ? $this->person->names[0] : NULL;
    }

    /**
     * @return Address
     */
    public function address():Address {
        return ($this->person && count($this->person->addresses) > 0) ? $this->person->addresses[0] : NULL;
    }

    /**
     * @return Phone
     */
    public function phone():Phone {
        return ($this->person && count($this->person->phones) > 0) ? $this->person->phones[0] : NULL;
    }

    /**
     * @return Email
     */
    public function email():Email {
        return ($this->person && count($this->person->emails) > 0) ? $this->person->emails[0] : NULL;
    }

    /**
     * @return Username
     */
    public function username():Username {
        return ($this->person && count($this->person->usernames) > 0) ? $this->person->usernames[0] : NULL;
    }

    /**
     * @return Userid
     */
    public function user_id():Userid {
        return ($this->person && count($this->person->user_ids) > 0) ? $this->person->user_ids[0] : NULL;
    }

    /**
     * @return Dob
     */
    public function dob():Dob {
        return ($this->person && $this->person->dob) ? $this->person->dob : NULL;
    }

    /**
     * @return Image
     */
    public function image():Image {
        return ($this->person && count($this->person->images) > 0) ? $this->person->images[0] : NULL;
    }

    /**
     * @return Job
     */
    public function job():Job {
        return ($this->person && count($this->person->jobs) > 0) ? $this->person->jobs[0] : NULL;
    }

    /**
     * @return Education
     */
    public function education():Education {
        return ($this->person && count($this->person->educations) > 0) ? $this->person->educations[0] : NULL;
    }

    /**
     * @return Gender
     */
    public function gender():Gender {
        return ($this->person && $this->person->gender) ? $this->person->gender : NULL;
    }

    /**
     * @return Ethnicity
     */
    public function ethnicity():Ethnicity {
        return ($this->person && count($this->person->ethnicities) > 0) ? $this->person->ethnicities[0] : NULL;
    }

    /**
     * @return Language
     */
    public function language():Language {
        return ($this->person && count($this->person->languages) > 0) ? $this->person->languages[0] : NULL;
    }

    /**
     * @return OriginCountry
     */
    public function origin_country():OriginCountry {
        return ($this->person && count($this->person->origin_countries) > 0) ? $this->person->origin_countries[0] : NULL;
    }

    /**
     * @return Relationship
     */
    public function relationship():Relationship {
        return ($this->person && count($this->person->relationships) > 0) ? $this->person->relationships[0] : NULL;
    }

    /**
     * @return Url
     */
    public function url():Url {
        return ($this->person && count($this->person->urls) > 0) ? $this->person->urls[0] : NULL;
    }

    /**
     * @return array
     */
    function jsonSerialize():array {
        return $this->to_array();
    }
}