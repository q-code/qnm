<?php

// QNM  1.0 build:20130410
function SqlSetting($key,$val)
{
  if ( !is_string($key) || !isset($_SESSION[QT][$key]) ) die('SqlSetting: Invalid key');
  global $oDB;
  $_SESSION[QT][$key] = (string)$val;
  // for module setting, use delete/insert
  if ( substr($key,0,2)=='m_' )
  {
    $oDB->Query('DELETE FROM '.TABSETTING.' WHERE param="'.$key.'"');
    return $oDB->Query('INSERT INTO '.TABSETTING.' VALUES ("'.$key.'","'.$_SESSION[QT][$key].'")');
  }
  return $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT][$key].'" WHERE param="'.$key.'"');
}

function LimitSQL($strState,$strOrder,$intStart=0,$intLength=50,$intCount=50)
{
  global $oDB;
  $strOrder = trim($strOrder); if ( substr($strOrder,-3,3)!="ASC" && substr($strOrder,-4,4)!="DESC" ) $strOrder .= " ASC";
  switch (substr($oDB->type,0,5))
  {
  case 'mysql':
    return "SELECT $strState ORDER BY $strOrder LIMIT $intStart,$intLength";
    break;
  case 'mssql':
    if ($intStart==0)
    {
      $strQ = "SELECT TOP $intLength $strState ORDER BY $strOrder";
    }
    elseif ( ($intLength+$intStart)>$intCount )
    {
      $strInvor = $strOrder;
      if ( substr($strOrder,-4,4)==' ASC' ) $strInvor=str_replace(' ASC',' DESC',$strOrder);
      if ( substr($strOrder,-4,4)=='DESC' ) $strInvor=str_replace(' DESC',' ASC',$strOrder);
      $intLast = $intCount-$intStart;
      $strQ = "SELECT TOP $intLast $strState ORDER BY $strInvor";
      $strOrder = str_replace('t.','',$strOrder); // to support t1,t2
      $strInvor = str_replace('t.','',$strInvor); // to support t1,t2
      $strQ = "SELECT * FROM ($strQ) As t1 ORDER BY $strOrder";
    }
    else
    {
      $strInvor = $strOrder;
      if ( substr($strOrder,-4,4)==' ASC' ) $strInvor=str_replace(' ASC',' DESC',$strOrder);
      if ( substr($strOrder,-4,4)=='DESC' ) $strInvor=str_replace(' DESC',' ASC',$strOrder);
      $invlength = $intLength+$intStart;
      $intFinaltop = $invlength; if ( ($intLength+$intStart)>$intCount ) $intFinaltop = $intCount-$intLength;
      $strQ = "SELECT TOP $invlength $strState ORDER BY $strOrder";
      $strOrder = str_replace('t.','',$strOrder); // to support t1,t2
      $strInvor = str_replace('t.','',$strInvor); // to support t1,t2
      $strQ = "SELECT * FROM (SELECT TOP $intLength * FROM ($strQ) As t1 ORDER BY $strInvor) As t2 ORDER BY $strOrder";
    }
    return $strQ;
    break;
  case 'pg':
    return "SELECT $strState ORDER BY $strOrder LIMIT $intLength OFFSET $intStart";
    break;
  case 'ibase':
    return "SELECT FIRST $intLength SKIP $intStart $strState ORDER BY $strOrder";
    break;
  case 'sqlit':
    return "SELECT $strState ORDER BY $strOrder LIMIT $intLength OFFSET $intStart";
    break;
  case 'db2':
    if ($intStart==0)
    {
      return "SELECT $strState ORDER BY $strOrder FETCH FIRST $intLength ROWS ONLY";
    }
    else
    {
      return "SELECT * FROM (SELECT ROW_NUMBER() OVER() AS RN, $strState) AS cols WHERE RN BETWEEN ($intStart+1) AND ($intStart+1+$intLength)";
    }
    break;
  case 'acces':
    if ($intStart==0)
    {
      $strQ = "SELECT TOP $intLength $strState ORDER BY $strOrder";
    }
    elseif ( ($intLength+$intStart)>$intCount )
    {
      $strInvor = $strOrder;
      if ( substr($strOrder,-4,4)==' ASC' ) $strInvor=str_replace(' ASC',' DESC',$strOrder);
      if ( substr($strOrder,-4,4)=='DESC' ) $strInvor=str_replace(' DESC',' ASC',$strOrder);
      $invlength = $intLength+$intStart;
      $intFinaltop = $intCount-$intLength;
      $strQ = "SELECT TOP $invlength $strState ORDER BY $strOrder";
      $strOrder = str_replace('t.','',$strOrder); // to support t1,t2
      $strInvor = str_replace('t.','',$strInvor); // to support t1,t2
      $strQ = "SELECT * FROM (SELECT TOP $intFinaltop * FROM ($strQ) As t1 ORDER BY $strInvor) As t2 ORDER BY $strOrder";
    }
    else
    {
      $strInvor = $strOrder;
      if ( substr($strOrder,-4,4)==' ASC' ) $strInvor=str_replace(' ASC',' DESC',$strOrder);
      if ( substr($strOrder,-4,4)=='DESC' ) $strInvor=str_replace(' DESC',' ASC',$strOrder);
      $invlength = $intLength+$intStart;
      $strQ = "SELECT TOP $invlength $strState ORDER BY $strOrder";
      $strOrder = str_replace('t.','',$strOrder); // to support t1,t2
      $strInvor = str_replace('t.','',$strInvor); // to support t1,t2
      $strQ = "SELECT * FROM (SELECT TOP $intLength * FROM ($strQ) As t1 ORDER BY $strInvor) As t2 ORDER BY $strOrder";
    }
    return $strQ;
    break;
  case 'oci':
    if ($intStart==0)
    {
      return "SELECT * FROM (SELECT $strState ORDER BY $strOrder) WHERE ROWNUM<=$intLength";
    }
    else
    {
      return "SELECT * FROM (SELECT a.*, rownum RN FROM (SELECT $strState ORDER BY $strOrder) a WHERE rownum<$intStart+1+$intLength) WHERE rn>=$intStart";
    }
    break;
  default: die('Unknown db type '.$oDB->type);
  }
}

// --------

function FirstCharCase($strField,$strCase='u')
{
  global $oDB;
  Switch (substr($oDB->type,0,5))
  {
  case 'mysql':
    if ( $strCase=='u' ) return "UPPER(LEFT($strField,1))";
    if ( $strCase=='l' ) return "LOWER(LEFT($strField,1))";
    if ( $strCase=='a-z' ) return "UPPER($strField) NOT REGEXP '^[A-Z]'";
    break;
  case 'mssql':
    if ( $strCase=='u' ) return "UPPER(LEFT($strField,1))";
    if ( $strCase=='l' ) return "LOWER(LEFT($strField,1))";
    if ( $strCase=='a-z' ) return "(ASCII(UPPER(LEFT($strField,1)))<65 OR ASCII(UPPER(LEFT($strField,1)))>90)";
    break;
  case 'acces':
    if ( $strCase=='u' ) return "UCASE(LEFT($strField,1))";
    if ( $strCase=='l' ) return "LCASE(LEFT($strField,1))";
    if ( $strCase=='a-z' ) return "(ASC(UCASE(LEFT($strField,1)))<65 OR ASC(UCASE(LEFT($strField,1)))>90)";
    break;
  case 'pg':
    if ( $strCase=='u' ) return "UPPER(SUBSTRING($strField,1,1))";
    if ( $strCase=='l' ) return "LOWER(SUBSTRING($strField,1,1))";
    if ( $strCase=='a-z' ) return "UPPER($strField) !~ '^[A-Z]'";
    break;
  case 'ibase':
    if ( $strCase=='u' ) return "UPPER(SUBSTRING($strField FROM 1 FOR 1))";
    if ( $strCase=='l' ) return "LOWER(SUBSTRING($strField FROM 1 FOR 1))";
    if ( $strCase=='a-z' ) return "(UPPER(SUBSTRING($strField FROM 1 FOR 1))<'A' OR UPPER(SUBSTRING($strField FROM 1 FOR 1))>'Z')";
    break;
  case 'sqlit':
    if ( $strCase=='u' ) return "UPPER(SUBSTR($strField,1,1))";
    if ( $strCase=='l' ) return "LOWER(SUBSTR($strField,1,1))";
    if ( $strCase=='a-z' ) return "(UPPER(SUBSTR($strField,1,1))<'A' OR UPPER(SUBSTR($strField,1,1))>'Z')";
    break;
  case 'db2':
    if ( $strCase=='u' ) return "UPPER(SUBSTR($strField,1,1))";
    if ( $strCase=='l' ) return "LOWER(SUBSTR($strField,1,1))";
    if ( $strCase=='a-z' ) return "(ASCII(UPPER(SUBSTR($strField,1,1)))<65 OR ASCII(UPPER(SUBSTR($strField,1,1)))>90)";
    break;
  case 'oci':
    if ( $strCase=='u' ) return "UPPER(SUBSTR($strField,1,1))";
    if ( $strCase=='l' ) return "LOWER(SUBSTR($strField,1,1))";
    if ( $strCase=='a-z' ) return "(ASCII(UPPER(SUBSTR($strField,1,1)))<65 OR ASCII(UPPER(SUBSTR($strField,1,1)))>90)";
    break;
  default: die('Unknown db type '.$oDB->type);
  }
}

// --------

function SqlDateCondition($strDate='',$strField='firstpostdate',$intLength=4,$strComp='=')
{
  // Creates a where close for a date field. strDate can be an integer or the string 'old' (5 years or more)
  global $oDB;
  if ( $strDate==='old' ) { $strDate = '<"'.(Date('Y')-4).'"'; } else { $strDate = $strComp.'"'.$strDate.'"'; }
  switch(substr($oDB->type,0,5))
  {
  case 'mysql': return 'LEFT('.$strField.','.$intLength.')'.$strDate; break;
  case 'mssql': return 'LEFT('.$strField.','.$intLength.')'.$strDate; break;
  case 'pg':    return 'SUBSTRING('.$strField.',1,'.$intLength.')'.$strDate; break;
  case 'ibase': return 'SUBSTRING('.$strField.' FROM 1 FOR '.$intLength.')'.$strDate; break;
  case 'sqlit': return 'SUBSTR('.$strField.',1,'.$intLength.')'.$strDate; break;
  case 'acces': return 'LEFT('.$strField.','.$intLength.')'.$strDate; break;
  case 'db2':   return 'SUBSTR('.$strField.',1,'.$intLength.')'.$strDate; break;
  case 'oci':   return 'SUBSTR('.$strField.',1,'.$intLength.')'.$strDate; break;
  default: die('Unknown db type '.$oDB->type);
  }
}