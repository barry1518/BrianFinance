<?php
namespace Check\Api\PiplClasses;

/**
 * Class Email
 * @package Check\Api\PiplClasses
 *
 * An email address of a person with the md5 of the address, might come
    in some cases without the address itself and just the md5 (for privacy
    reasons).
 */
class Email extends Field{

    protected $attributes = ['type', "disposable", "email_provider"];
    protected $children = ['address', 'address_md5'];
    protected $types_set = ['personal', 'work'];
    private $re_email = '/^[a-zA-Z0-9\'._%\-+]+@[a-zA-Z0-9._%\-]+\.[a-zA-Z]{2,24}$/';

    /**
     * Email constructor.
     * @param array $params
     *
     *`address`, `address_md5`, `type` should be strings.
     *`type` is one of PiplApl_Email::$types_set.
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        if (!empty($address)){
            $this->address = $address;
        }
        if (!empty($address_md5)){
            $this->address_md5 = $address_md5;
        }
        if (!empty($type)){
            $this->type = $type;
        }
        if (!empty($disposable)){
            $this->disposable = $disposable;
        }
        if (!empty($email_provider)){
            $this->email_provider = $email_provider;
        }
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the address is a valid email address.
     */
    public function is_valid_email():bool {

        return (!empty($this->address) && preg_match($this->re_email, $this->address));
    }

    /**
     * @return bool
     *
     * A bool value that indicates whether the email is a valid email to search by.
     */
    public function is_searchable():bool{

        return !empty($this->address_md5) || $this->is_valid_email();
    }
    /**
     * @param $name
     * @return mixed|null
     *
     * Needed to catch username and domain
     */
    public function __get($name){
        if (0 == strcasecmp($name, 'username')){
            if ($this->is_valid_email()){
                $all = explode('@', $this->address);
                return $all[0];
            }
        }
        else if (0 == strcasecmp($name, 'domain')){
            if ($this->is_valid_email()){
                $all = explode('@', $this->address);
                return $all[1];
            }
        }
        return parent::__get($name);
    }

    /**
     * @return string
     */
    public function __toString():string {
        return $this->address ? $this->address : "";
    }
}

