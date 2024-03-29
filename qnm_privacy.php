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

// INITIALISE

$oVIP->selfurl = 'qnm_privacy.php';
$oVIP->selfname = $L['Legal'];

// --------
// HTML START
// --------

$oHtml->scripts = array();
include 'qnm_inc_hd.php';

$oHtml->Msgbox($oVIP->selfname,'msgbox about');

include Translate('sys_rules.txt');

$oHtml->Msgbox(END);

$oHtml->Msgbox('About Q-Net Management','msgbox about');

$strFile = Translate('sys_about.php');
if ( file_exists($strFile) ) { include $strFile; } else { echo 'Missing file:<br />'.$strFile; }

echo '<p><a href="http://validator.w3.org/check?uri=referer">HTML5 + ARIA + SVG 1.1</a></p>
<p>
<a href="http://jigsaw.w3.org/css-validator/"><img src="admin/vcss.png" alt="Valid CSS" height="31" width="88" /></a>&nbsp;
<a href="http://www.w3.org/WAI/WCAG1AAA-Conformance" title="Explanation of Level Triple-A Conformance"><img height="31" width="88" src="admin/wcag1aaa.png" alt="Level Triple-A conformance icon, W3C-WAI Web Content Accessibility Guidelines 1.0" /></a>
';

// ----------
// module rss
if ( UseModule('rss') )
{
echo '<img height="31" width="88" src="admin/valid-rss-rogers.png" alt="[Valid RSS]" title="Valid RSS feed" />
<img height="31" width="88" src="admin/valid-atom.png" alt="[Valid RSS]" title="Valid RSS feed" />
';
}
// ----------

echo '</p>
';

$oHtml->Msgbox(END);

// HTML END

include 'qnm_inc_ft.php';