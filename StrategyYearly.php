<?php

include_once 'Ifaces/IStrategy.php';
include 'StrategyDraw.php';

class StrategyYearly extends StrategyDraw implements IStrategy {

    public function getDraw(\DateTime $date, $next_or_last,$frequency) {
        
        $month_day = $date->format('md');
        $hour = $date->format('H:i:s');
        $draw_month = substr($this->configParams, 0, 2);
        $draw_day = substr($this->configParams, 2, 2);
       	if( !empty($next_or_last) ) {

            if (
                $next_or_last == 'Next' && (
                    ($month_day == $this->configParams && $hour < $this->getDrawTime()) ||
                    ($month_day < $this->configParams)) ||
                $next_or_last == 'Last' && (
                    ($month_day == $this->configParams && $hour > $this->getDrawTime()) ||
                    ($month_day > $this->configParams)
                )
            ) {
                return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->getDrawTime()}"));
            } else {
                if ($next_or_last == 'Next') {
                    return new \DateTime($date->add(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->getDrawTime()}"));
                } else {
                    return new \DateTime($date->sub(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->getDrawTime()}"));
                }
            }

       	}
    }

}