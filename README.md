# Mediahub Preslog

* Developer: 4mation Technologies
* Author: Dave Newson <dave@4mation.com.au>

***

## Quick Start

0.  Install MongoDB (Quick crash course can be found at https://thenewboston.com/videos.php?cat=356&video=30008)
    Good idea to install the PHPStorm MongoDB plugin
    Get the MongoDB binary directory into your PATH so you can run the db server from anywhere, details in the videos above.
    
    Note, this uses an older MongoDB PHP Extension, running this locally will take some configuration.
    
    Windows: Go to {link to it here...} and install the .dll file (the version that matches your PHP version, rename it to php_mongo.dll and add the extension in php.ini)
             Create the folder C:\data\db\dump
             Copy the backed up database files and put them into the dump folder
             Navigate to the dump folder in bash and type the following:
```sh
$ mongorestore -d preslog ./
```

    To confirm that the DB is installed via the PHPStorm plugin under the right hand tab MongoExplorer (may need to refresh)

    Lastly, in /bin/cake/app/Config/database.php under the $development array, change the host to 'mongodb://127.0.0.:27017'

    Check that your APPLICATION_ENV var is working correctly too.

1.  Install Node.js
    See notes below for extra steps if using Windows Bash.

2.  Instll NodeJS modules
```sh
$ npm install -g grunt-cli bower karma
$ npm install
$ bower install
$ grunt watch
```

3.  If we're still using angular-placeholders, you may need to also:
```sh
$ cd vendor/angular-placeholders
$ npm install
$ grunt build
```

4.  Set up the following vhost rule for Apache:
```
<VirtualHost *:80>
    DocumentRoot "YOUR_PROJECT_DIRECTORY\build\webroot"
    ServerName local.preslog
    ErrorLog "logs/preslog-error.log"
    CustomLog "logs/preslog-access.log" combined
	SetEnv APPLICATION_ENV "local"
    SetEnv ENVIRONMENT "local"
</VirtualHost>
```

5.  Set up a Hosts rule for `127.0.0.1 local.preslog`

6.  Restart Apache, and go to http://local.preslog in your browser.

7. Copy bin/cakephp/app/Config/config.dist/xml to bin/cakephp/app/Config/config.xml and update the locations you want the logs to save.


## Deployment Notes

* Running `grunt watch` will watch individual files in your `/src` directory. When a change is made, the change is compiled to the `/build` directory.
* To build a minified copy of the site for deployment, execute `grunt` by itself. This will compile to the `/bin` directory.


## Project Notes

### Windows Machines
On windows it's great to do everything through the Bash prompt. You'll need to establish a few paths for this.
1.  Go to System > Advanced Config > Environment Variables
2.  edit Path and copy the contents. You will need to reformat the paths from `C:\directory\path` to `/c/directory/path`
3.  Add the following to `.bash_profile` (create it if it doesn't exist) in your user directory (eg. `C:/Users/Dave`)
```sh
export PATH="/C/Program Files/nodejs:/C/Users/_YOUR_USER_DIRECTORY_/AppData/Roaming/npm:$PATH"
export FIREFOX_BIN="/C/Program Files (x86)/Mozilla Firefox/firefox.exe"
```
5.  Save and restart Bash


### This project is build on:
* angular-js
* ng-boilerplate (http://joshdmiller.github.com/ng-boilerplate)
* CakePHP


### To install a new UI module, use Bower:
```sh
$ bower install YOUR_MODULE_NAME --save-dev
```
