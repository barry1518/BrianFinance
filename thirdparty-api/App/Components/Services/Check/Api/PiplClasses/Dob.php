<?php
namespace Check\Api\PiplClasses;
/**
 * Class Dob
 * @package Check\Api\PiplClasses
 *
 * Date-of-birth of A person.
    Comes as a DateRange (the exact date is within the range, if the exact
    date is known the range will simply be with start=end).
 */
class Dob extends Field{

    protected $children = ['date_range', 'display'];

    /**
     * Dob constructor.
     * @param array $params
     *
     *`date_range` is A DateRange object (DateRange), the date-of-birth is within this range.
     */
    function __construct(array $params=[]){

        extract($params);
        parent::__construct($params);

        if (!empty($date_range)){
            $this->date_range = $date_range;
        }
        if (!empty($display)){
            $this->display = $display;
        }
    }

    /**
     * @return bool
     */
    public function is_searchable():bool {

        return (!empty($this->date_range));
    }

    /**
     * @return int|null
     *
     * The estimated age of the person.
        Note that A Dob object is based on a date-range and the exact date is
        usually unknown so for age calculation the the middle of the range is
        assumed to be the real date-of-birth.
     */
    public function age():?int{

        if (!empty($this->date_range)){
            $dob = $this->date_range->middle();
            $today = new \DateTime('now', new \DateTimeZone('GMT'));

            $diff = $today->format('Y') - $dob->format('Y');

            if ($dob->format('z') > $today->format('z')){
                $diff -= 1;
            }

            return $diff;
        }
        return NULL;
    }

    /**
     * @return array
     *
     * An array of two ints - the minimum and maximum age of the person.
     */
    public function age_range():array {

        if (empty($this->date_range)){
            return [NULL, NULL];
        }
        if(empty($this->date_range->start) || empty($this->date_range->end)){
            return [$this->age(), $this->age()];
        }

        $start_date = new DateRange($this->date_range->start, $this->date_range->start);
        $end_date = new DateRange($this->date_range->end, $this->date_range->end);
        $start_age = new Dob(['date_range' => $start_date]);
        $start_age = $start_age->age();
        $end_age = new Dob(['date_range' => $end_date]);
        $end_age = $end_age->age();

        return ([$end_age, $start_age]);
    }

    /**
     * @param int $birth_year
     * @return Dob
     *
     * Take a person's birth year (int) and return a new Dob object suitable for him.
     */
    public static function from_birth_year(int $birth_year):Dob{

        if (!($birth_year > 0)){
            throw new \InvalidArgumentException('birth_year must be positive');
        }

        $date_range = DateRange::from_years_range($birth_year, $birth_year);
        return (new Dob(['date_range' => $date_range]));
    }

    /**
     * @param \DateTime $birth_date
     * @return Dob
     *
     * Take a person's birth date (Date) and return a new Dob object suitable for him.
     */
    public static function from_birth_date(\DateTime $birth_date):Dob{

        if (!($birth_date <= new \DateTime('now', new \DateTimeZone('GMT')))){
            throw new \InvalidArgumentException('birth_date can\'t be in the future');
        }

        $date_range = new DateRange($birth_date, $birth_date);
        return (new Dob(['date_range' => $date_range]));
    }

    /**
     * @param int $age
     * @return Dob
     *
     * Take a person's age (int) and return a new Dob object suitable for him.
     */
    public static function from_age(int $age):Dob{

        return (Dob::from_age_range($age, $age));
    }

    /**
     * @param int $start_age
     * @param int $end_age
     * @return Dob
     *
     * Take a person's minimal and maximal age and return a new Dob object suitable for him.
     */
    public static function from_age_range(int $start_age, int $end_age):Dob{

        if (!($start_age >= 0 && $end_age >= 0)){
            throw new \InvalidArgumentException('start_age and end_age can\'t be negative');
        }

        if ($start_age > $end_age){
            $t = $end_age;
            $end_age = $start_age;
            $start_age = $t;
        }

        $start_date = new \DateTime('now', new \DateTimeZone('GMT'));
        $end_date = new \DateTime('now', new \DateTimeZone('GMT'));

        $start_date->modify('-' . $end_age . ' year');
        $start_date->modify('-1 year');
        $start_date->modify('+1 day');
        $end_date->modify('-' . $start_age . ' year');

        $date_range = new DateRange($start_date, $end_date);
        return (new Dob(['date_range' => $date_range]));
    }
}

