<?php
require_once __DIR__ . '/vendor/autoload.php';
$HOLIDAYS = require(__DIR__ . "/config/holidays.config.php");
$SHOPOPENANDCLOSE = require(__DIR__ . "/config/times.config.php");

use HLS\Shop;

$shopToUse = new Shop($HOLIDAYS, $SHOPOPENANDCLOSE);

print("Please enter a date and press return.\n");
print("For Opening times of a day, please enter a day.\n");

$userInput = trim(fgets(STDIN));

print("you typed $userInput \n");

try 
{
    $dateToUse = new DateTime(trim($userInput));
}
catch(Exception $e)
{
    $dateToUse = new DateTime();
    $dateFromUserInput = strtotime($userInput);
    if ($dateFromUserInput === false)
    {
        print("Not a valid date or day, using today. \n");
    }
    else
    {
        $dateToUse->setTimeStamp(strtotime($userInput));
    }
}

if ($shopToUse->isOpen($dateToUse))
{
    $nextClosed = $shopToUse->nextClosed($dateToUse);
    print("The Shop is open \n");
    print("It will close on " . $nextClosed->format('d-m-y H:i') . " \n");
}
else
{
    $nextOpen = $shopToUse->nextOpen($dateToUse);
    print("The Shop is closed \n");
    print("It will open on " . $nextOpen->format('d-m-Y H:i') . " \n");
}

print("See below for opening times \n");

$OpeningTimesForDay = $shopToUse->listOpeningTimes($dateToUse);

foreach($OpeningTimesForDay as $key => $value)
{
    print($key . " - " . $value . " \n");
}

print("See below for public holidays in which the shop will be closed \n");

$listOfPublicHolidays = $shopToUse->listHolidaysShopIsClosed();

foreach($listOfPublicHolidays as $holiday)
{
    print($holiday["description"] . " \n");
    print($holiday["start"] . " - start \n");
    print($holiday["end"] . " - end \n");
}