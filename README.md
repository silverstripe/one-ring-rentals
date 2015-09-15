## Lesson 4: Working with Multiple Templates

### Creating a new page type

Let’s create our second template, based on **home.html** in our static site. In order to create a new page type, we first need to add a PHP class to represent it. Having a new class will give us the option of creating this page type in the CMS. Since this is code related, we’ll leave the theme folder for now, and add the file to the project directory, **mysite/**.

Create a file called **HomePage.php** in your **mysite/code** folder. Add the following content:

```php    
class HomePage extends Page {

}

class HomePage_Controller extends Page_Controller {

}
```

Both classes are deliberately empty, as they are just placeholders for the time being. Notice that we subclass the **Page** class so that we can inherit all of its properties and functionality, such as **$Title**, **$Content**, **$Menu**, etc. This first class is called the **model**. It will contain all of the custom database fields, data relationships, and functionality that can be expressed across multiple templates.

By convention, every page type is paired with a **controller** that follows the naming pattern [PageType]_Controller. The controller is the liaison between the HTTP request and the finalised template. Controllers can become very dense with functionality, and will commonly include functions for querying the database, handling form submissions, checking authentication, and dealing with an assortment of business logic.

Now that we have this new page type, it is necessary to rebuild the database so that the CMS is aware of its existence. Access the URL **/dev/build** on your website. When the script is complete, you should see some blue text indicating that the field **SiteTree.ClassName** was updated to include **HomePage**.

Let’s go into the CMS at the URL **/admin**, log in if necessary, and edit the page **Home**. On the **Settings** tab, change the Page type to **Home Page**. Save and publish.

Leave the CMS and reload the home page in your browser. You should see the default page type with the home page content.

### Using the $Layout variable

Earlier in the tutorial we discussed the **DRY** principle which discourages repeating content. One glaring problem you may have noticed is that, as we add new page types, we’ll have to copy over a lot of content (e.g. the head, navigation, and footer) to each page, but with little variation, all of our templates are going to share this content. This type of outer content is often called the “chrome” or your site. To prevent the redundancy of chrome in each template, SilverStripe offers template **layouts**.

To illustrate how this works, let’s first find all the content that will not be common between our default page and our home page. A quick glance through the mockups reveals that everything between the closing `</header>` tag and the opening `<footer>` tag is unique content.

Highlight all of the content between `</header>` and `<footer>` and cut it into your clipboard. Replace all of that content with the variable **$Layout**.

Create a new template in **templates/Layout** called **Page.ss**. Paste the content from your clipboard into that file, and save.

Likewise, create a new template in the same location called **HomePage.ss**. Copy the content between `</header>` and `<footer>` in the **themes/one-ring/static/home.html** file to your clipboard and paste it into this file.

Any time we create a new template, we need to flush the cache, so append `?flush` to the URL and reload. You should now see a distinct design for the Home page versus the other two pages.

It may seem trivial, but you’ve just achieved massive gains in efficiency and code organisation. Here’s how it works:

*   SilverStripe sees that you are requesting a URL for a page that uses the **HomePage.ss** template
*   It first looks in the main **templates/** directory to find the chrome for this page. If it finds **HomePage.ss** in there, it will select that as your chrome. If not, it will go through the ancestry of that page type until it finds a match. It finds the parent class of **HomePage**, which is **Page**, and uses it.
*   The **$Layout** variable tells SilverStripe to look in the **templates/Layout** directory for a template that matches this page type. It finds **HomePage.ss** and uses it. If it had not found **HomePage.ss**, it would chase up the ancestry and find **Page.ss**, and use that as a fallback.

A vast majority of SilverStripe projects have only one template, **Page.ss**, in the root **templates/**, leaving everything else to **Layout/**. In some circumstances, you may have a page type that has such a distinct design that it needs its own chrome. A common example of this is a login page, where the user is presented with a very streamlined, isolated form.

### Injecting assets through the controller

Right now, we have all the CSS and Javascript dependencies hardcoded in the template. This works okay, but often times you will benefit from handing over management of dependencies to the controller. This gives you the ability to require specific files for only certain pages as well as conditionally include or exclude files based on arbitrary business logic.

To include these dependencies, we’ll make a call to the **Requirements** class in our controller. Since these dependencies are common to all pages, we can add this to **Page_Controller** in **Page.php**.

Make the following update to the **init()** method.

```php
public function init() {   
  parent::init();
  Requirements::css("http://fonts.googleapis.com/css?family=Raleway:300,500,900%7COpen+Sans:400,700,400italic");
  Requirements::css($this->ThemeDir()."/css/bootstrap.min.css");
  Requirements::css($this->ThemeDir()."/css/style.css");
  Requirements::javascript($this->ThemeDir()."/javascript/common/modernizr.js");
  Requirements::javascript($this->ThemeDir()."/javascript/common/jquery-1.11.1.min.js");
  Requirements::javascript($this->ThemeDir()."/javascript/common/bootstrap.min.js");
  Requirements::javascript($this->ThemeDir()."/javascript/common/bootstrap-datepicker.js");
  Requirements::javascript($this->ThemeDir()."/javascript/common/chosen.min.js");
  Requirements::javascript($this->ThemeDir()."/javascript/common/bootstrap-checkbox.js");
  Requirements::javascript($this->ThemeDir()."/javascript/common/nice-scroll.js");
  Requirements::javascript($this->ThemeDir()."/javascript/common/jquery-browser.js");
  Requirements::javascript($this->ThemeDir()."/javascript/scripts.js");
}
```

The only script we haven’t included is the html5 shim that is conditionally included for IE8. While it is possible to add conditional comments via the Requirements layer, it’s a bit of a hack, and since this is an edge case, we’ll just leave it as is in the template.

Next, remove all the `<script>` and stylesheet tags from your **templates/Page.ss** file.

### Tidying up with includes

To keep our templates less dense and easier to work on, we’ll spin off parts of the template into the **templates/Includes** directory. Start by cutting the `<div id=”top-bar” />` into your clipboard. Replace that entire div with **<% include TopBar %>**. The include declaration tells SilverStripe to look in the **templates/Includes** directory for a template with the name that you specified.

Create a file named **TopBar.ss** in **templates/Includes** and paste the content from your clipboard.

Repeat this process for `<div id=”nav-section” />`, and call the template **MainNav.ss**.

Repeat the process once again for the entire `<footer />` tag, and call the template **Footer.ss**.

Lastly, remove all of the HTML comments from your Page.ss, as the template is now too sparse to require such guides.
