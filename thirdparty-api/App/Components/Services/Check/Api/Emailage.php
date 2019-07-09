<?php
/**
 * This class implements the API Emailage
 *
 * @package Check\Api
 * @author Techbanx (TH)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party
 */

namespace Check\Api;

use Techbanx\Service;

class Emailage extends Service
{
    /**
     * @var string $email
     */
    protected $email;

    /**
     * Emailage config params
     */
    protected $sId; // String security identifier
    protected $authToken; // String authorization token
    protected $url; // String api url
    protected $requestFormat; // String request format
    protected $oauthVersion; // String OAuth version
    protected $signatureMethod; // String signature method
    protected $method; // String request type


    /**
     * Emailage request build params
     */
    protected $urlParams = []; // Array OAuth params
    protected $urlParametersString; // String url params
    protected $oauthData; // String OAuth data
    protected $signature; // String encoded OAuth data with base64
    protected $requestUrl; // String request url


    /**
     * Emailage response params
     */
    protected $response; // String containing the content of the stream context
    protected $responseData = []; // Array storing the response data

    /**
     * __construct
     * Emailage constructor
     * Initiate the class attributes and the API config params
     * @param $email
     */
    public function __construct(string $email) {
        $this->email= $email; // Email to look up

        // Api configuration params
        $this->sId =                $this->config->check->emailage['sId'];
        $this->authToken =          $this->config->check->emailage['authToken'];
        $this->url =                $this->config->check->emailage['url'];
        $this->requestFormat =      $this->config->check->emailage['requestFormat'];
        $this->oauthVersion =       $this->config->check->emailage['oauthVersion'];
        $this->signatureMethod =    $this->config->check->emailage['signatureMethod'];
        $this->method =             $this->config->check->emailage['method'];
    }

    /**
     * reqManager
     * Request manager
     * @return array
     */
    public function reqManager(): array {
        try {
            // Building the request
            $this->buildReq();
            // If an error while sending the request
            if(!$this->sendReq()){
                throw new \ErrorException('Failed to send the request to Emailage', 400);
            }
            // Parsing the request and storing the result into $res
            $res = $this->parseRes();
            // Returns the result
            return ['code' => $res['code'], 'message' => $res['message']];
        } catch(\ErrorException $e) {
            return ['code' => $e->getCode(), 'message' => $e->getMessage()];
        }
    }

    /**
     * buildReq
     * Building the request
     */
    protected function buildReq() {
        // Initializing the params table
        $this->urlParams['format'] =                    $this->requestFormat;
        $this->urlParams['oauth_version'] =             $this->oauthVersion;
        $this->urlParams['oauth_consumer_key'] =        $this->sId;
        $this->urlParams['oauth_timestamp'] =           time();
        $this->urlParams['oauth_nonce'] =               substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10); // Generates a random string
        $this->urlParams['oauth_signature_method'] =    $this->signatureMethod;

        // Generate URL-encoded query string
        $this->urlParametersString = http_build_query($this->urlParams);

        // Building the OAuth string
        $this->oauthData = urlencode($this->method) . '&' . urlencode($this->url) . '&' . urlencode($this->urlParametersString);

        // Signing the OAuth string with the token then encoding the hash with MIME base64
        $hash = hash_hmac('sha256', $this->oauthData, $this->authToken . '&', true);
        $this->signature = base64_encode($hash);

        // Request url
        $this->requestUrl = $this->url . '?' . $this->urlParametersString . '&oauth_signature=' . urlencode($this->signature);
    }

    /**
     * sendReq
     * Send the request to Emailage API
     * @throws \ErrorException
     */
    protected function sendReq(): bool {
        // Request content
        $content = $this->email . '&';

        // Request options : use key 'http' even if you send the request to https://
        $options = [
            'http' => [
                'header' =>     'Content-Type: application/x-www-form-urlencoded\r\n' .
                    'Connection: close\r\n' .
                    'Content-Length: ' . strlen($content) . '\r\n',
                'method' =>     'POST',
                'content' =>    $content,
            ],
            'ssl' => [
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
            ]
        ];

        // If an error while getting the content of the created stream context
        if(!$this->response = file_get_contents($this->requestUrl, false, stream_context_create($options))){
            throw new \ErrorException('Error while getting the content of the created stream context', '400');
        }
        return true;
    }

    /**
     * parseRes
     * Parsing the response
     * @return array
     * @throws \ErrorException
     */
    protected function parseRes(): array {
        // Test if there is a response
        try {
            // Removing UTF-8 special characters that mess up json_decode when present
            $partialDecode = preg_replace('/[^(\x20-\x7F)]*/', '', urldecode($this->response));

            // Decoding json string into $decodedArray
            $decodedArray = $this->responseData = json_decode($partialDecode, true);

            // Storing into the res table if the status of the response is success and errorCode = 0
            if ($decodedArray['responseStatus']['status'] == 'success' && $decodedArray['responseStatus']['errorCode'] == 0) {
                $dataArray = $decodedArray['query']['results'][0];
                $res = [
                    'email'                 => $decodedArray['query']['email'],
                    'eName'                 => $dataArray['eName'],
                    'emailAge'              => $dataArray['emailAge'],
                    'domainAge'             => $dataArray['domainAge'],
                    'firstVerificationDate' => substr(strtr($dataArray['firstVerificationDate'], 'TZ', '  '), 0, 19),
                    'lastVerificationDate'  => substr(strtr($dataArray['lastVerificationDate'], 'TZ', '  '), 0, 19),
                    'status'                => $dataArray['status'],
                    'country'               => $dataArray['country'],
                    'fraudRisk'             => $dataArray['fraudRisk'],
                    'EAScore'               => $dataArray['EAScore'],
                    'EAReason'              => $dataArray['EAReason'],
                    'EAStatusID'            => $dataArray['EAStatusID'],
                    'EAReasonID'            => $dataArray['EAReasonID'],
                    'EAAdviceID'            => $dataArray['EAAdviceID'],
                    'EAAdvice'              => $dataArray['EAAdvice'],
                    'EARiskBandID'          => $dataArray['EARiskBandID'],
                    'EARiskBand'            => $dataArray['EARiskBand'],
                    'dob'                   => (!array_key_exists('dob', $dataArray) || strlen($dataArray['dob']) != 10)                            ? NULL                                  : $dataArray['dob'],
                    'gender'                => $dataArray['gender'],
                    'location'              => $dataArray['location'],
                    'smfriends'             => $dataArray['smfriends'],
                    'totalhits'             => $dataArray['totalhits'],
                    'uniquehits'            => $dataArray['uniquehits'],
                    'ipaddress'             => array_key_exists('ipaddress', $dataArray)                                                            ? $dataArray['ipaddress']               : NULL,
                    'imageurl'              => $dataArray['imageurl'],
                    'domainrisklevelID'     => $dataArray['domainrisklevelID'],
                    'company'               => $dataArray['company'],
                    'title'                 => $dataArray['title'],
                    'domaincompany'         => $dataArray['domaincompany'],
                    'domaincountryname'     => $dataArray['domaincountryname'],
                    'domaincategory'        => $dataArray['domaincategory'],
                    'domaincorporate'       => $dataArray['domaincorporate'],
                    'domainrisklevel'       => $dataArray['domainrisklevel'],
                    'domainrelevantinfo'    => $dataArray['domainrelevantinfo'],
                    'domainrelevantinfoID'  => $dataArray['domainrelevantinfoID'],
                    'smlinks'               => array_key_exists('smlinks', $dataArray) && is_array($decodedArray['query']['results'][0]['smlinks']) ? json_encode($dataArray['smlinks'])    : NULL,
                    'ip_risklevelid'        => array_key_exists('ip_risklevelid', $dataArray)                                                       ? $dataArray['ip_risklevelid']          : NULL,
                    'ip_risklevel'          => array_key_exists('ip_risklevel', $dataArray)                                                         ? $dataArray['ip_risklevel']            : NULL,
                    'ip_riskreasonid'       => array_key_exists('ip_riskreasonid', $dataArray)                                                      ? $dataArray['ip_riskreasonid']         : NULL,
                    'ip_riskreason'         => array_key_exists('ip_riskreason', $dataArray)                                                        ? $dataArray['ip_riskreason']           : NULL,
                    'ip_reputation'         => array_key_exists('ip_reputation', $dataArray)                                                        ? $dataArray['ip_reputation']           : NULL,
                    'ip_anonymousdetected'  => array_key_exists('ip_anonymousdetected', $dataArray)                                                 ? $dataArray['ip_anonymousdetected']    : NULL,
                    'ip_isp'                => array_key_exists('ip_isp', $dataArray)                                                               ? $dataArray['ip_isp']                  : NULL,
                    'ip_org'                => array_key_exists('ip_org', $dataArray)                                                               ? $dataArray['ip_org']                  : NULL,
                    'ip_userType'           => array_key_exists('ip_userType', $dataArray)                                                          ? $dataArray['ip_userType']             : NULL,
                    'ip_netSpeedCell'       => array_key_exists('ip_netSpeedCell', $dataArray)                                                      ? $dataArray['ip_netSpeedCell']         : NULL,
                    'ip_corporateProxy'     => array_key_exists('ip_corporateProxy', $dataArray)                                                    ? $dataArray['ip_corporateProxy']       : NULL,
                    'ip_continentCode'      => array_key_exists('ip_continentCode', $dataArray)                                                     ? $dataArray['ip_continentCode']        : NULL,
                    'ip_country'            => array_key_exists('ip_country', $dataArray)                                                           ? $dataArray['ip_country']              : NULL,
                    'ip_countryCode'        => array_key_exists('ip_countryCode', $dataArray)                                                       ? $dataArray['ip_countryCode']          : NULL,
                    'ip_region'             => array_key_exists('ip_region', $dataArray)                                                            ? $dataArray['ip_region']               : NULL,
                    'ip_city'               => array_key_exists('ip_city', $dataArray)                                                              ? $dataArray['ip_city']                 : NULL,
                    'ip_callingcode'        => array_key_exists('ip_callingcode', $dataArray)                                                       ? $dataArray['ip_callingcode']          : NULL,
                    'ip_metroCode'          => array_key_exists('ip_metroCode', $dataArray)                                                         ? $dataArray['ip_metroCode']            : NULL,
                    'ip_latitude'           => array_key_exists('ip_latitude', $dataArray)                                                          ? $dataArray['ip_latitude']             : NULL,
                    'ip_longitude'          => array_key_exists('ip_longitude', $dataArray)                                                         ? $dataArray['ip_longitude']            : NULL,
                    'ip_map'                => array_key_exists('ip_map', $dataArray)                                                               ? $dataArray['ip_map']                  : NULL,
                    'emailExists'           => array_key_exists('emailExists', $dataArray)                                                          ? $dataArray['emailExists']             : NULL,
                    'domainExists'          => array_key_exists('domainExists', $dataArray)                                                         ? $dataArray['domainExists']            : NULL,
                    'domainname'            => array_key_exists('domainname', $dataArray)                                                           ? $dataArray['domainname']              : NULL,
                    'result'                => base64_encode(serialize($decodedArray['query']['results'][0]))
                ];
            }
            // Sending the result
            return ['code' => 200, 'message' => $res];
        } catch (\ErrorException $e) {
            throw new \ErrorException('Error parsing the result:' . $e->getMessage(), $e->getCode());
        }
    }
}