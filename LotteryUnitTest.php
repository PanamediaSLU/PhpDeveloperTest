<?php
require_once('Lottery.php');

//extends PHPUnit_Framework_TestCase no tengo esta clasepara generar la herencia
class LotteryUnitTest 
{
    /**
     * method getNextDrawDate
     * when called
     * should returnProperResult
     * @dataProvider getConfigurationAndExpectedResult
     * @param $frequency
     * @param $draw_time
     * @param $now
     * @param $expectedDrawDate
     */
    public function test_getNextDrawDate_called_returnProperResult($frequency, $draw_time, $now, $expectedDrawDate)
    {
       return $this->exerciseGetDrawDate($frequency, $draw_time, $now, $expectedDrawDate, 'getNextDrawDate');
    }

    public function getConfigurationAndExpectedResult()
    {
        return [
            ['y1225', '10:00:00', '2015-03-02 15:00:01', '2015-12-25 10:00:00'],//december 25th each year
            ['y1225', '10:00:00', '2015-12-25 15:00:01', '2016-12-25 10:00:00'],//december 25th each year
            ['y1225', '10:00:00', '2015-12-25 09:59:01', '2015-12-25 10:00:00'],//december 25th each year
            ['m29', '09:15:00', '2016-02-01 01:01:01', '2016-02-29 09:15:00'], //29th of each month
            ['m29', '09:15:00', '2015-02-01 01:01:01', '2015-03-29 09:15:00'], //29th of each month
            ['w1000000', '09:15:00', '2015-02-01 01:01:01', '2015-02-02 09:15:00'], //each monday
            ['w0100000', '09:15:00', '2015-02-01 01:01:01', '2015-02-03 09:15:00'], //each and tuesday
            ['w1111100', '09:15:00', '2015-02-01 01:01:01', '2015-02-02 09:15:00'], //monday to friday
            ['w0000011', '09:15:00', '2015-02-01 01:01:01', '2015-02-01 09:15:00'], //saturday and friday
            ['w0100100', '09:15:00', '2015-02-01 01:01:01', '2015-02-03 09:15:00'], //tuesday and friday
            ['w0001010', '09:15:00', '2015-02-01 01:01:01', '2015-02-05 09:15:00'], //thursday and saturday
            ['w0010001', '09:15:00', '2015-02-01 10:01:01', '2015-02-04 09:15:00'], //wednesday and sunday but hour passed
            ['d', '09:15:00', '2015-02-01 10:01:01', '2015-02-02 09:15:00'], //daily, before hour
            ['d', '09:15:00', '2015-02-01 01:01:01', '2015-02-01 09:15:00'] //daily, after hour 
        ];
    }

    /**
     * method getLastDrawDate
     * when called
     * should returnProperResult
     * @dataProvider getConfigurationAndExpectedResultForLast
     * @param $frequency
     * @param $draw_time
     * @param $now
     * @param $expectedDrawDate
     */
    public function test_getLastDrawDate_called_returnProperResult($frequency, $draw_time, $now, $expectedDrawDate)
    {
        return $this->exerciseGetDrawDate($frequency, $draw_time, $now, $expectedDrawDate, 'getLastDrawDate');

    }

    public function getConfigurationAndExpectedResultForLast()
    {
        return [
            ['y1225', '10:00:00', '2015-03-02 15:00:01', '2014-12-25 10:00:00'],
            ['y1225', '10:00:00', '2015-12-25 15:00:01', '2015-12-25 10:00:00'],
            ['y1225', '10:00:00', '2015-12-25 09:59:01', '2014-12-25 10:00:00'],
            ['m29', '09:15:00', '2016-03-01 01:01:01', '2016-02-29 09:15:00'],
            ['m29', '09:15:00', '2015-03-01 01:01:01', '2015-01-29 09:15:00'],
            ['w1000000', '09:15:00', '2015-02-01 01:01:01', '2015-01-26 09:15:00'],
            ['w0100000', '09:15:00', '2015-02-01 01:01:01', '2015-01-27 09:15:00'],
            ['w1111100', '09:15:00', '2015-02-01 01:01:01', '2015-01-30 09:15:00'],
            ['w0000011', '09:15:00', '2015-02-01 01:01:01', '2015-01-31 09:15:00'],
            ['w0100100', '09:15:00', '2015-02-01 01:01:01', '2015-01-30 09:15:00'],
            ['w0001010', '09:15:00', '2015-02-01 01:01:01', '2015-01-31 09:15:00'],
            ['w0010001', '09:15:00', '2015-02-01 10:01:01', '2015-02-01 09:15:00'],
            ['d', '09:15:00', '2015-02-01 10:01:01', '2015-02-01 09:15:00'],
            ['d', '09:15:00', '2015-02-01 01:01:01', '2015-01-31 09:15:00']
        ];

    }

    /**
     * @param $frequency
     * @param $draw_time
     * @param $now
     * @param $expectedDrawDate
     * @param $method
     */
    private function exerciseGetDrawDate($frequency, $draw_time, $now, $expectedDrawDate, $method)
    {
        $sut = new Lottery();
        $sut->initialize([
            'frequency' => $frequency,
            'draw_time' => $draw_time
        ]);
        $actual = $sut->$method(new DateTime($now));
        $expected = new DateTime($expectedDrawDate);
       return $this->assertEquals($expected, $actual);
    }
}
?>
