## Using Data Extensions

In this tutorial, we'll discuss one of the major building blocks of modular and reusable code in SilverStripe Framework: extensions. We won't be writing a whole lot of code in this lesson. Rather, we'll illustrate a really key concept that is important to understand going forward.

### What we'll cover
* What are extensions, and why use them?
* Extensions vs. other approaches
* Extension gotchas
* Building and applying an extension

### What are extensions?
By definition, an extension is any subclass of the `DataExtension` core class in SilverStripe. In practice, however, it's a modular bit of code that can be injected into one or many other classes. The word "extend" might make you think of subclassing, but extensions are actually quite different from subclasses. Subclasses inherit all methods and properties from their one-and-only parent class. Extensions, on the other hand, supply a set of properties and methods that can be "magically" added to other classes. I use the word "magically" because extensions don't inject any hard code into your class definition. The methods and properties are added at runtime.

The simplest case for an extension is whenever you're writing identical or nearly identical functionality in multiple classes. Imagine that you have a website for a business that displays all of its stores on a Google map. It also has events, which happen at specific places, and can be put on a map. Both of these classes need to have code similar to this:

```php

    private static $db = array (
      'Address' => 'Varchar',
      'City' => 'Varchar',
      'Country' => 'Varchar(2)',
      'Postcode' => 'Varchar(5)',
      'Latitude' => 'Decimal(9,5)',
      'Longitude' => 'Decimal(9,5)',
    );

    public function getFullAddress() {
        //...
    }

    public function geocodeAddress() {
        //....
    }
```
You could put all of this in a parent class and your `Event` and `Store` data objects inherit from it, but that's not very practical or logical. Other than the business rule that says they both need to go on a map, there's no really good reason to put both of these classes in the same ancestry. Further, if the two classes don't share the same parent, the whole model falls apart.

So what do you do? Put all the shared code in an extension and apply that extension to every class that needs it. That way, you don't have to repeat yourself, and it becomes inexpensive to add mappability to any DataObject.

Some other examples might include adding functionality to send an email to an administrator after a given record is updated, or adding features that integrate a record with social media APIs. There are many good reasons to use extensions, and any decent sized SilverStripe project is bound to have a few in play.

A helpful metaphor to help distinguish between extensions and that subclasses are about *vertical* architecture, and extensions are about *horizontal* architecture.

### Extensions vs. other approaches
If you've ever used Ruby on Rails, or perhaps more popularly, LESS, you've probably already identified this familiar concept as a "mixin," and that is an accurate assessment. SilverStripe extensions are very similar to mixins. They're single-purpose bundles of functionality that augment existing code. 

Further, if you're fairly well-versed in PHP, you might be wondering why SilverStripe has reinvented the concept of *traits*, offered natively in PHP since its version 5.4 release. You're certainly not far off, but there are two good reasons why SilverStripe uses its own extensions pattern rather than PHP traits.

The first reason is simple history. The open-source release of SilverStripe predates PHP 5.4 by about seven years, so to some extent, extensions were built into the SilverStripe codebase as a long-standing workaround for a shortcoming in PHP.

Further, there are some SilverStripe idiosyncrasies that are not easily replaced by traits, such as the way arrays are merged rather than overloaded by subclasses, and the use of extension points, which we'll look at later in this tutorial.

Most importantly, however, extensions have one major advantage over PHP traits: they can be applied to classes that are outside the user space. That is to say, you can make changes to core classes without actually altering the source code. To reference our last example, it's easy to imagine adding mapping functionality to the `Event` and `Store` classes that live in our project code, but what if we wanted to add features to the core `File` class, or change the behaviour of a specific CMS controller? You wouldn't be able to assign the trait without altering the core class definition, and of course, we don't want to do that, because it will break when we upgrade.

You might wonder why we couldn't just create our own subclass of `File` to add new features to it. We could do that, and it would work just fine in our own project, but the problem is, everyone else -- the CMS and all your modules -- aren't going to know about your special class. They're all still using `File`. So if you want a global change, a subclass isn't a very good option. (You could use [dependency injection](http://doc.silverstripe.org/en/developer_guides/extending/injector/) to force your subclass, but that's a more advanced topic that we'll cover later.)

### Extension gotchas
We've established that extensions are somewhat of a workaround for functionality that is not offered natively by PHP, so there are bound to be a few tradeoffs and things we need to be aware of when working with extensions.

#### The "overloading" gotcha
The most common misconception about using extensions is that they can overload methods like subclasses. This is *not the case.* Let's say you want to update the `logIn()` method of the `Member` class so that it pings a thirdparty service, so you write something like this:

```php
class MyMemberExtension extends DataExtension {
    
    protected function apiCall() {
        //.. call API here...
    }

    public function logIn($remember = false) {
       $this->apiCall()
       //... handle normal login here
    }
}

This won't work. When an extension method collides with the class its extending, the native method always wins. You can only inject *new* functionality into a class. You can't overload it like you do with a subclass.

Fortunately, to address this, SilverStripe offers **extension points**. Extension points are created when the class being extended invokes the `$this->extend()` method and hands off the execution to any and all extensions of the class, providing any references that the extension may want to use.

Let's look again at our login method. In `framework/security/Member.php`, we can see that the `logIn()` method we're trying to update offers two extension points:

```php
  public function logIn($remember = false) {
    $this->extend('beforeMemberLoggedIn');

                // ... core login functionality here

    $this->extend('memberLoggedIn');
  }
```

Given this knowledge, we could write our extension to use either of those two hooks.

```php
class MyMemberExtension extends DataExtension {
    
    protected function apiCall() {
        //.. call API here...
    }

    public function beforeMemberLoggedIn() {
       $this->apiCall();
    }

    public function memberLoggedIn() {
        Email::create(
           'me@example.com',
           'admin@example.com',
           'Somebody logged in!'
       )->send();
    }
}
```

Think of `$this->extend()` as an event emitter, and the extension classes as event listeners. Extension points aren't offered everywhere, but they do appear in most of the areas of the codebase that you'd want to enhance or modify. As a module developer, it's very important to offer extension points so that others can make customisations as they see fit.

#### The "owner" gotcha
Let's look again at our absurd function that emails an administrator every time somebody logs in (hopefully this website isn't too popular, right?). Suppose we want to interpolate the user's name in the subject line.

```php
class MyMemberExtension extends DataExtension {

    public function memberLoggedIn() {
        Email::create(
           'me@example.com',
           'admin@example.com',
           $this->getName().' logged in!',
       )->send();
    }
}
```

This is imaginary code, so we'll spare ourselves the trouble of running it. The result would be something like this:
`
Fatal error: The method getName() does not exist on MyMemberExtension
`

How could that be? Member has the method `getName()`, right? Well, remember, we're not dealing with a subclass. We haven't inherited that method in our extension. This class runs parallel to the `Member` class, not beneath it.

Surely we'd want access to all those methods in our extension, and for that, SilverStripe provisions us with a property called `owner`, which refers to the instance of the class we're extending. To make this work, simply invoke `$this->owner->getName()`.

```php
class MyMemberExtension extends DataExtension {

    public function memberLoggedIn() {
        Email::create(
           'me@example.com',
           'admin@example.com',
           $this->owner->getName().' logged in!',
       )->send();
    }
}
```

Here is my promise to you: you will, with 100% certainty, forget about this idiosyncrasy multiple times in your SilverStripe projects. Everyone does. It's an antipattern, it's weird, it's easy to forget, and it's just one of those pitfalls you have to be aware of when working with extensions. So take a deep breath. Embrace it. You'll learn to love that error screen.

### Building and applying an extension
Believe it or not, we're actually going to write some code now. One of the most common extensions you'll want to write is one for the `SiteConfig` class. SiteConfig is a bit of an anomaly. It's a single-record database table that stores all of your site-wide settings, as seen on the *Settings* tab in the CMS. By default, SiteConfig gives you fields for the Title, Tagline, and Theme of your site, along with some simple global permissions settings. Invariably, you'll want to extend this inventory of fields to store settings that relate to your project.

We're primarily looking for data that appears on every page, so the header and footer of your site are great places to look for content that might be stored in SiteConfig. In our footer, we have some links to social media, and a brief description of the site over on the left. Let's throw all this into SiteConfig.

#### Defining an extension class
If your extension is going to be used to augment a core class, like SiteConfig, the convention is to use the name of the class you're extending, followed by "Extension." 

*mysite/code/SiteConfigExtension.php*
```php
class SiteConfigExtension extends DataExtension {

    private static $db = array (
        'FacebookLink' => 'Varchar',
        'TwitterLink' => 'Varchar',
        'GoogleLink' => 'Varchar',
        'YouTubeLink' => 'Varchar',
    );

    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldsToTab('Root.Social', array (
            TextField::create('FacebookLink','Facebook'),
            TextField::create('TwitterLink','Twitter'),
            TextField::create('GoogleLink','Google'),
            TextField::create('YouTubeLink','YouTube')
        ));
    }
}

We define a method for one of the most used extension points in the framework, `updateCMSFields`, which is offered by all DataObject classes to update their CMS interface before rendering. Notice that we don't have to return anything. The SiteConfig class will do that for us. Right now, we're just updating the object it passed us through `$this->extend('updateCMSFields', $fields)`. Since objects are passed by reference in PHP, we can feel free to mutate that `$fields` object as needed.

#### Registering your extension in the config
The last thing we need to do is apply the extension to the `SiteConfig` class. This is done through the Config layer.

*mysite/_config/config.yml*
```
SiteConfig:
  extensions:
    - SiteConfigExtension
```

Because we changed the config, we have to flush the cache. Build the database using `dev/build?flush`. You should see some new fields.

Now access the Settings tab in the CMS and populate the fields with some values.

Lastly, we'll update our template to use the new fields. All `Page` templates are given a variable called `$SiteConfig` that accesses the single SiteConfig record. Since we'll be getting multiple properties off that object, this is a great opportunity to use the `<% with %>` block.

*themes/one-ring/templates/Includes/Footer.ss* (line 78)
```html
<ul class="social-networks">
  <% with $SiteConfig %>
    <% if $FacebookLink %>
      <li><a href="$FacebookLink"><i class="fa fa-facebook"></i></a></li>
    <% end_if %>
    <% if $TwitterLink %>
      <li><a href="$TwitterLink"><i class="fa fa-twitter"></i></a></li>
    <% end_if %>
    <% if $GoogleLink %>
      <li><a href="$GoogleLink"><i class="fa fa-google"></i></a></li>
    <% end_if %>
    <% if $YouTubeLink %>
      <li><a href="#"><i class="fa fa-youtube"></i></a></li>    
    <% end_if %>
  <% end_with %>                                
</ul>
```

We've skipped over Pinterest, as it probably wouldn't apply to this business. We'll cover RSS in another tutorial, but it won't be a global RSS feed, so we can remove that button, as well.

