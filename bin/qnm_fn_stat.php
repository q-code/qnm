<?php

// QNM  1.0 build:20130410

function GetSerie($arrX,$arrXs,$y,$intMaxBt)
{
  $arrValues = array();
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
  {
  if ( isset($arrX[$y][$intBt]) ) { $arrValues[$intBt]=$arrX[$y][$intBt]; } else { $arrValues[$intBt]='&middot;'; }
  }
  if ( isset($arrXs[$y]) ) $arrValues[$intMaxBt+1]='<span class="bold">'.$arrXs[$y].'</span>';
  return $arrValues;
}

function GetSerieDelta($arrX,$arrXs,$y,$intMaxBt,$bPercent=false)
{
  $arrValues = array();
  for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
  {
    $arrValues[$intBt] = '0';
    $i = QTtrend((isset($arrX[$y][$intBt]) ? $arrX[$y][$intBt] : 0),(isset($arrX[$y-1][$intBt]) ? $arrX[$y-1][$intBt] : 0),$bPercent);
    if ( isset($i) )
    {
      if ( $i>0 ) $arrValues[$intBt] = '<span style="color:green">'.'+'.$i.($bPercent ? '%' : '').'</span>';
      if ( $i<0 ) $arrValues[$intBt] = '<span style="color:red">'.$i.($bPercent ? '%' : '').'</span>';
    }
    else
    {
      $arrValues[$intBt] = '&middot;';
    }
  }
  $arrValues[$intMaxBt+1] = '<span class="bold">0</span>';
  $i = QTtrend($arrXs[$y],$arrXs[$y-1],$bPercent);
  if ( isset($i) )
  {
    if ( $i>0 ) $arrValues[$intMaxBt+1] = '<span class="bold" style="color:green">'.'+'.$i.($bPercent ? '%' : '').'</span>';
    if ( $i<0 ) $arrValues[$intMaxBt+1] = '<span class="bold" style="color:red">'.$i.($bPercent ? '%' : '').'</span>';
  }
  else
  {
    $arrValues[$intMaxBt+1] = '&middot;';
  }
  return $arrValues;
}

function GetAbscise($strTime='m',$intMaxBt=13,$strTendaysago=-10)
{
  global $L;
  $arr = array();
  switch($strTime)
  {
  case 'q': for ($i=1;$i<=$intMaxBt;$i++) { $arr[$i]='Q'.$i; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;$i++) { $arr[$i]=substr($L['dateMM'][$i],0,2); } break; // 2 chars only
  case 'd': for ($i=1;$i<=$intMaxBt;$i++) { $arr[$i]=substr(DateAdd($strTendaysago,$i,'day'),-2,2); } break;
  }
  return $arr;
}

function CheckChartCache($Cache)
{
  $intHandle = opendir('pChart/Cache');
  $i = 0;
  while ( false!==($strFile = readdir($intHandle)) ) $i++;
  closedir($intHandle);
  if ( $i>60 || isset($_GET['clearcache']) ) $Cache->ClearCache();
}