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
* About text coding in the database
* This script will convert the text before inserting into the dabase as follow:
*
* 1) stripslashes
* 2) htmlspecialchar($text,ENT_QUOTES) <>&"' are converted to html
* 3) bbcodes remain UNCHANGED (they are converted while displayed)
*/

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role=='V' ) HtmlPage(11);
if ( !$oVIP->user->CanView('V6') ) HtmlPage(11);

include 'bin/qnm_fn_sql.php';
$arrClasses=explode(',',QNMCLASSES);

// --------
// INITIALISE
// --------

$s = -1;
$nid = ''; // if uid>0 create inside the NE uid
$a = '';   // class e=element, c=connector, l=line
QThttpvar('s nid a','int str str');

// check arguments
if ( $s<0 ) die('Missing parameters: section');
if ( empty($a) ) $oHtml->Redirect("qnm_form_newclass.php?s=$s&amp;nid=$nid");
if ( !cNE::IsClass($a) ) die('Missing parameters: class');
if ( $nid!=='' && GetUid($nid)==0 ) HtmlPage(20);

// create inside NE Parent, otherwise $oPARENT is an empty NE
$oPARENT = new cNE((empty($nid) ? NULL : $nid));

$oVIP->selfurl = 'qnm_form_new.php';
$oVIP->selfname = (empty($nid) ? L('Create_items') : L('Create_items_in').' '.$oPARENT->id);
$oVIP->exiturl = "qnm_item.php?nid=$nid"; if ( empty($nid) ) $oVIP->exiturl = "qnm_items.php?s=$s";

// default values
$strDfltId = '';
$strDfltType = '';
$strDfltFa = '';
if ( $a=='c' )
{
  $strDfltId = '%s';
  $strDfltType = 'Connector';
  $strDfltFa = 'ii';
}

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $class='e';
  $status=0;
  if ( isset($_POST['class']) ) $class = trim($_POST['class']);
  if ( isset($_POST['status']) ) $status = trim($_POST['status']);
  $na=1;
  $fa='i';
  $sa=1;
  $bMirror=isset($_POST['mirror']);
  if ( isset($_POST['na']) ) $na = intval(trim($_POST['na']));
  if ( isset($_POST['fa']) ) $fa = trim($_POST['fa']);
  if ( isset($_POST['sa']) ) $sa = strtoupper(trim($_POST['sa']));
  if ( !in_array($class,$arrClasses) ) $error = L('Invalid').' '.L('class');
  if ( empty($na) ) $error = L('E_missing_items');
  if ( !is_numeric($na) ) $error = L('E_missing_items');
  if ( $na<1 ) $error = L('E_missing_items');

  // check mandatory values to create several items

  if ( $na>1 && strpos($_POST['id'],'%s')===false ) return '%s is required when creating several items';

  // New element [$o] can be a NetworkElement or a NetworkConnector)

  $o = new cNE($class.'.++');
  $error = $o->SetFromPost();

  // Create the items

  $strReport = '';
  if ( empty($error) )
  {
    $strFid=$o->id;
    $strFdesc=$o->descr;
    $arr=array(0);
    if ( $na>1 ) $arr=AsSequence($na,$fa,$sa);
    if ( empty($arr) )
    {
      $error = 'Id number problem. Possible issue: %s exceeds 99.';
    }
    else
    {
      $arrNids = array(); // created objects ids [class][uid]
      $o->uid--;         // reset new object to the last uid
      foreach($arr as $strNum)
      {
        $o->uid++; // last uid+1
        $o->id = sprintf($strFid,$strNum);
        $o->section = $s;
        $o->class = $class;
        $o->status = $status;
        $o->pid = $oPARENT->uid;
        $o->descr = sprintf($strFdesc,$strNum);
        $o->insertdate = date('Ymd');
        // update section & sys stats after
        if ( $o->Insert() )
        {
          $arrNids[] = GetNid($o);
          if ( $bMirror )
          {
            $o2 = new cNE();
            $o->uid++; // last uid+1
            $o2->uid = $o->uid;
            $o2->id = '~'.$o->id;
            $o2->type = $o->type;
            $o2->section = $s;
            $o2->class = $class;
            $o2->status = $status;
            $o2->pid = $oPARENT->uid;
            $o2->descr = sprintf($strFdesc,$strNum);
            $o2->insertdate = date('Ymd');
            // update section & sys stats after
            if ( $o2->Insert() )
            {
              $arrNids[] = GetNid($o2);
            }
          }
        }
      }

      // Update section stats and updates links if created as sub-items ($uid not empty)
      if ( count($arrNids)>0 )
      {
      if ( !empty($nid) ) $oPARENT->AddRelations($arrNids,'e',0,0,false); // $arrNEs are linked as [e]mbeded items (no update of puid)
      $voidSEC = new cSection(); $voidSEC->uid=$s; $voidSEC->UpdateStats(array()); // updates section
      // update system stats
      unset($_SESSION[QT]['sys_stat_items'],$_SESSION[QT]['sys_stat_itemsZ']);
      }
      if ( count($arrNids)>1 ) $strReport = '<p>'.count($arrNids).' '.L('items').'</p>';
    }
  }

  // exit
  if ( empty($error) )
  {
    $_SESSION['pagedialog']='O|'.$strReport.' '.L('S_insert');
    $oHtml->Redirect($oVIP->exiturl);
  }
}

// --------
// HTML START
// --------

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.JQUERYUI_CSS_CDN.'" />';
$oHtml->scripts[] = '<script type="text/javascript" src="'.JQUERYUI_CDN.'"></script>
<script type="text/javascript">
<!--
var e0 = "'.L('No_result').'";
var e1 = "...";
var e2 = "...";
var e3 = "...";
$(function() {
  $( "#type" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_type.php",
        dataType: "json",
        data: { term: request.term, s:'.$s.', e0:e0, e1:e1, e2:e2, e3:e3 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#type" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#type" ).val( ui.item.rItem );
      return false;
    }
  })
  .data( "autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
      .appendTo( ul );
  };
});

function MultiParam(str)
{
  if ( str.indexOf("%s")>=0 )
  {
  document.getElementById("multiparam").innerHTML="'.$L['Create'].'&nbsp;<input class=\"small\" type=\"text\" id=\"na\" name=\"na\" size=\"2\" maxlength=\"2\" value=\"2\"/> '.$L['f_add_ne_using'].'&nbsp;<select class=\"small\" id=\"fa\" name=\"fa\"><option value=\"i\"'.($strDfltFa=='i' ? ' selected=\"selected\"' : '').'>1, 2, 3...<\/option><option value=\"ii\"'.($strDfltFa=='ii' ? ' selected=\"selected\"' : '').'>01, 02, 03...<\/option><option value=\"A\">A, B, C...<\/option><option value=\"AA\">AA, AB, AC...<\/option><option value=\"a\">a, b, c...<\/option><option value=\"aa\">aa, ab, ac...<\/option><\/select> '.$L['f_add_ne_starting'].'&nbsp;<input class=\"small\" type=\"text\" id=\"sa\" name=\"sa\" size=\"2\" maxlength=\"2\" value=\"1\"\/>";
  }
  else
  {
  document.getElementById("multiparam").innerHTML="<input type=\"hidden\" id=\"na\" name=\"na\" value=\"1\"\/><input type=\"hidden\" id=\"fa\" name=\"fa\" value=\"1\"\/><input type=\"hidden\" id=\"sa\" name=\"sa\" value=\"1\"\/>";
  }
}

function ValidateForm(theButton)
{
  if ( document.getElementById("id").value.length==0 ) { alert(qtHtmldecode("'.$L['Missing'].': ID")); return false; }
  if ( document.getElementById("na").value.length==0 ) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['f_add_ne_number'].'")); return false; }
  if ( !isFinite(document.getElementById("na").value) ) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['f_add_ne_number'].'")); return false; }
  if ( document.getElementById("na").value==0 ) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['f_add_ne_number'].'")); return false; }
  if ( document.getElementById("na").value>1 )
  {
    if ( document.getElementById("fa").value.toUpperCase()=="A" ) {
    if ( !document.getElementById("sa").value.match(/^[a-z]*$/i) ) {
      alert(qtHtmldecode("'.$L['f_add_ne_starting'].': '.$L['f_add_ne_az'].'")); return false;
    }}
    if ( document.getElementById("fa").value.substr(0,1)=="i" ) {
    if ( !document.getElementById("sa").value.match(/^[0-9]*$/) ) {
      alert(qtHtmldecode("'.$L['f_add_ne_starting'].': '.$L['f_add_ne_int'].'")); return false;
    }}
  }
  return true;
}
//-->
</script>
';

include 'qnm_inc_hd.php';

// FORM START

echo '<h2>',$oVIP->selfname,'</h2>',PHP_EOL;
if ( !empty($error) ) echo '<p><span class="error">',$error,'</span></p>';

// parent NE

if ( !empty($nid) ) echo '<p>',$oPARENT->Dump(),'<br/>',$oPARENT->DumpContent(false,'',20),'</p>';

// option (class,status)

echo '
<form id="add_ne" method="post" action="',Href(),'">
<div class="options">
<p>',$L['Class'],'&nbsp;<select class="small" name="class" size="1">
';
foreach($arrClasses as $strClass) echo '<option value="'.$strClass.'"'.($a==$strClass ? QSEL : '').($strClass=='c' && empty($nid) ? QDIS : '').'>'.cNE::Classname($strClass).'</option>';
echo '
</select>
&nbsp;',$L['Status'],'&nbsp;<select class="small" name="status" size="1">
';
if ( empty($nid) ) { echo QTasTag($oVIP->GetStatuses()); } else { echo QTasTag($oVIP->GetStatuses(),$oPARENT->status,array('current'=>$oPARENT->status,'classC'=>'bold')); }
echo '</select></p>
</div>
';

// fields

echo '<table class="data_o">
<tr>
<td class="headfirst"><label for="id">ID</label></td>
<td><input onkeyup="MultiParam(this.value);" onchange="MultiParam(this.value);" type="text" id="id" name="id" size="15" maxlength="24" value="',$strDfltId,'"/> <span class="small">',$L['f_add_ne_id'],'</span></td>
</tr>
<tr>
<td class="headfirst"><label for="type">type</label></td>
<td><input class="nefields" type="text" id="type" name="type" maxlength="64" value="',$strDfltType,'"/></td>
</tr>
';
foreach(array('descr'=>L('Description'),'address'=>L('Address'),'tags'=>L('Tags')) as $strField=>$strLabel)
{
  if ( $strField=='descr' )
  {
  $str='<textarea class="nefields" id="'.$strField.'" name="'.$strField.'"></textarea>';
  }
  else
  {
  $str = '<input class="nefields" type="text" id="'.$strField.'" name="'.$strField.'" maxlength="64" value=""/>';
  }
  echo '<tr>';
  echo '<td class="headfirst"><label for="',$strField,'">',$strLabel,'</label></td>';
  echo '<td>',$str,'</td>';
  echo '</tr>',PHP_EOL;
}
echo '<tr>
<td class="headfirst" colspan="2">
<span id="multiparam"><input type="hidden" id="na" name="na" value="1"/><input type="hidden" id="fa" name="fa" value="1"/><input type="hidden" id="sa" name="sa" value="1"/></span>&nbsp;';
if ( $a=='c' && $oPARENT->class=='l' ) echo '<br/><input type="checkbox" id="mirror" name="mirror" checked="checked">&nbsp;<label for="mirror">',$L['f_add_ne_mirror'],'</label>&nbsp;<br/>';
echo '&nbsp;<input type="submit" id="ok" name="ok" value="',$L['Add'],'" tabindex="98" onclick="return ValidateForm(this);"/>
<input type="hidden" name="s" value="',$s,'"/>
<input type="hidden" name="nid" value="',$nid,'"/>
<input type="hidden" name="a" value="',$a,'"/>&nbsp;</td>
</tr>
</table>
</form>
';

if ( !empty($strDfltFa) ) echo '<script type="text/javascript">MultiParam("%s");</script>';

include 'qnm_inc_ft.php';