<?php

class Lottery
{
    protected $frequency; //d, w0100100, m24, y1225
    protected $draw_time;


    public function getFrequency()
    {
        return $this->frequency;
    }

    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    public function getDrawTime()
    {
        return $this->draw_time;
    }

    public function setDrawTime($draw_time)
    {
        $this->draw_time = $draw_time;
    }

    /**
     * @param DateTime $now
     * @return \DateTime
     */
    public function getLastDrawDate(\DateTime $now = null)
    {
        return $this->getDrawDate($now, 'Last');
    }

    /**
     * @param \DateTime $now
     * @return \DateTime
     */
    public function getNextDrawDate(\DateTime $now = null)
    {
        return $this->getDrawDate($now, 'Next');
    }

    /**
     * @param \DateTime $now
     * @param string $next_or_last
     * @return mixed
     */
    private function getDrawDate(\DateTime $now, $next_or_last)
    {
        $function = 'get' . $next_or_last . 'DrawFrom';
        if (!$now) {
            $now = new \DateTime();
        }
        $strategy = substr($this->frequency, 0, 1);
        switch ($strategy) {
            case 'y':
                $function .= 'Yearly';
                break;
            case 'm':
                $function .= 'Monthly';
                break;
            case 'w':
                $function .= 'Weekly';
                break;
            case 'd':
                $function .= 'Daily';
                break;
            default:
                var_dump($this->frequency);
            //throw?
        }
        return $this->$function(substr($this->frequency, 1), $now);
    }


    protected function getLastDrawFromDaily($configParams, \DateTime $date)
    {
        if ($date->format("H:i:s") <= $this->draw_time) {
            return new \DateTime($date->sub(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
        } else {
            return new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        }
    }

    protected function getNextDrawFromDaily($configParams, \DateTime $date)
    {
        if ($date->format("H:i:s") > $this->draw_time) {
            return new \DateTime($date->add(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
        } else {
            return new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        }
    }

    protected function getLastDrawFromWeekly($configParams, \DateTime $date)
    {
        $weekday_index = (int)$date->format('N') - 1;
        $result_date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        $hour = $date->format("H:i:s");
        $one_day = new \DateInterval('P1D');
        $days_to_check = 7;
        while ($days_to_check) {
            if (1 == (int)$configParams[$weekday_index] && ($days_to_check < 7 || $hour > $this->draw_time)) {
                return $result_date;
            } else {
                $result_date = $result_date->sub($one_day);
                $weekday_index = ($weekday_index - 1) % 7;
            }
            $days_to_check--;
        }
    }

    protected function getNextDrawFromWeekly($configParams, \DateTime $date)
    {
        $weekday_index = (int)$date->format('N') - 1;
        $result_date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        $hour = $date->format("H:i:s");
        $one_day = new \DateInterval('P1D');
        $days_to_check = 7;
        while ($days_to_check) {
            if (1 == (int)$configParams[$weekday_index] && ($days_to_check < 7 || $hour < $this->draw_time)) {
                return $result_date;
            } else {
                $result_date = $result_date->add($one_day);
                $weekday_index = ($weekday_index + 1) % 7;
            }
            $days_to_check--;
        }
    }

    protected function getNextDrawFromMonthly($configParams, \DateTime $date)
    {
        $day_of_month = (int)$date->format('d');
        $hour = $date->format("H:i:s");
        $leap_year = $date->format('L');
        $month = $date->format("m");
        if ($day_of_month < (int)$configParams || ($day_of_month == (int)$configParams) && $hour < $this->draw_time) {
            if ($month != 2
                || ($month == 2 &&
                    ($configParams <= 28) ||
                    ($configParams == 29 && $leap_year)
                )
            ) {
                return new \DateTime($date->format("Y-m-{$configParams} {$this->draw_time}"));
            } else {
                return new \DateTime($date->format("Y-03-{$configParams} {$this->draw_time}"));
            }
        } else {
            $next_month = $date->add(new \DateInterval('P1M'));
            return new \DateTime($next_month->format("Y-m-{$configParams} {$this->draw_time}"));
        }
    }

    protected function getLastDrawFromMonthly($configParams, \DateTime $date)
    {
        $day_of_month = (int)$date->format('d');
        $hour = $date->format("H:i:s");
        $leap_year = $date->format('L');
        $month = $date->format("m");
        if ($day_of_month > (int)$configParams || ($day_of_month == (int)$configParams) && $hour > $this->draw_time) {
            return new \DateTime($date->format("Y-m-{$configParams} {$this->draw_time}"));
        } else {
            if ($month != 3
                || ($month == 3 &&
                    ($configParams <= 28) ||
                    ($configParams == 29 && $leap_year)
                )
            ) {
                $previous_month = $date->sub(new \DateInterval('P1M'));
                return new \DateTime($previous_month->format("Y-m-{$configParams} {$this->draw_time}"));
            } else {
                return new \DateTime($date->format("Y-01-{$configParams} {$this->draw_time}"));
            }
        }
    }

    protected function getNextDrawFromYearly($configParams, \DateTime $date)
    {
        $month_day = $date->format('md');
        $hour = $date->format('H:i:s');
        $draw_month = substr($configParams, 0, 2);
        $draw_day = substr($configParams, 2, 2);
        if (
            ($month_day == $configParams && $hour < $this->draw_time) ||
            ($month_day < $configParams)
        ) {
            return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        } else {
            return new \DateTime($date->add(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        }
    }

    protected function getLastDrawFromYearly($configParams, \DateTime $date)
    {
        $month_day = $date->format('md');
        $hour = $date->format('H:i:s');
        $draw_month = substr($configParams, 0, 2);
        $draw_day = substr($configParams, 2, 2);
        if (
            ($month_day == $configParams && $hour > $this->draw_time) ||
            ($month_day > $configParams)
        ) {
            return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        } else {
            return new \DateTime($date->sub(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        }
    }


    public function initialize($attributes)
    {
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