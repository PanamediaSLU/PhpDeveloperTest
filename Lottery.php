<?php

class Lottery {

    protected $_frequency; //d, w0100100, m24, y1225
    protected $_draw_time;

    const IS_NEXT = 'Next';
    const IS_LAST = 'Last';

    public function getFrequency() {
        return $this->frequency;
    }

    public function setFrequency($frequency) {
        $this->frequency = $frequency;
    }

    public function getDrawTime() {
        return $this->draw_time;
    }

    public function setDrawTime($draw_time) {
        $this->draw_time = $draw_time;
    }

    /**
     * @param DateTime $now
     * @return \DateTime
     */
    public function getLastDrawDate(\DateTime $now = null) {
        return $this->getDrawDate($now, self::IS_LAST); //Last operation is substraction 
    }

    /**
     * @param \DateTime $now
     * @return \DateTime
     */
    public function getNextDrawDate(\DateTime $now = null) {
        return $this->getDrawDate($now, self::IS_NEXT); //Next operation is addition
    }

    /**
     * @param \DateTime $now
     * @param string $next_or_last
     * @return mixed
     */
    private function getDrawDate(\DateTime $now, $next_or_last) {
        $configParams = substr($this->frequency, 1);
        $strategy = substr($this->frequency, 0, 1);

        switch ($strategy) {
            case 'y': return $this->getDrawFromYearly($configParams, $now, $next_or_last);
            case 'm': return $this->getDrawFromMonthly($configParams, $now, $next_or_last);
            case 'w': return $this->getDrawFromWeekly($configParams, $now, $next_or_last);
            case 'd': return $this->getDrawFromDaily($now, $next_or_last);
            default:
                throw new Exception('Frequency has incorrect format.');
        }
    }

    /**
     * @param \DateTime $date
     * @param string
     * @return \DateTime
     */
    protected function getDrawFromDaily(\DateTime $date, $next_or_last) {
        if ($next_or_last == self::IS_LAST) {
            $condition = $date->format("H:i:s") <= $this->draw_time;
            $operation = 'sub';
        } elseif ($next_or_last == self::IS_NEXT) {
            $condition = $date->format("H:i:s") > $this->draw_time;
            $operation = 'add';
        }

        if ($condition) {
            $one_day = new \DateInterval('P1D');
            return new \DateTime($date->$operation($one_day)->format("Y-m-d {$this->draw_time}"));
        } else {
            return new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        }
    }

    /**
     * @param string $configParams
     * @param \DateTime $date
     * @param string $next_or_last
     * @return \DateTime
     */
    protected function getDrawFromWeekly($configParams, \DateTime $date, $next_or_last) {
        $weekday_index = (int) $date->format('N') - 1;
        $result_date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        $hour = $date->format("H:i:s");
        $one_day = new \DateInterval('P1D');
        $days_to_check = 7;

        if ($next_or_last == self::IS_LAST) {
            $hourCondition = $hour > $this->draw_time;
            $operation = 'sub';
            $mod = 1;
        } elseif ($next_or_last == self::IS_NEXT) {
            $hourCondition = $hour < $this->draw_time;
            $operation = 'add';
            $mod = -1;
        }

        while ($days_to_check) {
            if (1 == (int) $configParams[$weekday_index] && ($days_to_check < 7 || $hourCondition)) {
                return $result_date;
            } else {
                $result_date = $result_date->$operation($one_day);
                $weekday_index = ($weekday_index - ($mod * 1)) % 7;
            }
            $days_to_check--;
        }
    }

    /**
     * 
     * @param string $configParams
     * @param \DateTime $date
     * @param string $next_or_last
     * @return \DateTime
     */
    protected function getDrawFromMonthly($configParams, \DateTime $date, $next_or_last) {
        $day_of_month = (int) $date->format('d');
        $hour = $date->format("H:i:s");
        $leap_year = $date->format('L');
        $month = $date->format("m");

        if ($next_or_last == self::IS_LAST) {
            $monthCondition = $day_of_month > (int) $configParams;
            $hourCondition = $hour > $this->draw_time;
            $isLeapDay = $this->isLeapDay($month, $configParams, $leap_year, 3);
            $operation = 'sub';
        } elseif ($next_or_last == self::IS_NEXT) {
            $monthCondition = $day_of_month < (int) $configParams;
            $hourCondition = $hour < $this->draw_time;
            $isLeapDay = $this->isLeapDay($month, $configParams, $leap_year, 2);
            $operation = 'add';
        }

        if ($monthCondition || ($day_of_month == (int) $configParams) && $hourCondition) {
            if ($next_or_last == self::IS_NEXT && $isLeapDay) {//next day and leap day
                return new \DateTime($date->format("Y-03-{$configParams} {$this->draw_time}"));
            }
            return new \DateTime($date->format("Y-m-{$configParams} {$this->draw_time}"));
        } else {
            if ($next_or_last == self::IS_LAST && $isLeapDay) {//last day and leap day
                return new \DateTime($date->format("Y-01-{$configParams} {$this->draw_time}"));
            }
            $prev_or_next_month = $date->$operation(new \DateInterval('P1M'));
            return new \DateTime($prev_or_next_month->format("Y-m-{$configParams} {$this->draw_time}"));
        }
    }

    /**
     * @param integer $month
     * @param integer $day_of_month
     * @param boolean $leap_year
     * @param integer $day_diff
     * @return boolean
     */
    protected function isLeapDay($month, $day_of_month, $leap_year, $day_diff) {
        if ($month != $day_diff || ($month == $day_diff &&
                ($day_of_month <= 28) || ($day_of_month == 29 && $leap_year))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $configParams
     * @param \DateTime $date
     * @param string $next_or_last
     * @return \DateTime
     */
    protected function getDrawFromYearly($configParams, \DateTime $date, $next_or_last) {
        $month_day = $date->format('md');
        $hour = $date->format('H:i:s');
        $draw_month = substr($configParams, 0, 2);
        $draw_day = substr($configParams, 2, 2);

        if ($next_or_last == self::IS_LAST) {
            $monthCondition = $month_day > $configParams;
            $hourCondition = $hour > $this->draw_time;
            $operation = 'sub';
        } elseif ($next_or_last == self::IS_NEXT) {
            $monthCondition = $month_day < $configParams;
            $hourCondition = $hour < $this->draw_time;
            $operation = 'add';
        }

        if (($month_day == $configParams && $hourCondition) ||
                $monthCondition) {
            return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        } else {
            return new \DateTime($date->$operation(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        }
    }

    public function initialize($attributes) {
        foreach ($attributes as $key => $value) {
            if ($key == 'id') {
                $this->$key = $value;
            } else {
                $field_name = implode('', array_map('ucfirst', explode('_', $key)));
                $setter = 'set' . $field_name;
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                } else {
                    throw new \Exception("Bad property name: \"$key\"");
                }
            }
        }
    }

}
