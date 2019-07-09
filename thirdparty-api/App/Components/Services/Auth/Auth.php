<?php
/**
 * Auth service
 *
 * @package Auth
 * @author Techbanx
 * @version 1.0.0
 */
namespace Auth;

use Techbanx\{
    Service,
    ServiceInterface,
    AuthInterface
};

class Auth extends Service implements ServiceInterface, AuthInterface
{
    public function init(): array{
        return [
            'login' => [
                'methods'   => ['POST'],
                'post'      => 'Login',
                'acl'       => ['public']
            ],
            'logout' => [
                'methods'   => ['GET'],
                'get'      => [],
                'acl'       => ['public']
            ]
        ];
    }

    /**
     * User/Contact authentication
     */
    public function login(){
        $post = $this->uri->params;
        $whois = [];
        $host_type = $this->core->whois['host_type'];//by default => backend

    /* ADAPT CODE HERE ********** ADAPT CODE HERE ********** ADAPT CODE HERE ********** ADAPT CODE HERE ********** ADAPT CODE HERE ***********/
    /* |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     |     | */
    /* V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V     V */

        //do your stuff and fill $whois if everything is good
        //if something wrong use $this->returnError
        switch($host_type){
            case 'backend':
                $whois = [
                    'lang'  => 'en',
                    'role'  => 'admin',
                    'id'    => 1,
                    //some optional stuffs, in fact everything you want
                    'name'  => 'alex',
                    'email' => 'ab@techbanx.com'
                ];
                break;
            default:
                $this->returnError(401, 'Unauthorized access : undefined host type');
                break;
        }

    /************************************DONT TOUCH CODE BELOW UNLESS YOU KNOW WHAT YOU DO ! *************************************************/
        //generate token, save user and send everything in the response
        $this->core->saveLogin($whois);//return 200, ['token'=> 'abcDEF', 'whois'=>['host', 'lang', 'role', 'id', ...........]
    }

    /**
     * User/Contact logout
     */
    public function logout(){
        $this->core->logout();
    }
}