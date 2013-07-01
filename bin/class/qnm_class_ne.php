<?php

// QNM 1.0 build:20130410

// =========
// class cNE -- See also, here after, the class cNL that extends cNE with the relation information
// =========

class cNE extends aQTcontainer implements IDatabase,IClassStatus
{
public $section=0;

//public $pid=-1;  // uid of the parent (0 means none or the entire network)
//public $uid=-1;
//public $class='e'; // [e]element, [l]link, [c]connector
//public $status=0;  // 0:Not actif, 1:Actif, -1:Deleted
//public $type='';
//public $items=0;   // [items] number of sub-item [e]class-link of [e|l]class item only "child items"
//public $error='';

public $nid='';    // NETWORK ELEMENT ID = CLASS+UID
public $conns=0;   // number of sub-item [e]class-link of [c]class items only "child connectors"
public $links=0;   // number of relations [c]class-link
public $posts=0;   // number of posts (actif only !)
public $docs=0;    // number of attached docs
public $id='';
public $address='';
public $descr='';
public $tags='';
public $x=0;
public $y=0;
public $z=0;
public $m=0;
public $insertdate='';

// -- Constructor method (accept an array, an element string [class].[uid] or a cNE object) --

function __construct($aElement=null)
{
  if ( empty($this->class) ) $this->class='e'; // element class is required, if missing this classe uses [e]lement
  if ( isset($aElement) )
  {
    if ( is_string($aElement) )
    {
      $this->IdDecode($aElement);
      if ( strpos($aElement,'++')===false ) $this->MakeFromArray($this->GetElementRow()); // retreive element attributes, except in case of new blanco element
    }
    elseif ( is_int($aElement) )
    {
      $this->uid=$aElement;
      $this->MakeFromArray($this->GetElementRow()); // retreive element attribute
    }
    elseif ( is_array($aElement) )
    {
      $this->MakeFromArray($aElement);
    }
    else
    {
      var_dump($aElement);
      Die('cNE constructor: Invalid parameter #1');
    }
  }
}

public function IdDecode($str='')
{
  // Explodes a string $str into [class][uid] AND returns the uid. Note: when uid is '++' computes the next uid
  if ( $str==='' ) die('cNE->IdDecode: Invalid nid ['.$str.']');
  if ( !strstr($str,'.') ) $str = 'e.'.$str; // If class is not set, use element
  $arr = Explode('.',$str);
  if ( count($arr)<2 ) die('cNE->IdDecode: Invalid nid ['.$str.']');
  $this->class = $arr[0];
  $this->uid = 0;
  if ( is_numeric($arr[1]) ) { $this->uid = (int)$arr[1]; }
  elseif ( $arr[1]=='++' ) { global $oDB; $this->uid = $oDB->Nextid(cNE::GetTable($this->class),'uid'); }
  return $this->uid;
}

private function GetElementRow()
{
  global $oDB;
  $oDB->Query( 'SELECT '.implode(',',cNE::GetFields($this->class)).' FROM '.cNE::GetTable($this->class).' WHERE uid='.$this->uid );
  $row = $oDB->Getrow();
  if ( $row===False ) { echo('No element '.$this->uid); return null; }
  return $row;
}

public function MakeFromArray($aElement)
{
  if ( is_array($aElement) )
  {
    foreach($aElement as $strKey=>$oValue) {
    switch ($strKey) {
      case 'section':if ( !empty($oValue) ) $this->section = intval($oValue); break;
      case 'uid':    if ( !empty($oValue) ) $this->uid = intval($oValue); break;
      case 'class':  if ( !empty($oValue) ) $this->class = $oValue; break;
      case 'status': if ( !empty($oValue) ) $this->status = intval($oValue); break;
      case 'type':   if ( !empty($oValue) ) $this->type = $oValue; break;
      case 'pid':    if ( !empty($oValue) ) $this->pid = intval($oValue); break;
      case 'items':  if ( !empty($oValue) ) $this->items = intval($oValue); break;
      case 'conns':  if ( !empty($oValue) ) $this->conns = intval($oValue); break;
      case 'links':  if ( !empty($oValue) ) $this->links = intval($oValue); break;
      case 'posts':  if ( !empty($oValue) ) $this->posts = intval($oValue); break;
      case 'docs':   if ( !empty($oValue) ) $this->docs = intval($oValue); break;
      case 'id':     if ( !empty($oValue) ) $this->id = $oValue; break;
      case 'descr':  if ( !empty($oValue) ) $this->descr = $oValue; break;
      case 'address':if ( !empty($oValue) ) $this->address = $oValue; break;
      case 'tags':   if ( !empty($oValue) ) $this->tags = $oValue; break;
      case 'x': if ( is_numeric($oValue) ) $this->x = floatval($oValue); break; // must be FLOAT (or 0)
      case 'y': if ( is_numeric($oValue) ) $this->y = floatval($oValue); break; // must be FLOAT (or 0)
      case 'z': if ( is_numeric($oValue) ) $this->z = floatval($oValue); break; // must be FLOAT (or 0)
      case 'm': if ( is_numeric($oValue) ) $this->m = floatval($oValue); break; // must be FLOAT (or 0)
      case 'insertdate': if ( !empty($oValue) ) $this->insertdate = $oValue; break;
    }}
  }
  else
  {
    $this->id = '[unknown]';
  }
}

// --------
// aQTcontainer implementations
// --------

public static function CountItems($uid,$status)
{
  return 0;
}


// --------
// IObject implementations
// --------

public static function Classnames($reject=array())
{
  // returns an array of key=>classname. $reject allows excluding some keys. $reject can be a key [string] an array of keys
  $arr = explode(',',QNMCLASSES); // list of class keys is defined as a constant in the qnm_init file
  if ( is_string($reject) ) $reject=array($reject);
  $arrClasses = array();
  foreach ($arr as $key)
  {
    if ( in_array($key,$reject,true) ) continue;
    $arrClasses[$key]=cNE::Classname($key);
  }
  return $arrClasses;
}

// --------

public static function Classname($key)
{
  // $key can be an oject cNE or cNL
  if ( is_a($key,'cNE') ) $key = $key->class;
  if ( is_a($key,'cNL') ) $key = $key->ne1->class;
  // return names
  switch($key)
  {
    case 'g': return L('Group'); break;
    case 'e': return L('Item'); break;
    case 'l': return L('Line'); break;
    case 'c': return L('Connector'); break;
    default:  return L('Unknown'); break;
  }
}

// --------

public static function IsClass($key)
{
  return in_array($key,explode(',',QNMCLASSES),true);
}

// --------

public static function Statusnames($reject=array())
{
  // returns an array of key=>name. $reject allows excluding some keys
  // $reject can be a key [string] an array of keys
  if ( is_string($reject) ) $reject=array($reject);
  $arr = array();
  foreach (array(0,1,-1) as $key)
  {
    if ( in_array($key,$reject,true) ) continue;
    $arr[$key]=cNE::Statusname($key);
  }
  return $arr;
}

// --------

public static function Statusname($key)
{
  // $key can be an oject cNE or cNL
  if ( is_a($key,'cNE') ) $key = $key->status;
  if ( is_a($key,'cNL') ) $key = $key->ne1->status;
  // return names
  switch($key)
  {
    case 0: return L('Inactive'); break;
    case 1: return L('Active'); break;
    case -1: return L('Deleted'); break;
    default: return L('Unknown'); break;
  }
}

// --------

public static function IsStatus($key)
{
  return in_array($key,array(0,1,-1),true);
}

// --------
// IDatabase implementations
// --------

public static function GetTable($class='e')
{
  return ($class=='c' ? TABNC : TABNE);
}

public static function GetFields($class='e',$type='')
{
  switch($class)
  {
    case 'e':
    case 'l':
      switch($type)
      {
      case 'int': return array('uid','section','pid','status','items','conns','links','posts','docs','x','y','z'); break;
      case 'str': return array('class','type','id','descr','address','tags','insertdate'); break;
      case 'useredit': return array('id','type','address','descr','tags'); break;
      }
      return array('uid','section','pid','status','class','type','id','descr','address','tags','items','conns','links','posts','docs','x','y','z','insertdate');
      break;
    case 'c':
      switch($type)
      {
      case 'int': return array('uid','section','pid','status'); break;
      case 'str': return array('class','type','id','descr','address','tags','insertdate'); break;
      case 'useredit': return array('id','type','address','descr','tags'); break;
      }
      return array('uid','section','pid','status','class','type','id','descr','address','tags','insertdate');
      break;
    default: die('cNE::GetFields: invalid argument #1, class ['.$class.']');
  }
}

public static function GetSqlValue($strField,$strValue)
{
  if ( in_array($strField,array('uid','section','status','pid','links','items','conns','posts','x','y','z'),$strField) ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( empty($strValue) ) {
    if ( in_array($strField,array('birthday','docdate','fielddate','firstdate','firstpostdate','issuedate','lastdate','lastpostdate','modifdate','statusdate','wisheddate','insertdate')) ) {
      return '"0"';
    }
  }
  return '"'.$strValue.'"';
}

public function UpdateField($strField,$strValue)
{
  if ( !is_string($strField) ) die('cNE::UpdateField: Invalid field ['.$strField.']');
  global $oDB;
  return $oDB->Query( 'UPDATE '.cNE::GetTable($this->class).' SET '.$strField.'='.$this->GetSqlValue($strField,$strValue).' WHERE uid='.$this->uid );
}

public function Insert()
{
  $arrValues = array();
  global $oDB;
  foreach(cNE::GetFields($this->class) as $strField)
  {
    if ( isset($this->$strField) ) $arrValues[$strField]=$this->GetSqlValue($strField,$this->$strField);
  }
  return $oDB->Query( 'INSERT INTO '.cNE::GetTable($this->class).' ('.implode(',',array_keys($arrValues)).') VALUES ('.implode(',',$arrValues).')' );
}

// --------

// Delete can be physical or logical
// Display of logically deleted items (or other functions like undelete, archive) is not yet implemented
// that's why physical delete is the default behaviour

function Delete($bPhysical=true,$bUpdateSectionStats=true)
{
  // Check

  if ( $this->uid<1 ) Die('cNE::Delete: Wrong argument (id<1)');

  // Process dependances: Delete messages, relations and connectors
  // When $this is a connector, DeleteRelations is executed, others are skipped

  $this->DeleteNotes($bPhysical);
  cNL::DeleteRelationsC(GetNid($this),array(),$bPhysical); // delete [c]onnection relations (and reverse relations)
  cNL::DeleteRelationsE(GetNid($this),array(),$bPhysical); // delete [e]mbeded relation (also delete connectors)

  // Process

  global $oDB;
  if ( $bPhysical )
  {
  $oDB->Query( 'DELETE FROM '.TABNE.' WHERE uid='.$this->uid );
  }
  else
  {
  $oDB->Query( 'UPDATE '.TABNE.' SET status=-1 WHERE uid='.$this->uid );
  }

  // Update section stats (not in case of connector)

  if ( $bUpdateSectionStats && $this->class=='e' )
  {
    $oSEC = new cSection($this->section);
    $arr = $SEC->ReadStats;
    unset($arr['items']);
    $oSEC->UpdateStats($arr); // updates section & system stats
  }
}

function DeleteNotes($bPhysical=true,$id='all')
{
  if ( $this->class=='c' ) return false; // connectors don't have notes

  // check
  $strWhere='';
  if ( $id=='all' ) $strWhere = 'pid='.$this->uid;
  if ( is_integer($id) ) $strWhere = ' pid='.$this->uid.' AND id='.$id;
  if ( is_array($id) ) $strWhere = ' pid='.$this->uid.' AND id IN ('.implode(',',$id).')';
  if ( empty($strWhere) ) Die('cNE::DeleteNotes: Invalid argument id');

  // process
  global $oDB;
  if ( $bPhysical )
  {
  return $oDB->Query( 'DELETE FROM '.TABPOST.' WHERE '.$strWhere );
  }
  else
  {
  return $oDB->Query( 'UPDATE '.TABPOST.' SET status=-1 WHERE '.$strWhere );
  }
}

// -------- ?? drop attr

function Idstatus($bHref=true,$strAttr='title="Id"',$bTitle=true)
{
  if ( $bHref )
  {
    return '<a href="'.Href('qnm_item.php').'?nid='.GetNid($this).'"'.($bTitle ? ' title="'.cNE::GetIconTitle($this).'"' : '').(empty($strAttr) ? '' : ' '.$strAttr).'>'.$this->id.'</a>'.($this->status==0 ? '<span class="inactivebull" title="'.L('inactive').'">&bull;</span>' : '').($this->status<0 ? '<span style="color:#ff0000" title="'.L('deleted').'">x</span>' : '');
  }
  else
  {
    return $this->id.($this->status==0 ? '<span class="inactivebull" title="'.L('inactive').'">&bull;</span>' : '').($this->status<0 ? '<span style="color:#ff0000" title="'.L('deleted').'">x</span>' : '');
  }
}

// --------

function Dump($bHref=true,$strIdHtmlTags='',$bLongDescr=true,$bBreak=false)
{
  $str = cNE::GetIcon($this,true).' '.$this->Idstatus($bHref,$strIdHtmlTags);
  if ( $this->class!='c' )
  {
  $str .= $this->DumpLinks();
  }
  if ( $bBreak ) $str .='<br/>';
  $str .= ' <span title="'.L('Type').'">'.(empty($this->type) ? 'unknown' : $this->type).'</span>';
  $str .= sprintf(' <span class="small" title="'.L('Address').'">[%s]</span>',(empty($this->address) ? '-' : $this->address));
  if ( !empty($this->descr) )
  {
  if ( $bBreak ) $str .='<br/>';
  $str .= ' <span class="small disabled" title="'.L('Description').'">'.QTcompact($this->descr,($bLongDescr ? 50 : 20)).'</span>';
  }
  $str .= $this->DumpNotes();
  return $str;
}

function DumpContent($bHref=true,$strAttr='',$intMax=50,$strClass='small')
{
  $str = '';
  if ( $this->class!='c' )
  {
    $str = AsImg($_SESSION[QT]['skin_dir'].'/ico_link_c.gif','-','','i_sub');
    $arrE = $this->GetNL('e','',$intMax+1);

    if ( count($arrE)>0 )
    {
      $arrE = cNL::GetNEs($arrE,2,false);
      $i = 1;
      foreach($arrE as $oSubNE)
      {
        $strTitle = ($oSubNE->class=='c' ? L('Connector') : L('Item') ).' of type '.(empty($oSubNE->type) ? 'undefined' : $oSubNE->type );
        if ( $bHref )
        {
        $str .= ' <a '.(empty($strAttr) ? '' : $strAttr).(empty($strClass) ? '' : ' class="'.$strClass.'"').' href="'.Href('qnm_item.php').'?uid='.$oSubNE->class.$oSubNE->uid.'" title="'.$strTitle.'">'.$oSubNE->id.'</a>';
        }
        else
        {
        $str .= ' <span '.(empty($strAttr) ? '' : $strAttr).(empty($strClass) ? '' : ' class="'.$strClass.'"').' title="'.$strTitle.'">'.$oSubNE->id.'</span>';
        }
        if ( $oSubNE->status==0 ) $str .= '<span style="color:#ff0000" title="'.L('inactive').'">&bull;</span>';
        $i++; if ( $i>$intMax ) { $str .=' ...'; break; }
      }
    }
    else
    {
    $str .= ' <span class="small disabled">'.L('No_sub-item').'</span>';
    }
  }
  return $str.'<br/>';
}

function DumpLinks($strHtmlTags='',$f=' (%s)')
{
  $str = '';
  if ( $this->class!='c' )
  {
    $intEC = $this->links+$this->items;
    if ( $intEC>0 ) $str = '<span %1$s title="'.L('Links_as_relation').' '.L('relations',$this->links).'">'.$this->links.'r,</span> <span %1$s title="'.L('Contains').': '.L('sub-item',$this->items).', '.L('connector',$this->conns).'">'.($this->items+$this->conns).'e</span>';
  }
  $str = sprintf($str,$strHtmlTags);
  if ( empty($str) ) return $str;
  return sprintf($f,$str);
}

function DumpNotes()
{
  $str = '';
  if ( $this->posts>0 ) $str = ' '.AsImg($_SESSION[QT]['skin_dir'].'/ico_note_1.gif','notes',L('in_process_note',$this->posts),'i_note');
  if ( $this->posts>1 ) $str.= '<span class="small">'.$this->posts.'</span>';
  return $str;
}

// --------
// basic methods
// --------

function UpdateParent()
{
  global $oDB; // select in two step as subselect may be null
  $uid = 0; // default parent uid
  $oDB->Query( 'SELECT FIRST(lid) as firstid FROM '.TABNL.' WHERE lclass="e" AND lid2='.$this->uid.' AND lstatus>=0' );
  $row = $oDB->Getrow();
  if ( $row!==false ) $uid=$row['firstid'];
  return $oDB->Query( 'UPDATE '.TABNE.' SET pid='.(empty($uid) ? 0 : $uid).' WHERE uid='.$this->uid );
}

function UpdateLinks()
{
  if ( $this->class=='c' ) return false;
  global $oDB;
  $oDB->Query( 'SELECT count(*) as countid FROM '.TABNL.' WHERE lclass="c" AND lidclass="'.$this->class.'" AND lid='.$this->uid.' AND lstatus>=0' );
  $this->links = 0; $row = $oDB->Getrow(); if ( !empty($row) ) $this->links = (int)$row['countid'];
  $oDB->Query( 'UPDATE '.TABNE.' SET links='.$this->links.' WHERE uid='.$this->uid );
  return true;
}
function UpdateItems()
{
  if ( $this->class=='c' ) return false;
  global $oDB;
  $oDB->Query( 'SELECT count(*) as countid FROM '.TABNL.' WHERE lclass="e" AND lidclass="'.$this->class.'" AND lid='.$this->uid.' AND lid2class<>"c" AND lstatus>=0' );
  $this->items = 0; $row = $oDB->Getrow(); if ( !empty($row) ) $this->items = (int)$row['countid'];
  $oDB->Query( 'SELECT count(*) as countid FROM '.TABNL.' WHERE lclass="e" AND lidclass="'.$this->class.'" AND lid='.$this->uid.' AND lid2class="c" AND lstatus>=0' );
  $this->conns = 0; $row = $oDB->Getrow(); if ( !empty($row) ) $this->conns = (int)$row['countid'];
  $oDB->Query( 'UPDATE '.TABNE.' SET items='.$this->items.', conns='.$this->conns.' WHERE uid='.$this->uid );
  return true;
}

function UpdateNotes()
{
  if ( $this->class=='c' ) return false; // connectors do not have notes
  global $oDB;
  return $oDB->Query( 'UPDATE '.TABNE.' SET posts=(SELECT count(*) FROM '.TABPOST.' WHERE status=1 AND pid='.$this->uid.') WHERE class="e" AND uid='.$this->uid );
}

function CountPosts($option='')
{
  if ( empty($option) ) $option='status=1'; // only the message in-process
  global $oDB;
  $oDB->query( 'SELECT count(*) as countid FROM '.TABPOST.' WHERE pid='.$this->uid.' AND '.$option );
  $row = $oDB->Getrow(); // do not reset $this->posts because options can be different
  if ( $row===false ) return 0;
  return intval($row['countid']);
}

function CountLinks($item='all',$options='')
{
  global $oDB;
  $strWhere = 'lid>0';
  switch($item)
  {
  case 'c': $strWhere .= ' AND lclass="c" AND lid='.$this->uid; break; // LINK e=embed element, c=connect
  case 'e': $strWhere .= ' AND lclass="e" AND lid='.$this->uid; break;
  default: $strWhere .= ' AND lid='.$this->uid; break;
  }
  if ( !empty($options) )
  {
  $arrOptions = explode(',',$options);
  foreach($arrOptions as $strOption) $strWhere .= ' AND '.$strOption;
  }
  $oDB->query( 'SELECT count(*) as countid FROM '.TABNL.' WHERE '.$strWhere );
  $row = $oDB->Getrow();
  if ( $row===False ) return 0;
  return intval($row['countid']);
}

function GetLinksId($items='all',$intMax=50)
{
  // Return a list of [class][uid]. When relation doesn't exist, this returns an empty array
  global $oDB;
  $arr = array();
  switch($items)
  {
  case 'c':  $strWhere = ' AND lclass="c"'; break; // LINK c=connected element
  case 'e':  $strWhere = ' AND lclass="e"'; break; // LINK e=embed element (all)
  case 'ec': $strWhere = ' AND lclass="e" AND lid2class="c"'; break; // Embeded connectors
  default: $strWhere = '';
  }
  $oDB->query( 'SELECT lid2class,lid2 FROM '.TABNL.' WHERE lidclass="'.$this->class.'" AND lid='.$this->uid.$strWhere );
  $i=0;
  while($row=$oDB->Getrow())
  {
    $arr[]=$row['lid2class'].'.'.$row['lid2'];
    $i++; if ($i>=$intMax) break;
  }
  // ec additionnal properties
  if ( $items=='ec' )
  {
    $this->conns =count($arr);
  }
  return $arr;
}

function CountEmbeded($class='all')
{
  $i = 0;
  global $oDB;
  switch($class)
  {
  case 'all':
    $oDB->query( 'SELECT (SELECT count(*) FROM '.TABNE.' WHERE pid='.$this->uid.') + (SELECT count(*) FROM '.TABNC.' WHERE pid='.$this->uid.') AS countid)' );
    $row=$oDB->Getrow();
    $i = intval($row['countid']);
    break;
  case 'c':
    $oDB->query( 'SELECT count(*) as countid FROM '.TABNC.' WHERE pid='.$this->uid );
    $row=$oDB->Getrow();
    $this->conns = intval($row['countid']);
    $i = $this->conns;
    break;
  default: // l or e returns l+e items
    $oDB->query( 'SELECT count(*) as countid FROM '.TABNE.' WHERE pid='.$this->uid );
    $row=$oDB->Getrow();
    $this->items = intval($row['countid']);
    $i = $this->items;
    break;
  }
  return $i;
}

function CountCC()
{
  global $oDB;
  $oDB->query( 'SELECT count(*) as countid FROM '.TABNL.' nl WHERE nl.lidclass="c" AND nl.lid IN (SELECT uid FROM '.TABNC.' WHERE pid='.$this->uid.') AND nl.lclass="c" AND nl.lid2class="c"' );
  $row = $oDB->Getrow();
  if ( $row===False ) return 0;
  return (int)$row['countid'];
}

function GetCC($arrParents=array(),$bUid=true)
{
  // Returns the connector+parent linked to these connectors
  // The array key is the connector uid, the value is a cNL object having the linked connector as ne1 and the parent (of ne1) as ne2
  // To avoid searching parent, use $arrParents=false
  // To increase search parent performance, you can provide a liste of [cNE] parents (attention keys must be the uid).
  global $oDB;
  $arr = array();
  // Get the link information and the parent of the linked connector
  $oDB->query( 'SELECT nl.*,ne.* FROM '.TABNL.' nl INNER JOIN '.TABNC.' ne ON nl.lid2=ne.uid WHERE nl.lclass="c" AND nl.lidclass="c" AND nl.lid IN (SELECT uid FROM '.TABNC.' WHERE pid='.$this->uid.')' );
  while($row=$oDB->Getrow())
  {
    $ne1 = new cNE($row); // linked connector
    $ne2 = null;
    if ( is_array($arrParents) )
    {
      if ( isset($arrParents[$ne1->pid]) )
      {
        $ne2 = $arrParents[$ne1->pid];
      }
      else
      {
        $ne2 = new cNE($ne1->GetParent());
        $arrParents[$ne1->pid] = $ne2;
      }
    }
    $arr[($bUid ? (int)$row['lid'] : $row['lidclass'].'.'.$row['lid'])] = new cNL($row['lclass'],$row['ldirection'],$row['lstatus'],$ne1,$ne2);
  }
  return $arr;
}

function GetNL($item='all',$option='',$intMax=250,$bUid=false)
{
  // This returns a array of cNL objects (key is the nid of the linked element)
  // This returns an empty array if relation doesn't exist
  // This also make a specific count of the linked connectors (if not yet defined)
  // $option can be use to add conditions AND order (if empty: order by id)

  $arr = array();
  global $oDB;
  if ( $option==='' ) $option = ' ORDER BY e.id';
  switch($item)
  {
  //-------
  case 'c': // link [c]connect
  //-------

    if ( $this->class=='c' )
    {
      $oDB->query( 'SELECT l.*,e.'.implode(',e.',cNE::GetFields('c')).' FROM '.TABNL.' l INNER JOIN '.TABNC.' e ON e.uid=l.lid2 WHERE l.lidclass="'.$this->class.'" AND l.lid='.$this->uid.' AND l.lclass="c" '.$option );
      $i=0;
      while($row=$oDB->Getrow())
      {
        $arr[($bUid ? (int)$row['uid'] : $row['class'].'.'.$row['uid'])] = new cNL($row['lclass'],$row['ldirection'],$row['lstatus'],$this,new cNE($row));
        $i++; if ($i>=$intMax) break;
      }
    }
    else
    {
      if ( $this->links>0 )
      {
        $oDB->query( 'SELECT l.*,e.'.implode(',e.',cNE::GetFields('e')).' FROM '.TABNL.' l INNER JOIN '.TABNE.' e ON e.uid=l.lid2 WHERE l.lidclass="'.$this->class.'" AND l.lid='.$this->uid.' AND l.lclass="c" '.$option );
        $i=0;
        while($row=$oDB->Getrow())
        {
          $arr[($bUid ? (int)$row['uid'] : $row['class'].'.'.$row['uid'])] = new cNL($row['lclass'],$row['ldirection'],$row['lstatus'],$this,new cNE($row));
          $i++; if ($i>=$intMax) break;
        }
      }
    }

    break;

  //-------
  case 'e': // link [e]mbeded
  //-------

    // get NE
    if ( $this->items>0 )
    {
      $oDB->query( 'SELECT l.*,e.'.implode(',e.',cNE::GetFields('e')).' FROM '.TABNL.' l INNER JOIN '.TABNE.' e ON e.uid=l.lid2 WHERE l.lidclass="'.$this->class.'" AND l.lid='.$this->uid.' AND l.lclass="e" AND l.lid2class<>"c" '.$option );
      $i=0;
      while($row=$oDB->Getrow())
      {
        $arr[$row['class'].'.'.$row['uid']] = new cNL($row['lclass'],$row['ldirection'],$row['lstatus'],$this,new cNE($row));
        $i++; if ($i>=$intMax) break;
      }
    }
    // get NC
    if ( $this->conns>0 )
    {
      $oDB->query( 'SELECT e.'.implode(',e.',cNE::GetFields('c')).' FROM '.TABNC.' e WHERE e.pid='.$this->uid.' '.$option );
      $i=0;
      while($row=$oDB->Getrow())
      {
        $arr['c.'.$row['uid']] = new cNL('e',0,1,$this,new cNE($row));
        $i++; if ($i>=$intMax) break;
      }
    }

    break;

  }
  return $arr;
}

function GetEmbeded($type='all',$intMax=250,$bUid=false)
{
  $arr = array();
  if ( $this->class=='c' ) return $arr;
  switch($type)
  {
  case 'all': $str = '(SELECT '.implode(',',cNE::GetFields('c')).',0 as posts FROM '.TABNC.' WHERE pid='.$this->uid.') UNION (SELECT '.implode(',',cNE::GetFields('c')).',posts FROM '.TABNE.' WHERE pid='.$this->uid.') ORDER BY class DESC,id ASC'; break;
  case 'c':   $str = 'SELECT '.implode(',',cNE::GetFields('c')).' FROM '.TABNC.' WHERE pid='.$this->uid.' ORDER BY id ASC'; break;
  default:    $str = 'SELECT '.implode(',',cNE::GetFields('e')).' FROM '.TABNE.' WHERE class="'.$type.'" AND pid='.$this->uid.' ORDER BY class DESC,id ASC';
  }
  global $oDB;
  $oDB->query( $str );
  $i=0;
  while($row=$oDB->Getrow())
  {
    $arr[($bUid ? (int)$row['uid'] : $row['class'].'.'.$row['uid'])] = new cNE($row);
    $i++; if ($i>=$intMax) break;
  }
  return $arr;
}

function GetConnected($intMax=250,$bUid=false)
{
  // return connected items (no connectors) as an array (with [class].[uid] as keys)
  $arr = array();
  global $oDB;
  $oDB->query( 'SELECT nl.*,ne.'.implode(',ne.',cNE::GetFields($this->class)).' FROM '.TABNL.' nl INNER JOIN '.cNE::GetTable($this->class).' ne ON ne.uid=nl.lid2 WHERE nl.lclass="c" AND nl.lidclass="'.$this->class.'" AND nl.lid='.$this->uid.' ORDER BY ne.id' );
  $i=0;
  while($row=$oDB->Getrow())
  {
    $arr[($bUid ? (int)$row['uid'] : $row['class'].'.'.$row['uid'])] = new cNL($row['lclass'],$row['ldirection'],$row['lstatus'],$this,new cNE($row));
    $i++; if ($i>=$intMax) break;
  }
  return $arr;
}

function GetPosts($options='',$intMax=50,$order='issuedate DESC')
{
  $arr = array();
  global $oDB;
  $strWhere = 'pid='.$this->uid;
  if ( !empty($options) )
  {
  $arrOptions = explode(',',$options);
  foreach($arrOptions as $strOption) $strWhere .= ' AND '.$strOption;
  }
  $oDB->query( 'SELECT * FROM '.TABPOST.' WHERE '.$strWhere.' ORDER BY '.$order );
  $i=0;
  while($row=$oDB->Getrow())
  {
    $arr[intval($row['id'])]=$row;
    $i++; if ($i>=$intMax) break;
  }
  return $arr;
}

function GetParent()
{
  // don't use global $oDB because this function can be used inside a $oDB fetching loog
  global $qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn;
  $oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
  $oDB->query( 'SELECT '.implode(',',cNE::GetFields('e')).' FROM '.TABNE.' WHERE uid='.$this->pid );
  $row=$oDB->Getrow();
  if ( $row===False ) return array();
  return $row;
}

// --------
// transformation methods
// --------

function ChangeDirection($arrNids,$d=0)
{
  if ( is_integer($arrNids) ) $arrNids = array('e'.$arrNids);
  if ( is_string($arrNids) ) $arrNids = array($arrNids);
  if ( !is_array($arrNids) ) Die('cNE::ChangeDirection: argument #1 must be an array');
  global $oDB;
  foreach($arrNids as $str)
  {
    $uid = GetUid($str);
    $class = substr($str,0,1);
    $oDB->Query( 'UPDATE '.TABNL.' SET ldirection='.$d.' WHERE lidclass="'.$this->class.'" AND lid='.$this->uid.' AND lclass="c" AND lid2class="'.$class.'" AND lid2='.$uid );
    // update reverse link (and direction 1,-1 are reversed)
    $oDB->Query( 'UPDATE '.TABNL.' SET ldirection='.(abs($d)==1 ? $d*-1 : $d).' WHERE lid2class="'.$this->class.'" AND lid2='.$this->uid.' AND lclass="c" AND lidclass="'.$class.'" AND lid='.$uid );
  }
}
function ChangeStatusSubElements($i,$arr=array())
{
  global $oDB;
  if ( $this->class=='c' ) return false;
  if ( !is_array($arr) ) return false;
  // empty $arr means all sub-items
  if ( empty($arr) )
  {
    $oDB->Query( 'UPDATE '.TABNE.' SET status='.(int)$i.' WHERE pid='.$this->uid );
    $oDB->Query( 'UPDATE '.TABNC.' SET status='.(int)$i.' WHERE pid='.$this->uid );
  }
  else
  {
    $arrClass = array();
    $arrClass['e'] = ExtractUids($arr,'e');
    $arrClass['l'] = ExtractUids($arr,'l');
    $arrClass['c'] = ExtractUids($arr,'c');
    foreach($arrClass as $key=>$arr)
    {
    if ( !empty($arr) ) $oDB->Query( 'UPDATE '.($key=='c' ? TABNC : TABNE).' SET status='.(int)$i.' WHERE uid IN ('.implode(',',$arr).')' );
    }
  }
  return true;
}
public function Move($i)
{
  global $oDB;
  $this->section=intval($i);
  // Move element (and connectors)
  $oDB->Query( 'UPDATE '.cNE::GetTable($this->class).' SET section='.$this->section.' WHERE uid='.$this->uid. ' OR (pid='.$this->uid.' AND class="c")' );
  return $this->section; // return null if failed
}
public function AddNote($str)
{
  if ( $this->class=='c' ) return false; // no notes on connectors

  // $str must be validated before using AddNote
  global $oDB,$oVIP;
  $oDB->Query( 'INSERT INTO '.TABPOST.' (section,id,pclass,pid,status,textmsg,userid,username,issuedate) VALUES ('.$this->section.','.$oDB->Nextid(TABPOST,'id').',"'.$this->class.'",'.$this->uid.',1,"'.$str.'",'.$oVIP->user->id.',"'.$oVIP->user->name.'","'.date('Ymd his').'")' );

  // NE, USER stats
  $oDB->Query( 'UPDATE '.TABNE.' SET posts=posts+1 WHERE uid='.$this->uid );
  $oDB->Query( 'UPDATE '.TABUSER.' SET numpost=numpost+1,lastdate="'.date('Ymd his').'" WHERE id='.$oVIP->user->id );
  return true;
}

// -------------------
// EDIT-LINKS METHODS
// -------------------

function AddRelations($arrNEs=array(),$lclass='e',$ldirection=0,$lstatus=1,$bUpdateParent=true)
{
  if ( $this->uid<1 ) return;
  // Works with several NEs as objects or a strings [class,uid]. (can also be one object or string)
  if ( is_a($arrNEs,'cNC') || is_a($arrNEs,'cNE') || is_string($arrNEs) ) $arrNEs = array($arrNEs);
  if ( !is_array($arrNEs) ) Die('cNE::AddRelations: argument #1 must be an array');

  // add relation
  global $oDB;
  foreach($arrNEs as $str)
  {
    if ( is_a($str,'cNE') ) $str=$str->class.'.'.$str->uid; // format the object to a simple string [class].[uid]
    // read class,uid from the simple string (and check format)
    $strClass = GetNclass($str); if ( !in_array($strClass,array('e','c','l')) ) Die('cNE::AddRelations: argument #1 must be an array of string [class],[uid]');
    $strUid = GetUid($str);

    // add relation // when adding link, existing same link is first deleted
    $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lclass="'.$lclass.'" AND lidclass="'.$this->class.'" AND lid='.$this->uid.' AND lid2class="'.$strClass.'" AND lid2='.$strUid );
    $oDB->Query( 'INSERT INTO '.TABNL.' (lid,lclass,lid2,ldirection,lidclass,lid2class,lstatus) VALUES ('.$this->uid.',"'.$lclass.'",'.$strUid.','.$ldirection.',"'.$this->class.'","'.$strClass.'",'.$lstatus.')' );
    if ( $lclass=='c' )
    {
      // reverse relation (number of links is updated after)
      $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lclass="'.$lclass.'" AND lid2class="'.$this->class.'" AND lid2='.$this->uid.' AND lidclass="'.$strClass.'" AND lid='.$strUid );
      $oDB->Query( 'INSERT INTO '.TABNL.' (lid2,lclass,lid,ldirection,lid2class,lidclass,lstatus) VALUES ('.$this->uid.',"'.$lclass.'",'.$strUid.','.($ldirection==1 ? -1 : $ldirection).',"'.$this->class.'","'.$strClass.'",'.$lstatus.')' );
    }
    if ( $bUpdateParent && $lclass=='e' ) $oDB->Query( 'UPDATE '.( $this->class=='c' ? TABNC : TABNE ).' SET pid='.$this->uid.' WHERE uid='.$strUid );
    if ( $this->class!='c' && $lclass=='c' ) $oDB->Query( 'UPDATE '.TABNE.' SET links=links+1 WHERE uid='.$strUid );
  }

  //update fields links,items,conns
  if ( $lclass=='c' ) { $this->UpdateLinks(); } else { $this->UpdateItems(); }
}

function RelationDirection($arrNids=array(),$lclass='e',$ldirection=0)
{
  // works on several links (works also if $arrNids is one integer)

  global $oDB;
  if ( is_integer($arrNids) || is_string($arrNids) ) $arrNids = array($arrNids);
  if ( !is_array($arrNids) ) Die('cNE::RelationDirection: argument #1 must be an array');

  // update relation (and reverse relation)
  foreach($arrNids as $str)
  {
  $oDB->Query( 'UPDATE '.TABNL.' SET ldirection='.$ldirection.' WHERE lidclass="'.$this->class.'" AND lid='.$this->uid.' AND lclass="'.$lclass.'" AND lid2class="" AND lid2='.GetUid($str) );
  $oDB->Query( 'UPDATE '.TABNL.' SET ldirection='.($ldirection==1 ? -1 : $ldirection).' WHERE lid2class="'.$this->class.'" AND lid2='.$this->uid.' AND lclass="'.$lclass.'" AND lidclass="'.$lclass.'" AND lid='.GetUid($str) );
  }
}

function Unlink($arrNids,$lclass='')
{
  // Remove one (or several) links. Warning: remove is by class!

  if ( is_integer($arrNids) || is_string($arrNids) ) $arrNids = array($arrNids);
  if ( !is_array($arrNids) ) Die('cNE::Unlinks: argument #1 must be an array');

  global $oDB;
  foreach($arrNids as $str)
  {
    // Delete sub-connectors relation (patching, and reverse patching)
    $oDB->Query( 'DELETE nl.* FROM '.TABNL.' nl, '.TABNE.' ne WHERE ne.class="c" AND ne.pid='.GetUid($str).' AND nl.lclass="c" AND nl.lid=ne.uid' );
    $oDB->Query( 'DELETE nl.* FROM '.TABNL.' nl, '.TABNE.' ne WHERE ne.class="c" AND ne.pid='.GetUid($str).' AND nl.lclass="c" AND nl.lid2=ne.uid' );

    // Delete existing links (and reverse)
    $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lid='.$this->uid.' AND lclass="'.$lclass.'" AND lid2='.GetUid($str) );
    $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lid2='.$this->uid.' AND lclass="'.$lclass.'" AND lid='.GetUid($str) );

    // Update uid's parent and links
    if ( $lclass=='e' ) $oDB->Query( 'UPDATE '.TABNE.' SET pid=0 WHERE uid='.GetUid($str) );
    if ( $lclass=='c' ) $oDB->Query( 'UPDATE '.TABNE.' SET links=(SELECT count(*) FROM '.TABNL.' WHERE lclass="c" AND lidclass="e" AND lid='.GetUid($str).' AND lstatus>=0) WHERE uid='.GetUid($str) );
  }

  // Update stats
  $this->UpdateLinks(); //update links
}

function UnlinkAll($arrNids)
{
  // Warning this remove all class-links (between this and uid). uid can be an array of uids.

  if ( is_integer($arrNids) || is_string($arrNids) ) $arrNids = array($arrNids);
  if ( !is_array($arrNids) ) Die('cNE::UnlinkAll: argument #1 must be an array');

  $arrUids = array_map('GetUid',$arrNids);
  $strUids = implode(',',$arrUids);

  global $oDB;
  // Delete existing links
  $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lidclass="'.$this->class.'" AND lid='.$this->uid.' AND lid2'.(count($arrNids)==1 ? '='.$strNids : ' IN ('.$strUids.')') );

  // Update this childs/links
  $oDB->Query( 'UPDATE '.TABNE.' SET pid=0 WHERE uid'.(count($arrNids)==1 ? '='.$strNids : ' IN ('.$strUids.')') );

  // Update this childs/links
  $oDB->Query( 'UPDATE '.TABNE.' SET items=0,conns=0,links=0 WHERE uid='.$this->uid );
}

function ConnectorLink($lid2,$ldirection=0,$lstatus=1)
{
  // This is similare to AddLink, but for one connector
  // Be sure that $this (and $lid2) is a connector (and not the element)
  if ( $this->class!='c' ) Die('cNE::ConnectorLink: Use this function with connector.');

  global $oDB;

  // Delete existing link (and reverse link).
  $this->ConnectorUnlink($lid2);

  // Add link and reverse link, always with class [c]onnect
  $oDB->Query( 'INSERT INTO '.TABNL.' (lidclass,lid,lclass,lid2class,lid2,ldirection,lstatus) VALUES ("c",'.$this->uid.',"c","c",'.GetUid($lid2).','.$ldirection.','.$lstatus.')' );
  $oDB->Query( 'INSERT INTO '.TABNL.' (lidclass,lid,lclass,lid2class,lid2,ldirection,lstatus) VALUES ("c",'.GetUid($lid2).',"c","c",'.$this->uid.','.( $ldirection==1 || $ldirection==-1 ? $ldirection*(-1) : $ldirection).','.$lstatus.')' );
  // update both connector stats
  //?? $oDB->Query( 'UPDATE '.TABNE.' SET links=1 WHERE uid='.$this->uid.' OR uid='.$lid2 );
}

function ConnectorUnlink($lid2)
{
  if ( $this->class!='c' ) Die('cNE::ConnectorUnlink: Use this function with connector.');
  global $oDB;
  $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lclass="c" AND lidclass="c" AND lid2class="c" AND (lid='.$this->uid.' OR lid='.GetUid($lid2).')' ); // Connector can only have one [c]onnect relation (no issue by deleting all types of relation)
}

// --------
// Other methods
// --------

public static function GetIconTitle($class='e')
{
  $type='';
  $address = '';
  if ( is_a($class,'cNL') ) $class= $class->ne1;
  if ( is_a($class,'cNE') ) { $type = $class->type; $address = $class->address; $class = $class->class; }
  return str_replace('"',"'",cNE::ClassName($class).(empty($type) ? '' : ': '.$type).(empty($address) ? '' : ' ['.$address.']'));
}

public static function GetIcon($class='e',$strTitle='',$strId='',$strClass='i_item',$strAttr='')
{
  // use $strTitle TRUE to get default title {classname: type [address]}
  if ( $strTitle===true ) $strTitle = cNE::GetIconTitle($class);
  if ( is_a($class,'cNL') ) $class= $class->ne1;
  if ( is_a($class,'cNE') ) { $type = $class->type; $address = $class->address; $class = $class->class; }
  return '<img src="'.(empty($_SESSION[QT]['skin_dir']) ? 'skin/default' : $_SESSION[QT]['skin_dir']).'/ico_ne_'.$class.'.gif"'.(empty($strId) ? '' : ' id="'.$strId.'"').(empty($strClass) ? '' : ' class="'.$strClass.'"').' alt="'.$class.'"'.(empty($strTitle) ? '' : ' title="'.$strTitle.'"').(empty($strAttr) ? '' : $strAttr).'/>';
}

// --------

public static function FormatTagsAsString($str)
{
  // Formats a string (or an array) into a semi-column-separated-value string. It also trims, removes accents, lowercases and removes duplicates.
  // Will return an empty string when $str is empty (or contains separators without tags)
  if ( is_array($str) ) $str=implode(';',$str);
  if ( !is_string($str) ) Die('cNE::FormatTagsAsString: wrong argument #1');
  $str = trim($str);
  if ( empty($str) ) return '';
  $str = str_replace(QNM_QUERY_SEPARATOR,';',$str); // change separator to ; (because forms may allow other separtors)
  if ( substr($str,-1,1)==';' ) $str = substr($str,0,-1);
  if ( $str==';' ) return '';
  $str = strtr(trim($str),'????????????????????????????????????????????','eeeeEEEEaaaaAAAAAaiiiiIIIIooooOOOOoOuuuuUUUU');
  if ( QNM_TAGS_CASE===0 ) $str=strtolower($str);
  if ( QNM_TAGS_CASE===1 ) $str=strtoupper($str);
  $str = array_unique(explode(';',$str)); // remove duplicate
  if ( QNM_TAGS_SORT ) sort($str);
  return implode(';',$str);
}

public function TagsAdd($str,$oSEC)
{
  if ( !is_string($str) ) Die('cNE::TagsAdd: wrong argument #1');
  $str = cNE::FormatTagsAsString($str);
  if ( empty($str) ) return false;

  $this->tags = cNE::FormatTagsAsString($this->tags.';'.$str);

  // Save

  global $oDB; $oDB->Query('UPDATE '.TABNE.' SET tags="'.$this->tags.'" WHERE uid='.$this->uid);

  // Update section stats

  if ( isset($oSEC) ) {
  if ( $oSEC->StatsGet('tags')==0 && !empty($this->tags) ) {
    $oSEC->UpdateStats( QTarradd(QTexplode($oSEC->StatsGet('notes')),'tags',count(explode(';',$this->tags))) );
  }}
}

// --------

function TagsDel($str,$oSEC)
{
  // Check and Separator changed to ; (because it can be , in the ajax autocomplete)

  if ( !is_string($str) ) Die('cNE::TagsDel: wrong argument #1');
  if ( empty($this->tags) ) return false;
  $str = trim($str);
  if ( empty($str) ) return false;
  $str = str_replace(',',';',$str);
  if ( substr($str,-1,1)==';' ) $str = substr($str,0,-1);
  if ( $str==';' ) return false;

  global $oDB;

  if ( $str=='*' )
  {
    // Delete [all]
    $this->tags='';
    $oDB->Query('UPDATE '.TABNE.' SET tags="" WHERE uid='.$this->uid);
  }
  else
  {
    // Read tags to delete

    $str = strtr(trim($str),'????????????????????????????????????????????','eeeeEEEEaaaaAAAAAaiiiiIIIIooooOOOOoOuuuuUUUU');
    $arrDel = explode(';',$str);
    foreach($arrDel as $intKey=>$strValue) { $arrDel[$intKey]=trim($strValue); }
    if ( QNM_TAGS_CASE===0 ) foreach($arrDel as $intKey=>$strValue) { $arrDel[$intKey]=strtolower($strValue); }
    if ( QNM_TAGS_CASE===1 ) foreach($arrDel as $intKey=>$strValue) { $arrDel[$intKey]=strtoupper($strValue); }

    // Read current tags

    $arrTag = array();
    if ( !empty($this->tags) )
    {
    if ( QNM_TAGS_CASE===0 ) $this->tags = strtolower($strValue);
    if ( QNM_TAGS_CASE===1 ) $this->tags = strtoupper($strValue);
    $arrTag = explode(';',$this->tags);
    foreach($arrTag as $intKey=>$strValue) { $arrTag[$intKey]=trim($strValue); }
    }

    // Delete tags

    $this->tags = '';
    foreach($arrTag as $strValue)
    {
      if ( !in_array($strValue,$arrDel) ) $this->tags .= $strValue.';';
    }
    if ( !empty($this->tags) ) { if ( substr($this->tags,-1,1)==';' ) $this->tags = substr($this->tags,0,-1); }

    // Save

    $oDB->Query('UPDATE '.TABNE.' SET tags="'.$this->tags.'" WHERE uid='.$this->uid);
  }

  // Update section stats

  if ( isset($oSEC) ) {
  if ( $oSEC->StatsGet('notes')>0 ) {
    $oSEC->UpdateStats( QTarradd(QTexplode($oSEC->stats),'tags',cSection::CountItems($oSEC->uid,'tags')) );
  }}

}

// --------

function SetFromPost()
{
  $error='';
  if ( isset($_POST['id']) )      { $this->id = strip_tags(trim($_POST['id'])); if ( get_magic_quotes_gpc() ) $this->id = stripslashes($this->id); }
  if ( isset($_POST['type']) )    { $this->type = strip_tags(trim($_POST['type'])); if ( get_magic_quotes_gpc() ) $this->type = stripslashes($this->type); }
  if ( isset($_POST['descr']) )   { $this->descr = strip_tags(trim($_POST['descr'])); if ( get_magic_quotes_gpc() ) $this->descr = stripslashes($this->descr); }
  if ( isset($_POST['address']) ) { $this->address = strip_tags(trim($_POST['address'])); if ( get_magic_quotes_gpc() ) $this->address = stripslashes($this->address); }
  if ( isset($_POST['x']) )       { $this->x = strip_tags(trim($_POST['x'])); if ( get_magic_quotes_gpc() ) $this->x = stripslashes($this->x); }
  if ( isset($_POST['y']) )       { $this->y = strip_tags(trim($_POST['y'])); if ( get_magic_quotes_gpc() ) $this->y = stripslashes($this->y); }
  if ( isset($_POST['z']) )       { $this->z = strip_tags(trim($_POST['z'])); if ( get_magic_quotes_gpc() ) $this->z = stripslashes($this->z); }
  if ( isset($_POST['m']) )       { $this->m = strip_tags(trim($_POST['m'])); if ( get_magic_quotes_gpc() ) $this->m = stripslashes($this->m); }
  if ( isset($_POST['tags']) )    { $this->tags = strip_tags(trim($_POST['tags'])); if ( get_magic_quotes_gpc() ) $this->tags = stripslashes($this->tags);
  }

  // Check values

  if ( !empty($this->tags) ) $this->tags = cNE::FormatTagsAsString($this->tags);
  if ( trim($this->id)==='' ) return 'Missing id';

  return $error;
}

// --------

}