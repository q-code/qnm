<?php

/**
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license.  If you did not receive a copy of
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
if ( $_SESSION[QT]['avatar']=='0' ) die(Error(10));
if ( !$oVIP->user->CanView('U') ) die(Error(11));
$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Missing parameter id...');
if ( $oVIP->user->role!='A' ) { if ($oVIP->user->id!=$id) die($L['R_user']); }

// --------
// INITIALISE
// --------

include Translate('qnm_reg.php');

if ( !isset($_SESSION['temp_key']) ) $_SESSION['temp_key']= "";
if ( !isset($_SESSION['temp_ext']) ) $_SESSION['temp_ext']= "";

$oVIP->selfurl = 'qnm_user_img.php';
$oVIP->selfname = $L['Change_picture'];
$oVIP->exiturl = Href('qnm_user.php').'?id='.$id;
$oVIP->exitname = $L['Profile'];

$oDB->Query('SELECT name,photo,children,role FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

$upload_path = QNM_DIR_PIC.TargetDir(QNM_DIR_PIC,$id); // The path to where the image will be saved
$large_image_location = $upload_path.'src'.$id.'_'.$_SESSION['temp_key'].$_SESSION['temp_ext'];
$thumb_image_location = $upload_path.$id.$_SESSION['temp_ext'];

// Save and notify (if coppa)
function saveThumbnail($id,$str)
{
  global $oDB;
  $oDB->Query('UPDATE '.TABUSER.' SET photo="'.str_replace(QNM_DIR_PIC,'',$str).'" WHERE id='.$id); //remove the QNM_DIR_PIC
}

// Staff cannot edit other staff
if ( $row['role']=='M' && $oVIP->user->role=='M' && $oVIP->user->id!=$id ) die(Error(13));

// --------
// SUBMITTED for Exit
// --------

if ( isset($_POST['exit']) )
{
  if ( file_exists($large_image_location) ) unlink($large_image_location);
  unset($_SESSION['temp_key']);
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// INITIALISE image and repository object
// --------

$photo = (empty($row['photo']) ? '' : QNM_DIR_PIC.$row['photo']); // Current photo (Can be empty)
$photolabel = $row['name'];

$max_file = 3;       // Maximum file size in MB
$max_width = 650;    // Max width allowed for the large image
$thumb_max_width = (isset($_SESSION[QT]['avatar_width']) ? $_SESSION[QT]['avatar_width'] : 150); // Above this value, the crop tool will start
$thumb_max_height = (isset($_SESSION[QT]['avatar_height']) ? $_SESSION[QT]['avatar_height'] : 150); // Above this value, the crop tool will start
$thumb_width = 100;  // Width of thumbnail image
$thumb_height = 100; // Height of thumbnail image
$strMimetypes = 'image/pjpeg,image/jpeg,image/jpg';
if ( strpos($_SESSION[QT]['avatar'],'gif')!==FALSE) $strMimetypes.=',image/gif';
if ( strpos($_SESSION[QT]['avatar'],'png')!==FALSE) $strMimetypes.=',image/png,image/x-png';

//Check to see if any images with the same name already exist
$large_photo_exists = ''; if ( file_exists($large_image_location) ) $large_photo_exists = "<img src=\"".$large_image_location."\" alt=\"Large Image\"/>";

// --------
// SUBMITTED for Delete
// --------

if ( isset($_POST['del']) )
{
  if ( file_exists($large_image_location) ) unlink($large_image_location);
  if ( file_exists($thumb_image_location) ) unlink($thumb_image_location);
  $oDB->Query('UPDATE '.TABUSER.' SET photo="0" WHERE id='.$id);
  unset($_SESSION['temp_key']);
  $oHtml->PageBox(NULL,$L['S_delete'],$_SESSION[QT]['skin_dir'],2);
}

// --------
// PAGE
// --------

include 'qnm_upload_img.php';