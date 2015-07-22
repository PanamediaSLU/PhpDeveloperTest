<?php

interface IStrategy {
    public function getDraw(\DateTime $date, $next_or_last, $frequency);
}