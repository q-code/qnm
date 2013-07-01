<?php // v 1.0 build:20130410

session_start();
$strAppl = 'Q-NetManagement 1.0';
if ( !isset($_SESSION['boardmail']) ) $_SESSION['boardmail']='';
if ( !isset($_SESSION['qnm_setup_lang']) ) $_SESSION['qnm_setup_lang']='en';


$arrLangs = array();
$arrLangs['en'] = 'English';
if ( file_exists('qnm_lang_fr.php')  ) $arrLangs['fr'] = 'Fran&ccedil;ais';
if ( file_exists('qnm_lang_nl.php')  ) $arrLangs['nl'] = 'Nederlands';
if ( file_exists('qnm_lang_it.php')  ) $arrLangs['it'] = 'Italiano';
if ( file_exists('qnm_lang_es.php')  ) $arrLangs['es'] = 'Espa&ntilde;ol';
if ( file_exists('qnm_lang_de.php')  ) $arrLangs['de'] = 'Deutsche';
if ( file_exists('qnm_lang_pt.php')  ) $arrLangs['pt'] = 'Portuguese';

// --------
// Html start
// --------

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
<head>
<title>',$strAppl,'</title>
<meta charset="windows-1252" />
<link rel="stylesheet" type="text/css" href="qnm_setup.css"/>
</head>
<body>

<!-- PAGE CONTROL -->
<div class="page">
<!-- PAGE CONTROL -->
';

echo '
<!-- HEADER BANNER -->
<div class="banner">
<div class="banner_in">
<img src="i_logo.gif" style="border-width:0; width:175px; height:50px" alt="QNM" title="Q-NetManagement"/>
</div>
</div>
<!-- END HEADER BANNER -->
';

echo '
<!-- BODY MAIN -->
<table>
<tr>
<td style="padding:10px">
<!-- BODY MAIN -->
';

echo '<h1>',$strAppl,'</h1>';
echo '<h2>Language ?</h2>';
echo '
<form method="get" action="qnm_setup_1.php">
<select name="language" size="1">';
foreach($arrLangs as $strKey=>$strLang) echo '<option value="',$strKey,'"',($_SESSION['qnm_setup_lang']==$strKey ? ' selected="selected"' : ''),'>',$strLang,'</option>';
echo '</select>
<input type="submit" name="ok" value="Ok"/>
</form>
';

// --------
// HTML END
// --------

echo '
<!-- END BODY MAIN -->
</td>
</tr>
</table>
<!-- END BODY MAIN -->

<p style="text-align:right; margin:10px">powered by <a href="http://www.qt-cute.org" class="footer_copy">QT-cute</a></p>

<!-- END PAGE CONTROL -->
</div>
<!-- END PAGE CONTROL -->

</body>
</html>';