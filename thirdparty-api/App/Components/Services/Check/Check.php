<?php
/**
 * Check service
 *
 * @package Check
 * @author Techbanx (TH)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party */

namespace Check;

use Techbanx\{
    Service,
    ServiceInterface
};
use Check\Api\{
    Emailage,
    LastNameOrigin,
    MaxMind,
    Pipl
};
class Check extends Service implements ServiceInterface
{
    private $lastName;
    private $email;
    private $ip;
    private $companyId;

    public function init(): array {
        // This function is automatically called when you make a doAction
        return [
            'wussy' => [
                'methods'   => ['GET'],
                'get'       => [],
                'acl'       => ['public']
            ],
            'checkName' => [
                'methods'   => ['GET'],
                'get'       => ['lastName' => 'alpha'],
                'acl'       => ['public']
            ],
            'checkEmail' => [
                'methods'   => ['POST'],
                'post'      => 'Email',
                'acl'       => ['public']
            ],
            'checkIp' => [
                'methods'   => ['POST'],
                'post'      => 'Ip',
                'acl'       => ['public']
            ],
            'checkPipl' => [
                'methods'   => ['POST'],
                'post'      => 'Pipl',
                'acl'       => ['public']
            ],
            'checkPiplTest' => [
                'methods'   => ['GET'],
                'get'       => [],
                'acl'       => ['public']
            ]
        ];
    }

    /**
     * Returns You wussy!! (test method)
     */
    public function wussy() {
        $this->returnJson(200, 'You wussy!!');
    }

    /**
     * Returns last name origin
     */
    public function checkName() {
        // Params
        $this->lastName = $this->uri->params['lastName'];
        // Class call
        $checkClass = new LastNameOrigin($this->lastName);
        $res = $checkClass->Call();
        // Return result
        $this->returnJson($res['code'], $res['message']);
    }

    /**
     * Returns email check
     */
    public function checkEmail() {
        // Params
        $this->email = $this->uri->params['email'];
        // Class call
        $checkClass = new Emailage($this->email);
        $res = $checkClass->reqManager();
        // Return result
        $this->returnJson($res['code'], $res['message']);
    }

    /**
     * Returns Ip check
     */
    public function checkIp() {
        // Params
        $this->ip           = $this->uri->params['ip'];
        $this->companyId    = $this->uri->params['companyId'];
        // Class Call
        $checkClass = new MaxMind($this->ip, $this->companyId);
        $res = $checkClass->call();
        // Return result
        $this->returnJson($res['code'], $res['message']);
    }

    /**
     * Return Pipl Check
     */
    public function checkPipl(){
        // Params
        $searchParams = (array) $this->uri->params;
        // Class Call
        $checkClass = new Pipl();
        $res = $checkClass->send($searchParams);
        // Return result
        $this->returnJson($res['code'], $res['message']);
    }

    /**
     * Return Pipl Check Test
     */
    public function checkPiplTest(){
        // Params
        $checkClass = new Pipl();
        // Class Call
        $res = $checkClass->test();
        // Return result
        $this->returnJson($res['code'], $res['message']);
    }
}