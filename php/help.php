<?php
$this->default['host']     = getenv("OPENSHIFT_DB_HOST");
$this->default['port']     = getenv("OPENSHIFT_DB_PORT");
$this->default['login']    = getenv("OPENSHIFT_DB_USERNAME");
$this->default['password'] = getenv("OPENSHIFT_DB_PASSWORD");
$this->default['database'] = getenv("OPENSHIFT_APP_NAME")
Echo "Hello World";
echo "$host";

echo "$port";
?>
