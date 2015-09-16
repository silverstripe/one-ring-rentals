
## Up and Running: Setting Up a Local Development Environment for SilverStripe

### Installing a local web server

SilverStripe is a PHP-based application that connects to a database, so in order to run it, you'll need a webserver. You won't want to be doing all of your development on a remote environment, so setting up a local webserver is highly recommended. If you're running OSX or Linux, you probably have all the tools you need already installed on your system, but if you're just starting out, you might find it easier to get a pre-configured package that just gets everything going for you in a single click.

There are a few local webservers out there to choose from. If you're using a Mac, you probably want [MAMP](https://docs.silverstripe.org/en/getting_started/installation/mac_osx). Windows users may prefer [WAMP](https://docs.silverstripe.org/en/getting_started/installation/windows). Both of these tools are free. Just for clarity, the *AMP* in both of these acronyms stands for *Apache*, *MySQL*, *PHP*, in case you didn't know what you were getting into. 

This tutorial will focus on using MAMP, but be aware that all of these bundled web servers operate and install very similarly. 

On the MAMP web page, just click the Download link and install it. They'll try to talk you into the pro version, which comes at a cost. If you're just starting out, the free version will serve you just fine.


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-1.png">
 
 

MAMP installs to our Applications folder, not as an application, but rather as another folder. If we take a peek inside, we'll find all the modules, libraries, and configuration files that create the server we'll be running. We just click on the MAMP package, and it pulls up a panel. 

Notice in the upper right corner that the Apache server and MySQL server are off. We can turn these on simply by clicking *Start servers*. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-2.png">
 
 

When the servers turn on MAMP lets us know that it was successful by rendering a web page in the browser. Take a close note of the host name for this web page, http://localhost:8888. This is where all of your projects will run when using MAMP. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-3.png">
 
 

MAMP is fairly configurable. The Pro version is more so. You can see that we can tweak things, like the port that runs our sites and the version of PHP. Most importantly, take note of the document root that MAMP is using. By default it runs in the `Applications/MAMP/htdocs` folder. This is where all the sites are going to run. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-4.png">
 
 

So let's just jump into that folder in the terminal and see if we can create a working PHP file. I'll make a script that renders some text to the browser. 

*/Applications/MAMP/htdocs/test.php*
```php
die('hello, world');
```

If we go to http://localhost:8888/test.php, you'll see that it is executing the script properly, and we now have a working local web server. 

<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-5.png">
 
  
  

## Introducing Composer

Before we get into installing Composer, we should probably go over what exactly Composer is and how it works. 

### What is Composer, and why do I need it?

[Composer](https://getcomposer.org/) is a package manager for PHP. Package managers are increasingly popular these days, especially for front-end libraries. You may have heard of [NPM](https://www.npmjs.com/) or [Bower](http://bower.io/). At their most fundamental level, package managers are simply abstractions of a source code repository. They obscure all the minute details about where the projects live and what branches are available, and they allow you to simply refer to packages semantically by name and by version number. 

A key feature of Composer is that it resolves dependencies. When one module requires one or more other modules in order to work properly, Composer will sort all that out and pull down everything you need. Further, Composer applies version constraints. So if a package requires a module that doesn't work with something you already have installed, it will apprise you of that conflict and halt the installation so that your project doesn't break. 

You might be wondering, "Why can't I just go and download the modules and install them manually?" Well, let's use an example SilverStripe project without Composer to illustrate why that isn't always a good choice. 

Let's say you want to get a gallery module for your website. You go out to some web page, download it, and drop it into your project. When you try to run the application, the module complains that it's missing the slideshow module, which is integral to the gallery module. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-6.png">
 
  

Now your project is hosed. You go and find the slideshow module. After some digging, you're able to track it down. You drop it in, hoping this will make your gallery module happy. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-7.png">
 
  
  

Now we have a new problem. The slideshow module is incompatible with the version of SilverStripe that we're running. You can see where this is going. All the players in your project don't get along, and your website blows up. 

Installing packages with Composer solves all these issues because you simply execute a nice, declarative command, asking to install a package and a specific version, and it handles all the orchestration for you. 

```
composer require example/some-module
```

This is by no means a magic bullet. You will still have to resolve conflicts, but it will tell you what those conflicts are, and it won't let your project exist in a state with incompatibilities. 

### Installing Composer

Installing Composer is just a matter of running two commands: 

```
$ curl -s https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer
```

These commands might look a little foreign to you if you're new to the terminal. If you need more information, SilverStripe documentation about [Composer](https://docs.silverstripe.org/en/getting_started/composer/). 

Let's run the first command, which installs Composer. It doesn't matter where in the file system we run this command. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-8.png">
 
  
  

The second command will move the Composer executable to a place where it's globally accessible, so we can just run Composer anywhere. 

## Creating a SilverStripe project

Let's create a SilverStripe project using Composer. Because this is a new project, we'll use the `create-project` command and point Composer at the `silverstripe/installer`. We'll specify a project name of *example*.

```
$ composer create-project silverstripe/installer example
``` 

Composer will now go out and read the SilverStripe installer package. Then, it's going to pull down all the dependencies, including SilverStripe Framework, and the CMS on top of that. Lastly, it's going to install the default theme that comes with the SilverStripe installer. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-9.png">
 
  
  

Now, if we go to the URL http://localhost:8888/example, we see an install page. It's full of red errors that are telling us that the install isn't going to work, so let's go through this and see if we can sort it out. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-10.png">
 
  
  
### Configuring the installer

One thing that it's complaining about is that there isn't enough information to connect to the database. So let's fill out the database username and password. If you're using MAMP, the default user for MySQL is username `root` with password `root`. 

Let's just change the database name to something a little bit more meaningful. We'll call it `SS_example`, because this is the project example. 

Lastly, we can create an admin account. Let's specify a password. That'll be the account we use to connect to the CMS. We'll recheck the requirements and install SilverStripe. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-11.png">
 
  
  

Now that the installation is complete, SilverStripe is going to prompt you to delete the install files, as they are a security risk. Click on that, and it will authenticate you before moving forward. Provide that admin password you chose earlier. 

## Refining your development environment

Now that we've installed SilverStripe, let's finely tune our development environment so we can get things working a little bit faster. 

### _ss_environment.php

The main ingredient in environment management in SilverStripe is the `_ss_environment.php` file. This file provides a shared configuration across all your projects. It should contain information such as database credentials, as those are most likely to be shared across all your projects. 

It can also include other application settings. You might have API keys or email addresses in there that you want to specify as globally accessible by all projects. 

Because `_ss_environment.php` is an executable PHP file, it can follow logic. So you can actually create a dynamic configuration by looking at something like the HTTP host that's coming in or the remote IP, and make decisions on the fly about how you want to configure the project. 

Most importantly, the `_ss_environment.php` file does not have to ship with the project. It can live outside the web root, outside of source control. When you deploy this project from your local environment to somewhere else, that remote environment might have its own configuration, so having the file outside the project means you don't have to worry about overriding settings.

### How _ss_environment.php works

Let's take a look at an example directory structure, where we have an `htdocs` folder, and three example projects underneath it.


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-12.png">
 
  
  

We'll put the `_ss_environment.php` file in `htdocs`. It will cascade its settings down to project A, B, and C. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-13.png">
 
  
You can place an SS environment file in, say project B, and it will override the parent `_ss_environment.php` file.   

There is a way to merge the settings so that you get some from the project level, and others are inherited, but that requires some custom coding, and it's probably something for another tutorial. 

### Some common configurations

In a typical `_ss_environment.php` file, you definitely want to define the database server, database username and database password. Everything is defined in constants.

*/Applications/MAMP/htdocs*
```php
define('SS_DATABASE_SERVER','localhost');
define('SS_DATABASE_USERNAME','root');
define('SS_DATABASE_PASSWORD','root');
```

Lastly, you'll probably want to define the SS environment type as *dev*, so you can take advantage of all the debugging tools and get some verbose errors.

*/Applications/MAMP/htdocs*
```php
define('SS_DATABASE_SERVER','localhost');
define('SS_DATABASE_USERNAME','root');
define('SS_DATABASE_PASSWORD','root');
define('SS_ENVIRONMENT_TYPE','dev');
```

Let's create a second SilverStripe project. We'll call it *example2*. 

```
$ composer create-project silverstripe/installer example2
```

So let's go to that *example2* URL (http://localhost:8888/example2). The install page comes up again, but it looks slightly different. 


<img width="600" src="https://raw.githubusercontent.com/silverstripe/one-ring-rentals/lesson-0/screenshots/lesson0-14.png">
 
  
  

Some of the fields have been populated for you, such as the database username and password, but you still have to provide a database name. Let's use `SS_example2`. Also, provide that admin password again. 

Click "Install SilverStripe," and once again, clear out those install files. 

Let's now take this a step further. There are some more things we want to throw into our `_ss_environment.php` file. We can use `SS_DATABASE_CHOOSE_NAME` to tell SilverStripe to intelligently determine a database name so that you don't have to. It will look at the filesystem, see where the project is installed, and choose a database name based on that.

Also, you can specify the default admin username and password. For local development, you're probably not too concerned about security. So having something easy to remember, like *root/root*, is just fine. 

*/Applications/MAMP/htdocs*
```php
define('SS_DATABASE_SERVER','localhost');
define('SS_DATABASE_USERNAME','root');
define('SS_DATABASE_PASSWORD','root');
define('SS_ENVIRONMENT_TYPE','dev');
define('SS_DATABASE_CHOOSE_NAME', true);
define('SS_DEFAULT_ADMIN_USERNAME','root');
define('SS_DEFAULT_ADMIN_PASSWORD','root');
```
Another setting you might want to turn on is `SS_SEND_ALL_EMAILS_TO`. If you provide your email address here it will force all emails to go to you, instead of to the places that your application might be sending them, which could include a client or anyone else who you don't want getting your tests. By applying this setting, it will force email to go to you, no matter what to address you've specified, so that's very useful in development mode. 

For a full list of settings you can go to the docs and just look up [environment management](https://docs.silverstripe.org/en/getting_started/environment_management) There are probably a dozen or so other settings you can throw in here. Some are more useful than others. Have a quick look through there because you might find something that's really useful to you. 

Let's save the changes to `_ss_environment.php`, and apply those new settings.

When we go to the http://localhost:8888/example3 URL, you'll notice that we bypass the install page. That's because SilverStripe has learned everything it needed to know about this project from `_ss_environment.php`. 

This is a really quick way is to light up a project and do some testing. You can just throw this project away when you're done and do it again, and you don't have to go through that install process every single time. `_ss_environment.php` comes in really useful here, as it applies all the settings you want for every single project. 

We're now off and running with a local development environment for SilverStripe development.

[Get started building your first SilverStripe website](learn/lessons/creating-your-first-theme) with our series of lessons.

