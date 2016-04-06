<?php
// ****************************************************************************
/**
 * Nurogames SeaClouds Casestudy
 *
 * Timer.php
 *
 * @author      Christian Tismer, Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2014, Nurogames GmbH
 */
// ****************************************************************************

// CLASS ======================================================================
/**
 * @brief
 */
class Timer
{
    /// @brief timer start
    public $start = 0;
    
    /// @brief timer stop
    public $stop = 0;
    
    /// @brief timer time
    public $real = 0.0;
    
    // FUNCTIONS ==============================================================
    
    // ------------------------------------------------------------------------
    /**
     * @brief constructor starts time
     * 
     * @param type $fStartTime
     */
    public function __construct( $fStartTime = NULL)
    {
        $this->start(  $fStartTime);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief starts time
     * 
     * @param $fStartTime - StartTime float
     */
    public function start( $fStartTime = NULL)
    {
        if( empty( $fStartTime))
        {
            $fStartTime = microtime( TRUE);
        }
        
        $this->start = $fStartTime;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief stops time
     * 
     * @param $fStopTime - StopTime float
     */
    public function stop( $fStopTime = NULL)
    {
        if( empty( $fStopTime))
        {
            $fStopTime = microtime( TRUE);
        }
        
        $this->stop = $fStopTime;

        $this->real = $fStopTime - $this->start;

    }
    
    // ------------------------------------------------------------------------
    /**
     * @brief print real string / returns it
     * 
     * @return realString
     */
    public function realString()
    {
        return sprintf( '%1$0.23F', $this->real);
    }

}
