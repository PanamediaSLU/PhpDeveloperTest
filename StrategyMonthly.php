<?php
include_once 'Ifaces/IStrategy.php';
include_once 'StrategyDraw.php';

class StrategyMonthly extends StrategyDraw implements IStrategy {
    
    public function getDraw(\DateTime $date, $next_or_last,$frequency) {
        
        $day_of_month = (int)$date->format('d');
        $hour = $date->format("H:i:s");
        $leap_year = $date->format('L');
        $month = $date->format("m");
        if ( !empty($next_or_last) ) {
	        if (($next_or_last == 'Next' &&
	                ($day_of_month < (int) $this->configParams || ($day_of_month == (int) $this->configParams) && $hour < $this->getDrawTime())
	            ) || ($next_or_last == 'Last' &&
	                ($day_of_month > (int) $this->configParams || ($day_of_month == (int) $this->configParams) && $hour > $this->getDrawTime())
	            )) {
	            if ($month != 2 || ($month == 2 && ($this->configParams <= 28) 
	            			|| ($this->configParams == 29 && $leap_year))) {
	                $date = new \DateTime($date->format("Y-m-{$this->configParams} {$this->getDrawTime()}"));
	            } else {
	                if ($next_or_last == 'Next') {
	                    $date = new \DateTime($date->format("Y-03-{$this->configParams} {$this->getDrawTime()}"));
	                } else {
	                    $date = new \DateTime($date->format("Y-01-{$this->configParams} {$this->getDrawTime()}"));
	                }
	            }
	        } else {
	            if ($next_or_last == 'Next') {
	                $next_month = $date->add(new \DateInterval('P1M'));

	                $date = new \DateTime($next_month->format("Y-m-{$this->configParams} {$this->getDrawTime()}"));
	            } else {
	                if ($month != 3
	                    || ($month == 3 &&
	                        ($this->configParams <= 28) ||
	                        ($this->configParams == 29 && $leap_year)
	                    )
	                ) {
	                    $previous_month = $date->sub(new \DateInterval('P1M'));
	                    $date = new \DateTime($previous_month->format("Y-m-{$this->configParams} {$this->getDrawTime()}"));
	                } else {
	                    $date = new \DateTime($date->format("Y-01-{$this->configParams} {$this->getDrawTime()}"));
	                }
	            }
	        }
        }

        return $date;
    }
}