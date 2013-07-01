<?php

// QNM 1.0 build:20130410

// =========
// class cNL
// =========

// $ne1 and $ne2 are cNE objects involved in the link (or null)
// As long as $ne1 and $ne2 are null, most of the methods are unusable
// Use SetNE to assign $ne1 or $ne2 after instentiation of a cLINK object

class cNL
{

// -- Properties --

public $lclass='e';   // e:embeds, c:connects
public $ldirection=0; // 0 Neutral, 1 Direct, -1 Reverse, 2 Bidirectionnal
public $lstatus=1;    // 0:Not actif, 1:Actif. Status DELETED (-1) is NOT allowed
public $ne1; // object cNE source (or null)
public $ne2; // object cNE destination (or null)

// -- Methods --

function __construct($lclass='e',$ldirection=0,$lstatus=1,$ne1=null,$ne2=null)
{
  if ( $lclass=='e' || $lclass=='c' ) $this->lclass=$lclass;
  if ( is_string($ldirection) ) $ldirection = (int)$ldirection;
  if ( is_string($lstatus) ) $lstatus = (int)$lstatus;
  if ( $ldirection===0 || $ldirection===1 || $ldirection===2 || $ldirection===-1 ) $this->ldirection=$ldirection;
  if ( $lstatus===0 || $lstatus===1 || $lstatus===-1 ) $this->lstatus=$lstatus;
  $this->SetNE($ne1,1);
  $this->SetNE($ne2,2);
}
public function SetNE($ne=null,$i=1)
{
  if ( $i!==1 && $i!==2 ) die('cNL::SetNE invalid argument #2');
  $i = 'ne'.$i;
  $this->$i = null;
  if ( is_a($ne,'cNE') ) { $this->$i = $ne; return; }
  if ( IsNid($ne) ) { $this->$i = new cNE(); $this->$i->class=GetNclass($ne); $this->$i->uid=GetUid($ne); return; }
}
public static function GetNEs($arrNL,$i=1,$bKeyNid=true)
{
  // Return a list of ne1 (or ne2) from the list of cNL. Array key is the nid (or the uid)  and values are the cNE)
  if ( !is_array($arrNL) ) die('cNL::GetNEs invalid argument #1');
  if ( empty($arrNL) ) return array();
  if ( $i!==1 && $i!==2 ) die('cNL::GetNEs invalid argument #2');
  $i = 'ne'.$i;
  $arr = array();
  foreach ($arrNL as $key=>$oNL)
  {
    if ( !is_a($oNL,'cNL') ) continue;
    $key = ($bKeyNid ? GetNid($oNL->$i) : $oNL->$i->uid);
    if ( !isset($arr[$key]) ) $arr[$key] = $oNL->$i;
  }
  return $arr;
}
public static function GetDirections($bIcon=true)
{
  $arr=array();
  $arr[0] = ($bIcon ? '&mdash; ' : '').L('Direction0');
  $arr[1] = ($bIcon ? '&rarr; ' : '').L('Direction1');
  $arr[2] = ($bIcon ? '&harr; ' : '').L('Direction2');
  $arr[-1] = ($bIcon ? '&larr; ' : '').L('Direction-1');
  return $arr;
}
// --------

public function NLDump($bHref=true,$strIdHtmlTags='')
{
  $str = cNL::NLGetIcon($this->ldirection);
  if ( is_a($this->ne2,'cNE') )
  {
    $str .= $this->ne2->Dump($bHref,$strIdHtmlTags);
    if ( $this->ne2->class=='c' )
    {
      $oP = new cNE($this->ne2->GetParent());
      $str .= ' in '.$oP->Idstatus();
    }
  }
  else
  {
    $str .= '?';
  }
  return $str;
}

// --------

public static function NLGetIcon($intClass=0,$strSkin='skin/default',$bShowStatus=true,$strHref='',$strTitleFormat='%s')
{
  $str='';
  switch($intClass)
  {
  case -1:
    $str = $strSkin.'/ico_nc_-1.gif';  if ( !file_exists($str) ) $str='admin/ico_nc_-1.gif';
    $str = AsImg($str,'&larr;',L('relation').' ('.L('reverse').')','i_rel','',$strHref);
    break;
  case 1:
    $str = $strSkin.'/ico_nc_1.gif';  if ( !file_exists($str) ) $str='admin/ico_nc_1.gif';
    $str = AsImg($str,'&rarr;',L('relation').' ('.L('direct').')','i_rel','',$strHref);
    break;
  case 2:
    $str = $strSkin.'/ico_nc_2.gif';  if ( !file_exists($str) ) $str='admin/ico_nc_2.gif';
    $str = AsImg($str,'&harr;',L('relation').' ('.L('bidirectional').')','i_rel','',$strHref);
    break;
  default:
    $str = $strSkin.'/ico_nc_0.gif';  if ( !file_exists($str) ) $str='admin/ico_nc_0.gif';
    $str = AsImg($str,'--',L('relation'),'i_rel','',$strHref);
  }
  return $str;
}

// --------

private static function NLdelete($strWhere,$bPhysical=true)
{
  if ( is_string($strWhere) ) {
  if ( !empty($strWhere) ) {
    global $oDB;
    if ( $bPhysical )
    {
    return $oDB->Query( 'DELETE FROM '.TABNL.' WHERE '.$strWhere );
    }
    else
    {
    return $oDB->Query( 'UPDATE '.TABNL.' SET lstatus=-1 WHERE '.$strWhere );
    }
  }}
  return false;
}

// ------------

public static function DeleteRelationsC($nid,$nids=array(),$bPhysical=true)
{
  // $nid can be a [object] cNE or a [string] class.uid
  if ( !IsNid($nid) ) die('cNL::DeleteRelationsC - Invalide argument #1');
  if ( is_a($nid,'cNE') ) $nid = GetNid($nid);

  // Empty $nids means 'all'. nids can be an array or a string of [class].[uid] comma-separated
  if ( $nids==='all' || $nids==='*' ) $nids=array();
  if ( is_string($nids) ) $nids=explode(',',$nids);
  if ( !is_array($nids) ) return false;

  if ( empty($nids) )
  {
    // Delete all "connected"
    cNL::NLdelete( 'lclass="c" AND (lidclass="'.GetNclass($nid).'" AND lid='.GetUid($nid).') OR (lid2class="'.GetNclass($nid).'" AND lid2='.GetUid($nid).')',$bPhysical );
  }
  else
  {
    // Delete "connected" by class. Loop through the classes : QNMCLASSES is 'e,l,c'
    $arrUids = array(); // Will store the "connected" (uids) by class
    foreach (explode(',',QNMCLASSES) as $class)
    {
      $arrUids[$class] = ExtractUids($nids,$class); // Get the "connected" (uids) by class
      if ( count($arrUids[$class])>0 )
      {
      cNL::NLdelete( 'lidclass="'.GetNclass($nid).'" AND lid='.GetUid($nid).' AND lclass="c" AND  lid2class="'.$class.'" AND lid2 IN ('.implode(',',$arrUids[$class]).')',$bPhysical );
      cNL::NLdelete( 'lid2class="'.GetNclass($nid).'" AND lid2='.GetUid($nid).' AND lclass="c" AND  lidclass="'.$class.'" AND lid IN ('.implode(',',$arrUids[$class]).')',$bPhysical );
      }
    }
  }
}

// ------------

public static function DeleteRelationsE($nid,$nids=array(),$bPhysical=true)
{
  // Note: Removing "Embeded" requires to delete connectors (if any) and to update the pid (parent) of the involved elements

  // $nid can be a [object] cNE or a [string] class.uid
  if ( !IsNid($nid) ) die('cNL::DeleteRelationsE - Invalide argument #1');
  if ( is_a($nid,'cNE') ) $nid = GetNid($nid);

  // Empty $nids means 'all'. uids can be a string of [class].[uid] comma-separated
  if ( $nids==='all' || $nids==='*' ) $nids=array();
  if ( is_string($nids) ) $nids=explode(',',$nids);
  if ( !is_array($nids) ) return false;

  global $oDB;

  if ( empty($nids) )
  {
    // Remove all "embeded".
    // Delete connectors and update pid of sub-items (where [e]mbeded relation exists)
    $oDB->Query( 'DELETE FROM '.TABNC.' WHERE pid='.GetUid($nid) );
    $oDB->Query( 'UPDATE '.TABNE.' SET pid=0 WHERE uid IN (SELECT lid2 FROM '.TABNL.' WHERE lidclass="'.GetNclass($nid).'" AND lid='.GetUid($nid).' AND lclass="e" AND lid2class<>"c")' );
    // Delete relations
    cNL::NLdelete( 'lclass="e" AND lidclass="'.GetNclass($nid).'" AND lid='.GetUid($nid),$bPhysical );
  }
  else
  {
    // Delete "embeded" by class. Loop through the classes : QNMCLASSES is 'e,l,c'
    $arrUids = array(); // Will store the "embeded" (uids) by class
    foreach (explode(',',QNMCLASSES) as $class)
    {
      $arrUids[$class] = ExtractUids($nids,$class); // Get the "embeded" (uids) by class
      if ( count($arrUids[$class])>0 && $class!='c')
      {
        // update pid of sub-items (where [e]mbeded relation exists)
        $oDB->Query( 'UPDATE '.TABNE.' SET pid=0 WHERE uid IN ('.implode(',',$arrUids[$class]).')' );
        // delete relations
        cNL::NLdelete( 'lidclass="'.GetNclass($nid).'" AND lid='.GetUid($nid).' AND lclass="e" AND lid2class="'.$class.'" AND lid2 IN ('.implode(',',$arrUids[$class]).')',$bPhysical );
      }
      if ( count($arrUids[$class])>0 && $class=='c')
      {
        // Attention: embeded connectors must be deleted, thus also ANY relations of these connectors
        cNL::NLdelete( 'lid2class="c" AND lid2 IN ('.implode(',',$arrUids['c']).')',$bPhysical );
        cNL::NLdelete( 'lidclass="c" AND lid IN ('.implode(',',$arrUids['c']).')',$bPhysical );
        // deleting [e]mbeded connectors
        $oDB->Query( 'DELETE FROM '.TABNC.' WHERE uid IN ('.implode(',',$arrUids['c']).')' );
      }
    }
  }
}

// ----------

}