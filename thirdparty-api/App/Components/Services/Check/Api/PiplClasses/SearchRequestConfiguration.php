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

class SearchRequestConfiguration extends Service{

    public $api_key = NULL;
    public $minimum_probability = NULL;
    public $minimum_match = NULL;
    public $show_sources = NULL;
    public $live_feeds = NULL;
    public $use_https = NULL;
    public $hide_sponsored = NULL;
    public $match_requirements = NULL;
    public $source_category_requirements = NULL;
    public $infer_persons = NULL;

    /**
     * SearchRequestConfiguration constructor.
     * @param string $api_key
     * @param float $minimum_probability
     * @param float $minimum_match
     * @param string $show_sources
     * @param bool $live_feeds
     * @param bool $hide_sponsored
     * @param bool $use_https
     * @param string $match_requirements
     * @param string|NULL $source_category_requirements
     * @param bool|NULL $infer_persons
     */
    function __construct(string $api_key = "SOCIAL-PREMIUM-0x1mck7klturzsewa9tgvfai",
                         float $minimum_probability = 0.9, float $minimum_match = 0.9,
                         string $show_sources = 'all',bool $live_feeds = true,
                         bool $hide_sponsored = false, bool $use_https = true,
                         string $match_requirements = 'social_profiles OR names OR addresses',
                         string $source_category_requirements = NULL, bool $infer_persons = NULL){

        $this->api_key = $api_key;
        $this->minimum_probability = $minimum_probability;
        $this->minimum_match = $minimum_match;
        $this->show_sources = $show_sources;
        $this->live_feeds = $live_feeds;
        $this->hide_sponsored = $hide_sponsored;
        $this->use_https = $use_https;
        $this->match_requirements = $match_requirements;
        $this->source_category_requirements = $source_category_requirements;
        $this->infer_persons = $infer_persons;
    }
}

