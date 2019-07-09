<?php
/**
 * This class implements the API MaxMind (GeoIP2)
 *
 * @package Check\Api
 * @author Techbanx (TH)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party
 */

namespace Check\Api;

use Techbanx\Service;

class MaxMind extends Service
{
    /**
     * MaxMind config params
     */
    protected $mxId;
    protected $mxLicense;
    protected $mxFraudInsights;
    protected $mxCert;

    /**
     * @var string IP address
     */
    protected $ip; // Ip address

    /**
     * @var array response
     */
    protected $ipDetails = [ // Ip details
        'creation_date'                 => '',
        'company_id'                    => 0,
        'contact_id'                    => 0,
        'queries_remaining'             => '',
        'risk_score'                    => 0,
        'risk'                          => 0,
        'proxy'                         => 0,
        'continent_code'                => '',
        'continent_geoid'               => '',
        'continent_name_en'             => '',
        'continent_name_fr'             => '',
        'country_confidence'            => '',
        'country_is_high_risk'          => '',
        'country_code'                  => '',
        'country_geoid'                 => '',
        'country_name_en'               => '',
        'country_name_fr'               => '',
        'city_confidence'               => '',
        'city_geoid'                    => '',
        'city_name_en'                  => '',
        'city_name_fr'                  => '',
        'location_latitude'             => '',
        'location_accuracy_radius'      => '',
        'location_average_income'       => '',
        'location_longitude'            => '',
        'location_population_density'   => '',
        'location_time_zone'            => '',
        'location_metro_code'           => '',
        'location_local_time'           => '',
        'postal_code'                   => '',
        'postal_confidence'             => '',
        'registered_country_code'       => '',
        'registered_country_geoid'      => '',
        'registered_country_name_en'    => '',
        'registered_country_name_fr'    => '',
        'represented_country_code'      => '',
        'represented_country_geoid'     => '',
        'represented_country_name_en'   => '',
        'represented_country_name_fr'   => '',
        'represented_country_type'      => '',
        'state_code'                    => '',
        'state_confidence'              => '',
        'state_geoid'                   => '',
        'state_name_en'                 => '',
        'state_name_fr'                 => '',
        'autonomous_system_organization'=> '',
        'autonomous_system_number'      => '',
        'organization'                  => '',
        'domain'                        => '',
        'isp'                           => '',
        'ip_address'                    => '',
        'user_type'                     => ''
    ];

    //params to check if the ipaddress is authorized
    protected $freeUrl = 'http://ip2c.org/';
    protected $authorizedCountries = ['CA', 'US', 'AU', 'GB', 'ZZ'];

    /**
     * MaxMind constructor.
     * Initiate the class attributes and the API config params
     * @param $ip
     * @param $companyId
     */
    public function __construct(string $ip, int $companyId) {
        // Init class attributes
        $this->ip = $ip;
        $this->ipDetails = [
            'company_id'    => $companyId,
            'creation_date' => date('Y-m-d H:i:s')
        ];

        // MaxMind init params
        $this->mxId             = $this->config->check->maxmind['mxId'];
        $this->mxLicense        = $this->config->check->maxmind['mxLicense'];
        $this->mxFraudInsights  = $this->config->check->maxmind['mxFraudInsights'];
        $this->mxCert           = DIR['service'].'/Services/Check/Certificate/'.$this->config->check->maxmind['mxCert'];
    }

    /**
     * call
     * Manages the call of the 3rd party
     * @return array
     */
    public function call() {
        try {
            // If the IP address is not from an authorized country
            if(!$this->ipCountry()){
                throw new \ErrorException('The IP address is from unauthorized country', 400);
            }
            // Return MaxMind Fraud Insights infos
            $res = $this->mxmFraudInsights();
            // Returns the result
            return ['code' => $res['code'], 'message' => $res['message']];
        } catch(\ErrorException $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    /**
     * ipCountry
     * Returns the country of IP address
     * @return bool
     */
    private function ipCountry() {
        $countryTab = file_get_contents($this->freeUrl . $this->ip);
        $code = '00';
        if(!empty($countryTab)){
            switch ($countryTab[0]) {
                case '1':
                    $reply = explode(';', $countryTab);
                    $code = $reply[1];
                    break;
                case '0':
                case '2':
                    $code = '00';
                    break;
            }
        }
        // Returns true if IP address is from an authorized country, else return false
        return in_array ($code, $this->authorizedCountries);
    }

    /**
     * mxmFraudInsights
     * Return MaxMind Fraud Insights infos
     * @return array
     */
    private function mxmFraudInsights(): array {
        $ch = curl_init(); // Create a new cURL resource

        try {
            // cURL post fields
            $json = '{
                    "device": {
                        "ip_address"        :"'. $this->ip.'",
                        "user_agent"        : "",
                        "accept_language"   : "en-US,en;q=0.8"
                    }
                }';

            // cURL headers
            $headers = [
                'Authorization: Basic ' . base64_encode($this->mxId.':'.$this->mxLicense),
                'Content-Type: application/json'
            ];

            // Set the options for a cURL transfer
            $opt = [
                CURLOPT_URL             => $this->mxFraudInsights,
                CURLOPT_HTTPHEADER      => $headers,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_SSL_VERIFYHOST  => 2,
                CURLOPT_FOLLOWLOCATION  => false,
                CURLOPT_POST            => true,
                CURLOPT_POSTFIELDS      => $json,
                CURLOPT_SSL_VERIFYPEER  => true,
                CURLOPT_CAINFO          => $this->mxCert
            ];
            curl_setopt_array($ch, $opt);

            // Throw a new error exception if cURL request went wrong
            if (curl_errno($ch)) {
                throw new \ErrorException('Erreur Curl : ' . curl_error($ch), curl_errno($ch));
            }

            $data = curl_exec($ch); // Perform a cURL session
            $res = $this->parseData(json_decode($data)); // getting the result
            return ['code' => 200, 'message' => $res]; // Returns the result
        } catch(\ErrorException $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        } finally {
            curl_close($ch); // Close cURL resource, and free up system resources
        }
    }

    /**
     * parseData
     * Parsing the Max Mind Fraud Insights data
     * @param $res
     * @return array
     */
    private function parseData(\stdClass $res): array {
        // Ip details array
        $this->ipDetails = [
            // Nbr of queries remaining
            'queries_remaining'                 => property_exists($res, 'queries_remaining')                                   ? $res->queries_remaining : '',

            // Risk infos
            'risk_score'                        => property_exists($res, 'risk_score')                                          ? $res->risk_score : '',
            'risk'                              => property_exists($res->ip_address, 'risk')                                    ? $res->ip_address->risk : 0,
            'proxy'                             => property_exists($res->ip_address,'risk')                                     ? ($res->ip_address->risk <= 0.2 ? 1 : 2) : 0, // 0 => unknown, 1 => no, 2 => yes

            // Contient infos
            'continent_code'                    => property_exists($res->ip_address->continent, 'code')                         ? $res->ip_address->continent->code : '',
            'continent_geoid'                   => property_exists($res->ip_address->continent, 'geoname_id')                   ? $res->ip_address->continent->geoname_id : '',
            'continent_name_en'                 => property_exists($res->ip_address->continent->names, 'en')                    ? $res->ip_address->continent->names->en : '',
            'continent_name_fr'                 => property_exists($res->ip_address->continent->names, 'fr')                    ? $res->ip_address->continent->names->fr : '',

            // Country infos
            'country_confidence'                => property_exists($res->ip_address->country, 'confidence')                     ? $res->ip_address->country->confidence : '0',
            'country_is_high_risk'              => property_exists($res->ip_address->country, 'is_high_risk')                   ? ($res->ip_address->country->is_high_risk == 1 ? 1 : 0) : '0',
            'country_code'                      => property_exists($res->ip_address->country, 'iso_code')                       ? $res->ip_address->country->iso_code : '00',
            'country_geoid'                     => property_exists($res->ip_address->country, 'geoname_id')                     ? $res->ip_address->country->geoname_id : '',
            'country_name_en'                   => property_exists($res->ip_address->country->names, 'en')                      ? $res->ip_address->country->names->en : '',
            'country_name_fr'                   => property_exists($res->ip_address->country->names, 'fr')                      ? $res->ip_address->country->names->fr : '',

            // City infos
            'city_confidence'                   => property_exists($res->ip_address->city, 'confidence')                        ? $res->ip_address->city->confidence : '0',
            'city_geoid'                        => property_exists($res->ip_address->city, 'geoname_id')                        ? $res->ip_address->city->geoname_id : '',
            'city_name_en'                      => property_exists($res->ip_address->city->names, 'en')                         ? $res->ip_address->city->names->en : '',
            'city_name_fr'                      => property_exists($res->ip_address->city->names, 'fr')                         ? $res->ip_address->city->names->fr : '',

            // Location infos
            'location_accuracy_radius'          => property_exists($res->ip_address->location, 'accuracy_radius')               ? $res->ip_address->location->accuracy_radius : '',
            'location_average_income'           => property_exists($res->ip_address->location, 'average_income')                ? $res->ip_address->location->average_income : '',
            'location_population_density'       => property_exists($res->ip_address->location, 'population_density')            ? $res->ip_address->location->population_density : '',
            'location_latitude'                 => property_exists($res->ip_address->location, 'latitude')                      ? $res->ip_address->location->latitude : '',
            'location_longitude'                => property_exists($res->ip_address->location, 'longitude')                     ? $res->ip_address->location->longitude : '',
            'location_time_zone'                => property_exists($res->ip_address->location, 'time_zone')                     ? $res->ip_address->location->time_zone : '',
            'location_metro_code'               => property_exists($res->ip_address->location, 'metro_code')                    ? $res->ip_address->location->metro_code : '',
            'location_local_time'               => property_exists($res->ip_address->location, 'local_time')                    ? $res->ip_address->location->local_time : '',

            // Postal infos
            'postal_code'                       => property_exists($res->ip_address->postal, 'code')                            ? $res->ip_address->postal->code : '',
            'postal_confidence'                 => property_exists($res->ip_address->postal, 'confidence')                      ? $res->ip_address->postal->confidence : '0',

            // Registered country infos
            'registered_country_code'           => property_exists($res->ip_address->registered_country, 'iso_code')            ? $res->ip_address->registered_country->iso_code : '',
            'registered_country_geoid'          => property_exists($res->ip_address->registered_country, 'geoname_id')          ? $res->ip_address->registered_country->geoname_id : '',
            'registered_country_name_en'        => property_exists($res->ip_address->registered_country->names, 'en')           ? $res->ip_address->registered_country->names->en : '',
            'registered_country_name_fr'        => property_exists($res->ip_address->registered_country->names, 'fr')           ? $res->ip_address->registered_country->names->fr : '',

            // Represented country Infos
            'represented_country_code'          => property_exists($res->ip_address->represented_country, 'iso_code')           ? $res->ip_address->represented_country->iso_code : '',
            'represented_country_geoid'         => property_exists($res->ip_address->represented_country, 'geoname_id')         ? $res->ip_address->represented_country->geoname_id : '',
            'represented_country_name_en'       => property_exists($res->ip_address->represented_country->names, 'en')          ? $res->ip_address->represented_country->names->en : '',
            'represented_country_name_fr'       => property_exists($res->ip_address->represented_country->names, 'fr')          ? $res->ip_address->represented_country->names->fr : '',
            'represented_country_type'          => property_exists($res->ip_address->represented_country, 'type')               ? $res->ip_address->represented_country->type : '',

            // Subdivisions infos
            'state_confidence'                  => property_exists($res->ip_address->subdivisions[0], 'confidence')             ? $res->ip_address->subdivisions[0]->confidence : '0',
            'state_code'                        => property_exists($res->ip_address->subdivisions[0],'iso_code')                ? $res->ip_address->subdivisions[0]->iso_code : '00',
            'state_geoid'                       => property_exists($res->ip_address->subdivisions[0], 'geoname_id')             ? $res->ip_address->subdivisions[0]->geoname_id : '',
            'state_name_en'                     => property_exists($res->ip_address->subdivisions[0]->names, 'en')              ? $res->ip_address->subdivisions[0]->names->en : '',
            'state_name_fr'                     => property_exists($res->ip_address->subdivisions[0]->names, 'fr')              ? $res->ip_address->subdivisions[0]->names->fr : '',

            // Traits infos
            'autonomous_system_organization'    => property_exists($res->ip_address->traits, 'autonomous_system_organization')  ? $res->ip_address->traits->autonomous_system_organization : '',
            'autonomous_system_number'          => property_exists($res->ip_address->traits, 'autonomous_system_number')        ? $res->ip_address->traits->autonomous_system_number : '',
            'organization'                      => property_exists($res->ip_address->traits, 'organization')                    ? $res->ip_address->traits->organization : '',
            'domain'                            => property_exists($res->ip_address->traits, 'domain')                          ? $res->ip_address->traits->domain : '',
            'isp'                               => property_exists($res->ip_address->traits, 'isp')                             ? $res->ip_address->traits->isp : '',
            'ip_address'                        => property_exists($res->ip_address->traits, 'ip_address')                      ? $res->ip_address->traits->ip_address : '',
            'user_type'                         => property_exists($res->ip_address->traits, 'user_type')                       ? $res->ip_address->traits->user_type : ''
        ];
        return $this->ipDetails;
    }
}