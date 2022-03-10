# Production Web Server Setup

## You will need to create a site as a "data dispatch server". This site will listen to posts from your various sites, either GET the data
via HTTP requests, or serve as a pseudo production server where your files will be uploaded in preparation for the move to AWS.

There is some setup that you will need to do for each site depending on whether you are using CodeCommit or S3 to upload your files to 
Amazon.  There are about three scenarios that I can come up with and the setup is different for each.

In all of these, your current *production server* will become the *staging server*. They get there either by changing your site
publish settings, or by  HTTP GET requests which download the files

### Sample 'data dispatch server' structure:
```
/
|_ index.pcf
|_ _resources
	|_php
		|_ functions.php # common functions that all your scripts use		
		|_ JWT # Extending functionality of Firebase/JWT
		|_ Email # Extending functionality of PHPMailer
		|_ XML # Utility functions to get values from XML config files
		|_ AWS # Libraries for uploading stuff to CodeCommit and S3
	|_ xsl
	|_ ou
	|_ assets
		
|_ codecommit
	|_ clear.php # for clearing logs
	|_ index.php # End point for handling all S3 posts. Can either be called directly by the /webhooks/index.php or some other way such as JWT
	|_ log.php # For viewing the logs
	|_ config.php # Global configuration variables
	|_ _websites # containing various folder names and configuration XML files for sites or files that you are storing on AWS
	
|_ s3
	|_ clear.php # for clearing logs
	|_ index.php # End point for handling all S3 posts. Can either be called directly by the /webhooks/index.php or some other way such as JWT
	|_ log.php # For viewing the logs
	|_ config.php # Global configuration variables
	|_ _websites # containing various folder names and configuration XML files for sites or files that you are storing on AWS
	
|_ webhooks # The initial endpoint where all your webhooks go
	|_ _resources/services.json # A JSON formatted file where all your authorized services go.
	|_ clear.php # for clearing logs
	|_ index.php # End point for handling all your posts. Reads the _resources/services.json to figure out what is authorized, also restricts based on the IP Address of the webhook's POST request
	|_ log.php # For viewing the logs
	|_ config.php # Global configuration variables

```
### Scenarios and different approaches
1. You want to upload to S3 directly, maybe you are only storing some files that you are serving on S3.
   i. Create a copy of your production site with a different name.
   ii. Point the 
2. You want to put an entire site on AWS Amplify
3. You want to put an entire site on Amazon S3
4. You want to put an entire site on Amazon S3 (But you don't want to change your site settings)

#### AWS Amplify simple website setup for 'www.example.edu' with OmniCMS account name 'Example'
1. Log in to CodeCommit
2. Create a repository called `example`
3. Log in to Amplify
4. Create a `New app` called `Example`
5. Link the repository to your CodeCommit repository
6. Data dispatch server
   1. Create folder `/codecommit/websites/example`
   2. Optionally create a .gitignore file for anything you don't want to publish to AWS.

### Amazon S3 simple site setup



## Setup AWS Access Keys for CodeCommit
https://docs.aws.amazon.com/IAM/latest/UserGuide/id\_credentials\_ssh-keys.html#ssh-keys-code-commit
https://docs.aws.amazon.com/codecommit/latest/userguide/setting-up-ssh-unixes.html

1. Log in to your production web server.
2. Generate a public key for AWS SSH access with ```ssh-keygen```
```
Enter file in which to save the key (/home/user-name/.ssh/id_rsa): Type /home/your-user-name/.ssh/ and a file name here, for example /home/your-user-name/.ssh/codecommit_rsa

Enter passphrase (empty for no passphrase): <Type a passphrase, and then press Enter>
Enter same passphrase again: <Type the passphrase again, and then press Enter>

Your identification has been saved in /home/user-name/.ssh/codecommit_rsa.
Your public key has been saved in /home/user-name/.ssh/codecommit_rsa.pub.
```
3. View the key with ```cat ~/.ssh/codecommit_rsa.pub``` and copy it to the clipboard
4. Upload the public SSH key to the AWS Console IAM Management Console
5. In the IAM console, in the navigation pane, choose Users, and from the list of users, choose your IAM user.
6. On the user details page, choose the Security Credentials tab, and then choose Upload SSH public key.
7. Paste the contents of your SSH public key into the field, and then choose Upload SSH public key.
8. Copy or save the information in SSH Key ID (for example, APKAEIBAERJR2EXAMPLE).
9. Return to your production web server and edit/create the ~/.ssh/config file, adding the sign-in parameters
```
Host git-codecommit.*.amazonaws.com
	User APKAEIBAERJR2EXAMPLE
	IdentityFile ~/.ssh/codecommit_rsa
```
10. Allow editing only by the owner with no execute permissions: ```chmod 600 config```
11. Test the configuration by typing ```ssh git-codecommit.us-east-1.amazonaws.com``` * Or whichever region you are working in


## Setting Up other PHP Libraries
You might want to go ahead and install some other libraries if you are using PHP to make your life easier.  These rely on the PHP Composer dependency manager
(https://getcomposer.org/download/)[https://getcomposer.org/download/]

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

### JSON Web Tokens
Needed if you are setting up separate services to talk to each other
```composer require firebase/php-jwt```

### AWS PHP SDK (required for some of the PHP libraries I use)
```composer require aws/aws-sdk-php```

### For sending PHP SMTP emails with a user-name and password
```composer require phpmailer/phpmailer```