<?php

// QNM 1.0 build:20130410

function Sectionlist($selected=-1,$arrReject=array(),$arrDisabled=array(),$strAll='')
{
  // attention $selected is type-sensitive (uses '*' or [int])
  // if $strAll is not empty, the list includes in first position an 'all' option having the value '*' and the label $strAll
  if ( is_int($arrReject) || is_string($arrReject) ) $arrReject = array((int)$arrReject);
  if ( is_int($arrDisabled) || is_string($arrDisabled) ) $arrDisabled = array((int)$arrDisabled);
  QTargs('Sectionlist',array($arrReject,$arrDisabled,$strAll),array('arr','arr','str'));
  global $oVIP;
  $str = '';

  if ( !empty($strAll) ) $str ='<option value="*"'.($selected==='*' ? QSEL : '').(in_array('*',$arrDisabled,true) ? ' disabled="disabled"': '').'>'.(strlen($strAll)>20 ? substr($strAll,0,19).'...' : $strAll).'</option>';

  if ( count($oVIP->sections)>0 ) {

    // reject
    $arr = $oVIP->sections;
    if ( count($arrReject)>0 ) { foreach($arrReject as $id) if ( isset($arr[$id]) ) unset($arr[$id]); }

    // format
    if ( count($arr)>3 && count($oVIP->domains)>1 )
    {
      $arr = GetSections($oVIP->user->role,-2,$arrReject);// get all sections at once (grouped by domain)
      foreach ($oVIP->domains as $intDom=>$strDom)
      {
        if ( isset($arr[$intDom]) ) {
        if ( count($arr[$intDom])>0 ) {
          $str .= '<optgroup label="'.(strlen($strDom)>20 ? substr($strDom,0,19).'...' : $strDom).'">';
          foreach($arr[$intDom] as $id=>$row) $str .= '<option value="'.$id.'"'.($id===$selected ? QSEL : '').(in_array($id,$arrDisabled,true) ? ' disabled="disabled"': '').'>'.(strlen($row['title'])>20 ? substr($row['title'],0,19).'...' : $row['title']).'</option>';
          $str .= '</optgroup>';
        }}
      }
    }
    else
    {
      foreach($arr as $id=>$strSection) $str .= '<option value="'.$id.'"'.($id===$selected ? QSEL : '').(in_array($id,$arrDisabled,true) ? ' disabled="disabled"': '').'>'.$strSection.'</option>';
    }

  }
  return $str;
}

// --------

function HtmlPageCtrl($intType=START)
{

switch ($intType)
{
case 1: echo '
<!-- PAGE CONTROL -->
<div class="page">
<!-- PAGE CONTROL -->
';
break;
case -1: echo '
<!-- END PAGE CONTROL -->
</div>
<div id="pagedialog"></div>
<!-- END PAGE CONTROL -->
';
break;
}
}

// --------

function HtmlBanner($logo='',$langMenu='',$mainMenu='',$class='banner')
{
  echo '<!-- banner -->',PHP_EOL;
  echo '<div class="',$class,'">',PHP_EOL;
  if ( !empty($logo) ) echo '<img class="logo" src="',$logo,'" alt="',$_SESSION[QT]['site_name'],'" title="',$_SESSION[QT]['site_name'],'"/>',PHP_EOL;
  if ( !empty($langMenu) ) echo $langMenu,PHP_EOL;
  if ( !empty($mainMenu) ) echo $mainMenu,PHP_EOL;
  echo '</div>',PHP_EOL;
}

// --------

function HtmlLettres($strGroup='all',$strAll='All',$strClass='lettres')
{
  // $strGroup is the current group, $strAll is the label of the 'all' group
  $strGroups = '<td class="'.$strClass.($strGroup==='all' ? ' active' : '').'" style="width:40px;">'.($strGroup==='all' ? $strAll : '<a class="'.$strClass.'" href="'.Href().'?group=all">'.$strAll.'</a>' ).'</td>';
  for ($g='A';$g!='AA';$g++) $strGroups .= '<td class="'.$strClass.($strGroup===$g ? ' active' : '').'" style="width:18px;">'.($strGroup===$g ? $g : '<a class="'.$strClass.'" href="'.Href().'?group='.$g.'">'.$g.'</a>' ).'</td>';
  $strGroups .= '<td class="'.$strClass.($strGroup==='0' ? ' active' : '').'" style="width:18px;">'.($strGroup==='0' ? '#' : '<a class="'.$strClass.'" href="'.Href().'?group=0">#</a>' ).'</td>';
  return '<td class="lettres" style="width:50px">'.L('Show').'</td>'.$strGroups.'<td class="hidden">&nbsp;</td>'.PHP_EOL;
}

// --------

function HtmlPage($e=0)
{

global $oVIP,$oHtml,$L;
$oVIP->selfurl='error';
include 'qnm_inc_hd.php';

if ( $e==99 )
{
  $strFile = Translate('sys_offline.txt'); if ( file_exists($strFile) ) { include $strFile; } else { echo Error(10); }
}
else
{
  $oHtml->Msgbox('!','msgbox about');
  $strBack = '';
  if ( isset($_SESSION[QT]['section']) ) $strBack='<p><a href="'.Href('qnm_items.php').'?s='.$_SESSION[QT]['section'].'">&laquo; '.L('Back').'</a></p>';
  if ( !$oVIP->user->CanView('V2') ) $strBack='<p><a href="'.Href('qnm_index.php').'">&laquo; '.L('Back').'</a></p>';
  if ( $_SESSION[QT]['visitor_right']<1 && $oVIP->user->role=='V' ) $strBack='<p><a href="'.Href('qnm_login.php').'">'.L('Login').'</a></p>';
  echo Error($e).$strBack;
  $oHtml->Msgbox(END);
}

include 'qnm_inc_ft.php';
exit;

}

// --------

function HtmlTabs($arrTabs=array(0=>'Empty'),$strUrl='',$keyCurrent=0,$intMax=6,$strWarning='Data not yet saved. Quit without saving?')
{

// tabx means the last tab (can be special due to popup)
// if defined, the class/style tab_on replaces the class/style tab (but you can cumulate the classes in the definition)
// if defined, the class/style tabx_on replaces the class/style tabx (but you can cumulate the styles in the definition)
// When strCurrent is defined, this tab will not be clickable
// $arrTabs can be an array of: strings, arrays, cTab

// check

if ( !is_array($arrTabs) ) die('HtmlTabs: Argument #1 must be an array');
if ( !empty($strUrl) ) { if ( !strstr($strUrl,'?') ) $strUrl .= '?'; }

// check current (if not found or not set, uses the first as current)

if ( !isset($arrTabs[$keyCurrent]) ) { $arr=array_keys($arrTabs); $keyCurrent=$arr[0]; }

// display

$strOuts='';
$strOut='';
$intCol=0;

foreach($arrTabs as $key=>$oTab)
{
  $intCol++;
  $strTab = '';
  $strTabDesc = '';

    if ( is_string($oTab) )
    {
      $strTab = $oTab;
    }
    elseif ( is_array($oTab) )
    {
      if ( isset($oTab['tabdesc']) )
      {
        if ( !empty($oTab['tabdesc']) ) $strTabDesc = $oTab['tabdesc'];
      }
      if ( isset($oTab['tabname']) )
      {
        if ( !empty($oTab['tabname']) ) { $strTab=$oTab['tabname']; } else { $strTab=ObjTrans('tab',$key); }
      }
      else
      {
        $strTab=ObjTrans('tab',$key);
      }
    }
    elseif ( is_a($oTab,'ctab') )
    {
      $strTabDesc = $oTab->tabdesc;
      $strTab = $oTab->tabname; if ( empty($strTab) ) $strTab = $oTab->tabid;
    }
    else
    {
      die('HtmlTabs: Arg #1 must be an array of strings, arrays or cTab');
    }

    $strOut .= '<li'.( $keyCurrent===$key ? ' class="active"' : '').'>';
    if ( empty($strUrl) || $keyCurrent===$key )
    {
      $strOut .=  $strTab;
    }
    else
    {
      $strOut .=  '<a href="'.$strUrl.'&amp;tt='.$key.'"'.(empty($strTabDesc) ? '' : ' title="'.$strTabDesc.'"').' onclick="return qtEdited(bEdited,\''.$strWarning.'\');">'.$strTab.'</a>';
    }
    $strOut .= '</li>'.PHP_EOL;

  if ( $intCol>=count($arrTabs) )
  {
    $strOuts = '<ul>'.$strOut.'</ul>'.PHP_EOL.$strOuts;
    break;
  }
  if ( $intCol>=$intMax )
  {
    $strOuts = '<ul>'.$strOut.'</ul>'.PHP_EOL.$strOuts;
    $intCol=0;
  }
}

return '
<!-- tab header begin -->
<div class="tab">
'.$strOuts.'
</div>
<!-- tab header end -->
';

}

// --------

function TableHeader($strTableId='t1',$arrFLD,$intCount=0,$strUrl='',$strOrder='id',$strDir='DESC')
{
  $img['ASC'] = ' <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_asc.gif" alt="+"/>';
  $img['DESC']= ' <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_desc.gif" alt="-"/>';

  foreach($arrFLD as $strKey=>$oFLD)
  {
    // change the text to hyperlink+image when necessary
    if ( $oFLD->sort && $intCount>2 && !empty($strUrl) )
    {
      if ( $strKey==$strOrder )
      {
      $str = '<a href="'.Href($strUrl).'&amp;order='.$oFLD->uid.'&amp;dir='.($strDir=='ASC' ? 'DESC' : '').($strDir=='DESC' ? 'ASC' : '').'"'.(!empty($oFLD->class_th) ? ' class="'.$oFLD->class_th.'"' : '').'>'.$oFLD->name.'</a>'.$img[$strDir];
      }
      else
      {
      $str = '<a href="'.Href($strUrl).'&amp;order='.$oFLD->uid.'&amp;dir='.$oFLD->sort.'"'.(!empty($oFLD->class_th) ? ' class="'.$oFLD->class_th.'"' : '').'>'.$oFLD->name.'</a>';
      }
    }
    else
    {
    $str = $oFLD->name;  if ( $strKey=='checkbox' && $intCount>1 ) $str = '<input type="checkbox" name="'.$strTableId.'_cb_all" id="'.$strTableId.'_cb" />';
    }
    echo '<td',(isset($oFLD->class_th) ? ' class="'.$oFLD->class_th.'"' : ''),(!empty($oFLD->style_th) ? ' style="'.$oFLD->style_th.'"' : ''),'>',$str,'</td>',PHP_EOL;
  }
}

// --------

function TableRowShow($arrFLD,$arr,$strRowClass='table_o r1',$strRowId='',$arrSrc=array())
{
  global $oVIP;
  echo '<tr class="',$strRowClass,'"',($strRowId==='' ? '' : ' id="'.$strRowId.'"'),'>',PHP_EOL;
  foreach($arrFLD as $strKey=>$oFLD)
  {
    $strFullClass = $oFLD->class_td.($oFLD->class_dynamic ? $oFLD->AddClassDynamic($arrSrc) : ''); // in case of dynamic class
    $strFullStyle = $oFLD->style_td.($oFLD->style_dynamic ? $oFLD->AddStyleDynamic($arrSrc) : ''); // in case of dynamic style
    $strClass = ''; if ( !empty($strFullClass) ) $strClass = ' class="'.$strFullClass.'"';
    $strStyle = ''; if ( !empty($strFullStyle) ) $strStyle = ' style="'.$strFullStyle.'"';
    // show column (empty value is replaced by &nbsp;)
    if ( !isset($arr[$strKey]) ) $arr[$strKey]='&nbsp;';
    if ( $arr[$strKey]==='' ) $arr[$strKey]='&nbsp;';
    echo '<td',$strClass,$strStyle,'>',$arr[$strKey],'</td>',PHP_EOL;
  }
  echo '</tr>',PHP_EOL;
}

// --------

function FormatCsvRow($arrFLD,$row)
{
  if ( is_a($row,'cNE') || is_a($row,'cNE') ) $row = get_object_vars($row);
  if ( !is_array($row) ) die('FormatTableRow: Wrong argument #3');
  $arrRows = array();
  $intEC = 0; // number of childs items+connnectors (used in case of NL|NE)
  $strMail='';
  $strWww='';
  if ( isset($row['items']) && isset($row['conns']) ) $intEC = $row['items'] + $row['conns'];

  foreach(array_keys($arrFLD) as $strKey)
  {
    $str='';
    switch($strKey)
    {
      case 'uid': $str = (int)$row['uid']; break;
      case 'status':  $str = cNE::Statusname($row['status']); break;
      case 'links':  $str = $row['links'].'r '.$intEC.'e'.( $row['pid']>0 ? ' p' : ''); break;
      case 'text':    $str = $row['preview']; break;
      case 'section': $str = $oVIP->sections[$row['section']]; break;
      case 'insertdate': $str = QTdatestr($row['insertdate'],'$',''); break;
      case 'posts': $str = (int)$row['posts']; break;
      case 'coord':
        if ( $bMap && isset($row['y']) && isset($row['x']) )
        {
          $y = floatval($row['y']);
          $x = floatval($row['x']);
          if ( !empty($y) && !empty($x) )  $str = str_replace('&#176;','°',QTdd2dms($y).','.QTdd2dms($x));
        }
        break;
      case 'tags':
        $arrTags = ( empty($row['tags']) ? array() : explode(';',$row['tags']) );
        foreach (array_keys($arrTags) as $i) if ( empty($arrTags[$i]) ) unset($arrTags[$i]);
        if ( count($arrTags)>5 )
        {
          $arrTags = array_slice($arrTags,0,5);
          $arrTags[]='...';
        }
        $str = implode(' ',$arrTags);
        break;
      case 'user.id': $str = (int)$row['id']; break;
      case 'user.name': $str = $row['name']; break;
      case 'user.role': $str = $row['role']; break;
      case 'user.contact': $str = (isset($row['mail']) ? $row['mail'].' ' : '').(isset($row['www']) ? $row['www'] : ''); break;
      case 'user.location': $str = $row['location']; break;
      case 'user.name': $str = $row['name']; break;
      case 'user.notes': $str = (int)$row['notes']; break;
      case 'user.firstdate': $str = QTdatestr($row['firstdate'],'Y-m-d',''); break;
      case 'user.lastdate': $str = QTdatestr($row['lastdate'],'Y-m-d','').(empty($row['ip']) ?  '&nbsp;' : ' ('.$row['ip'].')'); break;
      default: if ( isset($row[$strKey]) ) $str = $row[$strKey]; break;
    }
    $arrRows[$strKey] = ToCsv($str);
  }
  return $arrRows;
}

// --------

function FormatTableRow($strTableId='t1',$arrFLD,$row,$bSectionIds=false,$bMap=false,$bAllowCheckbox=true)
{
  $bNE = false;
  if ( is_a($row,'cNE') ) $row = get_object_vars($row);
  if ( is_a($row,'cNL') ) $row = get_object_vars($row);
  if ( !is_array($row) ) die('FormatTableRow: Wrong argument #3');
  if ( isset($row['class']) && cNE::IsClass($row['class']) && isset($row['uid']) ) $bNE=true;
  $arrRows = array();
  global $oVIP,$L,$arrExtData;
  $intEC = 0; // number of childs items+connnectors (used in case of NL|NE)
  $strMail='';
  $strWww='';
  if ( isset($row['items']) && isset($row['conns']) ) $intEC = $row['items'] + $row['conns'];

  // tags
  if ( isset($arrFLD['tags']) )
  {
    $arrTags = ( empty($row['tags']) ? array() : explode(';',$row['tags']) );
    foreach (array_keys($arrTags) as $i) if ( empty($arrTags[$i]) ) unset($arrTags[$i]);
    $arrMore = array();
    if ( count($arrTags)>1 )
    {
      $arrMore = array_slice($arrTags,3,10);
      $arrTags = array_slice($arrTags,0,3);
    }
    $arrTags = array_map( create_function('$str','return "<span class=\"tag\">$str</span>";'), $arrTags );
    $strTags = implode(' ',$arrTags).(empty($arrMore) ? '' : ' <abbr class="moretags" title="'.implode(',',$arrMore).'">...</abbr>');

  }

  // mail
  if ( isset($arrFLD['user.mail']) || isset($arrFLD['user.contact']) )
  {
    if ( !empty($row['mail']) )
    {
      if ( $row['privacy']=='2' ) $strMail = AsEmails($row['mail'],$row['id'],'0','img'.(QNM_JAVA_MAIL ? 'java' : ''),false,$_SESSION[QT]['skin_dir'],'Java protected email','&nbsp;');
      if ( $row['privacy']=='1' && $oVIP->user->role!='V' ) $strMail = AsEmails($row['mail'],$row['id'],'0','img'.(QNM_JAVA_MAIL ? 'java' : ''),false,$_SESSION[QT]['skin_dir'],'Java protected email','&nbsp;');
      if ( $oVIP->user->id==$row['id'] || $oVIP->user->IsStaff() ) $strMail = AsEmails($row['mail'],$row['id'],'0','img'.(QNM_JAVA_MAIL ? 'java' : ''),false,$_SESSION[QT]['skin_dir'],'Java protected email','&nbsp;');
    }
  }

  // www
  if ( isset($arrFLD['user.www']) || isset($arrFLD['user.contact']) )
  {
    if ( !empty($row['www']) ) $strWww = '<a href="'.$row['www'].'"><img class="ico" src="'.$_SESSION[QT]['skin_dir'].'/ico_user_w_1.gif" alt="www" title="web site"/></a>';
  }

  // coord
  $strCoord='';
  $strLatLon='';
  if ( $bMap && isset($row['y']) && isset($row['x']) )
  {
    $y = floatval($row['y']);
    $x = floatval($row['x']);
    if ( !empty($y) && !empty($x) )
    {
      $strCoord = '<a href="javascript:void(0)"'.($_SESSION[QT]['m_map_hidelist'] ? '' : ' onclick="gmapPan(\''.$y.','.$x.'\');"').' title="'.$L['Coord'].': '.round($y,8).','.round($x,8).'"><img class="ico i_user" src="'.$_SESSION[QT]['skin_dir'].'/ico_user_m_1.gif" alt="G" title="'.$L['Coord_latlon'].' '.QTdd2dms($y).','.QTdd2dms($x).'" /></a>';
      $strLatLon = QTdd2dms($y).'<br/>'.QTdd2dms($x);
    }
  }
  if ( $bMap && empty($strCoord) ) $strCoord = '<img class="ico i_user" src="'.$_SESSION[QT]['skin_dir'].'/ico_user_m_0.gif" alt="G" title="No coordinates"/>';

  if ( isset($row['privacy']) && isset($row['id']) )
  {
    if ( $row['privacy']!=2 && ($oVIP->user->IsStaff() || $oVIP->user->id==$row['id']) ) $strPriv=' <img class="ico" src="admin/private'.$row['privacy'].'.gif" alt="'.$row['privacy'].'" title="'.L('Privacy_visible_'.$row['privacy']).'"/>';
  }

  // FORMAT

  // ::::::::::
  foreach(array_keys($arrFLD) as $strKey) {
  // ::::::::::

      switch ($strKey)
      {
        case 'checkbox':
          $arrRows[$strKey] = '&nbsp;';
          if ( $bAllowCheckbox && $row['uid']>0 ) $arrRows[$strKey] = '<input type="checkbox" name="'.$strTableId.'_cb[]" id="'.$strTableId.'_cb'.$row['uid'].'"  value="'.$row['class'].'.'.$row['uid'].($bSectionIds && isset($row['section']) ? '.s.'.$row['section'] : '').'"/>';
          break;
        case 'icon': $arrRows[$strKey] = cNE::GetIcon($row['class']); break;
        case 'id':
          if ( $bNE )
          {
            $arrRows[$strKey] = '<a href="'.Href('qnm_item.php').'?nid='.GetNid($row).'">'.$row['id'].'</a>'.($row['status']==0 ? '<span style="color:#ff0000" title="inactive">&bull;</span>' : '').( empty($strCoord) ? '' : ' '.$strCoord );
          }
          else
          {
            $arrRows[$strKey] = $row[$strKey];
          }
          break;
        case 'links':
          if ( $row['links']==0 && $intEC==0 && $row['pid']<1 )
          {
            $arrRows[$strKey] = sprintf('<span class="small disabled">%s</span>',$L['None']);
          }
          else
          {
            $arrStr = array();
            $arrStr[]='<span title="'.L('Links_as_relation').' '.L('relation',$row['links']).'">'.$row['links'].'r</span>';
            $arrStr[]='<span title="'.L('Contains').': '.L('sub-item',$row['items']).', '.L('connector',$row['conns']).'">'.$intEC.'e</span>';
            if ( $row['pid']>0 ) $arrStr[]='<span title="'.L('Links_as_parent').'">p</span>';
            $arrRows[$strKey] = implode(', ',$arrStr);
          }
          break;
        case 'popup':
          if ( $row['links']==0 && $intEC==0 && $row['pid']<1 )
          {
            $arrRows[$strKey] = '&nbsp;';
          }
          else
          {
            $arrRows[$strKey] = ($row['uid']<0 ? '&nbsp;' : '<a class="popup_ctrl" id="popup_'.$strTableId.'_'.$row['uid'].'" href="javascript:void(0);"><img src="admin/ico_dw.gif" alt="#"/></a>');
          }
          break;
        case 'issuedate': $arrRows[$strKey] = cPost::IconMaker($row['poststatus']).' <a href="'.Href('qnm_item.php').'?nid='.GetNid($row).(isset($row['postid']) ? '&amp;note='.$row['postid'] : '').'#notes">'.QTdatestr($row['issuedate'],'$','$',true,true).' '.L('by').' '.$row['username'].'</a><br/>'.QTcompact($row['textmsg'],250,' '); break;
        case '(parent)': if ( $row['pid']>0 ) $arrRows[$strKey] = 'P'; break;
        case '(parent_red)': $arrRows[$strKey] = ( $row['pid']>0 ? '<span style="color:red">P</span>' : '&nbsp;'); break;
        case '(links)':
          $arrRows[$strKey] = sprintf('<span class="small disabled">(%s)</span>',$L['None']);
          if ( $intEC>0 || $row['links']>0 ) $arrRows[$strKey] = '('.$row['links'].'r, '.$intEC.'e)';
          if ( $row['class']=='c' && $row['links']>0 ) $arrRows[$strKey] = $L['Y'];
          break;
        case 'docs': $arrRows[$strKey] = ($row['docs']>0 ? $row['docs'] : '<span class="disabled">0</span>'); break;
        case 'posts': $arrRows[$strKey] = ( $row['posts']<1 ? '&nbsp;' : cPost::IconMaker(1,'note',L('in_process_note',$row['posts']),'i_note').($row['posts']==1 ? '' : $row['posts'])); break;
        case 'section': $arrRows[$strKey] = $oVIP->sections[$row['section']]; break;
        case 'status': $arrRows[$strKey] = cNE::Statusname($row['status']); break;
        case 'tags': $arrRows[$strKey] = ( empty($strTags) ? '&nbsp;' : $strTags ); break;
        case 'photo': $arrRows[$strKey] = ( empty($row['photo']) ? '&nbsp;' : AsImg(QNM_DIR_PIC.$row['photo'],'',$row['name'],'member','','qnm_user.php?id='.$row['id']) ); break;
        case 'name': $arrRows[$strKey] = '<a href="'.Href('qnm_user.php').'?id='.$row['id'].'">'.$row['name'].'</a>'.( empty($strCoord) ? '' : ' '.$strCoord ); break;
        case 'role': $arrRows[$strKey] = '<span class="small">'.L('Userrole_'.strtolower($row['role'])).'</span>'; break;
        case 'contact': $arrRows[$strKey] = (empty($strMail) ?  '<img class="ico i_user" src="'.$_SESSION[QT]['skin_dir'].'/ico_user_e_0.gif" alt="mail" title="'.L('Ico_user_eZ').'"/>' : $strMail ).' '.( empty($strWww) ? '<img class="ico" src="'.$_SESSION[QT]['skin_dir'].'/ico_user_w_0.gif" alt="email" title="'.L('Ico_user_wZ').'"/>' : $strWww).(empty($strPriv) ? '' : $strPriv); break;
        case 'location': $arrRows[$strKey] = '<span class="small">'.$row['location'].'</span>'; break;
        case 'coord': $arrRows[$strKey] = $strLatLon; break;
        case 'lid2': $arrRows[$strKey] = '<a href="'.Href('qnm_item.php').'?uid='.$row['lid2'].'">'.$row['lid2'].'</a>'.($row['status']==0 ? '<span style="color:#ff0000" title="inactive">&bull;</span>' : ''); break;
        case 'ldirection': $arrRows[$strKey] = cNL::NLGetIcon($row['ldirection'],$_SESSION[QT]['skin_dir']).cNE::GetIcon($row['class']); break;
        case 'void': $arrRows[$strKey] = '&nbsp;'; break;
        case 'insertdate': $arrRows[$strKey] = QTdatestr($row['insertdate'],'$','',true,true); break;

        default:
          if ( isset($row[$strKey]) )
          {
            $arrRows[$strKey] = $row[$strKey];
          }
          else
          {
            $arrRows[$strKey] = '';
          }
          if ( is_string($arrRows[$strKey]) && $arrRows[$strKey]!=='' ) $arrRows[$strKey] = '<span class="small">'.QTcompact($arrRows[$strKey],150).'</span>';
          break;
      }

    // ::::::::::
  }
  // ::::::::::

  return $arrRows;
}