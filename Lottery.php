<?php

class Lottery
{

    /**
     * Field that stores the frequency
     * @var string
     */
    protected $frequency; //d, w0100100, m24, y1225

    /**
     * Field that stores the draw time in format hh:mm:ss
     * @var string
     */
    protected $draw_time;

    /**
     * It returns the value of field frequency
     *
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * It sets the value of field frequency
     *
     * @param string
     * @return this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * It returns the value of field draw_time
     *
     * @return string
     */
    public function getDrawTime()
    {
        return $this->draw_time;
    }

    /**
     * It sets the value of field draw_time
     *
     * @param string
     * @return $this
     */
    public function setDrawTime($draw_time)
    {
        $this->draw_time = $draw_time;

        return $this;
    }

    /**
     * It gets the last draw date before $now
     *
     * @param  DateTime  $now
     * @return \DateTime
     */
    public function getLastDrawDate(\DateTime $now = null)
    {
        return $this->getDrawDate($now, 'Last');
    }

    /**
     * It gets the next draw date after $now
     *
     * @param  \DateTime $now
     * @return \DateTime
     */
    public function getNextDrawDate(\DateTime $now = null)
    {
        return $this->getDrawDate($now, 'Next');
    }

    /**
     * It returns the last or next draw date based on $date
     *
     * @param  \DateTime $date
     * @param  string    $next_or_last
     * @return mixed
     */
    private function getDrawDate(\DateTime $date, $next_or_last)
    {
        if (!$date) {
            $date = new \DateTime();
        }
        $strategy = substr($this->frequency, 0, 1);
        $configParams = substr($this->frequency, 1);
        $hour = $date->format("H:i:s");

        switch ($strategy) {

            case 'y':
                // Yearly
                $month_day = $date->format('md');
                $draw_month = substr($configParams, 0, 2);
                $draw_day = substr($configParams, 2, 2);
                if (
                    $next_or_last == 'Next' && (
                        ($month_day == $configParams && $hour < $this->draw_time) ||
                        ($month_day < $configParams)) ||
                    $next_or_last == 'Last' && (
                        ($month_day == $configParams && $hour > $this->draw_time) ||
                        ($month_day > $configParams)
                    )
                ) {
                    return new \DateTime($date->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
                } else {
                    if ($next_or_last == 'Next') {
                        return new \DateTime($date->add(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
                    } else {
                        return new \DateTime($date->sub(new \DateInterval('P1Y'))->format("Y-{$draw_month}-{$draw_day} {$this->draw_time}"));
                    }
                }
                break;

            case 'm':
                // Montly
                $day_of_month = (int) $date->format('d');
                $leap_year = $date->format('L');
                $month = $date->format("m");
                if (($next_or_last == 'Next' &&
                        ($day_of_month < (int) $configParams || ($day_of_month == (int) $configParams) && $hour < $this->draw_time)
                    ) || ($next_or_last == 'Last' &&
                        ($day_of_month > (int) $configParams || ($day_of_month == (int) $configParams) && $hour > $this->draw_time)
                    )) {
                    if ($month != 2 || ($month == 2 && ($configParams <= 28) || ($configParams == 29 && $leap_year))) {
                        $date = new \DateTime($date->format("Y-m-{$configParams} {$this->draw_time}"));
                    } else {
                        if ($next_or_last == 'Next') {
                            $date = new \DateTime($date->format("Y-03-{$configParams} {$this->draw_time}"));
                        } else {
                            $date = new \DateTime($date->format("Y-01-{$configParams} {$this->draw_time}"));
                        }
                    }
                } else {
                    if ($next_or_last == 'Next') {
                        $next_month = $date->add(new \DateInterval('P1M'));

                        $date = new \DateTime($next_month->format("Y-m-{$configParams} {$this->draw_time}"));
                    } else {
                        if ($month != 3
                            || ($month == 3 &&
                                ($configParams <= 28) ||
                                ($configParams == 29 && $leap_year)
                            )
                        ) {
                            $previous_month = $date->sub(new \DateInterval('P1M'));
                            $date = new \DateTime($previous_month->format("Y-m-{$configParams} {$this->draw_time}"));
                        } else {
                            $date = new \DateTime($date->format("Y-01-{$configParams} {$this->draw_time}"));
                        }
                    }
                }
                break;

            case 'w':
                // Weekly
                $weekday_index = (int) $date->format('N') - 1;
                $result_date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
                $one_day = new \DateInterval('P1D');
                $days_to_check = 7;
                while ($days_to_check) {
                    if (($next_or_last == 'Last' && 1 == (int) $configParams[$weekday_index] && ($days_to_check < 7 || $hour > $this->draw_time)) ||
                        ($next_or_last == 'Next' && 1 == (int) $configParams[$weekday_index] && ($days_to_check < 7 || $hour < $this->draw_time))) {
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
                $date = $result_date;
                break;

            case 'd':
                // Daily
                if ($next_or_last == 'Last' && $date->format("H:i:s") <= $this->draw_time) {
                    $date = new \DateTime($date->sub(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
                } elseif ($next_or_last == 'Next' && $date->format("H:i:s") > $this->draw_time) {
                    $date = new \DateTime($date->add(new \DateInterval('P1D'))->format("Y-m-d {$this->draw_time}"));
                } else {
                    $date = new \DateTime($date->format("Y-m-d {$this->draw_time}"));
                }
                break;

            default:
                // Invalid frequency
                throw new Exception("Invalid frequency");
                break;
        }

        return $date;
    }

    /**
     * It initializes class Lottery
     *
     * @param array $attributes
     */
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
