<?php

echo '<p class="bold">Application g&eacute;r&eacute;e par</p>
<p>',$_SESSION[QT]['site_name'],'<br />Webmaster: <a href="mailto:',$_SESSION[QT]['admin_email'],'">',$_SESSION[QT]['admin_email'],'</a><br />Contact: ',$_SESSION[QT]['admin_name'],' ',$_SESSION[QT]['admin_addr'],' ',$_SESSION[QT]['admin_fax'],'</p>

<p class="bold">Application cr&eacute;&eacute;e par</p>
<p>QT-cute (www.qt-cute.org) version ',QNMVERSION,'</p>

<p class="bold">Licences (anglais)</p>
<p><img src="admin/vgplv3.png" width="88" height="31" alt="GPL" title="GNU General Public License"/><br />Voyez les documents <a href="admin/license.txt">Application License</a> et <a href="admin/license_gpl.txt">GNU General Public License</a> pour plus amples informations.</p>

<p class="bold">Conformit&eacute;s</p>
';