<?php
include 'qnm_index.php';

if ( isset($_GET['debugsql']) )
{
  if ( $_GET['debugsql']=='0' ) { unset($_SESSION['QTdebugsql']); } else { $_SESSION['QTdebugsql']='1'; var_dump($_SESSION['QTdebugsql']); }
}
if ( isset($_GET['debuglang']) )
{
  if ( $_GET['debuglang']=='0' ) { unset($_SESSION['QTdebuglang']); } else { $_SESSION['QTdebuglang']='1'; var_dump($_SESSION['QTdebuglang']); }
}
if ( isset($_GET['debugvar']) )
{
  if ( $_GET['debugvar']=='0' ) { unset($_SESSION['QTdebugvar']); } else { $_SESSION['QTdebugvar']='1'; var_dump($_SESSION['QTdebugvar']); }
}