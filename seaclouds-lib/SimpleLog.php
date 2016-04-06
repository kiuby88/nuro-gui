<?php
// ****************************************************************************
/**
 * Nurogames SeaClouds Casestudy
 *
 * SimpleLog.php
 *
 * @author      Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2014, Nurogames GmbH
 */
// ****************************************************************************

// CLASS ======================================================================
/**
 * @brief creates log / updates it
 */
class SimpleLog extends JeDbObject
{
    /// @brief LogId number
    public $nLogId = -1;

    /// @brief RequestStart string
    public $sRequestStart = "0000-00-00 00:00:00";

    /// @brief PhpStart float
    public $fPhpStart = 0.0;

    /// @brief server Ip
    public $sIp = "";

    /// @brief client Ip
    public $sIpForward = "";

    /// @brief Request string
    public $sRequest = "";

    /// @brief UserAgent string
    public $sUserAgent = "";

    /// @brief Debug number
    public $nDebug = 0;

    // FUNCTIONS ==============================================================
    
    // ------------------------------------------------------------------------
    /**
     * @brief constructor initialize vars
     * 
     * @param $fPhpStart - PhpStart float
     * @param $aServer - Server array
     * 
     * @return NULL
     */
    public function __construct( $fPhpStart, $aServer)
    {
        if ( $fPhpStart == 0.0)
        {
            Debug::message( "Empty PHP start!");

            return NULL;
        }

        $this->fPhpStart = $fPhpStart;

        if ( empty( $aServer))
        {
            Debug::message( "Empty Server Array!");
            
            $aServer = $_SERVER;
        }

        $this->sRequestStart = date("Y-m-d H:i:s", $aServer['REQUEST_TIME']);

//        if ( $aServer['HTTP_CLIENT_IP'] != "")
//        {
//            $this->sIpForward = $aServer['HTTP_CLIENT_IP'];
//        }
//        elseif ( $aServer['HTTP_FORWARDED_FOR'] != "")
//        {
//            $this->sIpForward = $aServer['HTTP_FORWARDED_FOR'];
//        }

        $this->sIp = $aServer['REMOTE_ADDR'];

        $this->sRequest = $aServer['REQUEST_URI'];

        $this->sUserAgent = $aServer['HTTP_USER_AGENT'];

        $this->nLogId = $this->createNewLog(    NULL,
                                                $this->sRequestStart,
                                                $this->fPhpStart,
                                                $this->sIpForward,
                                                $this->sIp,
                                                $this->sRequest,
                                                $this->sUserAgent);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief creates new log and inserts it
     * 
     * @param $kResult - result object
     * @param $sRequestStart - RequestStart string
     * @param $fPhpStart - PhpStart float
     * @param $sIpForward - client ip
     * @param $sIp - server ip
     * @param $sRequest - Request string
     * @param $sUserAgent - String with information about user
     * 
     * @return id
     */
    public function createNewLog(   $kResult,
                                    $sRequestStart = NULL,
                                    $fPhpStart,
                                    $sIpForward,
                                    $sIp,
                                    $sRequest,
                                    $sUserAgent)
    {
        $kDb = JeDb::getInstance();

        $sSql = 'INSERT INTO logs ( log_request_start, '
                 .                ' log_php_start, '
                 .                ' log_ip_forward, '
                 .                ' log_ip, '
                 .                ' log_request, '
                 .                ' log_user_agent) '
                 . ' VALUES ( NOW(), '
                 .          ' "'. $fPhpStart .'", '
                 .          ' "'. $sIpForward .'", '
                 .          ' "'. $sIp .'", '
                 .          ' "'. $sRequest .'", '
                 .          ' "'. $sUserAgent .'")'
        ;

        $nId = $kDb->insert( $sSql);

        return $nId;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief updates log
     * 
     * @param $kDebug - debug object
     * @param $kForceDebug - ForceDebug object
     * @param $nResponseLength - ResponseLength number
     * @param $fPhpDuration - phpDuration float
     * @param $sMessageText - - MessageText string
     */
    public function updateLog(  $kDebug,
                                $kForceDebug,
                                $nResponseLength,
                                $fPhpDuration,
                                $sMessageText = "")
    {
        if( array_key_exists( 'boom', $_REQUEST))
        {
            $fPhpDuration += $_REQUEST[ 'boom'];
        }
        
        
        $kDb = JeDb::getInstance();

        if ( $kDebug && !$kForceDebug)
        {
            $this->nDebug = 1;
        }
        elseif ( $kForceDebug)
        {
            $this->nDebug = 2;
        }

        $sSql = 'UPDATE logs SET '
                 . ' log_do = '
                 .      ' "99", '
                 . ' log_debug_mode = '
                 .      ' "'. $this->nDebug .'", '
                 . ' log_response_length = '
                 .      ' "'. $nResponseLength .'", '
                 . ' log_text = '
                 .      ' "'. $sMessageText .'", '
                 . ' log_response_time = '
                 .      ' "'. $fPhpDuration .'" '
                 . ' WHERE log_id = "'. $this->nLogId .'"'
        ;

        $kDb->update( $sSql);
    }
}
?>
