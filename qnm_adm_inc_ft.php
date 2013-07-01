<?php

// QNM 1.0 build:20130410

HtmlPageCtrl(END);

echo '<!-- END MENU/PAGE -->
</td>
</tr>
</table>
<!-- END MENU/PAGE -->
';

if ( isset($oDB->stats) )
{
  $oDB->stats['end']=gettimeofday(true);
  echo '<br/>&nbsp;',$oDB->stats['num'],' queries in ',round($oDB->stats['end']-$oDB->stats['start'],3),' sec';
}

echo '
<div id="pagedialog"></div>
';

// CDN fallback

if ( isset($oHtml->scripts['jquery']) )
{
echo '
<!-- Jquery CDN fallback -->
<script type="text/javascript">
<!--
window.jQuery || document.write(\'<link rel="stylesheet" type="text/css" href="'.JQUERYUI_CSS_OFF.'" /><script type="text/javascript" src="'.JQUERY_OFF.'"></script><script type="text/javascript" src="'.JQUERYUI_OFF.'"></script>\');
//-->
</script>
';
}

echo $oHtml->End();

ob_end_flush();