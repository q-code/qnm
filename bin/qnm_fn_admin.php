<?php

function arrShift($arr,$oObj,$strDir)
{
  // Shifts an element up/down in the list. Keys are changed into numeric (0..n) except when $oObj is not found or impossible to move
  $arrS = array_values($arr); // Keys are replaced by an integer (0..n)
  $i = array_search($oObj,$arrS); // Search postition of $oObj, false if not found
  if ( $i===FALSE ) return $arr;
  if ( $i==0 && $strDir=='up' ) return $arr;
  if ( $i==(count($arr)-1) && $strDir=='down' ) return $arr;
  $arrO = $arrS;
  $intDir = ($strDir=='up' ? -1 : 1);
  $arrO[$i+$intDir] = $arrS[$i];
  $arrO[$i] = $arrS[$i+$intDir];
  return $arrO;
}
