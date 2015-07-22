<?php

include 'StrategyYearly.php';
include 'StrategyMonthly.php';
include 'StrategyWeekly.php';
include 'StrategyDaily.php';
include_once 'StrategyDraw.php';
include_once 'Ifaces/IStrategy.php';

class Lottery
{
    protected $frequency; //d, w0100100, m24, y1225
    protected $draw_time;
    private $strategy = NULL; //strategy instance

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
    private function getDrawDate(\DateTime $date, $next_or_last)
    {

        if( empty($date) ) {
            $date = new \DateTime();
        }

        $configParams = substr($this->frequency, 1);
        $draw_month = substr($configParams, 0, 2);
        $strategy = substr($this->frequency, 0, 1);
        switch ($strategy) {
            case 'y':
                $this->strategy = new StrategyYearly();
                //$function .= 'Yearly';
                break;
            case 'm':        
                $this->strategy = new StrategyMonthly();
                //$function .= 'Monthly';
                break;
            case 'w':
                $this->strategy = new StrategyWeekly();
                //$function .= 'Weekly';
                break;
            case 'd':
                $this->strategy = new StrategyDaily();
                //$function .= 'Daily';
                break;
            default:
                throw new Exception("Frequency not supported", 1);
                
            //throw?
        }
        $this->strategy->setDrawTime($this->draw_time);
        $this->strategy->setConfigParams($configParams);
        return $this->strategy->getDraw($date,$next_or_last, $this->frequency);
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