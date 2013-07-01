<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QNM
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2013 The PHP Group
* @version    1.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('V0') ) HtmlPage(11);

// INITIALISE

include GetLang().'qnm_reg.php';

$oVIP->selfurl = 'qnm_user_new.php';
$oVIP->selfname = $L['Register'];

// --------
// EXECUTE FORM
// --------

if ( isset($_POST['ok']) )
{
  if ( !isset($_POST['agreed']) )
  {
    include 'qnm_inc_hd.php';
    $oHtml->Msgbox($oVIP->selfname,'msgbox login');
    $strFile=GetLang().'sys_not_agree.txt';
    if ( file_exists($strFile) ) { include $strFile; } else { echo 'Rules not agreed...'; }
    echo '<p><a href="',Href(),'">',$L['Register'],'</a></p>';
    $oHtml->Msgbox(END);
    include 'qnm_inc_ft.php';
    Exit;
  }
  $oHtml->Redirect('qnm_form_reg.php',$L['Register']);
}

// --------
// HTML START
// --------

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_main2.css" title="cssmain" />';
$oHtml->scripts = array();

include 'qnm_inc_hd.php';

echo '
<div class="scrollmessage">
';

$strFile = GetLang().'sys_rules.txt';
if ( file_exists($strFile) ) { include $strFile; } else { echo "Missing file:<br/>$strFile"; }

echo '
</div>
';

echo '
<form method="post" action="',Href(),'"><p><input type="checkbox" id="agreed" name="agreed"/> <label for="agreed"><b>&nbsp;',$L['Agree'],'</b></label></p>
';
$oHtml->Msgbox($oVIP->selfname,'msgbox login');
echo '<p>';
echo $L['Proceed'];
echo '</p><p><input type="submit" name="ok" value="',$L['Ok'],'"/></p>';
$oHtml->Msgbox(END);
echo'
</form>
';

// HTML END

include 'qnm_inc_ft.php';