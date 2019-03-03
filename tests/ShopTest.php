<?php
$HOLIDAYS = require(__DIR__ . "/../config/holidays.config.php");
$SHOPOPENANDCLOSE = require(__DIR__ . "/../config/times.config.php");

use PHPUnit\Framework\TestCase;
use HLS\Shop;

class ShopTest extends TestCase
{
    private $systemUnderTest;

    public function testIsOpen()
    {
        global $HOLIDAYS, $SHOPOPENANDCLOSE;

        $systemUnderTest = new Shop($HOLIDAYS, $SHOPOPENANDCLOSE);

        /* Should be times when it is open */
        $morngingOpenDateWithSeconds = new DateTime("07-08-2017 09:11:31");
        $afternoonOpenDateWithoutSeconds = new DateTime("10-10-2018 14:25");
        $lunchDifferenceThursday = new DateTime("28-02-2019 12:25");
        $lunchDifferenceFriday = new DateTime("01-03-2019 12:25");

        $this->assertTrue($systemUnderTest->isOpen($morngingOpenDateWithSeconds));
        $this->assertTrue($systemUnderTest->isOpen($afternoonOpenDateWithoutSeconds));
        $this->assertTrue($systemUnderTest->isOpen($lunchDifferenceThursday));
        $this->assertTrue($systemUnderTest->isOpen($lunchDifferenceFriday));

    }

    public function testIsClosed()
    {
        global $HOLIDAYS, $SHOPOPENANDCLOSE;

        $systemUnderTest = new Shop($HOLIDAYS, $SHOPOPENANDCLOSE);

        /* Should not be open on weekends */
        $saturdayToTest = new DateTime("02-02-2019");
        $sundayToTest = new DateTime("03-02-2019");

        $this->assertTrue($systemUnderTest->isClosed($saturdayToTest));
        $this->assertTrue($systemUnderTest->isClosed($sundayToTest));

        /* Should not be open on holidays */
        $newYearsDay2017 = new DateTime("01-01-2017");
        $goodFriday2017 = new DateTime("14-04-2017");
        $springBankHoliday2017 = new DateTime("01-05-2017");
        $christmasDay2017 = new DateTime("25-12-2017");

        $this->assertTrue($systemUnderTest->isClosed($newYearsDay2017));
        $this->assertTrue($systemUnderTest->isClosed($goodFriday2017));
        $this->assertTrue($systemUnderTest->isClosed($springBankHoliday2017));
        $this->assertTrue($systemUnderTest->isClosed($christmasDay2017));

        $christmasHolidays2017Day1 = new DateTime("20-12-2017");
        $christmasHolidays2017Day2 = new DateTime("21-12-2017");
        $christmasHolidays2017Day3 = new DateTime("22-12-2017");
        $christmasHolidays2017Day4 = new DateTime("23-12-2017");
        $christmasHolidays2017Day5 = new DateTime("24-12-2017");
        $christmasHolidays2017Day6 = new DateTime("25-12-2017 10:12:35"); // Christmas Day with time
        $christmasHolidays2017Day7 = new DateTime("26-12-2017");
        $christmasHolidays2017Day8 = new DateTime("27-12-2017");
        $christmasHolidays2017Day9 = new DateTime("28-12-2017");
        $christmasHolidays2017Day10 = new DateTime("29-12-2017");
        $christmasHolidays2017Day11 = new DateTime("30-12-2017");

        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day1));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day2));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day3));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day4));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day5));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day6));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day7));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day8));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day9));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day10));
        $this->assertTrue($systemUnderTest->isClosed($christmasHolidays2017Day11));

        $newYearsEve2017 = new DateTime("31-12-2017");
        $newYearsDay2018 = new DateTime("01-01-2018");

        $this->assertTrue($systemUnderTest->isClosed($newYearsEve2017));
        $this->assertTrue($systemUnderTest->isClosed($newYearsDay2018));

        /* Should be times when the shop is closed */
        $morngingClosedDate = new DateTime("07-08-2017 08:11:31");
        $earlyLunchMonday = new DateTime("07-08-2017 12:25");
        $earlyLunchTuesday = new DateTime("08-08-2017 12:25");
        $earlyLunchWednesday = new DateTime("09-08-2017 12:25");
        $lunchTime = new DateTime("16-10-2017 13:15");
        $endOfDayClosedDate = new DateTime("16-10-2017 21:15");

        $this->assertTrue($systemUnderTest->isClosed($morngingClosedDate));
        $this->assertTrue($systemUnderTest->isClosed($earlyLunchMonday));
        $this->assertTrue($systemUnderTest->isClosed($earlyLunchTuesday));
        $this->assertTrue($systemUnderTest->isClosed($earlyLunchWednesday));
        $this->assertTrue($systemUnderTest->isClosed($morngingClosedDate));
        $this->assertTrue($systemUnderTest->isClosed($morngingClosedDate));
    }

    public function testNextOpen()
    {
        global $HOLIDAYS, $SHOPOPENANDCLOSE;

        $systemUnderTest = new Shop($HOLIDAYS, $SHOPOPENANDCLOSE);

        $morngingClosedDate = new DateTime("07-08-2017 08:11:31");
        $expectedMorningOpening = "07-08-2017 09:00";

        $actualNextOpeningFromMorning = $systemUnderTest->nextOpen($morngingClosedDate);
        $this->assertEquals($expectedMorningOpening, $actualNextOpeningFromMorning->format("d-m-Y H:i"));

        $lunchTime = new DateTime("16-10-2017 13:15");
        $expectedLunchOpening = "16-10-2017 13:30";

        $actualNextOpeningFromLunch = $systemUnderTest->nextOpen($lunchTime);
        $this->assertEquals($expectedLunchOpening, $actualNextOpeningFromLunch->format("d-m-Y H:i"));

        $endOfDayClosedDate = new DateTime("16-10-2017 21:15");
        $expectedNextMorningOpening = "17-10-2017 09:00";

        $actualNextOpeningFromEndOfDay = $systemUnderTest->nextOpen($endOfDayClosedDate);
        $this->assertEquals($expectedNextMorningOpening, $actualNextOpeningFromEndOfDay->format("d-m-Y H:i"));

        $christmasHolidays2017Day1 = new DateTime("20-12-2017");
        $expectedEndOfHolidayOpening = "02-01-2018 09:00";

        $actualNextOpeningFromStartOfHoliday = $systemUnderTest->nextOpen($christmasHolidays2017Day1);
        $this->assertEquals($expectedEndOfHolidayOpening, $actualNextOpeningFromStartOfHoliday->format("d-m-Y H:i"));

        // Test for holiday
        $dayBeforeGoodFriday = new DateTime("13-04-2017 18:00");
        $expectedOpeningAfterHoliday = "17-04-2017 09:00";

        $actualNextOpeningFromGoodFriday = $systemUnderTest->nextOpen($dayBeforeGoodFriday);
        $this->assertEquals($expectedOpeningAfterHoliday, $actualNextOpeningFromGoodFriday->format("d-m-Y H:i"));

        // Test for weekend
        $onWeekend = new DateTime("02-03-2019");
        $expectedOpeningAfterWeekend = "04-03-2019 09:00";

        $actualNextOpeningFromGoodFriday = $systemUnderTest->nextOpen($onWeekend);
        $this->assertEquals($expectedOpeningAfterWeekend, $actualNextOpeningFromGoodFriday->format("d-m-Y H:i"));
    }

    public function testNextClosed()
    {
        global $HOLIDAYS, $SHOPOPENANDCLOSE;

        $systemUnderTest = new Shop($HOLIDAYS, $SHOPOPENANDCLOSE);

        $morngingOpenDate = new DateTime("07-08-2017 09:11:31");
        $expectedMorningClosing = "07-08-2017 12:20";

        $actualMorningClosing = $systemUnderTest->nextClosed($morngingOpenDate);

        $this->assertEquals($expectedMorningClosing, $actualMorningClosing->format("d-m-Y H:i"));

        // Test a thursday as closing differs for morning
        $thursdayMorngingOpenDate = new DateTime("10-08-2017 09:11");
        $expectedThursdayMorningClosing = "10-08-2017 12:30";

        $actualthursMorningClosing = $systemUnderTest->nextClosed($thursdayMorngingOpenDate);

        $this->assertEquals($expectedThursdayMorningClosing, $actualthursMorningClosing->format("d-m-Y H:i"));

        $afternoonOpenDate = new DateTime("16-10-2017 15:15");
        $expectedAfternoonClosing = "16-10-2017 17:00";
        
        $actualAfternoonClosing = $systemUnderTest->nextClosed($afternoonOpenDate);

        $this->assertEquals($expectedAfternoonClosing, $actualAfternoonClosing->format("d-m-Y H:i"));

        // Test for holiday
        $dayBeforeGoodFriday = new DateTime("14-04-2017 18:00");
        $expectedClosingForHoliday = "14-04-2017";

        $actualClosingForHoliday = $systemUnderTest->nextClosed($dayBeforeGoodFriday);

        $this->assertEquals($expectedClosingForHoliday, $actualClosingForHoliday->format("d-m-Y"));

        // Test for weekend
        $fridayAfternoon = new DateTime("02-03-2019 18:00");
        $expectedClosingForWeekend = "02-03-2019";

        $actualClosingForWeekend = $systemUnderTest->nextClosed($fridayAfternoon);
        $this->assertEquals($expectedClosingForWeekend, $actualClosingForWeekend->format("d-m-Y"));
    }

    public function testListOpeningTimes()
    {
        global $HOLIDAYS, $SHOPOPENANDCLOSE;

        $systemUnderTest = new Shop($HOLIDAYS, $SHOPOPENANDCLOSE);

        $fridayTotest = new DateTime("01-03-2019 10:00");
        $expectedFridayOpeningTimes = [
            '09:00' => 'open',  // Open
            '12:30' => 'closed', // Close for Lunch
            '13:30' => 'open',  // Return from Lunch
            '17:00' => 'closed', // Close for the day
        ];
        $actualFridayOpeningTimes = $systemUnderTest->listOpeningTimes($fridayTotest);

        $this->assertEquals($expectedFridayOpeningTimes, $actualFridayOpeningTimes);

        // test weekend
        $saturdayTotest = new DateTime("02-03-2019 12:50");
        $expectedSaturdayOpeningTimes = [];
        $actualSaturdayOpeningTimes = $systemUnderTest->listOpeningTimes($saturdayTotest);

        $this->assertEquals($expectedSaturdayOpeningTimes, $actualSaturdayOpeningTimes);

        // test monday for different lunch, no time
        $mondayTotest = new DateTime("25-02-2019");
        $expectedMondayOpeningTimes = [
            '09:00' => 'open',  // Open
            '12:20' => 'closed', // Close for Lunch
            '13:30' => 'open',  // Return from Lunch
            '17:00' => 'closed', // Close for the day
        ];
        $actualMondayOpeningTimes = $systemUnderTest->listOpeningTimes($mondayTotest);

        $this->assertEquals($expectedMondayOpeningTimes, $actualMondayOpeningTimes);

        // test holidays?
    }
}