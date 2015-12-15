Opencart Open Security module

This module provides several security methods for admin and user authorization pages:

1. Default opencart captcha for auth forms.
2. Automatically ip blacklist.

Features:

 - Settings for email alerts, captcha and blacklist.
 - Automatically unblocking ip after expiration time.

Installation

1. Make files and DB backup.
2. Unpack "upload" directory to root opencart folder.
3. Go to admin dashboard -> Menu -> Extensions -> Modules -> Install opensecurity module.

***Next files will be REPLACED***

/admin/controller/common/login.php

/admin/view/template/common/login.tpl

/catalog/controller/account/login.php

/catalog/view/theme/default/template/account/login.tpl

/catalog/controller/checkout/login.php

/catalog/view/theme/default/template/checkout/login.tpl

***Next files will be added:***

/admin/controller/module/opensecurity.php

/admin/view/template/module/opensecurity.tpl

/admin/language/english/module/opensecurity.php

/admin/language/russian/module/opensecurity.php

/system/library/opslib.php

/catalog/language/english/module/opensecurity.php

/catalog/language/russian/module/opensecurity.php

***If you found any bug, please contact me: http://openweb.tech/contacts/***
