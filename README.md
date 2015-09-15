## Lesson 2: Migrating Static Templates Into Your Theme

### Drop your bags

You know that feeling you get when you’ve been traveling all day, and you finally breach the door to your hotel room? You drop your bags in the first vacant spot you can find, suspending your impending obligation to unpack and get organised, and gift yourself that moment of relaxation.

When migrating a static site into SilverStripe, it is often helpful to follow that same pattern. In this section, we’ll drop all of our HTML and other static assets into their own directory so that they are browseable by our web server and living in our project, albeit as poorly assimilated outsiders. Sure, we’ve still got a lot of “unpacking” to do, but you’ll feel better knowing that everything is where it is supposed to be, under one roof.

Let’s create a directory called **static/** in our theme. Again, this label is entirely up to you, so if it’s more meaningful to you to use something like **html/**, or **raw/**, feel free to invoke that option.

Your folder structure should now look like this:

*   themes
  *   one-ring
    *   css
    *   images
    *   javascript
    *   static
    *   templates
        *   Includes
        *   Layout

Now we’ll simply dump the all the contents of our static site into the **static/** folder, preserving the file structure. Since we’ve used relative paths for all the assets, having the site deep into the directory structure will not break anything.

Let’s test it out. Navigate your browser to **/themes/one-ring/static/default.html** off whatever hostname you’re using (e.g. http://localhost:8888/tutorial), and you should see our home page. Try the same thing for **home.html**.

### SS-izing

Now that we’ve dropped our bags and have rewarded ourselves with a quick glimpse of our site in living flesh, it’s time to start cutting up our static site into SilverStripe templates.

### Copy and correct

We’ll start with **default.html**. This mockup is intended to represent the most basic of page types in our site. As discussed earlier, SilverStripe conventionally purposes the **Page.ss** template for this case. Copy the contents of **default.html** into your **templates/Page.ss** file, which currently contains our “Hello, world” proof-of-concept, and reload your site on the base URL (e.g. http://localhost:8888/tutorial).

Look good? No, it shouldn’t. It should look like an unstyled mess. Let’s copy over all the static assets into our theme. Make the following copies:

<table style="width: 624px; height: 92px;">

<tbody>

<tr>

<td><strong>From</strong></td>

<td><strong>To</strong></td>

</tr>

<tr>

<td>themes/one-ring/static/css</td>

<td>themes/one-ring/css</td>

</tr>

<tr>

<td>themes/one-ring/static/js</td>

<td>themes/one-ring/javascript</td>

</tr>

<tr>

<td>themes/one-ring/static/images</td>

<td>themes/one-ring/images</td>

</tr>

<tr>

<td>themes/one-ring/static/fonts</td>

<td>themes/one-ring/fonts</td>

</tr>

</tbody>

</table>

The easiest place to start is in your Webkit web inspector tool. Click on the Network tab, and refresh. You should see a lot of red. The first thing we notice is that the CSS is not loading properly. The browser is trying to load css/bootstrap.min.css, which is incorrect. Let’s update all of the CSS to load from our theme dir. Make the following changes to all of the stylesheet “href” attributes:

<table style="width: 630px; height: 152px;">

<tbody>

<tr>

<td><strong>Original</strong></td>

<td><strong>New</strong></td>

</tr>

<tr>

<td>

css/bootstrap.min.css

</td>

<td>

themes/one-ring/css/bootstrap.min.css

</td>

</tr>

<tr>

<td>

css/style.css

</td>

<td>

themes/one-ring/css/style.css

</td>

</tr>

</tbody>

</table>

Reload the page, and you should see a bit more style.

### Using $ThemeDir

As a matter of best practice, we never want to repetitively hardcode any values in our template that are subject to change. This principle is more commonly referred to as DRY (Don’t Repeat Yourself). We’ll talk more about **DRY** practices in future tutorials, but for now, our main offender of this rule is the reference to **themes/one-ring**. SilverStripe provides a global helper variable that returns our current theme directory named **$ThemeDir**. Replace the references to **themes/one-ring** with **$ThemeDir**, as follows:

<table style="width: 688px; height: 56px;">

<tbody>

<tr>

<td><strong>Original</strong></td>

<td><strong>New</strong></td>

</tr>

<tr>

<td>themes/one-ring/css/bootstrap.min.css</td>

<td>$ThemeDir/css/bootstrap.min.css</td>

</tr>

<tr>

<td>themes/one-ring/css/style.css</td>

<td>$ThemeDir/css/style.css</td>

</tr>

</tbody>

</table>

Now, if we change our theme, we don’t have to make any updates to the paths in the template.

While the site looks better, a quick glance at the web inspector shows us that there are still a lot of 404s coming through, mostly for our Javascript files. Let’s update all the `<script>` tags to point to **$ThemeDir/javascript instead of js/ **. For example, **js/common/modernizr.js** becomes **$ThemeDir/javascript/common/modernizr.js**. Most of these updates are at the bottom of the document.

Refresh, and you should see a lot less red in your web inspector. The last 404 we need to fix is **logo.png**. Simply prepend **$ThemeDir/** to the <img> tags that reference that file.

Everything should be loading correctly now. We have migrated our static document into a SilverStripe theme.
