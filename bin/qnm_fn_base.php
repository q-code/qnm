<?php

// QNM 1.0 build:20130410

// --------
// HIGH LEVEL
// --------

function IsNid($str)
{
  // checks if it's a cNE [object] or if it's a valid nid [string] format
  if ( is_a($str,'cNE') ) return true;
  if ( empty($str) || !is_string($str) ) return false;
  if ( !strstr($str,'.') ) return false;
  $arr = Explode('.',$str);
  if ( count($arr)<2 ) return false;
  if ( !strstr(QNMCLASSES,$arr[0]) ) return false;
  if ( !is_numeric($arr[1]) || $arr[1]<0 ) return false;
  return true;
}
function GetNid($ne,$bSection=false)
{
  if ( is_a($ne,'cNL') ) $ne = $ne->ne1;
  if ( is_a($ne,'cNE') ) $ne = array('class'=>$ne->class,'uid'=>$ne->uid,'section'=>$ne->section);
  if ( !is_array($ne) ) die('GetNid - Invalid argument #1 - must be a cNE,cNL or array object');
  // If class is not set, it is a connector
  if ( empty($ne['class']) ) $ne['class']='c';
  if ( empty($ne['uid']) ) $ne['uid']=0;
  return $ne['class'].'.'.$ne['uid'].($bSection ? '.s.'.$ne['section'] : '');
}
function CheckDico($arr)
{
  if ( is_string($arr) ) $arr=explode(' ',$arr);
  if ( !is_array($arr) ) die('CheckDico: arg #1 be an array');
  global $oVIP;
  foreach($arr as $str)
  {
  if ( !isset($_SESSION['L'][$str]) ) $_SESSION['L'][$str] = cLang::Get($str,GetIso(),'*');
  }
}

// --------

function Error($i=0)
{
  include Translate('qnm_error.php');
  if ( isset($e[$i]) ) return $e[$i];
  return 'Error '.$i;
}

// --------

function GetIso($str='')
{
  if ( empty($str) ) $str=$_SESSION[QT]['language'];
  switch(strtolower($str))
  {
  case 'english': return 'en'; break;
  case 'francais': return 'fr'; break;
  case 'nederlands': return 'nl'; break;
  default: include 'bin/qnm_lang.php'; $arr=array_flip(QTarrget($arrLang,2)); if ( isset($arr[$str]) ) return $arr[$str]; break;
  }
  return 'en';
}

// --------

function GetLang($str='')
{
  if ( empty($str) ) $str=$_SESSION[QT]['language'];
  return 'language/'.$str.'/';
}

// --------

function GetNclass($str='')
{
  if ( empty($str) ) return 'c';
  if ( !strstr($str,'.') ) return 'c'; // If class is not set, it is a connector
  $arr = explode('.',$str);
  if ( count($arr)==0 ) die('GetNclass: Invalid id ['.$str.']');
  return $arr[0];
}

// --------

function GetUid($str='')
{
  // return 0 in case of wrong format
  if ( empty($str) ) return 0;
  if ( is_int($str) ) return $str;
  if ( is_string($str) )
  {
    if ( strpos($str,'.')===false )
    {
    if ( is_numeric($str) ) return (int)$str;
    }
    else
    {
    $arr = explode('.',$str);
    if ( count($arr)<2 ) return 0;
    if ( is_numeric($arr[1]) ) return (int)$arr[1];
    }
  }
  return 0;
}

// --------

function ExtractUids($arr,$class='e',$sort='')
{
  // Creates a new array containing all uids of class $class. Attention, may contains 0 (in case of wrong format e.g. 'e..1')
  // The result can be sorted ASC or DESC (or not sorted)
  if ( !is_array($arr) ) die('ExtractUids: arg#1 must be an array');
  $uids=array();
  foreach($arr as $str)
  {
    if ( substr($str,0,strlen($class))===$class ) $uids[]=GetUid($str);
  }
  if ( $sort=='ASC' ) asort($uids);
  if ( $sort=='DESC' ) sort($uids);
  return $uids;
}
function ExtractELuids($arr,$sort='')
{
  if ( !is_array($arr) ) die('ExtractELuids: arg#1 must be an array');
  $uids = array_merge(ExtractUids($arr,'e'),ExtractUids($arr,'l'));
  if ( $sort=='ASC' ) asort($uids);
  if ( $sort=='DESC' ) sort($uids);
  return $uids;
}

// --------

function Href($str='')
{
  // When urlrewriting is active, the url can be displayed in html format (they will be converted by the server's rewrite rule).
  // This function transforms a php url into a html like url (the url can have arguments): 'qnm_login.php' is displayed as 'login.html'.
  // Note: Don't worry, server's rewriting has NO effect when the url is in php format (i.e. when this function is not used or when QNM_URLREWRITE is FALSE)
  if ( empty($str) ) { global $oVIP; $str=$oVIP->selfurl; }
  if ( QNM_URLREWRITE ) {
  if ( substr($str,0,4)=='qnm_' && strstr($str,'.php') ) {
    $str = substr($str,4);
    $str = str_replace('.php','.html',$str);
  }}
  return $str;
}

// --------

function L($strKey='',$int=false,$bInclude=true)
{
  // Returns the corresponding word, or the lowercase string of the word (e.g. when the key is in lowercase).
  // Also search the plural word when $int>1 (it searches for $key with 's')
  // In case of $int exists (<>false), the $bInclude allows merging $int with the word. Note that in this case, $int can be negative or 0

  global $L;
  $str = str_replace('_',' ',$strKey); // When word is missing, returns the key code without _
  if ( isset($L[$strKey]) )
  {
    $str = $L[$strKey];
    if ( $int!==false ) { if ( $int>1 && isset($L[$strKey.'s']) ) $str = $L[$strKey.'s']; }
  }
  elseif ( isset($L[ucfirst($strKey)]) )
  {
    $str = strtolower($L[ucfirst($strKey)]);
    if ( $int!==false ) { if ( $int>1 && isset($L[ucfirst($strKey.'s')]) ) $str = strtolower($L[ucfirst($strKey.'s')]); }
  }
  else
  {
  if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang']==='1' ) $str = '<span style="color:red">'.$str.'</span>';
  }
  return ($int!==false && $bInclude ? $int.' ' : '').$str; // When $int<>false (and $bInclude is true) the value is merged with the word
}

// --------

function ObjTrans($strType,$strId,$bGenerate=true,$intMax=0,$strTrunc='...')
{
  // This function returns the translation of the objid
  // When translation is not defined and generate is true, returns the ucfirst(objid)
  // otherwise, returns ''
  // When $intMax>1, the text is truncated to intMax characters and the $strTrunc is added.

  $str = '';
  if ( isset($_SESSION['L'][$strType][$strId]) ) $str = $_SESSION['L'][$strType][$strId];

  if ( empty($str) && $bGenerate ) {
  switch($strType) {
    case 'field': $str = ucfirst(str_replace('_',' ',$strId)); break;
    case 'tabdesc': $str = $bGenerate ;
    case 'tab': $str = ucfirst(str_replace('_',' ',$strId)); break;
    case 'tabdesc': $str = $bGenerate; break;
    case 'index': $str = $_SESSION[QT]['index_name']; break;
    case 'domain': $str = $bGenerate; break;
    case 'sec': $str = $bGenerate; break;
    case 'secdesc': $str = $bGenerate; break;
  }}

  if ( $intMax>1 && strlen($str)>$intMax ) return substr($str,0,$intMax).$strTrunc;
  return $str;
}

// --------

function Translate($strFile)
{
  if ( file_exists(GetLang().$strFile) ) Return GetLang().$strFile;
  Return 'language/english/'.$strFile;
}

// --------
// COMMON FUNCTIONS
// --------

function AsSequence($intCount=1,$strType='i',$strShift=0)
{
  $arr = array();

  // convert $strShift to integer
  if ( is_numeric($strShift) )
  {
    $intShift = intval($strShift);
  }
  elseif ( is_string($strShift) )
  {
    $strShift = strtoupper($strShift);
    if ( ($strType=='A' || $strType=='a') && strlen($strShift)==1 ) $intShift = ord($strShift)-65;
    if ( ($strType=='AA' || $strType=='aa') && strlen($strShift)==1 ) $strShift='A'.$strShift;
    if ( ($strType=='AA' || $strType=='aa') && strlen($strShift)==2  ) $intShift = (ord(substr($strShift,0,1))-65)*26 + (ord(substr($strShift,1,1))-65);
  }

  // check limit
  if ( $intCount+$intShift>110 ) return $arr;

  // create range (due to character  do not exceed 100)

  for($i=$intShift;$i<$intShift+$intCount;$i++)
  {
    if ( $strType=='i' )   $arr[]=strval($i);
    if ( $strType=='ii' )  $arr[]=sprintf('%02s',$i);
    if ( $strType=='iii' ) $arr[]=sprintf('%03s',$i);
    if ( strtoupper($strType)=='A' )
    {
      $str=''; $j=$i; // in case out of A-Z range, add prefix A (then B...)
      if ( $j>=78 ) { $str.='C'; $j=$i-78; }
      if ( $j>=52 ) { $str.='B'; $j=$i-52; }
      if ( $j>=26 ) { $str.='A'; $j=$i-26; }
      $str .= chr(65+$j);
      $arr[] = ($strType=='A' ? $str : strtolower($str));
    }
    if ( strtoupper($strType)=='AA' )
    {
      $str=''; $j=$i;  // in case out of AA-ZZ range, add prefix Z
      if ( $j>626 ) { $str='Z'; $j=$i-626; }
      $c1 = floor(($j)/26);
      $c2 = $j-($c1*26);
      $str .= chr(65+$c1).chr(65+$c2);
      $arr[] = ($strType=='AA' ? $str : strtolower($str));
    }
  }
  return $arr;
}

function AsEmails($strEmails,$strId,$strSection='0',$strRender='txt',$bFirst=false,$strSkin='skin/default',$strNojava='Java protected email',$strEmpty='&nbsp;')
{
  if ( !is_string($strEmails) ) return $strEmpty;
  if ( empty($strEmails) ) return $strEmpty;
  // get list of Emails
  if ( strstr($strEmails,' ; ') ) { $arrEmails = explode(' ; ',$strEmails); } else { $arrEmails = array($strEmails); }
  // get first Email
  $strFirst = $arrEmails[0];
  // only one email
  if ( $bFirst ) $arrEmails = array($strFirst);
  // build expression
  $strReturn = '';
  switch ($strRender)
  {
  case 'txt':
    $strReturn .= '<a id="href'.$strId.'s'.$strSection.'" class="small" href="mailto:'.implode(';',$arrEmails).'">';
    $strReturn .= implode(' ; ',$arrEmails);
    $strReturn .= '</a>';
    break;
  case 'img':
    $strReturn .= '<a id="href'.$strId.'s'.$strSection.'" class="small" href="mailto:'.implode(';',$arrEmails).'">';
    $strReturn .= '<img class="ico ico_user" id="img'.$strId.'s'.$strSection.'" src="'.$strSkin.'/ico_user_e_1.gif" alt="email" title="'.$strFirst.'"/>';
    $strReturn .= '</a>';
    break;
  case 'txtjava':
    $strReturn .= '<script type="text/javascript">';
    foreach($arrEmails as $strEmail)
    {
    $arr = explode('@',$strEmail);
    $strReturn .= 'qtWritemailto("'.$arr[0].'","'.$arr[1].'"," ");';
    }
    $strReturn .= '</script><noscript class="small">'.$strNojava.'</noscript>';
    break;
  case 'imgjava':
    $str = implode(';',$arrEmails);
    $str = str_replace('@','-at-',$str);
    $str = str_replace('.','-dot-',$str);
    $strFirst = str_replace('@','-at-',$strFirst);
    $strFirst = str_replace('.','-dot-',$strFirst);
    $strReturn .= '<a id="href'.$strId.'s'.$strSection.'" onmouseover="qtVmail(\''.$strId.'s'.$strSection.'\');" onmouseout="qtHmail(\''.$strId.'s'.$strSection.'\');" class="small" href="javamail:'.$str.'">';
    $strReturn .= '<img class="ico ico_user" id="img'.$strId.'s'.$strSection.'" src="'.$strSkin.'/ico_user_e_1.gif" alt="email" title="'.$strFirst.'"/>';
    $strReturn .= '</a>';
    break;
  }

  return $strReturn;
}

// --------

function AsImg($strSrc='',$strAlt='',$strTitle='',$strClass='',$strStyle='',$strHref='',$strId='',$strAttr='')
{
  QTargs( 'AsImg',array($strSrc,$strAlt,$strClass,$strStyle,$strHref) );

  if ( empty($strSrc) ) return '';
  $strSrc = '<img src="'.$strSrc.'" alt="'.(empty($strAlt) ? 'i' : $strAlt).'"'.(empty($strTitle) ? '' : ' title="'.QTconv($strTitle).'"').(empty($strClass) ? '' : ' class="'.$strClass.'"').(empty($strStyle) ? '' : ' style="'.$strStyle.'"').(empty($strId) ? '' : ' id="'.$strId.'"').(empty($strAttr) ? '' : ' '.$strAttr).'/>';
  if ( empty($strHref) ) { return $strSrc; } else { return '<a href="'.Href($strHref).'">'.$strSrc.'</a>' ; }
}

// --------

function AsImgBox($strSrc='',$strClass='',$strStyle='',$strCaption='',$strHref='',$strId='')
{
  QTargs( 'AsImgBox',array($strSrc,$strClass,$strStyle,$strCaption,$strHref) );

  if ( !empty($strHref) ) $strCaption = '<a href="'.Href($strHref).'" class="small">'.$strCaption.'</a>';
  return '<div'.(empty($strClass) ? '' : ' class="'.$strClass.'"').(empty($strStyle) ? '' : ' style="'.$strStyle.'"').(empty($strId) ? '' : ' id="'.$strId.'"').'>'.$strSrc.(empty($strCaption) ? '' : '<p class="imgcaption">'.$strCaption.'</p>').'</div>';
}

// --------

function AsAvatarSrc($str='')
{
  if ( empty($str) ) return '';
  if ( isset($_SESSION[QT]['avatar']) ) { if ( $_SESSION[QT]['avatar']=='0' ) return ''; }
  return QNM_DIR_PIC.$str;
}

// --------

function DateAdd($d='0',$i=-1,$str='year')
{
   if ( $d=='0' ) die('DateAdd: Argument #1 must be a string');
   QTargs( 'DateAdd',array($d,$i,$str),array('str','int','str') );

   $intY = intval(substr($d,0,4));
   $intM = intval(substr($d,4,2));
   $intD = intval(substr($d,6,2));
   switch($str)
   {
   case 'year': $intY += $i; break;
   case 'month': $intM += $i; break;
   case 'day': $intD += $i; break;
   }
   if ( in_array($intM,array(1,3,5,7,8,10,12)) && $intD>31 ) { $intM++; $intD -= 31; }
   if ( in_array($intM,array(4,6,9,11)) && $intD>30 ) { $intM++; $intD -= 30; }
   if ( $intD<1 ) { $intM--; $intD += 30; }
   if ( $intM>12 ) { $intY++; $intM -= 12; }
   if ( $intM<1 ) { $intY--; $intM += 12; }
   if ( $intM==2 && $intD>28 ) { $intM++; $intD -= 28; }
   return strval($intY*10000+$intM*100+$intD).(strlen($d)>8 ? substr($d,8) : '');
}

// --------

function CanPerform($strParam,$strRole='V')
{
  // valid parameter are: upload, show_calendar, show_stats
  if ( empty($strParam) || !isset($_SESSION[QT][$strParam]) ) return false;
  if ( $_SESSION[QT][$strParam]=='A' && $strRole=='A' ) return true;
  if ( $_SESSION[QT][$strParam]=='M' && ($strRole=='A' || $strRole=='M') ) return true;
  if ( $_SESSION[QT][$strParam]=='U' && $strRole!='V' ) return true;
  if ( $_SESSION[QT][$strParam]=='V' ) return true;
  return false;
}

// --------

function GetDomains($strRole='0')
{
  // Returns an array of [key] id, [value] title (title is translated)
  // When $strRole is defined, returns only the domains containing visible sections

  global $oDB;
  $arr = array();

  if ( empty($strRole) || $strRole=='A' || $strRole=='M' )
  {
  $oDB->Query( 'SELECT uid,title FROM '.TABDOMAIN.' ORDER BY titleorder' );
  }
  else
  {
  $oDB->Query( 'SELECT d.uid,d.title FROM '.TABDOMAIN.' d INNER JOIN '.TABSECTION.' s ON s.pid=d.uid WHERE s.type<>"1" ORDER BY d.titleorder' );
  }
  while($row=$oDB->Getrow())
  {
    $id = intval($row['uid']);
    $arr[$id] = ObjTrans('domain','d'.$id,false); if ( empty($arr[$id]) ) $arr[$id]=$row['title'];
  }
  return $arr;
}

// --------

function GetSections($strRole='V',$intDomain=-1,$arrReject=array(),$strExtra='',$strOrder='d.titleorder,s.titleorder')
{
  // Returns an array of [key] section id, array of [values] section
  // Use $intDomain to get section in this domain only
  // $intDomain=-1 mean in alls domains. -2 means in all domains but grouped by domain
  // Attention: using $intDomain -2, when a domains does NOT contains sections, this key is NOT existing in the returned list !
  if ( is_int($arrReject) || is_string($arrReject) ) $arrReject = array($arrReject);
  QTargs( 'GetSections',array($strRole,$intDomain,$arrReject,$strExtra,$strOrder),array('str','int','arr','str','str') );

  global $oDB;

  if ( $intDomain>=0 ) { $strWhere = 's.pid='.$intDomain; } else { $strWhere = 's.pid>=0'; }
  if ( $strRole=='V' || $strRole=='U' ) $strWhere .= ' AND s.type<>"1"';
  if ( !empty($strExtra) ) $strWhere .= ' AND '.$strExtra;

  $arrSections = array();
  $oDB->Query( 'SELECT s.* FROM '.TABSECTION.' s INNER JOIN '.TABDOMAIN.' d ON s.pid=d.uid WHERE '.$strWhere.' ORDER BY '.$strOrder );
  while($row=$oDB->Getrow())
  {
    $id = (int)$row['uid'];
    // if reject
    if ( in_array($id,$arrReject,true) ) continue;
    // search translation
    $str = ObjTrans('sec','s'.$id,false);
    if ( !empty($str) ) $row['title']=$str;
    // compile sections
    if ( $intDomain==-2 )
    {
    $arrSections[(int)$row['pid']][$id] = $row;
    }
    else
    {
    $arrSections[$id] = $row;
    }
  }
  return $arrSections;
}

// --------

function GetParam($bRegister=false,$strWhere='')
{
  global $oDB;
  $arrParam = array();
  $oDB->Query('SELECT param,setting FROM '.TABSETTING.(empty($strWhere) ? '' : ' WHERE '.$strWhere) );
  while($row=$oDB->Getrow())
  {
  $arrParam[$row['param']]=$row['setting'];
  if ( $bRegister ) $_SESSION[QT][$row['param']]=$row['setting'];
  }
  Return $arrParam;
}

// --------

function GetURI($reject=array())
{
  if ( !is_array($reject) ) $reject=explode(',',$reject);
  $arr = QTexplodeUri();
  foreach($reject as $key) unset($arr[trim($key)]);
  return QTimplodeUri($arr);
}

/**
 *
 * GetUserInfo
 *
 * Options supported for the $intUser:
 * i [int] a userid,
 * "S" [string] all staff members,
 * "A" [string] all administrators,
 * "i" [string] the coordinator of section i
 * will return a list of values (as string)
 *
 **/
function GetUserInfo($intUser=null,$strField='name',$oSEC=null)
{
  global $oDB;

  if ( is_string($intUser) )
  {
    if ( $intUser=='A' || $intUser=='S' )
    {
      $lst = array();
      $oDB->Query('SELECT '.$strField.' FROM '.TABUSER.' WHERE role="'.$intUser.'"');
      while( $row=$oDB->Getrow() )
      {
      $lst[] = $row[$strField];
      }
      return $lst;
    }
    if ( is_numeric($intUser) )
    {
      if ( !isset($oSEC) ) $oSEC = new cSection(intval($intUser));
      $oDB->Query('SELECT '.$strField.' FROM '.TABUSER.' WHERE id='.$oSEC->modid);
      $row=$oDB->Getrow();
      return $row[$strField];
    }
  }
  if ( is_int($intUser) )
  {
    if ( $intUser<0 ) die ('GetUserInfo: Missing user id');
    $oDB->Query('SELECT '.$strField.' FROM '.TABUSER.' WHERE id='.$intUser);
    $row = $oDB->Getrow();
    return $row[$strField];
  }
  die ('GetUserInfo: Invalid argument #1 '.var_dump($intUser));
}

// --------

function GetUsers($strRole='A',$strValue='',$strOrder='name',$iMax=200)
{
  // Return an array of maximum iMax=200 users id/name
  // $strRole: Search 'A' admins, 'M' staff(+admin), 'M-' staff(-admin), 'NAME' a name, 'A*' a name beginning by A, 'ID' the user having id=$strValue
  // Attention: names are htmlquoted in the db, no need to stripslashes

  global $oDB;
  if ( substr($strRole,-1,1)==='*' )
  {
    $strQ = 'name '.($oDB->type=='pg' ? 'ILIKE' : 'LIKE' ).' "'.substr($strRole,0,-1).'%" ORDER BY '.$strOrder;
  }
  else
  {
    switch(strtoupper($strRole))
    {
    case 'A':   $strQ = 'role="A" ORDER BY '.$strOrder; break;
    case 'M':   $strQ = 'role="A" OR role="M" ORDER BY '.$strOrder; break;
    case 'M-':  $strQ = 'role="M" ORDER BY '.$strOrder; break;
    case 'NAME':$strQ = 'name="'.$strValue.'" ORDER BY '.$strOrder; break;
    case 'ID':  $strQ = 'id='.$strValue; break;
    default: die('GetUsers: Unkown search rule ['.$strRole.']');
    }
  }
  $oDB->Query( 'SELECT id,name FROM '.TABUSER.' WHERE '.$strQ );
  $arrUsers = array();
  $i=1;
  while ($row=$oDB->Getrow())
  {
    $arrUsers[$row['id']]=$row['name'];
    $i++; if ( $i>$iMax ) break;
  }
  return $arrUsers;
}

// --------

function InvalidUpload($arrFile=array(),$strExtensions='',$strMimes='',$intSize=0,$intWidth=0,$intHeight=0)
{
  // For the uploaded document ($arrFile), this function returns (as string):
  // '' (empty string) if it matches with all conditions (see parameters)
  // An error message if not, and unlink the uploaded document.
  //
  // @$arrFile: The uploaded document ($_FILES['fieldname']).
  // @$strExtensions: List of valid extensions (as string, without point). Empty to skip.
  // @$strMimes: List of valid mimetypes as csv (can be an array). Empty to skip
  // @$intSize: Maximum file size (kb). 0 to skip.
  // @$intWidth: Maximum image width (pixels). 0 to skip.
  // @$intHeight: Maximum image width (pixels). 0 to skip.

  if ( is_array($strExtensions) ) $strExtensions=implode(', ',$strExtensions);
  if ( is_array($strMimes) ) $strMimes=implode(', ',$strMimes);

  if ( !is_array($arrFile) ) die('CheckUpload: argument #1 must be an array');
  if ( !is_string($strExtensions) ) die('CheckUpload: argument #2 must be a string');
  if ( !is_string($strMimes) ) die('CheckUpload: argument #3 must be a string');
  if ( !is_int($intSize) ) die('CheckUpload: argument #4 must be an integer');
  if ( !is_int($intWidth) ) die('CheckUpload: argument #5 must be an integer');
  if ( !is_int($intHeight) ) die('CheckUpload: argument #6 must be an integer');

  global $L;

  // check load

  if ( !is_uploaded_file($arrFile['tmp_name']) )
  {
    if ( isset($arrFile['error']) )
    {
      switch($arrFile['error'])
      {
      case 1: return 'Upload error #1. File size exceeds the server limit.'; break;
      case 2: return 'Upload error #2. File size exceeds the form limit'; break;
      case 3: return 'Upload error #3. File not fully transmitted.'; break;
      default: return 'Upload error #'.$arrFile['error'].'. File not uploaded'; break;
      }
    }
    return 'You id not upload a file!';
  }

  // check size (kb)

  if ( $intSize>0 ) {
  if ( $arrFile['size'] > ($intSize*1024+16) ) {
    unlink($arrFile['tmp_name']);
    return $L['E_file_size'].' (&lt;'.$intSize.' Kb)';
  }}

  // check extension

  if ( !empty($strExtensions) )
  {
    $strExt = strrchr($arrFile['name'],'.');
    if ( $strExt===FALSE )
    {
    unlink($arrFile['tmp_name']);
    return 'File without extension not supported... Use '.$strExtensions;
    }
    $strExt = substr($strExt,1); //remove the point
    if ( strpos(strtolower($strExtensions),strtolower($strExt))===FALSE )
    {
    unlink($arrFile['tmp_name']);
    return 'File extension ['.$strExt.'] not supported... Use '.$strExtensions;
    }
  }

  // check mimetype

  if ( !empty($strMimes) ) {
  if ( strpos(strtolower($strMimes),strtolower($arrFile['type']))===FALSE ) {
    unlink($arrFile['tmp_name']);
    return 'Format ['.$arrFile['type'].'] not supported... Use '.$strExtensions;
  }}

  // check size (pixels)

  if ( $intWidth>0 || $intHeight>0 )
  {
    $size = getimagesize($arrFile['tmp_name']);
    if ( $intWidth>0 ) {
    if ( $size[0] > $intWidth ) {
      unlink($arrFile['tmp_name']);
      return $intWidth.'x'.$intHeight.' '.$L['E_pixels_max'];
    }}
    if ( $intHeight>0 ) {
    if ( $size[1] > $intHeight ) {
      unlink($arrFile['tmp_name']);
      return $intWidth.'x'.$intHeight.' '.$L['E_pixels_max'];
    }}
  }

  return '';
}

// --------

function MakePager($url,$intRows,$intSize=50,$intPage=1)
{
  // $url full url (with arguments)
  // $intRows total number of rows
  // $intSize number of rows per page
  // $intPage current page

  global $L;
  $arr = parse_url($url);
  $url = Href($arr['path']);
  $arg = QTimplodeUri(QTarradd(QTexplodeUri($arr['query']),'page')); // remove the 'page' argument
  $strPager='';
  if ( $intRows>($intSize*5) )
  {
    // firstpage
    if ( $intPage==1 )
    {
      $strFirstpage = '<span class="pagerleft">&laquo;</span>';
    }
    else
    {
      $strFirstpage = '<a class="pagerleft" href="'.$url.'?'.$arg.'&amp;page=1" title="'.$L['First'].'">&laquo;</a>';
    }
    // lastpage
    $intLastpage = ceil($intRows/$intSize);
    if ( $intPage==$intLastpage )
    {
      $strLastpage = '<span class="pagerright">&raquo;</span>';
    }
    else
    {
      $strLastpage = '<a class="pagerright" href="'.$url.'?'.$arg.'&amp;page='.$intLastpage.'" title="'.$L['Last'].': '.$intLastpage.'">&raquo;</a>';
    }
    // 3 pages
    if ( $intPage==1 )
    {
       $strThesepages = '<span class="currentpage">'.$intPage.'</span><a class="pager" href="'.$url.'?'.$arg.'&amp;page='.($intPage+1).'" title="'.$L['Next'].'">'.($intPage+1).'</a><a class="pager" href="'.$url.'?'.$arg.'&amp;page='.($intPage+2).'" title="'.$L['Next'].'">'.($intPage+2).'</a>';
    }
    elseif ( $intPage==$intLastpage )
    {
       $strThesepages = '<a class="pager" href="'.$url.'?'.$arg.'&amp;page='.($intPage-2).'" title="'.$L['Previous'].'">'.($intPage-2).'</a><a class="pager" href="'.$url.'?'.$arg.'&amp;page='.($intPage-1).'" title="'.$L['Previous'].'">'.($intPage-1).'</a><span class="currentpage">'.$intPage.'</span>';
    }
    else
    {
       $strThesepages = '<a class="pager" href="'.$url.'?'.$arg.'&amp;page='.($intPage-1).'" title="'.$L['Previous'].'">'.($intPage-1).'</a><span class="currentpage">'.$intPage.'</span> <a class="pager" href="'.$url.'?'.$arg.'&amp;page='.($intPage+1).'" title="'.$L['Next'].'">'.($intPage+1).'</a>';
    }
    // finish
    $strPager .= $strFirstpage.$strThesepages.$strLastpage;
  }
  elseif ($intRows>$intSize)
  {
    for($i=0;$i<$intRows;$i+=$intSize)
    {
      $p = $i/$intSize+1;
      if ( $intPage==$p )
      {
        $strPager .= '<span class="currentpage">'.$p.'</span>';
      }
      else
      {
        $strPager .= '<a class="pager" href="'.$url.'?'.$arg.'&amp;page='.$p.'">'.$p.'</a>';
      }
    }
  }
  return $strPager;
}

// --------

function TargetDir($strRoot='',$intId=0)
{
  // This check if directory/subdirectory is available for an Id

  $strDir = '';
  $intDir = ($intId>0 ? floor($intId/1000) : 0);
  if ( is_dir($strRoot.strval($intDir).'000') )
  {
    $strDir = strval($intDir).'000/';
    $intSDir = $intId-($intDir*1000);
    $intSDir = ($intSDir>0 ? floor($intSDir/100) : 0);
    if ( is_dir($strRoot.$strDir.strval($intDir).strval($intSDir).'00') ) $strDir .= strval($intDir).strval($intSDir).'00/';
  }
  return $strDir;
}

// --------

function ToCsv($str,$strSep=';',$strEnc='"',$strSepAlt=',',$strEncAlt="'")
{
  // Converts a value ($str) to a csv text with final separator [;]. A string is enclosed by ["].
  // When $str contains the separator or the encloser character, they are replaced by the alternates ($strSepAlt,$strEncAlt)
  // TIP: $strSep empty (or "\r\n") to generate a end-line value
  if ( is_int($str) || is_float($str) ) return $str.$strSep;
  if ( $str==='' || is_null($str) ) return $strEnc.$strEnc.$strSep;
  $str = str_replace('&nbsp;',' ',$str);
  $str = QTconv($str,'-4');
  $str = str_replace($strSep,$strSepAlt,$str);
  $str = str_replace($strEnc,$strEncAlt,$str);
  return $strEnc.$str.$strEnc.$strSep;
}

// --------

function UseModule($strName=null)
{
  if ( !is_string($strName) ) die('UseModule: arg #1 must be a string');
  if ( isset($_SESSION[QT]['module_'.$strName]) ) return TRUE;
  return FALSE;
}