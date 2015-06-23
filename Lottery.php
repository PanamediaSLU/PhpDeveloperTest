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
        if (!$now) {
            $now = new \DateTime();
        }
        $strategy = substr($this->frequency, 0, 1);
        switch ($strategy) {
            case 'y':
                return $this->getDrawFromYearly(substr($this->frequency, 1), $now, $next_or_last);
                break;
            case 'm':
                return $this->getDrawFromMonthly(substr($this->frequency, 1), $now, $next_or_last);
                break;
            case 'w':
                return $this->getDrawFromWeekly(substr($this->frequency, 1), $now, $next_or_last);
                break;
            case 'd':
                return $this->getDrawFromDaily(substr($this->frequency, 1), $now, $next_or_last);
                break;
            default:
                var_dump($this->frequency);
            //throw?
        }
    }


    protected function getDrawFromDaily($configParams, \DateTime $date, $time_option)
    {
        if ($date->format("H:i:s") <= $this->draw_time && $time_option == "Last") {
            return new \DateTime($date->sub(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
        }
        else  if ($date->format("H:i:s") > $this->draw_time && $time_option == "Next") {
            return new \DateTime($date->add(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
        }
        else {
            return new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        }
    }

    protected function getDrawFromWeekly($configParams, \DateTime $date, $time_option)
    {
        $weekday_index = (int)$date->format('N') - 1;
        $result_date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        $hour = $date->format("H:i:s");

        if (1 == (int)$configParams[$weekday_index]) {
            if (($time_option == "Last" && $hour > $this->draw_time) ||
                ($time_option == "Next" && $hour < $this->draw_time)) return $result_date;
        }
        $one_day = new \DateInterval('P1D');

        for ($days_to_check = 7; $days_to_check > 0; --$days_to_check) {
             if ((int)$configParams[$weekday_index] == 1 && $days_to_check != 7) return $result_date;
             else {
                if ($time_option == "Last") {
                    $result_date = $result_date->sub($one_day);
                    $weekday_index = ($weekday_index - 1) % 7;
                }
                else if ($time_option == "Next") {
                    $result_date = $result_date->add($one_day);
                    $weekday_index = ($weekday_index + 1) % 7;
                }
             }
        }
    }

    protected function getDrawFromMonthly($configParams, \DateTime $date, $time_option)
    {
        $day_of_month = (int)$date->format('d');
        $hour = $date->format("H:i:s");
        $leap_year = $date->format('L');
        $month = $date->format("m");
        if ($day_of_month > (int)$configParams || ($day_of_month == (int)$configParams && $hour > $this->draw_time)) {
            if ($time_option == "Last") return new \DateTime($date->format("Y-m-{$configParams} {$this->draw_time}"));
            else if ($time_option == "Next") {
                return new \DateTime($date->add(new \DateInterval('P1M'))->format("Y-m-{$configParams} {$this->draw_time}"));
            }
        } else {
            if ($time_option == "Last") {
                if ($month != 3
                    || ($month == 3 &&
                        ($configParams <= 28) ||
                        ($configParams == 29 && $leap_year)
                    )
                ) {
                    return new \DateTime($date->sub(new \DateInterval('P1M'))->format("Y-m-{$configParams} {$this->draw_time}"));
                }
                else {
                    return new \DateTime($date->format("Y-01-{$configParams} {$this->draw_time}"));
                }
            }
            else if ($time_option == "Next") {
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
            }
        }
    }

    protected function getDrawFromYearly($configParams, \DateTime $date, $time_option)
    {
        $month_day = $date->format('md');
        $hour = $date->format('H:i:s');
        $draw_month = substr($configParams, 0, 2);
        $draw_day = substr($configParams, 2, 2);

        if (
            ($month_day == $configParams && $hour < $this->draw_time) ||
            ($month_day < $configParams)
        ) {
            if ($time_option == "Next") {
                return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
            }
            else if ($time_option == "Last") {
                return new \DateTime($date->sub(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
            }
        } else {
            if ($time_option == "Next") {
                return new \DateTime($date->add(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
            }
            else if ($time_option == "Last") {
                return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
            }
        }
    }

    public function __construct($attributes)
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
