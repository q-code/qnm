<?php

$strSubject=$_SESSION[QT]['site_name'].' - '.$L['Notification'];

$strMessage="
Please note that the element is now: %s
-------------------------------
%s
-------------------------------

Regards,
The webmaster of {$_SESSION[QT]['site_name']}
{$_SESSION[QT]['site_url']}/index.php
";