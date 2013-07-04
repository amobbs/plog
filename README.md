# Mediahub Preslog

Developed by 4mation Technologies

***

## Quick Start

Install Node.js and then:

```sh
$ npm install -g grunt-cli bower karma
$ bower install
$ grunt watch
```

Set up the following vhost rule:
```
<VirtualHost *:80>
    DocumentRoot "YOUR_PROJECT_DIRECTORY\build"
    ServerName local.preslog
    ErrorLog "logs/preslog-error.log"
    CustomLog "logs/preslog-access.log" combined
	SetEnv APPLICATION_ENV "local"
    SetEnv ENVIRONMENT "local"
</VirtualHost>
```

Restart apache, and go to "http://preslog.local" in your browser.

## Deployment Notes

Running "grunt watch" will watch individual files in your /src directory. When a change is made, the change is compiled to the /build directory.

To build a minified copy of the site for deployment, execute "grunt" by itself. this will compile to the /bin directory.


## Project Notes

This project is build on:
 - angular-js
 - ng-boilerplate (http://joshdmiller.github.com/ng-boilerplate)
 - ZendFw2

To install a new UI module, use Bower:

```sh
$ bower install YOUR_MODULE_NAME --save-dev
```