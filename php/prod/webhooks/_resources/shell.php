#!/bin/bash

function create_git_ignore {
	#
	# Create a .gitignore file with default configuration.
	# We want to ignore PHP files
	echo "#
# General
.DS_Store
#
# Scripts
*.php
*.sh
" > $git_repository_dir/.gitignore
}

function join { 
	local IFS="$1"; shift; echo "$*"; 
}
#
#
git_repository_dir="<?php echo $repo_path;?>"
#
# should always be .git
git="$git_repository_dir/.git"
#
# The git remote URL for updating the repository.. for prod you will need to add the folder name at the end.	
git_remote="<?php echo $repo_remote; ?>"
output=('{"git_remote":"<?php echo $repo_remote; ?>"}')
first_run=""
#
# initialize empty git repository if it doesn't exist
if [ ! -f $git ]
then
	first_run="yes"
	cd $git_repository_dir
    git init
	git config credential.helper store
	#
	# Create the .gitignore file
	create_git_ignore
fi

#
# Configure remote git repository
git_remote_test=$(git config --get remote.origin.url)
#
# An Array for printing our output
output+=('{"checking":"<?php echo $repo_folder; ?>"}')
#
#
if [ ! $git_remote_test ]
then
    	git remote add origin $git_remote
		output+=('{"message":"Added remote <?php echo $repo_folder; ?>"}')
fi
#
#
output+=('{"message":"git add ."}')
git add .
#
output+=('{"message":"git commit"}')
git commit -m "Webhook Update - $(date +%Y-%m-%dT%H:%I:%S)"
#
# Force Updates
output+=('{"message":"git push -u origin master --force"}')
git push -u origin master --force

#
output+=('{"message":"Update ' "$(date +%Y-%m-%dT%H:%I:%S)"'"}')

#
# Printing our output
str=$(join , ${output[@]})
echo [ $str ]