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
include Translate('qnmm_import.php');
if ( $oVIP->user->role!='A' ) die(Error(13));

// FUNCTIONS

function startElement($parser, $strTag, $arrTagAttr)
{
  $strTag = strtolower($strTag);
  global $arrElement,$arrPosts;
  global $t,$p,$L;

  switch($strTag)
  {
  case 'element':
    $arrElement = array();
    $arrPosts = array();
    if ( isset($arrTagAttr['ID']) ) { $t=intval($arrTagAttr['ID']); } else { $t=0; }
    $arrElement['id'] = $t;
    $arrElement['type'] = (isset($arrTagAttr['TYPE']) ? $arrTagAttr['TYPE'] : 'T');
    break;
  case 'post':
    if ( isset($arrTagAttr['ID']) ) { $p=intval($arrTagAttr['ID']); } else { $p=0; }
    $arrPosts[$p] = array();
    $arrPosts[$p]['id'] = $p;
    $arrPosts[$p]['type'] = (isset($arrTagAttr['TYPE']) ? $arrTagAttr['TYPE'] : 'P');
    break;
  }
}
function characterData($parser, $data)
{
  global $strValue;
  $strValue = trim($data);
}
function endElement($parser, $strTag)
{
  $strTag = strtolower($strTag);

  global $arrElement,$arrPosts;
  global $t,$p,$intElementInsertId,$intPostInsertId;
  global $strValue;
  global $oDB, $arrCounts;

  switch($strTag)
  {
  case 'x':         $arrElement['x']=$strValue; break;
  case 'y':         $arrElement['y']=$strValue; break;
  case 'z':         $arrElement['z']=$strValue; break;
  case 'tags':      if ( !$_SESSION['m_import_xml']['droptags'] ) { $arrElement['tags']=$strValue; } break;
  case 'wisheddate':$arrElement['wisheddate']=$strValue; break;
  case 'firstpostdate': if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrElement['firstpostdate']=date('Ymd His'); } else { $arrElement['firstpostdate']=$strValue; } break;
  case 'lastpostdate': if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrElement['lastpostdate']=date('Ymd His'); } else { $arrElement['lastpostdate']=$strValue; } break;
  case 'param':     $arrElement['param']=$strValue; break;

  case 'icon':     $arrPosts[$p]['icon']=$strValue; break;
  case 'title':    $arrPosts[$p]['title']=$strValue; break;
  case 'userid':   $arrPosts[$p]['userid']=0; break; //userid must be reset to 0
  case 'username': $arrPosts[$p]['username']=$strValue; break;
  case 'issuedate':if ( $_SESSION['m_import_xml']['dropdate'] ) { $arrPosts[$p]['issuedate']=date('Ymd His'); } else { $arrPosts[$p]['issuedate']=$strValue; } break;
  case 'modifdate':$arrPosts[$p]['modifdate']=$strValue; break;
  case 'modifuser':$arrPosts[$p]['modifuser']=0; break; //userid must be reset to 0
  case 'modifname':$arrPosts[$p]['modifname']=$strValue; break;
  case 'textmsg':  $arrPosts[$p]['textmsg']=$strValue; break;
  case 'posts':    $arrElement['posts']=$arrPosts; break;

  case 'element':

    // Process element

    $oNE = new cNE($arrElement);
    $oNE->section = $_SESSION['m_import_xml']['dest'];
    $oNE->id = $intElementInsertId; $intElementInsertId++;
    $oNE->status = $_SESSION['m_import_xml']['status'];

    $oNE->Insert();
    $arrCounts['element']++;

    // Process posts

    foreach($arrElement['posts'] as $arrPost)
    {
      $oPost = new cPost($arrPost); if ( $_SESSION['m_import_xml']['dropreply'] && $oPost->type!='P' ) break;
      $oPost->id = $intPostInsertId; $intPostInsertId++;
      $oPost->element = $oNE->id;
      $oPost->section = $_SESSION['m_import_xml']['dest'];
      if ( $_SESSION['m_import_xml']['dropbbc'] ) $oPost->text = QTbbc($oPost->text,'drop');

      $oPost->Insert(false,false);
      if ( $oPost->type!='P' ) $arrCounts['reply']++; // count only the replies
    }

    // Element stats

    $oNE->UpdateStats(); // updates section & system stats

    break;

  default:
    if ( trim($strValue)!='' ) $arrElement[$strTag]=$strValue;
    break;
  }
}

// INITIALISE

$intDest   = -1;
$strStatus = 'Z';
$bDropbbc  = false;
$bDropreply= false;
$bDroptags = false;
$bDropdate = false;
$arrCounts = array('element'=>0,'reply'=>0);

$oVIP->selfurl = 'qnmm_import_adm.php';
$oVIP->selfname = $L['import']['Admin'];
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;
$strPageversion = $L['import']['Version'].' 1.0';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check file

  if (!is_uploaded_file($_FILES['title']['tmp_name'])) $error = $L['import']['E_nofile'];

  // check form value

  if ( empty($error) )
  {
    if ( isset($_POST['dropbbc']) ) $bDropbbc=true;
    if ( isset($_POST['dropreply']) ) $bDropreply=true;
    if ( isset($_POST['droptags']) ) $bDroptags=true;
    if ( isset($_POST['dropdate']) ) $bDropdate=true;
    $intDest = intval($_POST['section']);
    $strStatus = $_POST['status'];
    $_SESSION['m_import_xml']=array('dest'=>$intDest,'status'=>$strStatus,'dropbbc'=>$bDropbbc,'dropreply'=>$bDropreply,'droptags'=>$bDroptags,'dropdate'=>$bDropdate);
  }

  // check format

  if ( empty($error) )
  {
    if ( $_FILES['title']['type']!='text/xml' )
    {
    $error = $L['import']['E_format'];
    unlink($_FILES['title']['tmp_name']);
    }
  }

  // import xml

  if ( empty($error) )
  {
    $arrElement = array();
    $arrPosts = array();
    $t = 0;
    $p = 0;
    $strValue = '';
    $intElementInsertId = $oDB->Nextid(TABNE);
    $intPostInsertId = $oDB->Nextid(TABPOST);

    $xml_parser = xml_parser_create();
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
    xml_set_item_handler($xml_parser, 'startElement', 'endElement');
    xml_set_character_data_handler($xml_parser, 'characterData');
    if ( !($fp = fopen($_FILES['title']['tmp_name'],'r')) ) die('could not open XML input');
    while ($data = fread($fp,4096))
    {
      if ( !xml_parse($xml_parser, $data, feof($fp)) ) die(sprintf('XML error: %s at line %d', xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
    }
    xml_parser_free($xml_parser);
  }

  if ( empty($error) )
  {
    // Clean file

    unlink($_FILES['title']['tmp_name']);

    // Updates section & system stats

    $voidSEC = new cSection(); $voidSEC->uid=$intDest; $voidSEC->UpdateStats();

    // End message (pause)

    $str  = L('Item',$arrCounts['element']).'<br/>'.L('Note',$arrCounts['reply']).'<br/>';
    $oHtml->PageBox(NULL,'<p class="small">'.$str.'</p><br/>'.$L['import']['S_import'],'admin',0);
  }
}

// --------
// HTML START
// --------

$oHtml->links[] = '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert("'.$L['Missing'].': File"); return false; }
  return null;
}
//-->
</script>
';
include 'qnm_adm_inc_hd.php';

if ( isset($_SESSION['m_import_xml']['dest']) )      $intDest   = $_SESSION['m_import_xml']['dest'];
if ( isset($_SESSION['m_import_xml']['status']) )    $strStatus = $_SESSION['m_import_xml']['status'];
if ( isset($_SESSION['m_import_xml']['dropbbc']) )   $bDropbbc  = $_SESSION['m_import_xml']['dropbbc'];
if ( isset($_SESSION['m_import_xml']['dropreply']) ) $bDropreply= $_SESSION['m_import_xml']['dropreply'];
if ( isset($_SESSION['m_import_xml']['droptags']) )  $bDroptags = $_SESSION['m_import_xml']['droptags'];
if ( isset($_SESSION['m_import_xml']['dropdate']) )  $bDropdate = $_SESSION['m_import_xml']['dropdate'];


echo '<form method="post" action="',$oVIP->selfurl,'" enctype="multipart/form-data" onsubmit="return ValidateForm(this);">
<input type="hidden" name="maxsize" value="5242880"/>
<table class="data_o">
';
echo '<tr class="data_o">
<td class="colgroup" colspan="2">',$L['import']['File'],'</td>
</tr>
';
echo '<tr class="data_o">
<td  style="width:200px"><label for="title">',$L['import']['File'],'</label></td>
<td><input type="file" id="title" name="title" size="32"/></td>
</tr>
';
echo '<tr class="data_o">
<td class="colgroup" colspan="2">',$L['import']['Content'],'</td>
</tr>
';
echo '<tr class="data_o">
<td >',$L['import']['Drop_tags'],'</td>
<td><input type="checkbox" id="droptags" name="droptags"',($bDroptags ? QCHE : ''),'/> <label for="droptags">',$L['import']['HDrop_tags'],'</label></td>
</tr>
';
echo '<tr class="data_o">
<td >',$L['import']['Drop_reply'],'</td>
<td><input type="checkbox" id="dropreply" name="dropreply"',($bDropreply ? QCHE : ''),'/> <label for="dropreply">',$L['import']['HDrop_reply'],'</label></td>
</tr>
';
echo '<tr class="data_o">
<td >',$L['import']['Drop_bbc'],'</td>
<td><input type="checkbox" id="dropbbc" name="dropbbc"',($bDropbbc ? QCHE : ''),'/> <label for="dropbbc">',$L['import']['HDrop_bbc'],'</label></td>
</tr>
';
echo '<tr class="data_o">
<td class="colgroup" colspan="2">',$L['Destination'],'</td>
</tr>
';
echo '<tr class="data_o">
<td  style="width:200px"><label for="section">',$L['import']['Destination'],'</label></td>
<td><select id="section" name="section">',QTasTag(QTarrget(GetSections('A'))),'</select> <a href="qnm_adm_sections.php">',$L['Section_add'],'</a></td>
</tr>
';
echo '<tr class="data_o">
<td ><label for="status">',$L['Status'],'</label></td>
<td><select id="status" name="status">
';
foreach($oVIP->statuses as $strKey=>$arrStatus)
{
echo '<option value="',$strKey,'"',($strStatus==$strKey ? QSEL : ''),'>',$strKey.' '.$arrStatus['name'].'</option>',PHP_EOL;
}
echo '</select></td>
</tr>
';
echo '<tr class="data_o">
<td >',$L['import']['Drodate'],'</td>
<td><input type="checkbox" id="dropdate" name="dropdate"',($bDropdate ? QCHE : ''),'/> <label for="dropdate">',$L['import']['HDrodate'],'</label></td>
</tr>
';
echo '<tr class="data_o">
<td class="colgroup" colspan="2" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Ok'],'"/></td>
</tr>
</table>
</form>
';

// HTML END

include 'qnm_adm_inc_ft.php';
