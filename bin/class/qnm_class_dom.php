<?php

// QNM 1.0 build:20130410

class cDomain extends aQTcontainer
{

public $title = '';

// --------

function __construct($aDom=null)
{
  if ( isset($aDom) )
  {
    if ( is_int($aDom) )
    {
      if ( $aDom<0 ) die('No domain '.$aDom);
      global $oDB;
      $oDB->Query('SELECT * FROM '.TABDOMAIN.' WHERE uid='.$aDom);
      $row = $oDB->Getrow();
      if ( $row===False ) die('No domain '.$aDom);
      $this->MakeFromArray($row);
    }
    elseif ( is_array($aDom) )
    {
      $this->MakeFromArray($aDom);
    }
    else
    {
      die('Invalid constructor parameter #1 for the class cDomain');
    }
  }
}

// --------

private function MakeFromArray($arr)
{
  foreach($arr as $strKey=>$oValue) {
  switch ($strKey) {
    case 'uid':       $this->uid = intval($oValue); break;
    case 'title':     $this->title = $oValue; break;
  }}
}

// --------

public function Rename($str='')
{
  if ( !is_string($str) || empty($str) ) die('cDomain->Rename: Argument #1 must be a string');
  global $oDB;
  $oDB->Query('UPDATE '.TABDOMAIN.' SET title="'.addslashes($str).'" WHERE uid='.$this->uid);
  if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
}

// --------
// aQTcontainer implementations
// --------

public static function Create($title,$parentid)
{
  // parentid is no used here
  if ( !is_string($title) ) die('cDomain->Add: Argument #1 must be a string');
  global $oDB;
  $uid = $oDB->Nextid(TABDOMAIN,'uid');
  $oDB->Query('INSERT INTO '.TABDOMAIN.' (uid,title,titleorder) VALUES ('.$uid.',"'.addslashes($title).'",0)');
  return $uid;
}

public static function Drop($uid)
{
  if ( !is_int($uid) ) die('cDomain::Drop: Argument #1 must be a integer');
  if ( $uid<1 ) die('cDomain::Drop: Cannot delete domain 0');
  global $oDB,$oVIP;
  $oDB->Query('UPDATE '.TABSECTION.' SET pid=0 WHERE pid='.$uid); // sections return to domain 0
  $oDB->Query('DELETE FROM '.TABDOMAIN.' WHERE uid='.$uid);
  cLang::Delete('domain','d'.$uid);
  if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
}

public static function MoveItems($id,$dest)
{
  QTargs( 'cDomain::MoveItems',array($id,$dest),array('int','int') );
  if ( $id<0 || $destination<0 ) die('cDomain::MoveItems: source and destination cannot be <0');
  global $oDB;
  $oDB->Query('UPDATE '.TABSECTION.' SET pid='.$dest.' WHERE pid='.$id);
}

public static function CountItems($id,$status)
{
  // Count Sections in domain $id
  if ( $id<0 ) die('cDomain::CountItems: id cannot be <0');
  global $oDB;
  $oDB->Query( 'SELECT count(*) as countid FROM '.TABSECTION.' WHERE pid='.$id.(isset($status) ? ' AND status='.$status : '') );
  $row = $oDB->Getrow();
  return (int)$row['countid'];
}

// --------

}