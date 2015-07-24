<?php

class Lottery
{
    protected $frequency; //d, w0100100, m24, y1225
    protected $draw_time;

    public function __construct($frequency, $draw_time)
    {
        $this->frequency = $frequency;
        $this->draw_time = $draw_time;
    }

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
        return $this->getDrawDate($now, false);
    }

    /**
     * @param \DateTime $now
     * @return \DateTime
     */
    public function getNextDrawDate(\DateTime $now = null)
    {
        return $this->getDrawDate($now, true);
    }

    /**
     * @param \DateTime $now
     * @param bool $next
     * @return mixed
     */
    private function getDrawDate(\DateTime $now, $next)
    {
        if (!$now) {
            $now = new \DateTime();
        }
        try {
            $matches = null;
            if (preg_match('/^([A-Za-z])(\d+)?/', $this->frequency, $matches)) {
                $frequencyType = $matches[1];
                $frequencyConfigParams = isset($matches[2])?$matches[2]:null;
            } else {
                throw new Exception("Invalid frequency format: '$this->frequency'"); 
            }
            
            switch ($frequencyType) {
                case 'y':
                    return $this->getDrawFromYearly($frequencyConfigParams, $now, $next);
                    break;
                case 'm':
                    return $this->getDrawFromMonthly($frequencyConfigParams, $now, $next);
                    break;
                case 'w':
                    return $this->getDrawFromWeekly($frequencyConfigParams, $now, $next);
                    break;
                case 'd':
                    return $this->getDrawFromDaily($frequencyConfigParams, $now, $next);
                    break;
            }
        }
        catch (Exception $e) {
            throw $e;
        }
    }

    protected function getDrawFromDaily($configParams, \DateTime $date, $next)
    {
        if (!$next && $date->format("H:i:s") <= $this->draw_time) {
            return new \DateTime($date->sub(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
        } elseif ($next && $date->format("H:i:s") > $this->draw_time) {
            return new \DateTime($date->add(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
        } else {
            return new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        }
    }

    protected function getDrawFromWeekly($configParams, \DateTime $date, $next)
    {
        $weekday_index = (int)$date->format('N') - 1;
        $result_date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        $hour = $date->format("H:i:s");
        $one_day = new \DateInterval('P1D');
        $days_to_check = 7;
        while ($days_to_check) {
            if (
                ($next && (1 == (int)$configParams[$weekday_index] && ($days_to_check < 7 || $hour < $this->draw_time))) ||
                (!$next && (1 == (int)$configParams[$weekday_index] && ($days_to_check < 7 || $hour > $this->draw_time)))
            ) {
                return $result_date;
            } else {
                if($next) {
                    $result_date = $result_date->add($one_day);
                    $weekday_index = ($weekday_index + 1) % 7;
                } else {            
                    $result_date = $result_date->sub($one_day);
                    $weekday_index = ($weekday_index - 1) % 7;
                }
            }
            $days_to_check--;
        }
    }

    protected function getDrawFromMonthly($configParams, \DateTime $date, $next)
    {
        $day_of_month = (int)$date->format('d');
        $hour = $date->format("H:i:s");
        $leap_year = $date->format('L');
        $month = $date->format("m");
        if($next) {
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
        } else {
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
    }

    protected function getDrawFromYearly($configParams, \DateTime $date, $next)
    {
        $month_day = $date->format('md');
        $hour = $date->format('H:i:s');
        $draw_month = substr($configParams, 0, 2);
        $draw_day = substr($configParams, 2, 2);

        if (
            ($next &&
            (($month_day == $configParams && $hour < $this->draw_time) ||
            ($month_day < $configParams))) ||
            (!$next &&
            (($month_day == $configParams && $hour > $this->draw_time) ||
            ($month_day > $configParams)))
        ) {
            return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        } else {
            if($next) {
                return new \DateTime($date->add(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
            } else {
                return new \DateTime($date->sub(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
            }
        }
    }
}

class LotteryFactory
{
    public static function create($frequency, $draw_time)
    {
        return new Lottery($frequency, $draw_time);
    }
}