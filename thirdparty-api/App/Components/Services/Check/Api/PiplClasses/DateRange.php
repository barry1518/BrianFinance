<?php
namespace Check\Api\PiplClasses;
use Techbanx\Service;

/**
 * Class DateRange
 * @package Check\Api\PiplClasses
 *
 * A time intervel represented as a range of two dates.
   DateRange objects are used inside DOB, Job and Education objects.
 */
class DateRange extends Service{

    public $start;
    public $end;

    /**
     * DateRange constructor.
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * `start` and `end` are DateTime objects, at least one is required.

        For creating a DateRange object for an exact date (like if exact
        date-of-birth is known) just pass the same value for `start` and `end`.
     */
    function __construct(\DateTime $start, \DateTime$end){

        if (!empty($start)){
            $this->start = $start;
        }
        if (!empty($end)){
            $this->end = $end;
        }

        if (empty($this->start) && empty($this->end)){
            throw new \InvalidArgumentException('Start/End parameters missing');
        }

        if (($this->start && $this->end) && ($this->start > $this->end)){
            $t = $this->end;
            $this->end = $this->start;
            $this->start = $t;
        }
    }

    /**
     * @return string
     *
     * Return a representation of the object.
     */
    public function __toString():string {

        if($this->start && $this->end) {
            return sprintf('%s - %s', Utils::piplapi_date_to_str($this->start),
                Utils::piplapi_date_to_str($this->end));
        } elseif($this->start) {
            return Utils::piplapi_date_to_str($this->start);
        }
        return Utils::piplapi_date_to_str($this->end);
    }

    /**
     * @return bool
     *
     * True if the object holds an exact date (start=end),False otherwise.
     */
    public function is_exact():bool {

        return ($this->start == $this->end);
    }

    /**
     * @return \DateTime
     *
     * The middle of the date range (a DateTime object).
     */
    public function middle():\DateTime {

        if($this->start && $this->end) {
            $diff = ($this->end->format('U') - $this->start->format('U')) / 2;
            $newts = $this->start->format('U') + $diff;
            $newdate = new \DateTime('@' . $newts, new \DateTimeZone('GMT'));
            return $newdate;
        }
        return $this->start ? $this->start : $this->end;
    }

    /**
     * @return array|null
     *
     * A tuple of two ints - the year of the start date and the year of the end date.
     */
    public function years_range():?array {

        if(!($this->start && $this->end)){
            return NULL;
        }
        return [$this->start->format('Y'), $this->end->format('Y')];
    }

    /**
     * @param int $start_year
     * @param int $end_year
     * @return DateRange
     *
     * Transform a range of years (two ints) to a DateRange object.
     */
    public static function from_years_range(int $start_year, int $end_year):DateRange {

        $newstart = new \DateTime($start_year . '-01-01', new \DateTimeZone('GMT'));
        $newend = new \DateTime($end_year . '-12-31', new \DateTimeZone('GMT'));
        return new DateRange($newstart, $newend);
    }

    /**
     * @param array $d
     * @return DateRange
     *
     * Transform the dict to a DateRange object.
     */
    public static function from_array(array $d):DateRange{

        $newstart = $d['start'] ?? NULL;
        $newend = $d['end'] ?? NULL;
        if($newstart) {
            $newstart = Utils::piplapi_str_to_date($newstart);
        }
        if($newend){
            $newend = Utils::piplapi_str_to_date($newend);
        }
        return new DateRange($newstart, $newend);
    }

    /**
     * @return array
     *
     * Transform the date-range to a dict.
     */
    public function to_array():array {

        $d = [];
        if($this->start) {
            $d['start'] = Utils::piplapi_date_to_str($this->start);
        }
        if($this->end){
            $d['end'] = Utils::piplapi_date_to_str($this->end);
        }
        return $d;
    }
}