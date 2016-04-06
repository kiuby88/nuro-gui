<?php
// ****************************************************************************
/**
 * Nurogames SeaClouds Casestudy Test
 *
 * analytics.php
 *
 * @author      Christian Tismer, Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2015, Nurogames GmbH
 */
// ****************************************************************************

// HEADER =====================================================================

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

///////////////////////////////////////////////////////////////////////////////

?>
{
    "api": {
        "sensor.php":{
            "status": "ok"
        },
        "effector.php":{
            "status": "ok"
        },
        "analytics.php":{
            "status": "ok"
        }
    },
    "gui": {
        "benchmark.php":{
            "status": "ok"
        },
        "chart.php":{
            "status": "ok"
        },
        "simulator.php":{
            "status": "ok"
        }
    }
}