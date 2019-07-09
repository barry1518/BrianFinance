<?php
/**
 * This class implements the API LastNameOrigin
 *
 * @package Check\Api
 * @author Techbanx (TH)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party
 */

namespace Check\Api;

use Techbanx\Service;

class LastNameOrigin extends Service
{
    /**
     * URL of the web scraper
     */
    CONST URL = 'http://forebears.io/surnames?q=';

    /**
     * @var string $lastName
     */
    private $lastName;

    /**
     * __construct
     * LastNameOrigin constructor.
     * @param $lastName
     */
    public function __construct(string $lastName) {
        $this->lastName= trim(str_replace(' ', '+', $lastName));
    }

    /**
     * call
     * Manages the call of the 3rd party
     * @return array
     */
    public function call(): array {
        try {
            $content = $this->fileGetContentCurl();// Parsing the content
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($content);
            libxml_clear_errors();
            $xpath = new \DOMXpath($dom);
            $element = $xpath->query('//div[@class="inr match clearfix"]/div[2]/span')->item(0);
            $data = ['lastNameOrigin' => 'unknown'];// If the element exists
            if ($element) {
                $element = $element->getAttribute('class');
                $elementExplode = explode(' ', $element);
                $data = ['lastNameOrigin' => $elementExplode[1]];
            }
            // Return the result
            return ['code' => 200, 'message' => (array_key_exists('lastNameOrigin', $data) && isset($data['lastNameOrigin']) ? $data : ['lastNameOrigin' => 'unknown'])];
        } catch (\ErrorException $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    /**
     * fileGetContentCurl
     * Gets the content of the web page
     * @return mixed
     * @throws \ErrorException
     */
    private function fileGetContentCurl() {
        // Try the cURL
        try {
            $ch = curl_init(); // Create a new cURL resource

            // Set the options for a cURL transfer
            $opt = [
                CURLOPT_URL             => self::URL . $this->lastName,
                CURLOPT_HEADER          => false,
                CURLOPT_AUTOREFERER     => true,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTPHEADER      => ['User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36']
            ];
            curl_setopt_array($ch, $opt);

            $data = curl_exec($ch); // Perform a cURL session
            curl_close($ch); // Close cURL resource, and free up system resources

            return $data; // Return the data
        } catch(\ErrorException $e) {
            throw new \ErrorException('Erreur Curl : ' . curl_error($ch), curl_errno($ch));
        }
    }
}