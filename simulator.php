<?php
// ****************************************************************************
/**
 * SeaClouds Project: http://www.seaclouds-project.eu/
 *
 * simulator.php
 * 
 * generate traffic to the deployed web application and check the performance
 *
 * @author		Christian Tismer, Nuromedia GmbH
 * 
 * @copyright	Copyright (c) 2014, Nuromedia GmbH
 * 
 * @version: 1.0 alpha
 */
// ****************************************************************************

// HEADER =====================================================================

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1);
ini_set("log_errors", 1);

if(array_key_exists( 'requests', $_REQUEST))
{
    $nBoomRequests = $_REQUEST['requests'] + 0;
}
else
{
    $nBoomRequests = 50000;
}

if(array_key_exists( 'traffic', $_REQUEST))
{
    $nTraffic = $_REQUEST['traffic'] + 0;
}
else
{
    $nTraffic = 1;
}

$nBoomConcurrency = $_REQUEST['concurrency'] + 0;

$nComplexity = $_REQUEST['complexity'] + 0;

if( array_key_exists( 'simulate', $_REQUEST))
{
    $nBoomSimulate = $_REQUEST['simulate'] + 0;
}
else
{
    $nBoomSimulate = 0;
}



//
// Security
//

$sUser = str_replace("'", "", $_REQUEST['user']);
$sPassword = str_replace("'", "", $_REQUEST['password']);
$sProxy = str_replace("'", "", $_REQUEST['proxy']);

//
// Urls
//

$sDefaultUrl = 'http://' . $_SERVER[ 'SERVER_NAME'] . dirname( $_SERVER[ 'REQUEST_URI']);

if(array_key_exists( 'url', $_REQUEST))
{
    $sUrl = str_replace("'", "", $_REQUEST['url']);
}
elseif( getenv('nuro_api_uri'))
{
    $sUrl = getenv('nuro_api_uri');
}
else
{
    $sUrl = $sDefaultUrl;
}

$sAnalyticsUrl = $sUrl . '/analytics.php';

//$sSensorUrl = $sUrl . '/sensor.php?boom=1.5';
$sSensorUrl = $sAnalyticsUrl . '?interval=100000&boom=' . $nBoomSimulate;

$sEffectorUrl = $sUrl . '/effector.php';

function generateHtmlOptions( $axOptionValues,
                              $xSelctedValue = NULL,
                              $sCaptionPrintfFormat = '%s')
{
    foreach ( $axOptionValues as $xOptionValue)
    {
        $sSelectedSwitch = $xOptionValue === $xSelctedValue ? ' selected' : '';
        
        print '<option value="' . $xOptionValue . '"' . $sSelectedSwitch . '>';
        
        printf( $sCaptionPrintfFormat, $xOptionValue);
        
        print '</option>';
    }
}

?>

<html>
  <head>
    <meta charset="utf-8">
    
    <!-- jquery -->
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SeaSlouds Project - NURO Casestudy Simulator">
    <meta name="author" content="Christian Tismer, Nurogames GmbH">
    <link rel="shortcut icon" href="http://www.seaclouds-project.eu/assets/ico/favicon.png">
    
    <title>SeaClouds | NURO Casestudy Simulator</title>

   </head>
   <body>

        <?php include( "header.html");?>

        <div>
            <p style="color:#FFF; font-size:30px;">NURO GUI: Simulator / Monitoring</p>
            <p style="color:#FFF; font-size:12px;"> For details see D6.1, D6.2, D6.3.1, D6.3.2, D6.3.3</p>
            <p style="padding-top:9px;">
            <a href="http://www.nurogames.com/" target="_blank">
                    <img alt="Nurogames" 
                             src="./img/LogoNurogames150x15.png" 
                     width="150"
                             height="15"></a></p>
        </div>

        <p>The NURO CaseStudy Simulator is intended to simulate different load scenarios.</p>
        <hr />

        <form method="get">
            <table border="0">
            <tr>
                <td colspan="5"><h3>Deployment Configuration</h3></td>
            </tr>

            <tr>
                <td>Deployment</td>
                <td>URL</td>
                <td colspan="4"><input name="url" id="deploymentUrl" type="text" size="90" value="<?php print $sUrl;?>" /></td>
                <td align="right"><input type="button" onclick="changeDeploymentUrl()" value="change" /></td>
            </tr>
            <tr>
                <td>optional:</td>
                <td>User</td>
                <td><input name="user" type="text" size="10" value="<?php print $sUser ? $sUser : 'seaclouds';?>" />
                </td>
                <td>Password <input name="password" type="text" size="10" value="<?php print $sPassword ? $sPassword : 'preview';?>" />
                </td>
                <td>&nbsp;</td>
                <td>Proxy:Port&nbsp;<input name="proxy" type="text" size="30" value="<?php print $sProxy;?>" /></td>
            </tr>
            </table>
            <hr />
            <table border="0">
            <tr>
                <td colspan="6"><h3>Simulation Configuration</h3></td>
            </tr>
            <tr>
                <td><b>Base Traffic</b></td>
                <td>
                    <select onchange="switchBaseTraffic()" id="traffic" name="traffic">
                        <option value="0" >No Base Traffic</option>
                        <?php generateHtmlOptions(
                                array( 1, 2, 3, 5, 7, 10, 15, 20, 25, 30, 40, 50, 75, 100, 150, 200, 300, 400, 500),
                                $nTraffic,
                                '%s Paralell');?>
                    </select>
                </td>
                <td>
                    <select onchange="switchBaseComplexity()" id="simulate" name="simulate">
                        <?php generateHtmlOptions(
                            array( 0, 0.5, 0.9, 1, 1.2, 1.6, 1.5, 1.7, 1.9, 2, 2.5, 3, 5, 10),
                            $nBoomSimulate,
                            'Additonal Runtime: %s');?>
                    </select>
                </td>
                <td>
                    <select onchange="switchBaseComplexity()" id="complexity" name="complexity">
                        <?php generateHtmlOptions(
                                array( 2, 10, 20, 50, 100, 150, 200, 500, 1000, 5000, 10000, 50000, 1000000),
                                $nComplexity,
                                'Complexity(%s)');?>
                    </select>
                </td>
                <td>
                    <select onchange="switchBaseSize()" id="size" name="size">
                        <option value="7" >Small Response</option> 
                        <option value="16" selected>Medium Response</option>
                        <option value="18" >Large Response</option>
                        <option value="19" >Big Response</option>
                    </select>
                </td>
            </tr>
            </table>
            <hr />
            <!-- Disabled for some issues on PaaS
            <table border="0">
            <tr>
                <td><b>Boom Simulation</b></td>
                <td>Concurrency:</td>
                <td>
                    <select name="concurrency">
                        <?php generateHtmlOptions(
                            array( 1, 2, 5, 10, 25, 50, 100, 150, 250),
                            $nBoomConcurrency);?>
                    </select>
                </td>
                <td align="right"><input type="submit" value="BOOM" /></td>
            </tr>
            </table>
            -->
        </form>
        <iframe id='chart' src="<?php print $sUrl;?>/chart.php" width="100%" height="450"></iframe>
        <hr />
        <pre><?php
            if( $sUrl && $nBoomRequests && $nBoomConcurrency)
            {
                file_get_contents ( $sEffectorUrl . '?text=SimulatorStart');

                if( $sUser)
                {
                    $sLogin = " -A '$sUser:$sPassword' ";
                }

                if( $sProxy)
                {
                    $sProxyParameter = " -X '$sProxy' ";
                }

                ?><h3>Simulation Result:</h3><h4>Command:</h4><?php

                $aPath = array(
                    '/usr/local/sbin',
                    '/usr/local/bin',
                    '/usr/sbin',
                    '/usr/bin',
                    '/sbin',
                    '/bin',
                    '/app/httpd/bin',
                    '~/app/httpd/bin'
                    );

                $sAbLib = '';

                foreach ( $aPath as $sDir)
                {
                    $sAbCommand = "$sDir/ab";

                    if( file_exists( $sAbCommand))
                    {
                        if ( $sDir == '/app/httpd/bin')
                        {
                            $sAbLib = "LD_LIBRARY_PATH=/app/httpd/lib ";
                        }
                        elseif ( $sDir == '~/app/httpd/bin')
                        {
                            $sAbLib = "LD_LIBRARY_PATH=~/app/httpd/lib ";
                        }
                        break;
                    }
                }

                if( !file_exists( $sAbCommand))
                {
            ?></pre>
                <hr />
                <h3 style="color:red;">ApacheBench: ab not found!</h3>
                <p style="color:red;">Install apache2-utils.</p>
        <pre><?php

                die;
            }

            //ini_set(  'max_execution_time', 120);

            $nMaxTime = ini_get ( 'max_execution_time') - 5;

            $sCommand = "$sAbLib $sAbCommand"
                    . " -t $nMaxTime "
                    . " -n $nBoomRequests "
                    . " -c $nBoomConcurrency $sLogin $sProxyParameter "
                    . " '$sSensorUrl'";

            print "</pre><div class=\"Command\">$sCommand</div><pre>\n\n";

            $sResult = `$sCommand 2>&1 | grep -v Completed`;
            
            if( stristr( $sResult, 'Non-2xx responses'))
            {
            ?></pre>
                    <hr />
                    <h3 style="color:red;">Non-2xx responses:</h3>
                    <ul style="color:red;">
                        <li>Wrong URL?</li>
                        <li>Wrong User/Passwd?</li>
                        <li>Server error?</li>
                    </ul>
        <pre>
            <?php
                }

                preg_match( '/Complete requests: *(\d*)/', $sResult, $aCompleteRequests);

                if( $aCompleteRequests[ 1] != $nBoomRequests)
                {
            ?>
        </pre>
                <hr />
                <h3 style="color:red;"><?php print $aCompleteRequests[ 0] . ' of ' . $nBoomRequests; ?></h3>
                <p style="color:red;">Execution is limited to <?php print $nMaxTime; ?> seconds.</p>
        <pre>
            <?php
                }

                ?><hr /><h4>Result:</h4><?php


                print $sResult;

                file_get_contents ( $sEffectorUrl . '?text=SimulatorEnd');

            ?>
            <hr />
        <?php
            }
        ?>
        <h3>Base Traffic Analytics:</h3>
        </pre>
                <div id="TestDiv"></div>
                <div id="BaseDiv"></div>

        <?php include( "footer.html");?>
    </body>
    <script type="text/javascript">
  
        var sUrl;
  
        var sDeploymentUrl = "<?php print $sUrl;?>";

        var nCustomConcurreny;
        
        setInterval(createTraffic, 10000);

        function switchBaseTraffic()
        {
            //Send Effector
            
            createTraffic();
        }

        function changeDeploymentUrl()
        {
            var kDeploymentUrl = document.getElementById("deploymentUrl");
            
            sDeploymentUrl = kDeploymentUrl.value;
            
            var kChartIframe = document.getElementById("chart");
            
            kChartIframe.src = sDeploymentUrl + '/chart.php'
            
            createTraffic();
        }

        function switchBaseComplexity()
        {
            //Send Effector
            
            createTraffic();
        }

        function switchBaseSize()
        {
            //Send Effector
            
            createTraffic();
        }

        function createTraffic()
        {
            var kTraffic = document.getElementById("traffic");

            nBaseConcurreny = kTraffic.options[ kTraffic.selectedIndex].value;


            var kSimulate = document.getElementById("simulate");

            nSimulateBoom = kSimulate.options[ kSimulate.selectedIndex].value;
            

            var kKomplexity = document.getElementById("complexity");

            nAnalyticsInterval = kKomplexity.options[ kKomplexity.selectedIndex].value;
            
            
            var kSize = document.getElementById("size");

            nAnalyticsPrecision = kSize.options[ kSize.selectedIndex].value;
            
            
            sAnalyticsUrl = encodeURIComponent(
                //"<?php print $sAnalyticsUrl;?>" +
                sDeploymentUrl +
                "/analytics.php?interval=" + nAnalyticsInterval +
                "&precision=" + nAnalyticsPrecision +
                "&boom=" + nSimulateBoom
            );
            
            sUrl =  "benchmark.php" + 
                    "?noheader=1" +
                    "&requests=100000" + 
                    "&runtime=10" + 
                    "&concurrency=" + nBaseConcurreny + 
                    "&user=" <?php $sUser ?> + 
                    "&password=" <?php $sPassword ?> + 
                    "&proxy=" <?php $sProxy ?> +
                    "&url=" + sAnalyticsUrl;
                    
            //console.log( sUrl);
            
            $( '#BaseDiv').load(
                    sUrl
            );
           
            //console.log("TRAFFIC");
            //console.log("nCustomConcurreny:" + nCustomConcurreny);
         } 
    </script>
</html>