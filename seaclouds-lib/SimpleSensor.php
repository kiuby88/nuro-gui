<?php
// ****************************************************************************
/**
 * Nurogames SeaClouds Casestudy
 *
 * SimpleSensor.php
 *
 * @author      Christian Tismer, Nurogames GmbH
 * 
 * @copyright   Copyright (c) 2014, Nurogames GmbH
 */
// ****************************************************************************

// CLASS ======================================================================
/**
 * 
 */
class SimpleSensor
{
    /// @brief header var
    public $header;
    
    public $result;
    
    /// @brief member result object
    protected $m_kResult;
    
    /// @brief member timer object
    protected $m_kTimer;
    
    /// @brief member PhpTimer object
    protected $m_kPhpTimer;
    
    /// @brief member Log object
    protected $m_kLog;
    
    /// @brief static array AverageIntervals
    protected static $s_aAverageIntervals =
            array(
                '10seconds' => 10,
                'minute' => 60,
                'hour' => 3600,
                'day' => 86400,
                'week' => 0);
    
    // FUNCTIONS ==============================================================
    
    // ------------------------------------------------------------------------
    /**
     * @brief constructor initialize vars
     * 
     * @param $fStartTime - StartTime float
     */
    public function __construct( $fStartTime)
    {
        $this->m_kResult = new stdClass();
        
        $this->header = new stdClass;
        
        $this->result = new stdClass;
        
        $this->m_kResult->header = &$this->header;
    
        $this->m_kResult->result = &$this->result;
    
        $this->m_kTimer = new Timer();
    
        $this->m_kPhpTimer = new Timer( $fStartTime);
    }
    
    // ------------------------------------------------------------------------
    /**
     * @brief connect with db
     */
    public function connectDb()
    {
        $this->m_kResult->database1 = new stdClass();

        $this->m_kTimer->start();

        $kDb = JeDb::getInstance();

        $this->m_kTimer->stop();
        
        $this->m_kResult->database1->_INFO =
                                          'ToDo: redesign for multi DB support';
        
        if( $kDb)
        {
            $this->m_kResult->database1->status = 'Connected';
        }
        else
        {
            $this->m_kResult->database1->status = 'Connection Failure';
        }
        
        $this->m_kResult->database1->host =
                               g_DatabaseHost . '(ToDo: N/A on live systems!)';

        $this->m_kResult->database1->database =
                               g_DatabaseName . '(ToDo: N/A on live systems!)';

        $this->m_kResult->database1->connection_time =
                                                 $this->m_kTimer->realString();

    }

    // ------------------------------------------------------------------------
    /**
     * @brief creates log
     */
    public function createLog()
    {
        $this->m_kTimer->start();

        $this->m_kLog = new SimpleLog( $this->m_kPhpTimer->start, $_SERVER);

        $this->m_kTimer->stop();
        
        $this->m_kResult->header->log_id = $this->m_kLog->nLogId;

        $this->m_kResult->database1->insert_log_time = $this->m_kTimer->realString();
    }

    // ------------------------------------------------------------------------
    /**
     * @brief gets avg/max response time, 
     *             avg/max run time,
     *             avg/max request time
     *             and counts them
     */
    public function getAverages()
    {
        $kDb = JeDb::getInstance();

        $this->m_kResult->request_analytics = new stdClass();

        $this->m_kTimer->start();

        foreach( self::$s_aAverageIntervals as $sInterval => $nSeconds)
        {
            $sSql =
            'SELECT '
                . $nSeconds . ' AS seconds, '
                . ' AVG( log_response_time) avg_response_time, '
                . ' MAX( log_response_time) max_response_time, '
                . ' AVG( log_response_time) avg_run_time, '
                . ' MAX( log_response_time) max_run_time, '
                . ' AVG( log_client_request_time) avg_request_time, '
                . ' MAX( log_client_request_time) max_request_time, '
                . ' COUNT(*) request_count, '
                . ' COUNT( DISTINCT player_id) player_count '
            . 'FROM logs '
            . 'WHERE log_request_start > '
                    . ' DATE_SUB( NOW(), INTERVAL ' . $nSeconds . ' SECOND);';

            $this->m_kResult->request_analytics->$sInterval =
                                                   $kDb->fetchFirstRow( $sSql);
        }
        
        $this->m_kResult->request_analytics->week->_INFO =
                                                    "week node is DEPRECATRED";

        $this->m_kTimer->stop();

        $this->m_kResult->database1->analytics_time =
                                                 $this->m_kTimer->realString();
    }

    // ------------------------------------------------------------------------
    /**
     * @brief gets analytics based on period and precision,
     * means period of time and precision of timestamp
     * 
     * @param $nPeriodInSeconds - PeriodInSeconds number
     * @param $nPrecision - Precision number
     */
    public function getAnalytics( $nPeriodInSeconds = 360000, $nPrecision = 16)
    {
        $kDb = JeDb::getInstance();

        $this->m_kTimer->start();

        $sSql =
            'SELECT '
                . ' LEFT( log_request_start, "' . $nPrecision . '") time_group, '
                . ' RIGHT( log_request_start, 8) request_time, '
                . ' COUNT(*) AS requests, '
                . ' COUNT( DISTINCT player_id) AS users, '
                . ' AVG( log_response_time) AS avg_run_time, '
                . ' GROUP_CONCAT('
                    . ' DISTINCT IF( log_do = 99 AND log_text = "",'
                                 . ' NULL,'
                                 . ' log_text)'
                . ' ) AS messages '
            . ' FROM logs '
            . ' WHERE log_request_start < '
                . 'DATE_SUB( NOW( ) , INTERVAL 1 SECOND ) '
            . ' AND log_request_start > '
                . 'DATE_SUB( NOW( ) , '
                    . ' INTERVAL "' . $nPeriodInSeconds . '" SECOND ) '
            . ' GROUP BY 1;';

        $this->m_kResult->result->analytics = $kDb->fetchAllRows( $sSql);

        $this->m_kTimer->stop();

        $this->m_kResult->database1->analytics_time =
                                                 $this->m_kTimer->realString();
    }

    // ------------------------------------------------------------------------
    /**
     * @brief updates log
     * 
     * @param $sMessageText - MessageText string
     */
    public function updateLog( $sMessageText = '')
    {
        $this->m_kPhpTimer->stop();

        $this->m_kTimer->start();

        $this->m_kLog->updateLog( NULL,
                                  0,
                                  strlen( $this->toJson()),
                                  $this->m_kPhpTimer->realString(),
                                  $sMessageText);

        $this->m_kTimer->stop();

        $this->m_kResult->database1->update_log_time =
                                                $this->m_kTimer->realString();
    }

    // ------------------------------------------------------------------------
    /**
     * @brief returns data to json
     * 
     * @param $bAnalyticsOnly - boolean
     * 
     * @return json 
     */
    public function toJson( $bAnalyticsOnly = FALSE)
    {
        $this->m_kResult->debug = Debug::messages();

        if( $bAnalyticsOnly)
        {
            return json_encode( $this->m_kResult->result->analytics);
        }
        
        return json_encode( $this->m_kResult);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief
     * 
     * @param $aGuideTemplate GuideTemplate array
     * 
     * @return json data
     */
    public function analyticsGuide( $aGuideTemplate = NULL)
    {
//                   {
//                        "category": "11:25:01",
//                        "lineColor": "#CC0000",
//                        "lineAlpha": 1,
//                        "dashLength": 2,
//                        "inside": true,
//                        "labelRotation": 90,
//                        "label": "Wxxxxxx",
//                        "balloonText":"detailed information: only for testing purposes"
//                   }

        if( !$aGuideTemplate)
        {
            $aGuideTemplate = //array( 'balloonText' => '');
                            array(
                                "inside" => false,
                                "above" => true,
                                "labelRotation" => 90,
                                "fontSize" => 11,
                                "color" => "#00000",
                                "lineColor" => "#000000",
                                "lineAlpha" => 0.5,
                                "lineThickness" => 2,
                                "dashLength" => 3,
                                "balloonText" => "Effector Event:"
                        );
        }
        
        $aResultGuides = array();
        
        foreach( $this->m_kResult->result->analytics as $kAnlyticsRow)
        {
            if( $kAnlyticsRow->messages)
            {
                $aActualGuide = $aGuideTemplate;

                $aActualGuide[ 'category'] = $kAnlyticsRow->request_time;

                $aActualGuide[ 'label'] = $kAnlyticsRow->messages;
                
                $aActualGuide[ 'balloonText'] .= "\n"
                        . $kAnlyticsRow->messages
                        . "\n"
                        . $kAnlyticsRow->request_time;
                
                $aResultGuides[] = $aActualGuide;
            }
        }
        
        return $aResultGuides;
    }

    // ------------------------------------------------------------------------
    /**
     * @brief
     * 
     * @param $aGuideTemplate GuideTemplate array
     * 
     * @return json data
     */
    public function analyticsGuideAsJson( $aGuideTemplate = NULL)
    {
        $aResultGuides = $this->analyticsGuide( $aGuideTemplate);

        return json_encode( $aResultGuides);
    }

    // ------------------------------------------------------------------------
    /**
     * @brief closeDb() Close Database Connection
     */
    public function closeDb()
    {
        $kDb = JeDb::getInstance();
        
        $kDb->close();
    }
}
?>