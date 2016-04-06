<?php
// ****************************************************************************
/**
 * SeaClouds Project: http://www.seaclouds-project.eu/
 *
 * benchmark.php
 * 
 * Frontend to Apache HTTP server benchmarking tool
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

$nRequests = $_REQUEST['requests'] + 0;
$nConcurrency = $_REQUEST['concurrency'] + 0;

$nNoHeader = $_REQUEST['noheader'] + 0;

if( $_REQUEST['runtime'] + 0)
{
    $nMaxTime = $_REQUEST['runtime'] + 0;
}
else
{
    $nMaxTime = ini_get ( 'max_execution_time') - 1;
}


$sUser = str_replace("'", "", $_REQUEST['user']);
$sPassword = str_replace("'", "", $_REQUEST['password']);
$sProxy = str_replace("'", "", $_REQUEST['proxy']);

$sDefaultUrl = 'http://' . $_SERVER[ 'SERVER_NAME'] . dirname( $_SERVER[ 'REQUEST_URI']) . '/sensor.php';

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

if( ! $nNoHeader)
{
?>

<html>
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="description" content="SeaSlouds Project - NURO Benchmark">
      <meta name="author" content="Christian Tismer, Nurogames GmbH">
      <link rel="shortcut icon" href="http://www.seaclouds-project.eu/assets/ico/favicon.png">

      <title>SeaClouds | NURO Benchmark</title>

      <?php include( "header.html");?>
   </head>
   <body>
      <p style="color:#FFF; font-size:30px;">NURO GUI: Benchmark</p>
      <p style="color:#FFF; font-size:12px;"> For details see D6.1, D6.2, D6.3.1, D6.3.2, D6.3.3</p>
      <p style="padding-top:9px;">
      <a href="http://www.nurogames.com/" target="_blank">
              <img alt="Nurogames" 
                       src="./img/LogoNurogames150x15.png" 
               width="150"
                       height="15"></a></p>
      </div>
      <p>You can use this benchmark tool to generate traffic to the deployed web application and check the performance.</p>
      <p>It is based on "ab - Apache HTTP server benchmarking tool".</p>
      <p><a href="http://httpd.apache.org/docs/2.2/programs/ab.html">Documentation of ApacheBench with options and output description can be found here</a>.</p>

      <hr />
      <h2>Parameters:</h2>
      <p>To simulate boom scenarios, use requests >= 100 and concurrency >= 10.</p>
      <table border="0">
          <form method="get">
              <tr>
                  <td>URL:</td>
                  <td colspan="5"><input name="url" type="text" size="100" value="<?php print $sUrl;?>" /></td>
              </tr>
              <tr>
                  <td>Request:</td>
                  <td><select name="requests">
                      <?php foreach ( array( 1, 10, 100, 500, 1000, 2000) as $n)
                      {
                          ?><option<?php print $n == $nRequests? ' selected':'';?>><?php print $n;?></option><?php 
                      }?>
                      </select>
                  </td>
              </tr>
              <tr>
                  <td>Concurrency:</td>
                  <!--td><input name="concurrency" type="text" size="10" value="<?php print $nConcurrency;?>" /></td-->
                  <td><select name="concurrency">
                      <?php foreach ( array( 1, 10, 25, 50) as $n)
                      {
                          ?><option<?php print $n == $nConcurrency? ' selected':'';?>><?php print $n;?></option><?php 
                      }?>
                      </select>
                  </td>
              </tr>
              <tr>
                  <td colspan=1>optional:</td>
                  <td>User:&nbsp;<input name="user" type="text" size="10" value="<?php print $sUser ? $sUser : 'seaclouds';?>" />&nbsp;&nbsp;
                      Password&nbsp;<input name="password" type="text" size="10" value="<?php print $sPassword ? $sPassword : 'preview';?>" />&nbsp;&nbsp;
                      Proxy:Port&nbsp;<input name="proxy" type="text" size="30" value="<?php print $sProxy;?>" /></td>
                  <td align="right"><input type="submit" value="ok" /></td>
              </tr>
          </form>
      </table>
      <hr />
      <?php
      }
      ?>
        <pre>
                    <?php
                    if( $sUrl && $nRequests && $nConcurrency)
                    {
                        if( $sUser)
                        {
                            $sLogin = " -A '$sUser:$sPassword' ";
                        }

                        if( $sProxy)
                        {
                            $sProxyParameter = " -X '$sProxy' ";
                        }

                        ?><h4>Command:</h4><?php

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
                  ?>
        </pre>
            <hr />
            <h3 style="color:red;">ApacheBench: ab not found!</h3>
            <p style="color:red;">Install apache2-utils.</p>
        <pre>
            <?php

                            die;
                        }

                        $sCommand = "$sAbLib $sAbCommand"
                                . " -t $nMaxTime "
                                . " -n $nRequests "
                                . " -c $nConcurrency $sLogin $sProxyParameter "
                                . " '$sUrl'";

                        print "</pre><div class=\"Command\">$sCommand</div><pre>\n\n";

                        $sResult = `$sCommand 2>&1 | grep -v Completed`;

                        preg_match( '/Complete requests: *(\d*)/', $sResult, $aCompleteRequests);

                        if( stristr( $sResult, 'Non-2xx responses'))
                        {
            ?>
        </pre>
            <hr />
            <h3 style="color:red;">Non-2xx responses:</h3>
            <ul style="color:red;">
            <li>Wrong URL?</li>
            <li>Wrong User/Passwd?</li>
            <li>Server error?</li>
            </ul>
                <div id="Result">
                  <pre>
                    <?php
                        }
                        if( $aCompleteRequests[ 1] != $nRequests)
                        {
                    ?>
                  </pre>
                      <hr />
                      <h3 style="color:red;"><?php print $aCompleteRequests[ 0] . ' of ' . $nRequests; ?></h3>
                      <p style="color:red;">Execution is limited to <?php print $nMaxTime; ?> seconds.</p>
                   <pre>
                    <?php
                        }
                    ?>
                    <hr /><h4>Result:</h4>
                    <?php
                        print $sResult;
                    ?>
                    <hr />
                    <?php
                    }
                    ?>
                   </pre>  
                </div>
        <?php
        if( ! $nNoHeader)
        {
            include( "footer.html");
        }
        ?>
    </body>
</html>