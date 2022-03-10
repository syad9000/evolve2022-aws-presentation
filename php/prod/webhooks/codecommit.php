<?php
#
# Turn PHP error reporting on|off
#error_reporting(E_ALL);
#ini_set( 'display_errors', '1' );
#
# Get data
if(!isset($settings))
	logger_exit("No settings found");
#
# Make sure we have the data we want in our POST request.
if( $settings["type"] === "codecommit" && isset($settings["folder"]) ){
	try {
		$repofolder = WEBSITES . DIRECTORY_SEPARATOR . $settings["folder"];
		$configxml = $repofolder . "/config.xml";
		
		if(!is_dir($repofolder))
			create_repo_folder($repofolder);
			
		if(!is_file($configxml))
			logger("File config.xml not found in " . $settings["folder"] . " add this file if connecting to an S3 bucket");
		else {
			#
			# Variables set from local config XML file
			$info = simplexml_load_file($configxml);
			$remove_txt = Extractor::extract_from_xml($info, "/config/entry[@key='REMOVE_TXT']");
			$ignore = Extractor::extract_from_xml($info, "/config/entry[@key='IGNORE']");
			$feed = Extractor::extract_from_xml($info, "/config/entry[@key='RSS']");
			logger("Variables extracted from config.xml");
		}
		#
		# Shell script to update the local repo and force push to the CodeCommit Repo
		$shell_code_dest =  $repofolder . REPO_CODE_FILENAME;
		#
		# Update script will only be uploaded one time to the Git Repo folder.  
		# If it is desired that the script be recreated every time, then 
		# the if statement below should be broken out of the current if condition.
		if(!file_exists($shell_code_dest)){
			# 
			#
			# Templating function which applies local and global variables to a shell script.
			# Creating the Git repo code from the template using the variables needed.
			$shell_code = create_repo_code(REPO_TEMPLATE, [
				"repo_folder" => $settings["folder"],
				"repo_path" => __DIR__ . DIRECTORY_SEPARATOR . $settings["folder"],
				"repo_remote" => create_repo_url(REPO_REMOTE_LOCATION, $settings["folder"])
			]);
			#
			# Upload shell script with variables.
			if(!empty($shell_code) && !empty( $shell_code_dest )){
				logger(sprintf("Updating CodeCommit Repository. %s", REPO_TEMPLATE));
				file_put_contents($shell_code_dest, $shell_code);
				chmod($shell_code_dest, 0755);
				#
				# Execute shell script to push to the git repo. Add output to settings 
				logger("Executing git commands");
				$output = exec($shell_code_dest);
				logger("Output: " . $output);
			} else
				logger(sprintf("Could not output shell code. Either shell code is empty or dest: %s is wrong.\n# Check file: %s to make sure there is repo code.", $shell_code_dest, REPO_TEMPLATE));
		
		}
		#
		# Print the log
		logger_exit(sprintf("%s %s", (new DateTime("NOW", new DateTimeZone("America/New_York")))->format("Y-m-d h:i:s"), "Finished"));
	} catch (Exception $e){
		logger($e->getMessage());
	}
}
?>