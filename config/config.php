<?php

/**
 * Nurogames SeaClouds Casestudy
 *
 * configuration template file
 *
 * @author      Christian Tismer, Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2016, Nurogames GmbH
 * 
 */

if( file_exists( 'config/config_local.php'))
{
	require_once( 'config/config_local.php');
} 
else
{
    if( getenv('db_connection_uri'))
    {
        $aUrl = parse_url( getenv('db_connection_uri'));
        
        define('g_DatabaseHost', $aUrl['host']);
        define('g_DatabaseName', basename( $aUrl['path']));
        define('g_DatabaseUser', $aUrl['user']);
        define('g_DatabasePassword', $aUrl['pass']);
        define('g_DatabasePort', $aUrl['port']); //not used yet
    }
    elseif( getenv('db_host'))
    {
        define('g_DatabaseHost', getenv('db_host'));
        define('g_DatabaseName', getenv('db_name'));
        define('g_DatabaseUser', getenv('db_user'));
        define('g_DatabasePassword', getenv('db_pass'));
        define('g_DatabasePort', getenv('db_port')); //not used yet
    }
    else
    {
        print "Please Configure at least one DB!";
    }

    if( $aUrl = parse_url( getenv('db2_connection_uri')))
    {
        define('g_Database2Host', $aUrl['host']);
        define('g_Database2Name', $aUrl['name']);
        define('g_Database2User', $aUrl['user']);
        define('g_Database2Password', $aUrl['pass']);
        define('g_Database2Port', $aUrl['port']); //not used yet
    }
    
}

define( 'kDebugMode', TRUE);
?>