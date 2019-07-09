<?php
/**
 * This class implements the Pipl API
 *
 * @package Check\Api
 * @author Techbanx (Yuan He)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party
 */
namespace Check\Api;

use Techbanx\Service;
use Check\Api\PiplClasses\SearchAPIRequest;

/**
 * Class Pipl
 * @package Check\Api
 */
class Pipl extends Service{

    /**
     * send()
     * Pipl request.
     * @param $searchParams
     * @return array
     */
    public function send(array $searchParams):array {
        try{
            $request = new SearchAPIRequest($searchParams);
            return $request->send();

        } catch (\Exception $e){
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    /**
     * test
     * Pipl Api test
     * @return array
     */
    public function test():array {
        try{
            $searchParams1 = [
                'person' => NULL,
                'contact_id' => 10994,
                'first_name' =>'Tess',
                'last_name' =>'Finlay',
                'middle_name' => NULL,
                'raw_name' => NULL,
                'email' =>'tessfinlay@hotmail.com',
                'phone' =>'4167105075',
                'username' => NULL,
                'user_id' => NULL,
                'url' => NULL,
                'country' =>'CA',
                'state' =>'ON',
                'city' =>'Toronto',
                'raw_address' => NULL,
                'from_age' => NULL,
                'to_age' => NULL,
                'search_pointer' => NULL
            ];

            $searchParams = [
                'person' => NULL,
                'contact_id' => 12117,
                'first_name' =>'Christina',
                'last_name' =>'Clark',
                'middle_name' => NULL,
                'raw_name' => NULL,
                'email' =>'christina.clark@ymail.com',
                'phone' => NULL,
                'username' => NULL,
                'user_id' => NULL,
                'url' => NULL,
                'country' =>'CA',
                'state' =>'ON',
                'city' =>'Pickering',
                'raw_address' => NULL,
                'from_age' => NULL,
                'to_age' => NULL,
                'search_pointer' => NULL
            ];
            return $this->send($searchParams);
        } catch (\Exception $e){
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }
}