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
        $strategy = substr($this->frequency, 0, 1);
        return $this->getDraw(substr($this->frequency, 1), $now, $next_or_last, $strategy);
    }
    private function getDrawFromDaily($configParams, \DateTime $date, $next_or_last){
        $dateTime = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        $format = "Y-m-d {$this->draw_time}";
        if($next_or_last == 'Next'){
            if ($date->format("H:i:s") > $this->draw_time) {
                $dateTime = new \DateTime($date->add(new \DateInterval('P1D'))->format($format));
            }
        }else{
            if ($date->format("H:i:s") <= $this->draw_time) {
                $dateTime = new \DateTime($date->sub(new \DateInterval('P1D'))->format($format));
            }
        }
        return $dateTime;
    }
    private function getDrawFromWeekly($configParams, \DateTime $date, $next_or_last){
        $weekday_index = (int)$date->format('N') - 1;
        $result_date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
        $hour = $date->format("H:i:s");
        $one_day = new \DateInterval('P1D');
        $days_to_check = 7;
        while ($days_to_check) {
            if($next_or_last == 'Last' && !(1 == (int)$configParams[$weekday_index] &&
                    ($days_to_check < 7 || $hour > $this->draw_time))){
                $result_date = $result_date->sub($one_day);
                $weekday_index = ($weekday_index - 1) % 7;
            }elseif($next_or_last == 'Next' && !(1 == (int)$configParams[$weekday_index] &&
                    ($days_to_check < 7 || $hour < $this->draw_time))){
                $result_date = $result_date->add($one_day);
                $weekday_index = ($weekday_index + 1) % 7;
            }
            $days_to_check--;
        }
        return $result_date;
    }

    private function getDrawFromMonthly($configParams, \DateTime $date, $next_or_last){
        $day_of_month = (int)$date->format('d');
        $hour = $date->format("H:i:s");
        $leap_year = $date->format('L');
        $month = $date->format("m");
        $dateTime = new \DateTime($date->format("Y-01-{$configParams} {$this->draw_time}"));
        if ($day_of_month < (int)$configParams || ($day_of_month == (int)$configParams) && $hour < $this->draw_time) {
            if($next_or_last == 'Next') {
                if ($month != 2
                    || ($month == 2 &&
                        ($configParams <= 28) ||
                        ($configParams == 29 && $leap_year)
                    )
                ){
                    $dateTime = new \DateTime($date->format("Y-m-{$configParams} {$this->draw_time}"));
                }else{
                    $dateTime =  new \DateTime($date->format("Y-03-{$configParams} {$this->draw_time}"));
                }
            }else{
                if ($month != 3
                    || ($month == 3 &&
                        ($configParams <= 28) ||
                        ($configParams == 29 && $leap_year)
                    )
                ){
                    $previous_month = $date->sub(new \DateInterval('P1M'));
                    $dateTime = new \DateTime($previous_month->format("Y-m-{$configParams} {$this->draw_time}"));
                }
            }
        }else{
            if($next_or_last == 'Next'){
                $next_month = $date->add(new \DateInterval('P1M'));
                $dateTime = new \DateTime($next_month->format("Y-m-{$configParams} {$this->draw_time}"));
            }else{
                $dateTime = new \DateTime($date->format("Y-m-{$configParams} {$this->draw_time}"));
            }
        }
        return $dateTime;
    }
    private function getDrawFromYearly($configParams, \DateTime $date, $next_or_last){
        $month_day = $date->format('md');
        $hour = $date->format('H:i:s');
        $draw_month = substr($configParams, 0, 2);
        $draw_day = substr($configParams, 2, 2);
        $format = "Y-{$draw_month}-{$draw_day} {$this->draw_time}";
        $dateTime = new \DateTime($date->format($format));


        if($next_or_last == 'Next'){
            if (
                ($month_day == $configParams && $hour > $this->draw_time) ||
                ($month_day > $configParams)
            )
                $dateTime = new \DateTime($date->add(new \DateInterval('P1Y'))->format($format));
        }else{//last
            if(
                ($month_day == $configParams && $hour < $this->draw_time) ||
                ($month_day < $configParams)
            )
                $dateTime = new \DateTime($date->sub(new \DateInterval('P1Y'))->format($format));
        }
        return $dateTime;


    }
    private function getDraw($configParams, \DateTime $date, $next_or_last, $from){
        switch($from){
            case 'y': //yearly
                return $this->getDrawFromYearly($configParams, $date, $next_or_last);
                break;
            case 'm': //monthly
                return $this->getDrawFromMonthly($configParams, $date, $next_or_last);
                break;
            case 'w': //weekly
                return $this->getDrawFromWeekly($configParams, $date, $next_or_last);
                break;
            case 'd': //diary
                return $this->getDrawFromDaily($configParams, $date, $next_or_last);
                break;
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