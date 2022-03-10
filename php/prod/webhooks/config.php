<?php
#
# Set __ROOT__ to the path that the path that the
# files used by listen.html are in, not necessarily to the path that
# this file is in.
define("__ROOT__", $_SERVER["HOME"] . "/www");
#
# The main file defining the valid services that listen.html will respond to.
define("SERVICES", __DIR__ . "/services.json");
#
# Location where the websites uploaded to Amazon Web Services are stored.
define("WEBSITES", __DIR__ . "/websites");
#
# S3 Bucket where log files are stored
define("S3LOGS", "omni-logs");
#
# A file used by listen.html in case there are any errors on the POST request.
define("ERRORLOG", __DIR__ . "/error.log");
#
# The following two variables defined aren't being used, but you can create a custom HTTP Error log if you want.
#
# Place that output_log() dumps the JSON log file
define("JSONLOG", __DIR__ . "/out.json");
#
# Place that output_log() dumps the HTML log file
define("HTMLLOG", __DIR__ . "/out.html");
#
# 
define("LOCAL_CONFIG", "/config.xml");
#
# OTHER FILES
define("RESOURCES", __DIR__ . "/_resources");
define("REPO_TEMPLATE", RESOURCES  . "/shell.php");
define("REPO_CODE_FILENAME", "/update.sh");
#
# AWS CodeCommit repo. For production use.
define("REPO_REMOTE_LOCATION", "ssh://git-codecommit.us-east-1.amazonaws.com/v1/repos");
#
#
define("SENDER", "webmaster@example.edu");