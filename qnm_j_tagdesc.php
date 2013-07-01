<?php

// QNM build:20130410

if ( !isset($_POST['val']) ) exit;
if ( empty($_POST['val']) ) exit;
$strKey = strtoupper(strip_tags($_POST['val']));
if ( !isset($_POST['lang']) ) exit;
if ( empty($_POST['lang']) ) exit;
$strLang = strtolower(strip_tags($_POST['lang']));
$strSec = '*';
if ( isset($_POST['s']) ) $strSec = strip_tags($_POST['s']);

include 'bin/qnm_fn_tags.php';

// search in specific (if value provided)

if ( $strSec!='*' )
{
  $arrTags = TagsRead($strLang,$strSec);
  if ( count($arrTags)>0 )
  {
    $arrTags = array_change_key_case($arrTags, CASE_UPPER);
    if ( isset($arrTags[$strKey]) )
    {
      echo utf8_encode($arrTags[$strKey]);
      exit;
    }
  }
}

// search in common

  $arrTags = TagsRead($strLang,'*');
  if ( count($arrTags)>0 )
  {
    $arrTags = array_change_key_case($arrTags, CASE_UPPER);
    if ( isset($arrTags[$strKey]) )
    {
      echo utf8_encode($arrTags[$strKey]);
      exit;
    }
  }

// search others

for ($i=0;$i<20;$i++)
{
  $arrTags = TagsRead($strLang,$i);
  if ( count($arrTags)>0 )
  {
    $arrTags = array_change_key_case($arrTags, CASE_UPPER);
    if ( isset($arrTags[$strKey]) )
    {
      echo utf8_encode($arrTags[$strKey]);
      exit;
    }
  }
}

// No result

if ( isset($_POST['na']) ) echo utf8_encode($_POST['na']);