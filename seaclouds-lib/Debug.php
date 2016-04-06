<?php
// ****************************************************************************
/**
 * Nurogames SeaClouds Casestudy
 *
 * @author      Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2014, Nurogames GmbH
 */
// ****************************************************************************

// CLASS ======================================================================
/**
 * @brief debug message handling
 */
class Debug
{
    ///@brief debug messages array
    static $s_kMessages = array();

    // FUNCTIONS ==============================================================
    
    // ------------------------------------------------------------------------
    /**
     * @brief saves messages in array
     * 
     * @param $kMessage - debug message
     */
    static public function message( $kMessage)
    {
        self::$s_kMessages []= $kMessage;
    }

    // ------------------------------------------------------------------------
    /** 
     * @brief returns debug messages
     * 
     * @return debug messages
     */
    static public function messages()
    {
        return self::$s_kMessages;
    }
}
?>
