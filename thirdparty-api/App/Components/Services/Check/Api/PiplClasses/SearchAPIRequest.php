<?php
/**
 * @package Check\Api\PiplClasses
 * @author Techbanx (Yuan He)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party
 */
/* PHP wrapper for easily making calls to Pipl's Search API.

 Pipl's Search API allows you to query with the information you have about
 a person (his name, address, email, phone, username and more) and in response
 get all the data available on him on the web.

 The classes contained in this module are:
 - SearchAPIRequest -- Build your request and send it.
 - SearchAPIResponse -- Holds the response from the API in case it contains data.
 - SearchAPIError -- An exception raised when the API response is an error.

 The classes are based on the person data-model that's implemented here in containers.php
*/

namespace  Check\Api\PiplClasses;

use Techbanx\Service;
/**
 * Class SearchAPIRequest
 * @package Check\Api\PiplClasses
 *  A request to Pipl's Search API.

Building the request from the query parameters can be done in two ways:

Option 1 - directly and quickly (for simple requests with only few
parameters):

require_once dirname(__FILE__) . '/search.php';
$request = new SearchAPIRequest(['email' => 'clark.kent@example.com']);
$response = $request->send();

Option 2 - using the data-model (useful for more complex queries; for
example, when there are multiple parameters of the same type
such as few phones or a few addresses or when you'd like to use
information beyond the usual identifiers such as name or email,
information like education, job, relationships etc):

require_once dirname(__FILE__) . '/search.php';
require_once dirname(__FILE__) . '/data/fields.php';
$fields = [new Name(['first' => 'Clark', 'last' => 'Kent')),
new Address(['country' => 'US', 'state' => 'KS', 'city' => 'Metropolis']),
new Address(['country' => 'US', 'state' => 'KS']),
new Job(['title' => 'Field Reporter']);
$request = new SearchAPIRequest(['person' => new Person(['fields' => $fields])]);
$response = $request->send();

Sending the request and getting the response is very simple and can be done calling $request->send().
 */
class SearchAPIRequest extends Service {

    public static $default_configuration;
    public $person;
    public $configuration;
    public $contact_id;

    public static $base_url = 'api.pipl.com/search/?';

    /**
     * @param \Check\Api\PiplClasses\SearchRequestConfiguration $configuration
     */
    static function set_default_configuration(SearchRequestConfiguration $configuration){
        self::$default_configuration = $configuration;
    }

    /**
     * @return \Check\Api\PiplClasses\SearchRequestConfiguration
     */
    static function get_default_configuration():SearchRequestConfiguration {
        if (!isset(self::$default_configuration)) {
            self::$default_configuration = new SearchRequestConfiguration();
        }
        return self::$default_configuration;
    }

    /**
     * SearchAPIRequest constructor.
     * @param array $searchParams
     * @param \Check\Api\PiplClasses\SearchRequestConfiguration|NULL $configuration

    Initiate a new request object with given query params.

    Each request must have at least one searchable parameter, meaning
    a name (at least first and last name), email, phone or username.
    Multiple query params are possible (for example querying by both email
    and phone of the person).

    Args:

    first_name -- string, minimum 2 chars.
    middle_name -- string.
    last_name -- string, minimum 2 chars.
    raw_name -- string, an unparsed name containing at least a first name
    and a last name.
    email -- string.
    phone -- string. A raw phone number.
    username -- string, minimum 4 chars.
    country -- string, a 2 letter country code from:
    http://en.wikipedia.org/wiki/ISO_3166-2
    state -- string, a state code from:
    http://en.wikipedia.org/wiki/ISO_3166-2%3AUS
    http://en.wikipedia.org/wiki/ISO_3166-2%3ACA
    city -- string.
    raw_address -- string, an unparsed address.
    from_age -- int.
    to_age -- int.
    person -- A PiplApi::Person object (available at containers.php).
    The person can contain every field allowed by the data-model
    (fields.php) and can hold multiple fields of
    the same type (for example: two emails, three addresses etc.)
    search_pointer -- a pointer from a possible person, received from an API response object.

    Each of the arguments that should have a string that accepts both
    strings.
     */
    public function __construct(array $searchParams = [], SearchRequestConfiguration $configuration = NULL){

        if (is_null(self::$default_configuration)) {
            self::$default_configuration = new SearchRequestConfiguration();
        }

        $person = $searchParams['person'] ?? new Person();
        $this->contact_id = $searchParams['contact_id'] ?? NULL;

        if (!empty($searchParams['first_name']) || !empty($searchParams['middle_name']) || !empty($searchParams['last_name'])) {
            $name = new Name(['first' => $searchParams['first_name'], 'middle' => $searchParams['middle_name'], 'last' => $searchParams['last_name']]);
            $person->add_fields([$name]);
        }

        if (!empty($searchParams['raw_name'])) {
            $person->add_fields([new Name(['raw' => $searchParams['raw_name']])]);
        }

        if (!empty($searchParams['email'])) {
            $person->add_fields([new Email(['address' => $searchParams['email']])]);
        }

        if (!empty($searchParams['phone'])) {
            $person->add_fields([Phone::from_text($searchParams['phone'])]);
        }

        if (!empty($searchParams['username'])) {
            $person->add_fields([new Username(['content' => $searchParams['username']])]);
        }

        if (!empty($searchParams['user_id'])) {
            $person->add_fields([new Userid(['content' => $searchParams['user_id']])]);
        }

        if (!empty($searchParams['url'])) {
            $person->add_fields([new Url(['url' => $searchParams['url']])]);
        }

        if (!empty($searchParams['country']) || !empty($searchParams['state']) || !empty($searchParams['city'])) {
            $country    = $searchParams['country'] ?? NULL;
            $state      = $searchParams['state'] ?? NULL;
            $city       = $searchParams['city'] ?? NULL;
            $address = new Address(['country' => $country, 'state' => $state, 'city' => $city]);
            $person->add_fields([$address]);
        }

        if (!empty($searchParams['raw_address'])) {
            $person->add_fields([new Address(['raw' => $searchParams['raw_address']])]);
        }

        if (!empty($searchParams['from_age']) || !empty($searchParams['to_age'])) {
            $dob = Dob::from_age_range($searchParams['from_age'] ?? 0, $searchParams['to_age'] ?? 1000);
            $person->add_fields([$dob]);
        }

        if (!empty($searchParams['search_pointer'])) {
            $person->search_pointer = $searchParams['search_pointer'];
        }

        $this->person = $person;
        $this->configuration = $configuration;
    }

    /**
     * @param bool $strict
     *
     * Check if the request is valid and can be sent, raise InvalidArgumentException if not.

    `strict` is a boolean argument that defaults to true which means an
    exception is raised on every invalid query parameter, if set to false
    an exception is raised only when the search request cannot be performed
    because required query params are missing.
     */
    public function validate_query_params(bool $strict = true){

        if (empty($this->get_effective_configuration()->api_key)) {
            throw new \InvalidArgumentException('API key is missing');
        }

        if ($strict && (isset($this->get_effective_configuration()->show_sources) &&
                !in_array($this->get_effective_configuration()->show_sources, ["all", "matching", "true"]))
        ) {
            throw new \InvalidArgumentException('show_sources has a wrong value, should be "matching", "all" or "true"');
        }

        if ($strict && isset($this->get_effective_configuration()->minimum_probability) &&
            (!(is_float($this->get_effective_configuration()->minimum_probability) ||
                (0. < $this->get_effective_configuration()->minimum_probability ||
                    $this->get_effective_configuration()->minimum_probability > 1)))
        ) {
            throw new \InvalidArgumentException('minimum_probability should be a float between 0 and 1');
        }

        if ($strict && isset($this->get_effective_configuration()->minimum_match) &&
            (!(is_float($this->get_effective_configuration()->minimum_match) ||
                (0. < $this->get_effective_configuration()->minimum_match ||
                    $this->get_effective_configuration()->minimum_match > 1)))
        ) {
            throw new \InvalidArgumentException('minimum_match should be a float between 0 and 1');
        }

        if ($strict && isset($this->get_effective_configuration()->infer_persons) &&
            (!(is_bool($this->get_effective_configuration()->infer_persons) ||
                is_null($this->get_effective_configuration()->infer_persons)))
        ) {
            throw new \InvalidArgumentException('infer_persons must be true, false or null');
        }

        if ($strict && $unsearchable = $this->person->unsearchable_fields()) {
            $display_strings = array_map(create_function('$field', 'return $field->get_representation();'), $unsearchable);
            throw new \InvalidArgumentException(sprintf('Some fields are unsearchable: %s', implode(', ', $display_strings)));
        }

        if (!$this->person->is_searchable()) {
            throw new \InvalidArgumentException('No valid name/username/phone/email/address/user_id/url in request');
        }
    }

    /**
     * @return string
     */
    public function url():string{
        // The URL of the request (string).
        return $this->get_base_url() . http_build_query($this->get_query_params());
    }

    /**
     * @param bool $strict_validation
     * @return array
     * @throws Error
     *
    Send the request and return the response or raise SearchAPIError.

    Calling this method blocks the program until the response is returned,

    The response is returned as a SearchAPIResponse object
    Also raises an SearchAPIError object in case of an error

    `strict_validation` is a bool argument that's passed to the
    validate_query_params method.
     */
    public function send(bool $strict_validation = true):array {

        $this->validate_query_params($strict_validation);
        $curl = curl_init();
        $params = $this->get_query_params();

        $url = $this->get_base_url();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_VERBOSE => 0,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => Utils::PIPLAPI_USERAGENT,
            CURLOPT_POST => count($params),
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => ['Expect:']
        ]);
        $resp = curl_exec($curl);

        list($header_raw, $body) = explode("\r\n\r\n", $resp, 2);
                
        $headers = $this->extract_headers_from_curl($header_raw);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (in_array($http_status, range(200, 299))) {
            //return json_decode($body, true);
            // Trying to parse header_raw from curl request
            $res = SearchAPIResponse::from_array(json_decode($body, true), $headers);
            // save the raw json to response object
            $res->raw_json = $body;
           
            $matched = $res->persons_count == 1? 1 : 0;
            $body = json_decode($body,true);
            $ret = ['code' => 200, 'message' => $body];
            //Save into model.
            //$ret = self::savePipl($body, $headers, $this->contact_id, $changed, $trigger_by);
            $ret['message']['matched'] = $matched;
            
            return $ret;
            
        } elseif ($resp) {
            $err = SearchAPIError::from_array(json_decode($body, true), $headers);
            throw $err;
        } else {
            $err = SearchAPIError::from_array(
                ["error" => curl_error($curl),
                    "warnings" => null,
                    "@http_status_code" => $http_status],
                $headers);
            throw $err;
        }
    }

    /**
     * @return \Check\Api\PiplClasses\SearchRequestConfiguration
     */
    private function get_effective_configuration():SearchRequestConfiguration {
        if (is_null($this->configuration)) {
            return self::get_default_configuration();
        }
        return $this->configuration;
    }

    /**
     * @return array
     */
    private function get_query_params():array {

        $query = ['key' => $this->get_effective_configuration()->api_key];
        if ($this->person->search_pointer) {
            $query['search_pointer'] = $this->person->search_pointer;
        } elseif ($this->person) {
            $query['person'] = json_encode($this->person->to_array());
        }
        if ($this->get_effective_configuration()->show_sources) {
            $query['show_sources'] = $this->get_effective_configuration()->show_sources;
        }
        if (isset($this->get_effective_configuration()->live_feeds)) {
            $query['live_feeds'] = $this->get_effective_configuration()->live_feeds;
        }
        if (isset($this->get_effective_configuration()->hide_sponsored)) {
            $query['hide_sponsored'] = $this->get_effective_configuration()->hide_sponsored;
        }
        if ($this->get_effective_configuration()->minimum_probability) {
            $query['minimum_probability'] = $this->get_effective_configuration()->minimum_probability;
        }
        if ($this->get_effective_configuration()->minimum_match) {
            $query['minimum_match'] = $this->get_effective_configuration()->minimum_match;
        }
        if ($this->get_effective_configuration()->match_requirements) {
            $query['match_requirements'] = $this->get_effective_configuration()->match_requirements;
        }
        if ($this->get_effective_configuration()->source_category_requirements) {
            $query['source_category_requirements'] = $this->get_effective_configuration()->source_category_requirements;
        }
        if ($this->get_effective_configuration()->infer_persons) {
            $query['infer_persons'] = $this->get_effective_configuration()->infer_persons;
        }

        return $query;
    }

    /**
     * @return string
     */
    private function get_base_url():string {
        $prefix = $this->get_effective_configuration()->use_https ? "https://" : "http://";
        return $prefix . self::$base_url;
    }

    /**
     * @param $header_raw
     * @return array
     */
    private function extract_headers_from_curl($header_raw):array {
        $headers = [];
        foreach (explode("\r\n", $header_raw) as $i => $line) {
            if ($i === 0)
                $headers['http_code'] = $line;
            else {
                list ($key, $value) = explode(': ', $line);
                $key = strtolower($key);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}


