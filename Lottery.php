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
        $function = 'getDrawFrom';
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
                throw new \Exception("Bad frecuency declaration {$this->frequency}");
        }

        return $this->$function(substr($this->frequency, 1), $now, $next_or_last);
    }

    protected function getDrawFromDaily($configParams, \DateTime $date, $next_or_last)
    {
        $interval = new \DateInterval('P1D');
        
        if ($next_or_last == 'Last') {
            $interval->invert = 1;
        }
        
        if ($this->checkDate($date, $next_or_last)) {
            return $this->getFormattedDate($date);
        } else {
            return $this->getFormattedDate($date->add($interval)); 
        }
    }

    protected function getDrawFromWeekly($configParams, \DateTime $date, $next_or_last)
    {
        $weekday_index = (int)$date->format('N') - 1;
        $result_date   = $this->getFormattedDate($date);
        $interval      = new \DateInterval('P1D');
        $days_to_check = 7;
        
        if ($next_or_last == 'Last') {
            $interval->invert = 1;
            $increment        = -1;
        } else {
            $increment        = 1;
        }
        
        while ($days_to_check) {
            if (1 == (int) $configParams[$weekday_index] && ($days_to_check < 7 || $this->checkDate($date, $next_or_last))) {
                return $result_date;
            } else {
                $result_date   = $result_date->add($interval);
                $weekday_index = ($weekday_index + $increment) % 7;
            }
            $days_to_check--;
        }
    }
    
    protected function getDrawFromMonthly($configParams, \DateTime $date, $next_or_last)
    {
        $actualDay   = (int) $date->format('d');
        $actualMonth = (int) $date->format('m');
        $actualHour  = $date->format('H:i:s');
        $interval = new \DateInterval('P1M');
        
        if ($next_or_last == 'Last') {
            $drawDate  = $date->sub($interval);
            $increment = -1;
        } else {
            if ($actualDay > (int) $configParams || ($actualDay == (int) $configParams && $actualHour > '09:15:00')) {
                $drawDate = $date->add($interval);
            } else {
                $drawDate  = $date;
                $increment = 1;
            }
        }
        
        $drawMonth = $drawDate->format('m');
        $drawYear  = $drawDate->format('Y');
    
        if (isset($increment) && !checkdate($drawMonth, $configParams, $drawYear)) {
            $drawMonth = $drawMonth + $increment;
        }
        
        return new DateTime("$drawYear-$drawMonth-$configParams 09:15:00");
    }
    
    protected function getDrawFromYearly($configParams, \DateTime $date, $next_or_last)
    { 
        $month_day  = $date->format('md');
        $hour       = $date->format('H:i:s');
        $draw_month = substr($configParams, 0, 2);
        $draw_day   = substr($configParams, 2, 2);
        $interval   = new \DateInterval('P1Y');
        
        if ($next_or_last == 'Last') {
            $interval->invert = 1;
            $monthDayCheck    = $month_day > $configParams;
        } else {
            $monthDayCheck    = $month_day < $configParams;
        }
        
        if (
            $month_day == $configParams && $this->checkDate($date, $next_or_last) ||
            $monthDayCheck
        ) {
            return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
        } else {
            return new \DateTime($date->add($interval)->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
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
	
	private function getFormattedDate($date)
	{
		return new \DateTime($date->format("Y-m-d {$this->draw_time}"));
	}
    
    private function checkDate(\DateTime $date, $next_or_last)
    {
        if ($next_or_last == 'Next') {
            $result = $date->format("H:i:s") < $this->draw_time;
        } else {
            $result = $date->format("H:i:s") > $this->draw_time;
        }
        
        return $result;
    }
    
}