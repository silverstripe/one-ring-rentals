* An overview of environments
* Setting up logging on different environments
* Dealing with email
* Beyond MAMP/WAMP: Creating a custom environment
* Creating custom virtual hosts

## An overview of environments

So far we've been working on our project only in the context of a development environment, but it's important to consider that we're eventually going to want to deploy to a remote test environment, and hopefully soon after, a production website. A given project can have many environments, especially large projects that are entertaining multiple, concurrent development efforts. Each environment introduces new state, and managing that state can be really cumbersome if you don't have a solid set up. Therefore, it's important to start considering your environments early on in your development process.

We've already talked a bit about the centrepiece of environment management in SilverStripe, the _ss_environment.php file. Just as a reminder, this file is intended to reside above the web root to provide environment-specific variables to the project. This allows you to deploy one coherent codebase to each environment without having to write a whole lot of conditional logic to serve each environment. It does introduce the complexity of needing a higher level of write access to your server, however, so you'll want to make sure you have shell access or a highly privileged FTP account that will allow you to edit files above the web root.

## Setting up logging on different environments

One of the services you'll want enabled on test and, even more so, production, is good error logging and notification. In our dev environment, we want to suppress this, as we don't mind getting verbose errors, but once the project is on the web, you'll want to suppress showstopping errors as much as possible and simply log them out so you can proactively fix them.

For this, we'll use the `SS_Log` class. `SS_Log` is a central hub that will dispatch to any number of installed logger interfaces. To log an error, simply invoke:

```php
SS_Log::log('Description of the error', SS_Log::WARN);
```

Also available are:

* `SS_Log::ERR`
* `SS_Log::WARN`
* `SS_Log::NOTICE`
* `SS_Log::INFO`
* `SS_Log::DEBUG`

It's a good idea to throw these in your user code where appropriate. Logging can be very useful for debugging.

What the `log()` command actually does depends on how you've configured the logger. Let's add a logger for our test environement.

*mysite/_config.php*
```php
if(Director::isTest()) {
    SS_Log::add_writer(new SS_LogFileWriter('../silverstripe-errors-warnings.log'), SS_Log::WARN, '<=');
}
```

This will write anything equally or more severe than a warning to file one directory above the web root. Let's create a separate file for more severe errors.

```php
    SS_Log::add_writer(new SS_LogFileWriter('../silverstripe-errors.log'), SS_Log::ERR);
```

For production, we might want something more alarming. We'll have the logger send us an email if things go horribly wrong in the live environment.

```php
if(Director::isLive()) {
    SS_Log::add_writer(new SS_LogEmailWriter('me@example.com'), SS_Log::ERR);
}
```

## Dealing with email

While we're on the topic of email, let's take some control over transactional emails in our environments. This can be a really annoying problem for a couple of reasons. For one, if we're testing with real data, we don't want transactional emails to be sent to real users from our developement environment. Second, we want to be able to test whether those emails would be sent, and what their contents would be if we were in production.

A simple solution to this problem is to simply force the "to" address to go to you in the dev envrionment. You can configure this in the config yaml.

*mysite/_config/config.yml*
```yaml
Email:
  send_all_emails_to: 'me@example.com'
```

Pretty straightforward, but we're forgetting something. We don't want this setting to apply to all environments. We need to ensure that this yaml is only loaded in the dev environment. We're not writing PHP, so we don't have the convenience of if/else blocks, but fortunately, the SilverStripe YAML parser affords us a basic API for conditional logic.

*mysite/_config/config.yml*
```yaml
---
Only:
  environment: dev
---
Email:
  send_all_emails_to: 'me@example.com'
```

Perhaps in the test and production environments, we want to monitor transactional email from a bit of a distance. We could force a BCC to our email address in that case.

*mysite/_config/config.yml*
```yaml
---
Only:
  environment: dev
---
Email:
  send_all_emails_to: 'me@example.com'
```
---
Except:
  environment: dev
---
Email:
  bcc_all_emails_to: 'me@example.com'

This works okay, but it's kind of broad. If we have other developers on the project, they're not going to get any emails, and we also can't accurately test from our dev environment what the "to" address would actually be in production or test.

A much more thorough solution is to use a thirdparty tool to capture outgoing emails from your dev environment. There are a few of these tools available, but the one I like, and reommend, is Mailcatcher. Follow the instructions on the home page to install the software, and with just a bit of configuration, you can pipe all email into a local inbox. To browse the inbox, simply visit localhost:1080. Now, you can monitor all outgoing emails, and know exactly who would receive them and what their contents would be in a production environment.

## Beyond MAMP/WAMP: Creating a custom environment

We've been running our project on MAMP thus far. If you're on Windows, you might be using WAMP. They're essentially the same thing. We love these tools because in a single click, they give us a full LAMP stack, and there's really no hassle or technical knowhow required. It just works.

Typically in technology, the tradeoff for convenience is lack of control, and that cost is precisely why as developers upskill, they tend to move away from MAMP and into a more customised environment, where they can install all the modules they like, and fine-tune PHP and MySQL.

Practically speaking, this is a really difficult process to demo, because this workstation already has an environment up and running, and it's not very easy to tear it down once it's installed. So we'll deviate from the pattern of doing live demos to something more instructional and informative.

If you're running OSX, the good news is, you pretty much already have a LAMP stack installed on your machine. It just needs to be tweaked a bit. Let's start with Apache.

The primary configuration file for Apache is `httpd.conf`. It's located in the directory `/etc/apache2/`. Let's open that file. You'll need to edit it as an administrator, so if you're loading the editor from the command line, be sure to preface it with `sudo` and enter your password. Otherwise, your text editor should prompt you for a password when you try to save.

Let's look at a few key lines.

First, make sure that we're loading PHP as an Apache module. The line: 
```
LoadModule php5_module libexec/apache2/libphp5.so
```
Should be uncommented, that is, remove the leading `#`, if there is one.

Likewise, we should make sure the rewrite module is installed, so we can have nice clean URLs.

```
LoadModule rewrite_module libexec/apache2/mod_rewrite.so
```

Under the `User` and `Group` settings, set yourself as the user, and your primary group as the group. If you need clarity on this, just find a file you commonly use, and open its info window. Under "sharing and permissions" you'll get some clues about what your username and group should be.

Setting the `DocumentRoot` paramter is paramount. This defines where all your projects live. If you're using MAMP, this is the equivalent of `/Applications/MAMP/htdocs`. Set it to something like `/Users/[your user]/Sites`. Immediately below that, change the `<Directory>` node to use your document root, and change `AllowOverride` to `All`.

Near the bottom of the file, ensure that you have a line that includes your vhost configurations. This will be important later.

```
Include /private/etc/apache2/extra/vhosts/*.conf
```

Now ensure that the `vhosts` directory exists.

```
$ sudo mkdir /etc/apache2/extra/vhosts
```

Next we'll need to configure PHP. For the most part, PHP comes configured just fine, but we should edit a few things. If you're using a recent version of OSX, your php.ini file will be located in `/usr/local/etc/php/5.5/php.ini`. That's likely to change as this tutorial ages, so if you need to track down your `php.ini` file, simply run this command at the terminal:
```
$ php -i | grep ini
```
And find the line "Loaded Configuration File".

Edit the `php.ini` file as an administrator. Needless to say, there's a lot in here to configure, and your preferences will vary, but there are a few things I like to adjust. 

First, set the `error_log` to a file path on your system. This will provide you with some basic logging, and give you a place to turn when things are mysteriously broken.

Next, change `post_max_size` and `upload_max_filesize` to more realistic values. I like `50M` for this. They're way too small by default, and you don't want annoying upload errors in your dev environment.

A critical setting to change is `date.timezone`. PHP will complain about this until you've set it. Uncomment it, by removing the leading semicolon, and add your timezone. If you don't know the title of your timezone, you can find it on the (http://php.net/manual/en/timezones.php)[PHP website].

If you're using MySQL with SilverStripe, you'll want to edit the `MySQLi` settings. If you're using an alternative MySQL database like PDO, you'll want to update that section of the ini file. 

The key update here is the `default_socket`. Set it to `/var/mysql/mysql.sock`. For good measure, find out where your MySQL socket is by running `mysqladmin variables` at the terminal. If it's in a different place, you'll need to either move it to `/var/mysql`, or change your MySQLi setting to the correct place.

That's good for now.

Because we've made updates to Apache and PHP, we'll need to restart the server. Run:

```
$ sudo apachectl restart
```

This wouldn't be a complete tutorial if we didn't at least touch on the topic of package managers. Like Composer for PHP, system applications and modules can be installed via package managers, as well, and setting up a LAMP stack with a package manager is a really good idea for a number of reasons:

* You're not tied to whatever version of PHP/MySQL/Apache ship with the current version of OSX
* When you upgrade OSX, your changes don't get overwritten
* You can run multiple versions of PHP, to emulate different environments
* You can add PHP libraries and Apache modules easily with a single command

The most popular package manager for OSX is (http://brew.sh/)[Homebrew]. There are some wonderful (https://guynathan.com/install-lamp-stack-on-mavericks-with-homebrew-with-php-mcrypt)[online tutorials] for setting up a LAMP stack with Homebrew. 

## Creating custom virtual hosts

Lastly, we'll want to create our own vhosts, so that instead of running everything off `localhost:8888/my-website`, we can use proper hostnames. This will allow our dev environment to more closely mimic a production server. 

There's a great tool for this called (https://github.com/jamiemill/osx-vhost-manager)[osx-vhost-manager] that will do all the work for you. Simply install the script to your `/usr/local/bin` directory:

```
$ git clone git://github.com/jamiemill/osx-vhost-manager.git /usr/local/bin
$ mv /usr/local/bin/vhostman.rb /usr/local/bin/vhostman
$ chmod +x /usr/local/bin/vhostman
```

Then, you can run `sudo vhostman add example ~/Sites/example`. You will now have a vhost named `http://example.local` that resolves to `~/Sites/example`.

