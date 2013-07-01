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
if ( !$oVIP->user->CanView('V4') ) HtmlPage(11);
include Translate('qnm_stat.php');

include 'bin/qnm_fn_sql.php';
include 'bin/qnm_fn_stat.php';

// --------
// INITIALISE
// --------

$oVIP->selfurl = 'qnm_stats.php';
$oVIP->selfname = $L['Statistics'];

$s = '*'; // section filter. $s can be '*' or a INT
$y = (int)date('Y'); if ( (int)date('n')<2 ) $y--; // year filter
$class = '*'; // class filter
$type = '*'; // type filter
$tag = '*'; // tags filter
$tt = 'g'; // tab: g=global, gt=globaltrend, d=detail, dt=detailtrend
$ch = array('time'=>'m','type'=>'b','value'=>'a','trend'=>'a'); // chart parameters
// blocktime: m=month, q=quarter, d=10days
// graph type: b=bar, l=line, B=bar+variation, L=line+variation
// graphics reals: a=actual, p=percent
// trends reals: a=actual, p=percent
$lang = GetIso();
$arrSeries = array();

// --------
// SUBMITTED
// --------

QThttpvar('y s class type tag tt','int str str str str str',true,true,false);

// check null values

if ( !cNE::IsClass($class) ) $class='*';
if ( $type==='' ) $type='*';
if ( $tag==='' ) $tag='*';
if ( $s!=='*' ) $s = (int)$s;

// build basic filter

$strQfilter = '';
if ( $s!=='*' ) $strQfilter .= ' AND section='.$s;
if ( $type!=='*' ) $strQfilter .= ' AND type="'.urldecode($type).'"';
if ( $tag!=='*' && strlen($tag)>0 )
{
  if ( substr($tag,-1,1)==QNM_QUERY_SEPARATOR ) $tag = substr($tag,0,-1);
  $arrTags = explode(';',$tag);
  $str = '';
  foreach($arrTags as $strTag)
  {
  if ( !empty($str) ) $str .= ' OR ';
  $str .= 'UPPER(tags) LIKE "%'.strtoupper($strTag).'%"';
  }
  if ( !empty($str) ) $strQfilter .= ' AND ('.$str.')';
}

// build graph options

if ( isset($_GET['ch']) )
{
  $str = strip_tags($_GET['ch']);
  if ( strlen($str)>0 ) $ch['time'] = substr($str,0,1); // blocktime
  if ( strlen($str)>1 ) $ch['type'] = substr($str,1,1); // graph type
  if ( strlen($str)>2 ) $ch['value'] = substr($str,2,1); // value type
  if ( strlen($str)>3 ) $ch['trend'] = substr($str,3,1); // trends value type
}
if ( $tt=='g' || $tt=='d' ) $ch['type'] = strtolower($ch['type']);

// --------
// INITIALISE RANGES
// --------

if ( $tt=='gt' || $tt=='dt' )
{
$arrYears = array($y-1,$y); // Normal is 1 year but for Trends analysis, 2 years
}
else
{
$arrYears = array($y);
}

$oDB->Query( 'SELECT min(insertdate) as startdate, max(insertdate) as lastdate FROM '.TABNE );
$row = $oDB->Getrow();
if ( empty($row['startdate']) ) $row['startdate']=strval($y-1).'0101';
if ( empty($row['lastdate']) ) $row['lastdate']=strval($y).'1231';
$strLastdaysago = substr($row['lastdate'],0,8);
$strTendaysago = DateAdd($strLastdaysago,-10,'day');
$intStartyear = intval(substr($row['startdate'],0,4));
$intStartmonth = intval(substr($row['startdate'],4,2));
$intEndyear = intval(date('Y'));
$intEndmonth = intval(date('n'));

// --------
// HTML START
// --------

$arrTags = cSection::GetTagsUsed(-1);
$str  = '';
foreach($arrTags as $strName=>$strDesc) { $str .= '{n:"'.$strName.'",d:"'.($strName==$strDesc ? ' ' : substr($strDesc,0,64)).'"},'; }
$str = substr($str,0,-1);

$oHtml->scripts[] = '<script type="text/javascript">
<!--
var e0 = "'.L('No_result').'";
var e1 = "'.L('try_other_lettres').'";
var e2 = "'.L('try_without_options').'";

function split( val ) { return val.split( "'.QNM_QUERY_SEPARATOR.'" ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }

$(function() {

  $( "#ft" ).autocomplete({
    minLength: 0,
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_type.php",
        dataType: "json",
        data: { term: request.term, fs:function() {return $("#fs").val();}, fc:function() {return $("#fc").val();}, fy:function() {return $("#fy").val();}, e0:e0, e1:e1, e2:e2 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#ft" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#ft" ).val( (ui.item.rInfo=="*" ? "Type:"+ui.item.rItem : ui.item.rItem) );
      return false;
    }
  })
  .data( "autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
      .appendTo( ul );
  };

  $( "#ftag" ).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_tag.php",
        dataType: "json",
        data: { term: request.term, fs:function() {return $("#fs").val();}, fc:function() {return $("#fc").val();}, fy:function() {return $("#fy").val();}, ft:function() {return $("#ft").val();}, e0:e0, e1:e1, e2:e2 },
        success: function(data) { response(data); }
      });
    },
    search: function() {
      // custom minLength
      var term = extractLast( this.value );
      if ( term.length < 1 ) { return false; }
  },
    focus: function( event, ui ) { return false; },
    select: function( event, ui ) {
      var terms = split( this.value );
      terms.pop(); // remove current input
      terms.push( ui.item.rItem ); // add the selected item
      terms.push( "" ); // add placeholder to get the comma-and-space at the end
      this.value = terms.join( "'.QNM_QUERY_SEPARATOR.'" );
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
//-->
</script>
';

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_stats.css"/>';

include 'qnm_inc_hd.php';

// TITLE and OPTIONS

echo '<table class="hidden">';
echo '<tr class="hidden">';
echo '<td class="hidden"><h1>',$L['Statistics'],'</h1></td>',PHP_EOL;
echo '<td class="hidden">&nbsp;</td>',PHP_EOL;
echo '<td class="hidden" style="width:450px">
';
if ( $oVIP->stats->items>2 )
{
echo '
<form method="get" action="',Href(),'">
<div class="legendbox optionstats">
<p class="legendtitle">',$L['Options'],'</p>
';

$arrY = array(); // all possible years
for ($i=$intStartyear;$i<=$intEndyear;$i++) $arrY[$i]=$i;

echo '<p class="small right" style="margin:0 5px 5px 5px">';
echo '<span class="bold">'.$L['Year'],'</span>&nbsp;<select name="y" id="fy">',QTasTag($arrY,$y),'</select> ';
if ( count($oVIP->sections)>0 ) echo $L['Section'],'&nbsp;<select class="small" name="s" id="fs"><option value="*">'.$L['All_sections'].'</option>'.Sectionlist($s).'</select>';
echo ' ',$L['Class'],'&nbsp;<select class="small" name="class" id="fc"><option value="*">&nbsp;</option>',QTasTag(cNE::Classnames('c'),$type),'</select></p>',PHP_EOL;
echo '<p class="small right" style="margin:0 5px 5px 5px">',$L['Type'],'&nbsp;<input class="small" type="text" id="ft" name="type" size="18" value="',($type==='*' ? '' : $type),'"/>';
if ( $_SESSION[QT]['tags']!='0' ) echo ' ',$L['Tag'],'&nbsp;<input class="small" type="text" id="ftag" name="tags" size="18" value="'.($tag==='*' ? '' : $tag).'"/>';
echo '<input type="hidden" name="tt" value="',$tt,'"/><input type="hidden" name="ch" value="',implode('',$ch),'"/> <input type="submit" name="ok" class="small" value="',$L['Ok'],'"/></p>',PHP_EOL;
echo '</div>
</form>
';
}
echo '</td>
</tr>
</table>
';

// STATISTIC TABS and GRAPHIC MENUS definition

$arrTabs = array();
$arrTabs['g'] = array('tabname'=>$L['Global'],'tabdesc'=>$L['H_Global']);
$arrTabs['gt'] = array('tabname'=>$L['Global_trends'],'tabdesc'=>$L['H_Global_trends']);
$arrTabs['d'] = array('tabname'=>$L['Details'],'tabdesc'=>$L['H_Details']);
$arrTabs['dt'] = array('tabname'=>$L['Details_trends'],'tabdesc'=>$L['H_Details_trends']);

$arrMenuTime = array(); // Block time: q=quarter, m=month, d=10days
if ( $ch['time']=='q' ) { $arrMenuTime[]=$L['Per_q']; } else { $arr=$ch; $arr['time']='q'; $arrMenuTime[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Per_q'].'</a>'; }
if ( $ch['time']=='m' ) { $arrMenuTime[]=$L['Per_m']; } else { $arr=$ch; $arr['time']='m'; $arrMenuTime[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Per_m'].'</a>'; }
if ( $ch['time']=='d' ) { $arrMenuTime[]=$L['Per_d']; } else { $arr=$ch; $arr['time']='d'; $arrMenuTime[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Per_d'].'</a>'; }

$arrMenuType = array(); // Chart tyle: b=bar, l=line, B=bar+variation labels, L=line+variation labels
if ( $ch['type']=='b' ) { $arrMenuType[]=$L['Chart_bar']; }  else { $arr=$ch; $arr['type']='b'; $arrMenuType[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Chart_bar'].'</a>'; }
if ( $ch['type']=='l' ) { $arrMenuType[]=$L['Chart_line']; } else { $arr=$ch; $arr['type']='l'; $arrMenuType[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Chart_line'].'</a>'; }
if ( $tt=='gt' || $tt=='dt' )
{
if ( $ch['type']=='B' ) { $arrMenuType[]=$L['Chart_bar_var']; } else { $arr=$ch; $arr['type']='B'; $arrMenuType[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Chart_bar_var'].'</a>'; }
if ( $ch['type']=='L' ) { $arrMenuType[]=$L['Chart_line_var']; } else { $arr=$ch; $arr['type']='L'; $arrMenuType[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Chart_line_var'].'</a>'; }
}

$arrMenuValue = array(); // Value type: a=actual, p=percent
if ( $ch['value']=='a' ) { $arrMenuValue[]=$L['Per_a']; } else { $arr=$ch; $arr['value']='a'; $arrMenuValue[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Per_a'].'</a>'; }
if ( $ch['value']=='p' ) { $arrMenuValue[]=$L['Per_p']; } else { $arr=$ch; $arr['value']='p'; $arrMenuValue[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Per_p'].'</a>'; }

$arrMenuTrend = array(); // Trend value type: a=actual, p=percent
if ( $ch['trend']=='a' ) { $arrMenuTrend[]=$L['Per_a']; } else { $arr=$ch; $arr['trend']='a'; $arrMenuTrend[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Per_a'].'</a>'; }
if ( $ch['trend']=='p' ) { $arrMenuTrend[]=$L['Per_p']; } else { $arr=$ch; $arr['trend']='p'; $arrMenuTrend[]='<a href="'.Href().'?'.GetURI('ch,ok').'&amp;ch='.implode('',$arr).'" class="small">'.$L['Per_p'].'</a>'; }

echo HtmlTabs($arrTabs, $oVIP->selfurl.'?'.GetURI('tt,ch,ok').'&amp;ch='.implode('',$arr), $tt);

echo '<div class="pan">
<div class="pan_top">',$arrTabs[$tt]['tabdesc'],'</div>
';

// Statistic computation

include 'qnm_stats_inc.php';

// Table header definition

$arrHeader = array();
switch($ch['time'])
{
case 'q': for ($i=1;$i<=$intMaxBt;$i++) $arrHeader[$i]='Q'.$i; break;
case 'm': for ($i=1;$i<=$intMaxBt;$i++) $arrHeader[$i]=$L['dateMM'][$i]; break;
case 'd': for ($i=1;$i<=$intMaxBt;$i++) $arrHeader[$i]=str_replace(' ','<br/>',QTdatestr(DateAdd($strTendaysago,$i,'day'),'d M','')); break;
}
$arrHeader[$intMaxBt+1] = '<span class="bold">'.($ch['time']=='d' ? '10 '.strtolower($L['Days']) : $L['Year']).'</span>';

// DISPLAY title & option

$str = $y; if ($tt=='gt' || $tt=='dt' ) $str = ($y-1).'-'.$str;
echo '<p class="small" style="float:right">',implode(' &middot; ',$arrMenuTime),'</p>
<h2>',$str,' ',($s==='*' ? '' : $oVIP->sections[$s]),($tag==='*' ? '' : ', '.L('With_tag').' '.str_replace(';',' '.L('or').' ',$tag)),'</h2>
';

// Display panel content

include 'qnm_stats_out.php';

echo '
</div>
';

// CSV

if ( file_exists('qnm_stats_csv.php') )
{
  echo '<p style="margin:2px;text-align:right"><a class="csv" href="',Href('qnm_stats_csv.php'),'?',GetURI('ch,ok'),'&amp;ch=',implode('',$ch),'" title="',$L['H_Csv'],'">',$L['Csv'],'</a></p>';
}

// HTML END

include 'qnm_inc_ft.php';