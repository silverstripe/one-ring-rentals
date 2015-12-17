In the previous lesson, we covered how to install a thirdparty module. We'll now discuss how to create your own module to share with the community.

## What we'll cover
* Why and when to create a module
* What we'll build
* Anatomy of a module
* Migrating project code into a module
* Hosting on Github
* Publishing on Packagist
* Releasing your module

## Why and when to create a module

As you're building our your project, you're likely to find yourself writing code that could conceivably be used on one or many other projects that require the same, or at least a similar feature. When you identify code like this, you should consider pulling it out of your project and placing it into a module.

Even if you can't imagine anyone but yourself reusing this code, consider modularising it anyway. There are a lot of developers out there, and you'd be surprised how  frequently requirements overlap. But creating modules isn't always about altruism -- it is often symbiotic, and you may benefit yourself by releasing your code. With more eyes on your work, you may receive contributions that enhance the features, patch security holes, or maintain its compatability with new releases of the CMS.

For the most part, modules should do one thing, and we recommend keeping your modules as small as possible. This makes them more testable, and doesn't create an unnecessarily large footprint for the consumers of your module. A calendar module, for instance, should not handle bookings and registration, as that targets only a specific subset of people who want a calendar on their website. This is a case where you would want to create a separate module that injects those features, and declares the core calendar module as a dependency.

In our case this is exactly the type of module we'll be creating.

## What we'll build

Now that we have the blog module installed, we've realised it's missing a key provision that we need for our site, and that is the ability to feature posts. We want the landing page of the blog to show a featured post at the top.

Fortunately, an imaginary developer has written this code, and it's all been added to our `mysite/code` directory. It's completely conceivable that other people would want featured posts, so let's create a module called blog-featured-posts.

## Anatomy of a module

In its most basic form, a module is simply another directory in the web root of your project. The only requirement is that the directory contain a `_config/` subdirectory, or a `_config.php` file immediately below it. It may surprise you to learn that cms and mysite are modules!

So let's start by creating that folder in our web root, `blog-featured-posts`. Directly below it, create a `_config` folder.

We'll also need to add this directory to our .gitignore file, so that it doesn't get tracked with the rest of our project.

*.gitignore*
```
...
...
blog-featured-posts/
```

Migrating project code into a module

Just like in your `mysite/` "module," all of our PHP classes will go into a subfolder called `code/`. Let's create that now.

The featured posts functionality is injected almost entirely with code, so let's move the following files from `mysite/code` to `blog-featured-posts/code`:

* BlogExtension.php
* BlogPostExtension.php
* BlogCategoryExtension.php

While these files are now where they need to be, we should seriously consider a name change. It's not unreasonable to think that other projects will be extending the blog, and we don't want to lay claim to generic titles like `BlogExtension`. Let's make these file names more clear:

* FeaturedBlogExtension.php
* FeaturedBlogPostExtension.php
* FeaturedBlogCategoryExtension.php

And don't forget to update the name of the class in the code, as well.

*blog-featured-posts/code/FeaturedBlogExtension.php* 
```php
class FeaturedBlogExtension extends DataExtension {
	//...
}
```
*blog-featured-posts/code/FeaturedBlogPostExtension.php*
```php
class FeaturedBlogPostExtension extends DataExtension {
	//...
}
```
*blog-featured-posts/code/FeaturedBlogCategoryExtension.php*
```php
class FeaturedBlogCategoryExtension extends DataExtension {
	//...
}
```

If you're shifting in your seat wondering why this problem can't be solved by simply namespacing the classes, that's a pretty normal reaction. We absolutely can do that, but we'll hold off for now, since SilverStripe will be moving to full namespacing in version 4.0. Until then, while namespacing is permitted, workarounds like this are also exceedingly common.

The only other bit we'll need for our module is the snippet of config YAML that applies these three extensions. You'll recall from our lesson on Data Extensions and SiteConfig that extensions are applied through the config layer. Let's now move that code over.

Cut the following out of your `mysite/_config/config.yml` file:

```yaml
Blog:
  extensions:
    - BlogExtension
BlogPost:
  extensions:
    - BlogPostExtension
BlogCategory:
  extensions:
    - BlogCategoryExtension
```

And paste it into the file `blog-featured-posts/_config/config.yml`, only with the new class names.

*blog-featured-posts/_config/config.yml*
```
Blog:
  extensions:
    - FeaturedBlogExtension
BlogPost:
  extensions:
    - FeaturedBlogPostExtension
BlogCategory:
  extensions:
    - FeaturedBlogCategoryExtension
```

We've made a lot of changes, so let's run a `?flush` and see that we still have our featured posts.

## Hosting your module on Github

Making your module available to others starts with giving it its own public repository. A vast majority of SilverStripe modules are hosted on Github. Let's go there now and create a new repository. We'll initialise it with a `README` file.

Now, in your module directory, run the following commands:

```
$ git init
$ git add .
$ git commit -m "Initial commit"
```

Now that we've commited our changes, we need to connect this local copy to the remote repository. The remote URL to your repository is provided on its main page in Github, with a copy-to-clipboard button beside it for your convenience.

```
$ git remote add origin git@github.com:/path/to/your-repo.git
```

Lastly, because the remote repository has a commit that we don't (the empty README), we'll need to pull down those changes before we can push to remote.

```
$ git pull -u origin master
```

Now, let's push to remote.

```
$ git push -u origin master
```

## Publishing your module on Packagist

Having our module on Github is good, but our endgame is for people to be able to install this module with Composer. For that, we'll need to publish it on Packagist.

If you don't already have an account on Packagist, simply create one. It takes less than 30 seconds.

Before we can submit our module, we'll need a `composer.json` file. This will inform Composer and Packagist what the requirements are for your module, along with various other metadata.

Let's create a simple `composer.json` file in the root of the module directory.

*blog-featured-posts/composer.json*
```js
{
	"name": "unclecheese/silverstripe-blog-featured-posts",
	"description": "Adds featured posts to the blog module.",
	"keywords": [
		"silverstripe",
		"blog"
	],
	"type": "silverstripe-module",
	"require": {
		"silverstripe/blog": "^2.0"
	},
	"extra": {
		"installer-name": "blog-featured-posts"
	},
	"license": "BSD-3-Clause",
	"authors": [
		{
			"name": "Your Name Here",
			"email": "your_email@example.com"
		}
	]
}
```

Let's walk through this bit by bit:

*"name":: This is the most important part of the composer.json. It is name of the package a user will use to install the module, e.g. composer require [package name]. The naming convention is `vendor-name/package-name`. Typically the package name should start with the word `silverstripe-` to help people understand that this is a module for another product, and also to future-proof you against packages you may create that do similar things in different contexts. For instance, you might have a MailChimp adapter for both SilverStripe and Magento. If one of them was simply named `your-name/mailchimp` you'd have a hard time disambiguating the second one.

* "description": This is pretty loosely defined. Try to keep it to one brief sentence that tells the user everything he or she needs to know.

"keywords": This is just used for search fodder. Don't go to crazy here. The goal isn't to turn up in every search. You're not selling anything!

"type": It is critical that you specify the type as `silverstripe-module`. This gives specific instructions to Composer on how to install the package, e.g. not in the `vendor/` directory, which is the default destination for Composer packages.

"require": Notice that we only specify the blog as a dependency, because we know that blog already requires the CMS and framework.

"installer-name": This tells Composer what the name of the module directory should be. Without it, it will use the name of the repository, which includes the prefix "silverstripe-". We try to avoid that so the modules don't all cluster together in the directory listing.

"license": Open source code needs to be licensed for distribution and modification. We've chosen a BSD-3 license for now. We'll talk more about licensing in the next lesson.

"authors": It's a good idea to list yourself as the author, so people know who to go to for support. Plus, if it's a great module, it never hurts to earn some credibility.

Save this `composer.json` file, commit it, and push to remote.

```
$ git add .
$ git commit -m "Added composer.json"
$ git push origin master
```

Let's go back to Packagist. Click on the "submit" button, and paste in the URL to your repository. It will acquire all the information it needs from your `composer.json` file. Your package is now published.

Notice that we have a warning stating that the package is not auto-updated. This means that every time we update our module, we'll need to manually tell Packagist to look for the changes. It would be great if this could happen transparently, so let's do that.

Go into your Packagist profile, and click "show API key." Copy it to your clipboard. This is sensitive data, so be sure to keep it a secret. Now go back to your Github repository and click on Settings, then Webhooks & Services. Click Add Service, and find Packagist. Add your API key, username, and save. Then click "Test service."

Back on your Packagist page, you should see that the module is now auto-updated.

## Releasing

Before anyone can legally use our module, we need to add a license. We've already specified in our `composer.json` file that we're going to use the BSD-3 license. The easiest way to put a license in your project is when the repository is created. Github will ask you what type of license you want before you even make your first commit. Fortunately, doing it retroactively is very simple as well.

Just click on "new file", and type "LICENSE" as the name of the file. Github will ask you if you want to use a template. Select BSD-3, or whatever license you're comfortable with, as long as it's consistent with your `composer.json` file. Make a new commit.

Our module is now public facing, Composer is aware of it, and people can use it in their projects legally, but one thing is missing – we do not yet have a stable release. The only release available on our module right now is `dev-master`, which is effectively the most recent commit to the master branch. We want to leave the master branch open to accepting and testing pull requests, and doing new development. It should not be considered stable.

Creating a release is easily done in Github. Just click on the "Releases" tab, and then "Draft new release." Since this is fairly stable code, let's just call it `1.0.0`. We'll point this release to the latest commit on master. Let's now publish the release. Thanks to our auto-update hook, Packagist will soon be aware of it.

Now that Packagist has our release, let's update the README to include installation instructions.

*blog-featured-posts/README.md*
```
## Featured posts for the SilverStripe blog module
This module adds the option for blog posts to be marked as "featured" in the CMS.

## Installation
composer require unclecheese/silverstripe-blog-featured-posts
```

Now the last thing we can do is go back into our project and actually start *consuming* the module we've created, because we're not just the authors of the module, we also want to be users. Let's go into our project and delete the directory `blog-featured-posts`, and then run `composer require unclecheese/silverstripe-blog-featured-posts`. We should get the latest stable release of the module. 

We have now created a new module with our project code, released it, published it, licensed it, and now we're consuming it. The cycle is complete!
