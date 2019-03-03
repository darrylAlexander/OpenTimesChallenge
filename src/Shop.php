<?php
namespace HLS;

use DateTime;

class Shop
{
    private $holidays;
    private $shopOpenAndClose;

    function __construct($holidays, $shopOpenAndClose)
    {
        $this->holidays = $holidays;
        $this->shopOpenAndClose = $shopOpenAndClose;
    }


    /**
     * Is the shop open on the provided date/time
     * If provided a DateTime object, check relative to that, otherwise use now
     *
     * @param DateTime $dt
     * @return boolean
     */
    public function isOpen(DateTime $dt = null)
    {
        $dateToCheckShopIsOpen = $dt ?? new DateTime();
        
        $dateAsUnixTimeStamp = $dateToCheckShopIsOpen->getTimeStamp(); 
        $isHoliday = false;
        $isOpen = false;

        /* Check for weekends */
        $dayOfWeek = date("l", $dateAsUnixTimeStamp);
        if (($dayOfWeek === "Saturday") || ($dayOfWeek === "Sunday"))
        {
            return false;
        }

        /* Check for holidays */
        $isHoliday = $this->isHoliday($dateToCheckShopIsOpen);

        /* Check for opening */
        $timesOfDay = $this->shopOpenAndClose[$dayOfWeek];
        $timeFromDaySelected = $dateToCheckShopIsOpen->format('H:i');
        $openingTimes = array_keys($timesOfDay, "open");
        $closingTimes = array_keys($timesOfDay, "closed");

        if(((strtotime($timeFromDaySelected) >= strtotime($openingTimes[0])) &&
            (strtotime($timeFromDaySelected) < strtotime($closingTimes[0]))) ||
            ((strtotime($timeFromDaySelected) >= strtotime($openingTimes[1])) &&
            (strtotime($timeFromDaySelected) < strtotime($closingTimes[1]))))
        {
            $isOpen = true;
        }
        else
        {
            $isOpen = false;
        }

        return ($isOpen && ($isHoliday === false)) ? $isOpen : false;
    }


    /**
     * Is the shop closed on the provided date/time
     * If provided a DateTime object, check relative to that, otherwise use now
     *
     * @param DateTime $dt
     * @return boolean
     */
    public function isClosed(DateTime $dt = null)
    {
        $isOpen = $this->isOpen($dt);
        return !$isOpen;
    }


    /**
     * At what date/time will the shop next be open
     * If provided a DateTime object, check relative to that, otherwise use now
     * If the shop is already open, return the provided datetime/now
     *
     * @param DateTime $dt
     * @return DateTime
     */
    public function nextOpen(DateTime $dt = null)
    {
        $dateToCheckNextOpen = $dt ?? new DateTime();

        if ($this->isOpen($dateToCheckNextOpen) === true)
        {
            return $dateToCheckNextOpen;
        }

        /* Check for holidays */
        $isHoliday = $this->isHoliday($dateToCheckNextOpen);
        $dayOfWeek = date("l", $dateToCheckNextOpen->getTimeStamp());

        if (($isHoliday === true) || ($dayOfWeek === "Saturday" || $dayOfWeek === "Sunday"))
        {
            // its within a holiday so find out when next working day is.
            $dateToCheckNextOpen = $this->nextWorkingDay($dateToCheckNextOpen);
        }

        $dayOfWeek = date("l", $dateToCheckNextOpen->getTimeStamp());
        $nextOpeningTime = null;

        /* Check for opening */
        $timesOfDay = $this->shopOpenAndClose[$dayOfWeek];
        $timeFromDaySelected = $dateToCheckNextOpen->format('H:i');
        $openingTimes = array_keys($timesOfDay, "open");
        $closingTimes = array_keys($timesOfDay, "closed");
        $today = $dateToCheckNextOpen->format("d-m-Y");

        if (strtotime($timeFromDaySelected) < strtotime($openingTimes[0]))
        {
            $nextOpeningTime = new DateTime($today . " " . $openingTimes[0]);                
        }
        else if (((strtotime($timeFromDaySelected) >= strtotime($openingTimes[0])) &&
            (strtotime($timeFromDaySelected) < strtotime($closingTimes[0]))) ||
            ((strtotime($timeFromDaySelected) >= strtotime($closingTimes[0])) &&
            (strtotime($timeFromDaySelected) < strtotime($openingTimes[1]))))
        {
            $nextOpeningTime = new DateTime($today . " " . $openingTimes[1]);
        }
        else
        {
            $timeToSet = $openingTimes[0];

            // Because it will move into next day 
            // Re-Check for next working day
            $dateToTestForNextWorkingDay = new DateTime($today . $timeToSet);
            $dateToCheckNextOpen = $this->nextWorkingDay($dateToTestForNextWorkingDay);

            return $dateToCheckNextOpen;
        }

        return $nextOpeningTime;
    }


    /**
     * At what date/time will the shop next be closed
     * If provided a DateTime object, check relative to that, otherwise use now
     * If the shop is already closed, return the provided datetime/now
     *
     * @param DateTime $dt
     * @return DateTime
     */
    public function nextClosed(DateTime $dt = null)
    {
        $dateToCheckNextClose = $dt ?? new DateTime();

        if ($this->isClosed($dateToCheckNextClose) === true)
        {
            return $dateToCheckNextClose;
        }

        $dayOfWeek = date("l", $dateToCheckNextClose->getTimeStamp());
        $nextClosingTime = null;
        $timesOfDay = $this->shopOpenAndClose[$dayOfWeek];
        $timeFromDaySelected = $dateToCheckNextClose->format('H:i');
        $openingTimes = array_keys($timesOfDay, "open");
        $closingTimes = array_keys($timesOfDay, "closed");
        $today = $dateToCheckNextClose->format("d-m-Y");

        if ((strtotime($timeFromDaySelected) < strtotime($closingTimes[0])) ||
            ((strtotime($timeFromDaySelected) >= strtotime($openingTimes[0])) &&
            (strtotime($timeFromDaySelected) < strtotime($closingTimes[0]))))
        {
            $nextClosingTime = new DateTime($today . " " . $closingTimes[0]);              
        }
        else if ((strtotime($timeFromDaySelected) < strtotime($closingTimes[1])) ||
            ((strtotime($timeFromDaySelected) >= strtotime($openingTimes[1])) &&
            (strtotime($timeFromDaySelected) < strtotime($closingTimes[1]))))
        {
            $nextClosingTime = new DateTime($today . " " . $closingTimes[1]); 
        }
        else
        {
            $dayOfWeek = date("l", $dateToCheckNextClose->getTimeStamp());

            $dateToCheckNextClose->setTimeStamp(strtotime("tomorrow", $dateToCheckNextClose->getTimeStamp()));
            if (!$this->isHoliday($dateToCheckNextClose))
            {
                $nextClosingTime = new DateTime($dateToCheckNextClose->format("d-m-Y") . " " . $closingTimes[0]);
                return $nextClosingTime;
            }

            return $dateToCheckNextClose;
        }
        
        return $nextClosingTime;
    }


    /**
     * Provide the opening times for a given day
     * If provided a DateTime object, check relative to that, otherwise use now
     *
     * @param DateTime $dt
     * @return array
     */
    public function listOpeningTimes(DateTime $dt = null)
    {
        $dayToCheckForOpeningTimes = $dt ?? new DateTime();

        $dayOfWeek = date("l", $dayToCheckForOpeningTimes->getTimeStamp());

        $dayOpenAndClose = $this->shopOpenAndClose[$dayOfWeek];

        return $dayOpenAndClose;
    }


    /**
     * Provide the public holidays in which the shop is closed
     * If provided a DateTime object, check relative to that, otherwise use now
     *
     * @param DateTime $dt
     * @return array
     */
    public function listHolidaysShopIsClosed()
    {
        return $this->holidays;
    }


    /**
     * Is it a holiday on the provided date/time
     * If provided a DateTime object, check relative to that, otherwise use now
     *
     * @param DateTime $dt
     * @return boolean
     */
    private function isHoliday(DateTime $dt = null)
    {
        $dateToCheckForHoliday = $dt ?? new DateTime();

        $isHoliday = false;
        $dateWithoutTimeForHolidays = strtotime($dateToCheckForHoliday->format('d-m-Y'));
        foreach($this->holidays as $holiday)
        {
            $holidayStart = strtotime($holiday["start"]);
            $holidayEnd = strtotime($holiday["end"]);

            if (($dateWithoutTimeForHolidays >= $holidayStart) &&
            ($dateWithoutTimeForHolidays <= $holidayEnd))
            {
                $isHoliday = true;
                break;
            }
        }

        return $isHoliday;
    }


    /**
     * At what date/time will be the next working day
     * If provided a DateTime object, check relative to that, otherwise use now
     * If the shop is already closed, return the provided datetime/now
     *
     * @param DateTime $dt
     * @return DateTime
     */
    private function nextWorkingDay(DateTime $dt = null)
    {
        $dateToCheckForNextWorkingDay = $dt ?? new DateTime();

        $isWorkingDay = false;
        do
        {
            $dateToCheckForNextWorkingDay->setTimeStamp(strtotime("tomorrow " . $dateToCheckForNextWorkingDay->format("H:i"), $dateToCheckForNextWorkingDay->getTimeStamp()));
            $isWorkingDay = !$this->isHoliday($dateToCheckForNextWorkingDay);
        }
        while(!$isWorkingDay);

        $dayOfWeek = date("l", $dateToCheckForNextWorkingDay->getTimeStamp());

        if ($dayOfWeek === "Saturday" || $dayOfWeek === "Sunday")
        {
            $dateToCheckForNextWorkingDay->setTimeStamp(strtotime("next monday " . $dateToCheckForNextWorkingDay->format("H:i"), $dateToCheckForNextWorkingDay->getTimeStamp()));
        }

        return $dateToCheckForNextWorkingDay;
    }
}