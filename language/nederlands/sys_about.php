<?php

echo '<p class="bold">Applicatie eigenaar</p>
<p>',$_SESSION[QT]['site_name'],'<br />Webmaster: <a href="mailto:',$_SESSION[QT]['admin_email'],'">',$_SESSION[QT]['admin_email'],'</a><br />Contact: ',$_SESSION[QT]['admin_name'],' ',$_SESSION[QT]['admin_addr'],' ',$_SESSION[QT]['admin_fax'],'</p>

<p class="bold">Applicatie gemaakt door</p>
<p>QT-cute (www.qt-cute.org) versie ',QNMVERSION,'</p>

<p><b>Vergunning (engels)</b></p>
<p><img src="admin/vgplv3.png" width="88" height="31" alt="GPL" title="GNU General Public License"/><br />Zie documenten <a href="admin/license.txt">Application License</a> en <a href="admin/license_gpl.txt">GNU General Public License</a> voor meer informatie.

<p class="bold">Naleving</p>
';