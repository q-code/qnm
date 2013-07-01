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
*
*/

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role=='V' ) HtmlPage(11);
include 'bin/qnm_fn_sql.php';

// --------
// INITIALISE
// --------

$s = -1;
$nid = ''; // if uid>0 create inside the NE uid
$a = 'e';
QThttpvar('s nid a','int str str');

// check arguments
if ( $s<0 ) die('Missing parameters: section');
if ( !cNE::IsClass($a) ) die('Missing parameters: class');
if ( $a=='c' && empty($nid) ) $a='e';
if ( $nid!=='' && GetUid($nid)==0 ) HtmlPage(20);

$oVIP->selfurl = 'qnm_form_newclass.php';
$oVIP->selfname = $L['Create_items'];
$oVIP->exiturl = "qnm_form_new.php?s=$s&amp;nid=$nid&amp;a=$a";
$oVIP->exitname = $L['Items'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

// FORM START

echo '<h2>',$oVIP->selfname,'</h2>',PHP_EOL;
if ( !empty($error) ) echo '<p><span class="error">',$error,'</span></p>';

echo '
<br/>
<form id="form_classes" method="post" action="',Href(),'">
<table class="hidden">
';
foreach(explode(',',QNMCLASSES) as $type)
{
echo '<tr class="hidden">
<td class="hidden"><input type="radio" name="a" value="',$type,'" id="class_',$type,'"',($a==$type ? QCHE : ''),($type=='c' && empty($nid) ? ' disabled="disabled"' : ''),'/></td>
<td class="hidden"><label for="class_',$type,'">',cNE::GetIcon($type),'</label></td>
<td class="hidden" style="padding-bottom:10px"><label for="class_',$type,'"><span class="bold">'.cNE::Classname($type).'</span><br/>'.$L['H_Item_'.$type].'</label></td>
</tr>
';
}
echo '<tr class="hidden">
<td class="hidden">&nbsp;</td>
<td class="hidden">&nbsp;</td>
<td class="hidden"><input type="submit" name="ok" value="'.L('Continue').'"/><input type="hidden" name="s" value="',$s,'"/><input type="hidden" name="nid" value="',$nid,'"/></td>
</tr>
</table>
</form>
';

// --------
// HTML END
// --------

include 'qnm_inc_ft.php';