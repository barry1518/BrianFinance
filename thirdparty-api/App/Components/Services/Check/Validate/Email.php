<?php
namespace Check\Validate;

use Techbanx\Validator;

class Email extends Validator
{
    public function initialize($entity = null, $options = null) {
        $this->Email('email', 0);
    }

}