<?php
namespace Check\Validate;

use Techbanx\Validator;

class Ip extends Validator
{
    public function initialize($entity = null, $options = null) {
        $this->ip('ip', 0);
        $this->Numericality('companyId', 0, 0);
    }
}