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

// Attention: documents not applicable to connector !

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('V2') ) HtmlPage(11);

include 'bin/qnm_fn_sql.php';

// --------
// INITIALISE
// --------

$nid = ''; QThttpvar('nid','str'); if ( empty($nid) ) die('Missing parameters: nid');

$oNE = new cNE($nid);

if ( $oNE->class=='c' ) die('Document are not allowed on connectors...');

$oVIP->selfurl = 'qnm_form_docs.php';
$oVIP->selfname = $L['Documents'];
$oVIP->exiturl = 'qnm_item.php?nid='.$nid;
$oVIP->exitname = 'Element';

if (isset($_GET['view'])) { $_SESSION[QT]['viewmode'] = $_GET['view']; }

// ---------
// SUBMITTED url upload
// ---------

if ( isset($_POST['add']) && $_POST['type']=='url' )
{
  if ( $oVIP->user->role=='V' ) HtmlPage(11);


  $strType = 'url'; // can also be an urlimg
  $strFile = strtolower($_POST['docurl']);
  $strPath = '';
  if ( substr($strFile,0,7)=='http://' ) { $strPath='http://'; $strFile=substr($strFile,7); }
  if ( substr($strFile,0,8)=='https://' ) { $strPath='https://'; $strFile=substr($strFile,8); }

  // Check the name

  $strName = strip_tags($_POST['docname']); if ( get_magic_quotes_gpc() ) $strName = stripslashes($strName);
  $strName = QTconv($strName,'3'); if ( empty($strName) ) $strName = $strFile;
  if ( strlen($strName)>30 )
  {
    if ( substr($strName,-1,1)=='/' ) $strName = substr($strName,0,-1);
    if ( substr($strName,0,7)=='http://' ) $strName = str_replace('http://','',$strName);
    if ( substr($strName,0,8)=='https://' ) $strName = str_replace('https://','',$strName);
  }
  if ( strlen($strName)>64 ) $strName = substr($strName,0,63).'...';

  // check if urlimg

  if ( QTiswebimageext($strFile) ) $strType = "urlimg";

  // copy the file

  if ( empty($error) )
  {
    $oDB->Query( 'INSERT INTO '.TABDOC.' (id,doctype,docname,docfile,docpath,docdate) VALUES ('.GetUid($nid).',"'.$strType.'","'.$strName.'","'.$strFile.'","'.$strPath.'","'.date('Ymd His').'")' );
    $oDB->Query( 'UPDATE '.TABNE.' SET docs=(SELECT count(*) FROM '.TABDOC.' WHERE id='.GetUid($nid).') WHERE uid='.GetUid($nid) );
  }
  else
  {
    $error = 'Unable to register '.$strFile.'...';
  }

  // Exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// ---------
// SUBMITTED document upload
// ---------

if ( isset($_POST['add']) && $_POST['type']=='file' )
{
  $bSuccess = false;
  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  // Split filename and extension, format filename then add extension

  $strFile = strtolower($_FILES['docfile']['name']);
  $strExt = strrchr($strFile,'.');
  if ( $strExt ) $strFile = substr($strFile,0,-strlen($strExt));
  if ( strlen($strFile)>200 ) $strFile = substr($strFile,0,200);
  $strFile = QTconv($strFile,'F',false,false);
  $strFile .= $strExt;

  // Check the name

  $strName = strip_tags($_POST['docname']); if ( get_magic_quotes_gpc() ) $strName = stripslashes($strName);
  $strName = QTconv($strName,'3'); if ( empty($strName) ) $strName = $strFile;

  // Final physical name (add id prefix)

  $strFile = GetUid($nid).'_'.$strFile;

  // load file

  include 'bin/qnm_upload.php';
  $error = InvalidUpload($_FILES['docfile'],$arrFileextensions,$arrMimetypes,intval($_SESSION[QT]['upload_size'])*1024+16);

  // copy the file

  if ( empty($error) )
  {
    $intDirY = intval(date('Y'));
    $intDirM = intval(date('m'));
    $intDirD = intval(date('d'));
    $strDir = QNM_DIR_DOC;
    if ( is_dir($strDir.strval($intDirY)) ) $strDir .= strval($intDirY).'/';
    if ( is_dir($strDir.strval($intDirY*100+$intDirM)) ) $strDir .= strval($intDirY*100+$intDirM).'/';
    if ( is_dir($strDir.strval($intDirY*10000+$intDirM*100+$intDirD)) ) $strDir .= strval($intDirY*10000+$intDirM*100+$intDirD).'/';

    // File has passed all validation, copy it to the final destination and remove the temporary file:
    $strType = "dlc";
    if ( $strExt=='.jpeg' || $strExt=='.jpg' || $strExt=='.png' || $strExt=='.gif' ) $strType = "img";
    if ( $strExt=='.pdf' ) $strType = "pdf";
    if ( $strExt=='.txt' || $strExt=='.csv' ) $strType = "txt";
    if ( copy($_FILES['docfile']['tmp_name'],$strDir.$strFile) )
    {
      $oDB->Query( 'INSERT INTO '.TABDOC.' (id,doctype,docname,docfile,docpath,docdate) VALUES ('.GetUid($nid).',"'.$strType.'","'.$strName.'","'.$strFile.'","'.$strDir.'","'.date('Ymd His').'")' );
      $oDB->Query( 'UPDATE '.TABNE.' SET docs=(SELECT count(*) FROM '.TABDOC.' WHERE id='.GetUid($nid).') WHERE uid='.GetUid($nid)
      );
      $bSuccess=true;
    }
    else
    {
      $error = 'Unable to copy the file. The directory /'.$strDir.' is probably not writeable...';
    }
    unlink($_FILES['docfile']['tmp_name']);
  }

  // Generate thumbnail for large jpeg/png
  if ( empty($error) )
  {
    if ( isset($strDir) && isset($strFile) ) {
    if ( $strExt=='.jpeg' || $strExt=='.jpg' || $strExt=='.png' || $strExt=='.gif' ) {
    if ( file_exists($strDir.$strFile)  ) {

      if ( !extension_loaded('gd') ) return;
      $image = $strDir.$strFile;
      $thumb_image = $strDir.'thumb_'.$strFile;
      list($imagewidth, $imageheight, $imageType) = getimagesize($image);
      if ( $imagewidth>300 || $imageheight>300 )
      {
        $imageType = image_type_to_mime_type($imageType);
        $scale = 250/max($imagewidth,$imageheight);
        $newImageWidth = ceil($imagewidth * $scale);
        $newImageHeight = ceil($imageheight * $scale);
        $newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
        switch($imageType)
        {
          case 'image/gif': $source=imagecreatefromgif($image); break;
          case 'image/pjpeg':
          case 'image/jpeg':
          case 'image/jpg': $source=imagecreatefromjpeg($image); break;
          case 'image/png':
          case 'image/x-png': $source=imagecreatefrompng($image); break;
        }
        imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$imagewidth,$imageheight);
        switch($imageType)
        {
          case 'image/gif': imagegif($newImage,$thumb_image); break;
          case 'image/pjpeg':
          case 'image/jpeg':
          case 'image/jpg': imagejpeg($newImage,$thumb_image,90); break;
          case 'image/png':
          case 'image/x-png': imagepng($newImage,$thumb_image); break;
        }
        chmod($thumb_image, 0777);
      }

    }}}
  }

  // Exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);

}

// --------
// HTML START
// --------

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_form.css" media="all"/>';
include 'qnm_inc_hd.php';

// Header

echo '
<div class="formdocs header">
<div id="elementdef" class="elementheader"><h1>',L('Documents'),'</h1></div>
<p>',$oNE->Dump(true,'class="bold"'),'<br/>',$oNE->DumpContent(false,'',20),'</p>';

if ( !empty($error) ) echo '
<p id="errormessage" class="error">',$error,'</p>
';
echo '</div>
';

// Form placeholder (right)

echo '
<div class="formdocs itemselect">
';

// Form (for register users)

if ( $oVIP->user->role!='V' )
{
echo '<form method="post" action="',Href(),'?nid=',$nid,'" enctype="multipart/form-data" onsubmit="return ValidateForm(this);">
<h1 style="margin:0; padding:0 0 6px 0; border-bottom:1px solid #dddddd">',$L['Add'],': <input type="radio" name="type" value="file" id="type_file" checked="checked" onclick="formshow(\'pFile\');"><label for="type_file">',L('File'),'</label>&nbsp;<input type="radio" name="type" value="url" id="type_url" onclick="formshow(\'pUrl\');"><label for="type_url">',L('Url'),'</label></h1>
<p class="right" id="pFile"><input type="file" id="iFile" name="docfile" size="35"/></p>
<p class="right" id="pUrl" style="display:none"><input type="text" id="iUrl" pattern="(http://|https://).*" name="docurl" style="width:285px"/></p>
<p class="right">',$L['Document_name'],' <input type="text" name="docname" id="docname" style="width:250px"/></p>
<p class="right" style="margin:0; padding:6px 0 0 0; border-top:1px solid #dddddd">
<input type="hidden" name="s" value="',$oNE->pid,'"/>
<input type="hidden" name="nid" value="',$nid,'"/>
<input type="submit" name="add" value="',$L['Ok'],'"/>
</p>
</form>
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.type_file.checked)
  {
  if (theForm.iFile.value.length==0 ) { alert("',$L['Missing'],'"); return false; }
  }
  else
  {
  if (theForm.iUrl.value.length==0 || theForm.iUrl.value=="http://" || theForm.iUrl.value=="https://" ) { alert("',$L['Missing'],'"); return false; }
  }
  return null;
}
function formshow(id)
{
  var pFile = document.getElementById("pFile");
  var pUrl = document.getElementById("pUrl");
  var iUrl = document.getElementById("iUrl");
  if ( pFile && pUrl )
  {
    pFile.style.display = (id==pFile.id ? "block" : "none");
    pUrl.style.display = (id==pUrl.id ? "block" : "none");
    if ( iUrl && iUrl.value.length==0 ) iUrl.value="http://";
  }
}
//-->
</script>
';
}
else
{
echo '<h1 style="margin:0; padding:0 0 6px 0; border-bottom:1px solid #dddddd">',$L['Add'],'</h1>
<p>',Error(11),'</p>
';
}
echo '</div>
';

// Get documents in this element.
// Attention, the document-id is docfile

$arrDocs = array();
  $oDB->Query('SELECT doctype,docname,docfile,docpath,docdate FROM '.TABDOC.' WHERE id='.GetUid($nid).' ORDER by docdate ASC');
  while($row=$oDB->Getrow()) $arrDocs[$row['docfile']] = $row;

$str = '&nbsp;';
if ( !empty($arrDocs) )
{
  if ( $_SESSION[QT]['viewmode']=='N' )
  {
  $str = '<a class="small" href="'.Href().'?nid='.$nid.'&amp;view=C">'.L('View_compact').'</a> | <span class="disabled">'.L('View_large').'</span>';
  }
  else
  {
  $str = '<span class="disabled">'.L('View_compact').'</span> | <a class="small" href="'.Href().'?nid='.$nid.'&amp;view=N">'.L('View_large').'</a>';
  }
}
echo '<p class="small" style="margin:10px 5px 7px 0;text-align:right">',$str,' </p>
';

echo '<div class="frameelement">
';

echo '<table class="doc">
';

// display fields

$intCount=0;
$bStart=true;
foreach($arrDocs as $intDoc =>$arrDoc)
{
  // check file exists
  $strImg = AsImg($_SESSION[QT]['skin_dir'].'/qnm_doc_err.png','doc',(!empty($arrDoc['docname']) ? $arrDoc['docname'] : $arrDoc['docfile']).' &middot; '.Error(21),'docfile doc_'.$_SESSION[QT]['viewmode'],'vertical-align:middle');
  $strHref = '';

  switch($arrDoc['doctype'])
  {
  case 'txt':
  case 'pdf':
  case 'url':
    $strHref = $arrDoc['docpath'].$arrDoc['docfile'];
    $strImg = AsImg($_SESSION[QT]['skin_dir'].'/qnm_doc_url.png','doc',$arrDoc['docfile'],'docfile doc_'.$_SESSION[QT]['viewmode'],'vertical-align:middle',$strHref);
    break;
  case 'urlimg':
    $strHref = $arrDoc['docpath'].$arrDoc['docfile'];
    $strImg = AsImg($strHref,'doc',$arrDoc['docfile'],'docimg doc_'.$_SESSION[QT]['viewmode'],'vertical-align:middle',$strHref);
    break;
  case 'img':
    if ( file_exists($arrDoc['docpath'].$arrDoc['docfile']) )
    {
      $strHref = $arrDoc['docpath'].$arrDoc['docfile'];
      $strExt = substr(strrchr($arrDoc['docfile'],'.'),1);
      if ( in_array($strExt,array('gif','jpg','jpeg','png')) )
      {
        $str = $arrDoc['docpath'].$arrDoc['docfile'];
        if ( file_exists($arrDoc['docpath'].'thumb_'.$arrDoc['docfile']) ) $str = $arrDoc['docpath'].'thumb_'.$arrDoc['docfile'];
        $strImg = AsImg($str,'doc',$arrDoc['docfile'],'docimg doc_'.$_SESSION[QT]['viewmode'],'vertical-align:middle',$arrDoc['docpath'].$arrDoc['docfile']);
      }
    }
    break;
  default:
    if ( file_exists($arrDoc['docpath'].$arrDoc['docfile']) ) $strImg = AsImg($_SESSION[QT]['skin_dir'].'/qnm_doc_dlc.png','doc',$arrDoc['docfile'],'docfile doc_'.$_SESSION[QT]['viewmode'],'vertical-align:middle',$arrDoc['docpath'].$arrDoc['docfile']);
    break;
  }

  // display documents
  if ( $bStart ) { echo '<tr class="doc">'; $bStart=false; }

    echo '<td class="doc">';
    if ( $_SESSION[QT]['viewmode']=='C' ) echo '<table class="hidden"><tr class="hidden"><td style="width:40%;text-align:center">';
    echo $strImg;
    if ( $_SESSION[QT]['viewmode']=='C' ) echo '</td><td style="text-align:left">';
    echo '<br/>';
    if ( empty($strHref) )
    {
      echo '<span title="',Error(21),'">',(!empty($arrDoc['docname']) ? $arrDoc['docname'] : $arrDoc['docfile']),'</span>';
    }
    else
    {
      echo '<a href="',$strHref,'">',(!empty($arrDoc['docname']) ? $arrDoc['docname'] : $arrDoc['docfile']),'</a>';
    }
    echo '<br/><span class="disabled">',(empty($arrDoc['docdate']) ? '' : QTdatestr($arrDoc['docdate'],'$','$',true)),'</span><br/>';
    echo '<a class="small" href="',Href('qnm_change.php'),'?a=docrename&amp;t=',$nid,'&amp;v=',$arrDoc['docfile'],'&amp;p=',$oVIP->user->id,'">',$L['Edit'],'</a>';
    echo ' &middot; <a class="small" href="',Href('qnm_change.php'),'?a=docdelete&amp;t=',$nid,'&amp;v=',$arrDoc['docfile'],'&amp;p=',$oVIP->user->id,'">',$L['Delete'],'</a>';
    if ( $_SESSION[QT]['viewmode']=='C' ) echo '</td></tr></table>';
    echo '</td>';

    if ( !is_int($intCount/2) ) $bStart=true;
    if ( $bStart ) { echo '</tr>',PHP_EOL; }
  $intCount++;
}
if ( $intCount>0 )
{
  if ( is_int($intCount/2) ) { echo '</tr>',PHP_EOL; } else { echo '<td class="doc">&nbsp;</td></tr>',PHP_EOL; }
}

if ( $intCount==0 ) echo '<tr class="field"><td class="field disabled" colspan="3">'.$L['None'].'...</td></tr>';

echo '
</table>
';

echo '
</div>
';

echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>
';

// --------
// HTML END
// --------

include 'qnm_inc_ft.php';