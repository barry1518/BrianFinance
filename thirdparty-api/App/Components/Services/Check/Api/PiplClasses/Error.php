<?php
namespace Check\Api\PiplClasses;

/**
 * Class Error
 * @package Check\Api\PiplClasses
 *
 * An exception raised when the response from the API contains an error.
 */
class Error extends \Exception{

    private $error;
    private $warnings;
    private $http_status_code;

    /**
     * Error constructor.
     * @param string $error
     * @param string $warnings
     * @param int $http_status_code
     * @param int|NULL $qps_allotted
     * @param int|NULL $qps_current
     * @param int|NULL $quota_allotted
     * @param int|NULL $quota_current
     * @param \DateTime|NULL $quota_reset
     *
     * Extend Exception::__construct and set two extra attributes
     */
    public function __construct(string $error, string $warnings, int $http_status_code, int $qps_allotted = NULL,
                                int $qps_current = NULL, int $quota_allotted = NULL, int $quota_current = NULL,
                                \DateTime $quota_reset = NULL){

        parent::__construct($error);
        $this->error = $error;
        $this->warnings = $warnings;
        $this->http_status_code = $http_status_code;
        $this->qps_allotted = $qps_allotted;
        $this->qps_current = $qps_current;
        $this->quota_allotted = $quota_allotted;
        $this->quota_current = $quota_current;
        $this->quota_reset = $quota_reset;
    }

    /**
     * @return bool
     *
     * A bool that indicates whether the error is on the user's side.
     */
    public function is_user_error():bool {

        return in_array($this->http_status_code, range(400, 499));
    }

    /**
     * @return bool
     *
     * A bool that indicates whether the error is on Pipl's side.
     */
    public function is_pipl_error():bool {

        return !$this->is_user_error();
    }

    /**
     * @param array $d
     * @param array $headers
     * @return Error
     *
     * Transform the dict to a error object and return the error.
     */
    public static function from_array(array $d, array $headers=[]):Error{

        $qps_allotted = intval($headers['x-apikey-qps-allotted']) ?? null;
        $qps_current = intval($headers['x-apikey-qps-current']) ?? null;
        $quota_allotted = intval($headers['x-apikey-quota-allotted']) ?? null;
        $quota_current = intval($headers['x-apikey-quota-current']) ?? null;
        $quota_reset = \DateTime::createFromFormat(Utils::PIPLAPI_DATE_QUOTA_RESET, $headers['x-quota-reset']) ?? null;

        $error = $d['error'] ?? "";
        $warnings = $d['warnings'] ?? "";
        $http_status_code = $d['@http_status_code'] ?? 0;

        return new self($error, $warnings, $http_status_code, $qps_allotted, $qps_current,
                        $quota_allotted, $quota_current, $quota_reset);
    }

    /**
     * @return array
     *
     * Return a dict representation of the error.
     */
    public function to_array():array {

        return ['error' => $this->error,
            '@http_status_code' => $this->http_status_code, 'warnings' => $this->warnings];
    }
}