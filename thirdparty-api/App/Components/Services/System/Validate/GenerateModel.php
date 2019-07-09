<?php
namespace System\Validate;

use Techbanx\Validator;

class GenerateModel extends Validator
{
    public function initialize($entity = null, $options = null){
        $this->PresenceOf('table_name');
        $this->PresenceOf('database');
    }
}