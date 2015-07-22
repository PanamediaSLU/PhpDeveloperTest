<?php
include_once 'Ifaces/IStrategy.php';
include_once 'StrategyDraw.php';


class StrategyWeekly extends StrategyDraw implements IStrategy {
    
    public function getDraw(\DateTime $date, $next_or_last, $frequency) {
        
        $weekday_index = (int) $date->format('N') - 1;
        $result_date = new \DateTime($date->format("Y-m-d {$this->getDrawTime()}"));
        $one_day = new \DateInterval('P1D');
        $days_to_check = 7;
		$hour = $date->format("H:i:s");
        while ($days_to_check) {
            if (($next_or_last == 'Last' && 1 == (int) $this->configParams[$weekday_index] && ($days_to_check < 7 || $hour > $this->getDrawTime())) ||
                ($next_or_last == 'Next' && 1 == (int) $this->configParams[$weekday_index] && ($days_to_check < 7 || $hour < $this->getDrawTime()))) {
                break;
            } else {
                if ($next_or_last == 'Last') {
                    $result_date = $result_date->sub($one_day);
                    $weekday_index = ($weekday_index - 1) % 7;
                } else {
                    $result_date = $result_date->add($one_day);
                    $weekday_index = ($weekday_index + 1) % 7;
                }
            }
            $days_to_check--;
        }
        return $date = $result_date;
    }
    
}