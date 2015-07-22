<?php
include_once 'Ifaces/IStrategy.php';
include_once 'StrategyDraw.php';

class StrategyDaily extends StrategyDraw implements IStrategy {
    
    public function getDraw(\DateTime $date, $next_or_last, $frequency) {
        // Daily
        if ($next_or_last == 'Last' && $date->format("H:i:s") <= $this->getDrawTime()) {
            $date = new \DateTime($date->sub(new \DateInterval('P1D'))->format("Y-m-d {$this->getDrawTime()}"));
        } elseif ($next_or_last == 'Next' && $date->format("H:i:s") > $this->getDrawTime()) {
            $date = new \DateTime($date->add(new \DateInterval('P1D'))->format("Y-m-d {$this->getDrawTime()}"));
        } else {
            $date = new \DateTime($date->format("Y-m-d {$this->getDrawTime()}"));
        }

        return $date;
    }
    
}