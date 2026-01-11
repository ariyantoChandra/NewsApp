<?php
#region ADDRESS
define("API_ADDRESS","http://localhost/NewsApp/Backend/APP/");
define("IMAGE_DATABASE_ADDRESS",API_ADDRESS . "DATABASE/IMAGE/"); // API Address
#endregion

#region VALUES
define("ACCOUNT_ROLES",array("USER","WRITER","ADMIN"));
define("USERNAME_MAX_LENGTH",30);
define("FULLNAME_MAX_LENGTH",200);
define("USER_GENDERS",array("MALE","FEMALE","OTHER"));
define("MEDIA_TYPE",array("NEWS","JOURNAL","BLOG","TV","RADIO","PUBLISHER"));
#endregion

#region REGEX
define('REGEX_DATE_TIME', '/^(19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) ([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/');
define('REGEX_DATE','/^(19|20)\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/');
#endregion
