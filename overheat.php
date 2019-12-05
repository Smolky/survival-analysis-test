<?php 

/**
 * Generate sample data
 *
 * This fake data will break a machine two hours 
 * after an overheat 
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


/** @var $will_overheat false */
$will_overheat = false;


/** @var $electric_supply float Current electry supply */
$electric_supply = 0;


// For each period...
foreach ($daily_period as $date) {
    
    /** @var $difference Get the difference in time since last maintenance */
    $difference = $date->diff ($last_maintenance_date);
    
    
    /** @var $minutes_working_without_maintenance int How many minutes the machine was working without failures */
    $minutes_working_without_maintenance = $difference->format ('%i') + ($difference->format ('%h') * 60);
    
    
    /** @var $will_overheat boolean Detect if the machine will broken sooner */
    if ( ! $will_overheat) {
        $will_overheat = $minutes_working_without_maintenance <= (60 * 10) ? false : (0 === rand (0, 10));
    }
    
    
    /** @var $maintenance boolean The machine crashes after two hours after an overheat */
    $maintenance = $will_overheat && $minutes_working_without_maintenance == 120;
    
    
    // If the machine will overheat we will add extra electric power
    // We divide first the minutes between intervals of teen minutes
    // Then we remove 60 pieces, that is when the machine has been working fine
    // Finally, divide into 10 to get a real temperature
    if ($will_overheat) {
        $electric_supply += (($minutes_working_without_maintenance / 10) - 60) / 10;
    } else {
        $electric_supply = $faker->biasedNumberBetween (4, 20 - 1, function ($x) use ($i) {
            return ($i / 100) + sqrt ($x);
        }) + $faker->randomFloat (5, 0, 1);
        
    }
    
    
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
        $will_overheat = false;
    } else {
        $i++;
    }
}
