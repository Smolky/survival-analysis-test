<?php 

ini_set ('display_errors', 1);
ini_set ('display_startup_errors', 1);
error_reporting (E_ALL); 


// Autoload
require_once 'vendor/autoload.php';


/** @var $faker Factory use the factory to create a Faker\Generator instance */
$faker = Faker\Factory::create ('es_ES');


/** @var $start_day DateTime */
$start_date = new DateTime ('last day of previous year');


/** @var $end_date DateTime A whole year */
$end_date = clone $start_date;
$end_date->add (new \DateInterval ('P1M'));


/** @var $last_maintenance_date DateTime */
$last_maintenance_date = clone $start_date;


/** @var $daily_period Array A period of each day between the interval */
$daily_period = new \DatePeriod ($start_date, new DateInterval ('PT15M'), $end_date);


// Response
header ('Content-type: text/plain');
// header ('Cache-Control: no-store, no-cache');
// header ('Content-Disposition: inline');


/** @var $providers Array */
$providers = ['providerA'];


/** @var $teams Array */
$teams = ['teamA'];



/** @var $headers Array */
$headers = ['minutes_working_since_last_maintenance', 'maintenance', 'chlorine_level_ppm', 'electric_supply', 'provider', 'team'];


// Headers
echo implode (',', $headers) . "\r\n";


// For each period...
foreach ($daily_period as $index => $date) {
    
    /** @var $date_string String */
    $date_string = $date->format ('Y-m-d H:i:s');
    
    
    /** @var $maintenance boolean */
    $maintenance = $index % (4 * rand (20, 24)) === 0 ? 1 : 0;
    
    
    /** @var $maintenance_is_near boolean */
    $maintenance_is_near = $index % (4 * 24) === 0 <= 10;
    
    
    // Update last maintenance date
    if ($maintenance) {
        $last_maintenance_date = clone $date;
    }
    
    
    /** @var $difference */
    $difference = $date->diff ($last_maintenance_date);
    
    
    /** @var $electricy_max_peak int */
    $electricy_max_peak = $maintenance_is_near ? 20 : 20;
    
    
    /** @var $chlorine_level_ppm_max_peak int */
    $chlorine_level_ppm_max_peak = $maintenance_is_near ? 5 : 5;
    
    
    
    /** @var $hours_working_without_maintenance String */
    $hours_working_without_maintenance = $difference->format ('%i') + ($difference->format ('%h') * 60);
    
    
    /** @var $electric_supply float */
    $electric_supply = $faker->randomFloat (3, 4, $electricy_max_peak);
    
    
    /** @var $chlorine_level_ppm float */
    $chlorine_level_ppm = $faker->randomFloat (3, 0, $chlorine_level_ppm_max_peak);
    
    
    /** @var $provider Array */
    $provider = $providers[rand(0, count($providers) - 1)];


    /** @var $team Array */
    $team = $teams[rand(0, count($teams) - 1)];

    
    
    // Output line
    echo implode (',', [
        $hours_working_without_maintenance,
        $maintenance,        
        $chlorine_level_ppm,
        $electric_supply,
        $provider,
        $team
    ]) . "\r\n";
    
}
