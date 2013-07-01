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
include Translate('qnm_adm.php');
if ( $oVIP->user->role!='A' ) die($L['E_admin']);

// INITIALISE

$strVersion='v1.0';
$oVIP->selfurl = 'qnmm_import_install.php';
$oVIP->selfname = 'Installation module IMPORT '.$strVersion;

$bStep1 = true;
$bStep2 = true;

// STEP 1

if ( empty($error) )
{
  $strFile = 'qnmm_import_adm.php';
  if ( !file_exists($strFile) ) $error="Missing file: $strFile. Check installation instructions.<br/>This module cannot be used.";
  if ( !empty($error) ) $bStep1 = false;
}

// STEP 2

if ( empty($error) )
{
  $oDB->Query('DELETE FROM '.TABSETTING.' WHERE param="module_import" OR param="module_import_qnm"');
  $oDB->Query('INSERT INTO '.TABSETTING.' (param,setting) VALUES ("module_import","Import")');
}

// --------
// Html start
// --------
include 'qnm_adm_inc_hd.php';

echo '
<h1>',$oVIP->selfname,'</h1>
';

echo '<h2>Checking components</h2>';
if ( !$bStep1 )
{
  echo '<p class="error">',$error,'</p>';
  include 'qnm_adm_inc_ft.php';
  exit;
}
echo '
<p>Ok</p>
<h2>Database settings</h2>
<p>Ok</p>
<h2>Installation completed</h2>
';

include 'qnm_adm_inc_ft.php';