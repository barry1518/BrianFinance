<?php
/**
 * @package Check\Api\PiplClasses
 * @author Techbanx (Yuan He)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party
 *
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

/**
 * Class SearchAPIError
 * @package Check\Api\PiplClasses
 *An exception raised when the response from the search API contains an error.
 */
class SearchAPIError extends Error{
}

