<?php
# --------------------------------------------------------------------------------------------
#   ____      _ _           _   _              _                         
#  / ___|___ | | | ___  ___| |_(_)_   _____   / \   ___ ___ ___  ___ ___ 
# | |   / _ \| | |/ _ \/ __| __| \ \ / / _ \ / _ \ / __/ __/ _ \/ __/ __|
# | |__| (_) | | |  __/ (__| |_| |\ V /  __// ___ \ (_| (_|  __/\__ \__ \
#  \____\___/|_|_|\___|\___|\__|_| \_/ \___/_/   \_\___\___\___||___/___/
#
#               Open-source collections management software
#               Version 1.3
#				
# -------------------------------------------------------------------------------------------
# Need help setting up? Visit http://www.collectiveaccess.org/forum
#
# --------------------------------------------------------------------------------------------
# THE VALUES BELOW ***MUST*** BE CHANGED TO SUIT YOUR ENVIRONMENT
# --------------------------------------------------------------------------------------------

#
# The following settings allow CollectiveAccess to connect to its database. 
# These settings should have been given to you by your system administrator or hosting provider.
#
# __CA_DB_HOST__ = Database server host name (often 'localhost')
#
define("__CA_DB_HOST__", getenv('OPENSHIFT_MYSQL_DB_HOST').":".getenv('OPENSHIFT_MYSQL_DB_PORT') );

# __CA_DB_USER__ = Database login user name
#
define("__CA_DB_USER__", getenv('OPENSHIFT_MYSQL_DB_USERNAME') );

# __CA_DB_PASSWORD__ = Database login password
#
define("__CA_DB_PASSWORD__", getenv('OPENSHIFT_MYSQL_DB_PASSWORD') );

# __CA_DB_DATABASE__ = The name of your CollectiveAccess database
#
define("__CA_DB_DATABASE__", getenv('OPENSHIFT_APP_NAME') );

#
# __CA_APP_DISPLAY_NAME__ = the name of your system for display purposes
#
#		This value will be used on emails, on the login screen, in window titles, etc.
#
define("__CA_APP_DISPLAY_NAME__", "My First CollectiveAccess System");


#
# __CA_ADMIN_EMAIL__ = the email address to receive administrative messages from CollectiveAccess
#
define("__CA_ADMIN_EMAIL__", 'info@put-your-domain-here.com');


#
# The following settings define how CA sends email on your server. These settings
# should have been given to you by your system administrator or hosting provider. 
#
# If you have no idea what to set these values to leave them as-is. There's a
# chance the defaults will work.
#
# __CA_SMTP_SERVER__ = server to use for outgoing mail
#
define("__CA_SMTP_SERVER__", 'localhost');

# __CA_SMTP_PORT__ = port to use for outgoing mail
#
define("__CA_SMTP_PORT__", 25);


#
# If your outgoing (SMTP) mail server requires you to authenticate then you can set the login information
# below by uncommenting the __CA_SMTP_AUTH__, __CA_SMTP_USER__ and __CA_SMTP_PASSWORD__ lines and providing appropriate values
#
#define("__CA_SMTP_AUTH__", 'login');
#define("__CA_SMTP_USER__", 'my_smtp_username');
#define("__CA_SMTP_PASSWORD__", 'my_smtp_password');


# *** 
# __   __          _                _                  
# \ \ / /__  _   _( )_ __ ___    __| | ___  _ __   ___ 
#  \ V / _ \| | | |/| '__/ _ \  / _` |/ _ \| '_ \ / _ \
#   | | (_) | |_| | | | |  __/ | (_| | (_) | | | |  __/
#   |_|\___/ \__,_| |_|  \___|  \__,_|\___/|_| |_|\___|
#                                                     
# That's it! The settings here should be enough to get you up and running...
# If they're not you can find help at http://www.collectiveaccess.org/forum
#
# ***

# --------------------------------------------------------------------------------------------
# IF THE INSTALLER FAILS TO FIND THE SYSTEM CONFIGURATION OR SOME OF THE LESS COMMON 
# OPTIONS NEED CHANGING THEN YOU MAY NEED TO CHANGE ONE OR MORE SETTINGS UNDER THIS LINE
#
# Most of the time you won't need to change anything below
# --------------------------------------------------------------------------------------------

#
# __CA_QUEUE_ENABLED__ = enable background processing using the task queue
#
# The task queue allows users to push potentially long running processes, such as processing of large video and image files,
# into the "background," allowing users to continue working.
# 
# Set this to a non-zero value if you want to use the task queue. Be sure to configure the task 
# queue processing script to run (usually via CRON) if you set this option. Set up details are 
# available at http://docs.collectiveaccess.org
#
define("__CA_QUEUE_ENABLED__", 0);


#
# __CA_DEFAULT_LOCALE__ = The locale to use when no user-preferred locale has been set
#
# The locale set here is used in situations where no locale is specifically set by the user: prior to login
# or prior to setting your preferred locale in user preferences for the first time.
#
# You should set this to the locale in which your users generally work.
#
# Note that whatever value you set *MUST* be configured in your system, either by hand or by an installation
# profile. The default value is US/English, which exists in most configurations.
# 
define("__CA_DEFAULT_LOCALE__", "en_US");


#
# __CA_APP_NAME__ = a short name alphanumeric-only (no spaces or punctuation) name for this CollectiveAccess installation
#
# 		If you are running more than one instance of CollectiveAccess on the same server make sure each instance has its
#		own unique __CA_APP_NAME__ setting.  __CA_APP_NAME__ must include letters, numbers and underscores only - no spaces or punctuation!
#
#   	If you don't know what to set this to then leave it as is. The default "collectiveaccess" value is perfectly acceptable.
#
define("__CA_APP_NAME__", "collectiveaccess");


#
# __CA_BASE_DIR__ = the absolute server path to the directory containing your CollectiveAccess installation
#
#		The default value attempts to determine the path automatically. You should only change this if it's
#		failing to derive the correct value.
#
# 		If you must to set this manually, enter the correct directory but omit trailing slashes!
# 		For Windows hosts, use a notation similar to "C:/PATH/TO/COLLECTIVEACCESS"; do NOT use backslashes
#
define("__CA_BASE_DIR__", pathinfo(preg_replace("!/install|/viewers/apps!", "", $_SERVER['SCRIPT_FILENAME']), PATHINFO_DIRNAME));


#
# __CA_URL_ROOT__ = the root-relative URL path to your CollectiveAccess installation
#
#		The default value attempts to determine the relative URL path automatically. You should only change 
#		this if it's failing to derive the correct value.
#
#		If you must to set this manually leave the __CA_URL_ROOT_ *BLANK* if the CollectiveAccess directory is the 
#		web server root or in the root directory of a virtual host. If CollectiveAccess is in a subdirectory or
#		an alias is used to point the web server to the correct path, set '__CA_URL_ROOT__' to
#		the relative url path to the subdirectory; start the path with a slash ('/') but omit trailing slashes.
#
# 		Example: If CollectiveAccess will be accessed via http://www.mysite.org/apps/ca then __CA_URL_ROOT__ would be set to /apps/ca
#
define("__CA_URL_ROOT__", str_replace($_SERVER['DOCUMENT_ROOT'], '', __CA_BASE_DIR__));


#
# __CA_SITE_HOSTNAME__ = the hostname for your system
#
#		The default value attempts to determine the relative URL path automatically. You should only change 
#		this if it's failing to derive the correct value.
#
#		If you must set this manually, it must be the full host name. Do not include http:// or any other prefixes.
#
define("__CA_SITE_HOSTNAME__", $_SERVER['HTTP_HOST']);


#
# You can set your time zone here. The default is to use US Eastern Standard Time. 
# You can, and should, set an explicit time zone if you are not in the Eastern United States.
# A list of valid time zone settings is available at http://us3.php.net/manual/en/timezones.php
#
date_default_timezone_set('America/New_York');


#
# If you use PayPal for e-commerce payments then you can set your account information 
# below by uncommenting the __CA_PAYPAL* lines and assigning them appropriate values
#
#define("__CA_PAYPAL_API_USERNAME__", "your_paypal_api_username");
#define("__CA_PAYPAL_API_PASSWORD__", "your_paypal_api_password");
#define("__CA_PAYPAL_API_SIGNATURE__", "your_paypal_api_signature");
#define("__CA_PAYPAL_API_ENVIRONMENT__", "sandbox");		// "sandbox" for testing or "live" for production use


# --------------------------------------------------------------------------------------------
# IT IS VERY UNLIKELY THAT YOU WILL NEED TO CHANGE ANYTHING UNDER THIS LINE
# --------------------------------------------------------------------------------------------

# Only MySQL databases are currently supported so leave this set to 'mysql'!
define("__CA_DB_TYPE__", 'mysql');

# Path to CollectiveAccess 'app' directory 
define("__CA_APP_DIR__", __CA_BASE_DIR__."/app");

# Path to CollectiveAccess 'models' directory containing database table model classes
define("__CA_MODELS_DIR__", __CA_APP_DIR__."/models");

# Path to CollectiveAccess 'lib' directory containing software libraries CA needs to function
define("__CA_LIB_DIR__", __CA_APP_DIR__."/lib");

# Path to CollectiveAccess 'lib' directory containing software libraries CA needs to function
define("__CA_CONF_DIR__", __CA_APP_DIR__."/conf");

# Path to local config directory - configuration containing installation-specific configuration
# Note that this is not the same as the __CA_CONF_DIR__, which contains general application configuration
# Installation-specific configuration simply allows you to override selected application configuration as-needed without having to modify the stock config
define("__CA_LOCAL_CONFIG_DIRECTORY__", __CA_CONF_DIR__."/local");

# Set path to instance configuration file
# (If you want to run several CA distinct instances using a single install
#  you can add additional configuration files here. See http://trac.collectiveaccess.org/wiki/MultipleInstances)
$_CA_INSTANCE_CONFIG_FILES = array(
	'_default_'	=> __CA_CONF_DIR__.'/app.conf'	// the _default_ value must always be defined
);

if (!isset($_SERVER['HTTP_HOST']) || !isset($_CA_INSTANCE_CONFIG_FILES[$_SERVER['HTTP_HOST']]) || !($_CA_CONFIG_PATH = $_CA_INSTANCE_CONFIG_FILES[$_SERVER['HTTP_HOST']])) {
	$_CA_CONFIG_PATH = $_CA_INSTANCE_CONFIG_FILES['_default_'];
} 

if (!(file_exists($_CA_CONFIG_PATH))) {
	$opa_error_messages = array("Configuration files are missing for hostname '".$_SERVER['HTTP_HOST']."'!<br/>Please check the <em>__CA_BASE_DIR__</em> configuration setting in your <em>setup.php</em> file.");
	if (!include_once("./themes/default/views/system/configuration_error_html.php")) {
		die("Fatal error: Configuration files are missing for hostname '".$_SERVER['HTTP_HOST']."'! Please check the __CA_BASE_DIR__ configuration setting in your setup.php file.");
	}
	exit();
}

set_include_path(__CA_LIB_DIR__.'/ca'.PATH_SEPARATOR.__CA_LIB_DIR__.'/core'.PATH_SEPARATOR.__CA_MODELS_DIR__.PATH_SEPARATOR.get_include_path());

# The path to the main instance configuration file defined as a constant
define('__CA_APP_CONFIG__', $_CA_CONFIG_PATH);

# --------------------------------------------------------------------------------------------

# include version number
require_once(__CA_APP_DIR__.'/version.php');

# includes commonly used classes
require_once(__CA_APP_DIR__.'/helpers/preload.php');

if (defined('E_DEPRECATED')) {	// PHP 5.3/5.4
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
} else {	// PHP <= 5.2
	error_reporting(E_ALL & ~E_NOTICE);
}

# If you set this to true the installer will be able to overwrite an existing installation.
# Only set this to true if you're debugging code or an installation profile.
# Leaving it set to 'true' is a major security hole - it will allow *ANYONE* with access to the installer 
# to destroy your database!
#
# Note that in overwriting your database you will destroy *all* data in the database 
# including any non-CollectiveAccess tables. Use this option at your own risk!
define('__CA_ALLOW_INSTALLER_TO_OVERWRITE_EXISTING_INSTALLS__', false);

# If you set this to true the configuration settings are loaded from the plain text
# files in __CA_CONF_DIR__ on each page refresh. That's why performance will suffer.
# However, there are certain scenarios where want to prevent caching, e.g.
# if you want to load different configuration file sets depending on the
# location of the user. DO NOT touch this unless you know what you're doing!
define('__CA_DISABLE_CONFIG_CACHING__', false);

# If you set this to true and your database needs updating (typically after a code update)
# then the web-base "database out-of-date" warning will offer an automated update option. This
# can be a very convenient way to update your database but could present a mild security risk if your system
# is publicly accessible on the internet. The risk is that by exposing the update control in a public url on
# a publicly accessible site you are potentially allowing anyone to initiate the database update. That's all they
# can do, which in and of itself should not be harmful, but some system administrators may not be 
# comfortable with it. 
define('__CA_ALLOW_AUTOMATIC_UPDATE_OF_DATABASE__', true);

/* ----------------------------------------------------------------------
 * setup.php : 	defines paths for CollectiveAccess Providence cataloguing module, enables 
 *				the application to find various support files. This is the first file 
 *				you should set up when configuring the application.
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2008-2013 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
?>
