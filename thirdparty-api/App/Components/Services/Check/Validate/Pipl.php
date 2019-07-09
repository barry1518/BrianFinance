<?php
/**
 * Check service
 *
 * @package Check
 * @author Techbanx (YH)
 * @version 1.0.0
 * @copyright (c) 2018, Techbanx
 * @category Third Party */

namespace Check\Validate;

use Techbanx\Validator;

class Pipl extends Validator {
    public function initialize($entity = null, $options = null) {

        $this->Optional('person');

        $this->PresenceOf('contact_id');
        $this->Numericality('contact_id', 0, 0);

        $this->Optional('first_name');

        $this->Optional("last_name");

        $this->Optional('middle_name');

        $this->Optional('raw_name');

        $this->Optional('email');
        $this->Email('email');

        $this->Optional('phone');

        $this->Optional('username');

        $this->Optional('user_id');

        $this->Optional('url');

        $this->PresenceOf('country');//Country Code

        $this->PresenceOf('state');//State Code

        $this->PresenceOf('city');

        $this->Optional('raw_address');

        $this->Optional('from_age');

        $this->Optional('to_age');

        $this->Optional('search_pointer');
    }
}