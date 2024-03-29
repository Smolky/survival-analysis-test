<?php 

/**
 * Generate sample data
 *
 * This script is used to generate sample data to test 
 * pysurvival library
 *
 * @author José Antonio García-Díaz <joseantonio.garcia8@um.es>
 */

ini_set ('display_errors', 1);
ini_set ('display_startup_errors', 1);
error_reporting (E_ALL); 


// Autoload
require_once 'vendor/autoload.php';


/** @var $faker Factory use the factory to create a Faker\Generator instance */
$faker = Faker\Factory::create ('es_ES');


/** @var $start_day DateTime */
$start_date = new DateTime ('last month');


/** @var $end_date DateTime A whole month */
$end_date = clone $start_date;
$end_date->add (new \DateInterval ('P1M'));


/** @var $last_maintenance_date DateTime */
$last_maintenance_date = clone $start_date;


/** @var $daily_period Array A period of each day between the interval */
$daily_period = new \DatePeriod ($start_date, new DateInterval ('PT15M'), $end_date);


// Response
header ('Content-type: text/plain');
header ('Cache-Control: no-store, no-cache');


/** @var $providers Array Include more providers here. There we fetched randomly */
$providers = ['providerA'];


/** @var $teams Array Include more tems here. There we fetched randomly */
$teams = ['teamA'];



/** @var $headers Array The first item is the time, the second the event and the rest the features */
$headers = [
    'minutes_working_since_last_maintenance', 
    'maintenance', 
    'chlorine_level_ppm', 
    'electric_supply', 
    'provider', 
    'team'
];


// Headers
echo implode (',', $headers) . "\r\n";


/** @var i int */
$i = 0;


// For each period...
foreach ($daily_period as $date) {
    
    /** @var $difference Get the difference in time since last maintenance */
    $difference = $date->diff ($last_maintenance_date);
    
    
    /** @var $minutes_working_without_maintenance int How many minutes the machine was working without failures */
    $minutes_working_without_maintenance = $difference->format ('%i') + ($difference->format ('%h') * 60);
    
    
    /** @var $maintenance boolean Detect if the machine crashes and maintenance has to be performed */
    $maintenance = $minutes_working_without_maintenance <= (60 * 10) ? false : (0 === rand (0, 10));
    
    
    /** @var $electric_supply float Current electry supply */
    $electric_supply = $faker->biasedNumberBetween (4, 20 - 1, function ($x) use ($i) {
        return ($i / 100) + sqrt ($x);
    }) + $faker->randomFloat (5, 0, 1);
    
    
    /** @var $chlorine_level_ppm float */
    $chlorine_level_ppm = $faker->biasedNumberBetween (0, 5 - 1, function ($x) use ($i) {
        return ($i / 100) + sqrt ($x);
    }) + $faker->randomFloat (5, 0, 1);
    
    
    /** @var $provider Array A random provider*/
    $provider = $providers[rand (0, count($providers) - 1)];


    /** @var $team Array A random team */
    $team = $teams[rand (0, count($teams) - 1)];

    
    // Output line
    echo implode (',', [
        $minutes_working_without_maintenance,
        $maintenance * 1,        
        $chlorine_level_ppm,
        $electric_supply,
        $provider,
        $team
    ]) . "\r\n";
    
    
    // Update the last maintenance date
    if ($maintenance) {
        $last_maintenance_date = clone $date;
        $i = 0;
    } else {
        $i++;
    }
}
