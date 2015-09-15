## Lesson 7: Working with Files and Images

Let’s take a quick look at the gaps we’re trying to close. First, we see that the list view of articles has a small image on the left that ostensibly represents a photo that is associated with the article. On the detail view, we have a larger photo. The design doesn’t explicitly dictate whether these are different images or the same image just sized differently, but for the purposes of this tutorial, we’re going to assume the user only has to upload a single image.

The client has also informed us that he will sometimes want to attach a PDF travel brochure to each travel guide, so we’ll need to make a provision in the CMS for a file upload as well.

### Common approaches to file storage against database records

If you’ve used other web application frameworks or CMS’s, you may have a few ideas about how files can be persisted to database records. Let’s cover a few common approaches:

##### 1. Save a local file path to a text field on the record

While this solution scores a lot of points for its simplicity, it has serious shortcomings in its longevity. If the file ever moves to a different place, you have to backfill the records that refer to the file with the new path, which makes this a particularly fragile method.

##### 2. Upload the file to a CDN, and save an absolute URI to the file on the record

This is a wonderfully scalable solution, as it leverages the nearly infinite storage limits of cloud hosting. Cloud hosted files tend to be pretty robust and permanent, so it’s unlikely to have the same syncing issues as a local file, but pulling the file remotely hampers our ability to manipulate it, as we’re working over HTTP rather than the local file system.

##### 3. Store the file in a BLOB field

Blobs are a special type database field that store arbitrary binary data, which makes them a great candidate for persisting files. Since it allows you to basically upload directly into the record, this approach has none of the syncing issues that you have with a filesystem, which makes it fairly reliable. The downside is that you can very quickly bloat your database, and it presents a poor separation of concerns. Your database can quickly become repurposed into a de-facto file server if you’re not judicious about how much you’re using it.

All of these techniques make sense in certain contexts, of course, but a framework tries to serve a broad range of implementations, and SilverStripe therefore chooses its own flavour of file storage that balances ease of use, reliability, and scalability.

### How SilverStripe handles files

In SilverStripe, syncing the database with the filesystem is an accepted cost, albeit tedious and problematic at times. To minimise this cost, SilverStripe provides a **File** object, with its own table in the database, that essentially keeps a leger of all the files in the filesystem. The responsibility of keeping it in sync is left entirely to these file records. Any pages or other types of database content that rely on files do not have to worry about this problem. Instead, all they need to store is the **ID** of the file they need. An **ID**, as you might know, is considered immutable in the database world, and therefore, no matter what happens to the file -- whether it moves, changes its name, or gets replaced -- the page doesn’t need to be informed. It retains the **ID** of the file, and can acquire all of its metadata when it needs to.

This doesn’t mean that the overhead of syncing two disparate systems is mitigated by any means. If you were to FTP a bunch of files directly to the server, for instance, the **File** table would not be informed of the changes. It is therefore necessary to run a task from time to time to keep the database in sync with the filesystem.

Notice the “Sync Files” button in the **Files** section of the CMS. If you ever do any direct management of the filesystem on your server, be sure to run this task!

### Introducing the has_one

So far we’ve been talking about fields that are native to the page type. **$Author**, **$Date**, and **$Teaser** are all stored on the **ArticlePage** table, and are stored in the **$db** array. Sometimes fields are stored on foreign table, and all the native table needs is a reference to the **ID** of the foreign record. The main advantage of this design is that if the foreign content ever changes, all the records who refer to it don’t need to worry about staying up to date.

To relate a page type to a foreign object, you might think all you need is afield in the **$db** array, cast as an **Int**, storing the ID of the foreign record. That’s an option, but it’s much more clean to set up that field as a foreign key, so that both the database and the SilverStripe framework will know how to handle it properly.

Let’s create a new private static array in the **ArticlePage** class called **$has_one**. This works much like the **$db** array, only instead of mapping the field names to field types, we’ll map them to the class name (or table name) of the related object. Let’s call our image field “Photo” and our file field “Brochure”.

```php
class ArticlePage extends Page {

    // ...

    private static $has_one = array (
  		'Photo' => 'Image',
  		'Brochure' => 'File'
  	);

  	// …
}
```

Notice that **Image** has its own class. Appropriately, it’s a subclass of **File**, but offers its own set of special features, particularly around resizing and resampling.

Run **dev/build** and notice the new fields that are created. They take on the names **PhotoID** and **BrochureID**. SilverStripe automatically appends **ID** to any **$has_one** field. After all, the only thing that will be stored here is the **ID** of a foreign record.

### Adding file upload fields

Let’s now add some upload functionality to our **getCMSFields** function. For file relations, **UploadField** is the best choice. For tidiness, we’ll put the uploaders on their own tab.

```php
class ArticlePage extends Page {
  	// ...
  	public function getCMSFields() {
         $fields = parent::getCMSFields();
         // ...

         $fields->addFieldToTab('Root.Attachments', UploadField::create('Photo'));
         $fields->addFieldToTab('Root.Attachments', UploadField::create('Brochure','Travel brochure, optional (PDF only)'));

    	   return $fields;
    }
}
```

Log into the CMS and try uploading a few files. Save, and see that the fields hold their state.

This works well, but we can tighten it up a bit. First, giving a written indication of the file type we’re expecting (PDF) is good, but it would be better if we could actually enforce that constraint. After all, we should always expect that if it can be broken, a user will break it.

For this, we’ll tap into the UploadField’s **validator**.

```php
  public function getCMSFields() {
        $fields = parent::getCMSFields();

        // ..

        $fields->addFieldToTab('Root.Attachments', UploadField::create('Photo'));
        $fields->addFieldToTab('Root.Attachments', $brochure = UploadField::create(
          'Brochure',
          'Travel brochure, optional (PDF only)'
        ));

        $brochure->getValidator()->setAllowedExtensions(array('pdf'));

        return $fields;
  }
```

Notice that we can use the shortcut of concurrently adding the field to the tab, and assigning it to a variable. This technique is often used when making updates to form fields after instantiation.

Now when we try to upload anything but a PDF to the brochure field, it refuses it, and throws an error.

It would also be nice if the uploader put all the files in a folder of our choosing. By default, everything will end up in **assets/Uploads**, and that directly can become quite polluted if you don’t stay on top of configuring your upload directories.

We can use **setFolderName()** on the **UploadField** to assign a folder, relative to **assets/*. If the folder doesn’t exist, it will be created, along with any non-existent ancestors your specify, i.e. “does/not/exist” would create three new folders.

```php
    	public function getCMSFields() {
              $fields = parent::getCMSFields();

              //...

              $fields->addFieldToTab('Root.Attachments', $photo = UploadField::create('Photo'));
              $fields->addFieldToTab('Root.Attachments', $brochure = UploadField::create('Brochure','Travel brochure, optional (PDF only)'));

              $photo->setFolderName('travel-photos');
              $brochure
                ->setFolderName('travel-brochures')
                ->getValidator()->setAllowedExtensions(array('pdf'));

              return $fields;
    	}
```
Try uploading a new file, and see that it goes to the appropriate place.

### Working with files on the template

Because we declared the file relation as a **$has_one**, we can access the properties of the File record just as if it’s a native field. SilverStripe will automatically handle all the querying for us.

Let’s make an update to **ArticlePage.ss** to show a download button for the brochure, if one exists. Below **<div class=”share-wrapper” />**, add the following:

```html
    <% if $Brochure %>
      <div class="row">
        <div class="col-sm-12"><a class="btn btn-warning btn-block" href="$Brochure.URL"> Download brochure ($Brochure.Extension, $Brochure.Size)</a>
        </div>
      </div>
    <% end_if %>
```

Calling the property **$Brochure**, as defined in our **$has_one** gets us a **File** object with its own set of properties. We’ll display some of them, but there are many others made available to you, including **$Brochure.Filename**, **$Brochure.Title**, and more.

Reload the page and give it a test. You should be able to download your PDF.

### The <% with %> block

This file download works great, but we can clean up the template syntax a bit. There are multiple references to properties that we’re getting by traversing the **$Brochure** object. We can remove all that dot-separated syntax by wrapping the whole thing in a scope block, known as **<% with %>**.

    <% if $Brochure %>
    <div class="row">
    	<% with $Brochure %>
    	<div class="col-sm-12">
    		<a href="$URL" class="btn btn-warning btn-block"><i class="fa fa-download"></i> Download brochure [$Extension] ($Size)</a>					
    	</div>
    	<% end_with %>
    </div>
    <% end_if %>

While there’s little, if any, performance gain to this approach, some may find it easier to read. Some developers make more use of scope operators than others. Generally speaking, the more properties you’re getting of the object, the more utility you’ll get out of a **<% with %>** block.

### How image resampling works

You might have noticed that we’ve only chosen to use a single upload field for what appears to be two different photo sizes -- a small one in list view, and a larger one on the detail view. This is because, when dealing with images, we’re only concerned about distinct content. The sizing and resampling of the photos is done on page load through function calls on the template, effectively giving you an unlimited number of different sizes and formats of any given image.

If you’re even remotely concerned about page optimisation, the very thought of resampling images on page load is probably turning your stomach. Fortunately, as we’ll see in a moment, it’s not quite that simple.

Given the image field **Photo**, we can simply invoke **$Photo** to create an image tag for the photo, as it was uploaded, in its raw form. Generally speaking, you want to avoid this, as in most use cases, images can be layout-breaking, and we don’t want to blindly trust what a CMS user uploaded (i.e. a 5MB JPEG).

If we invoke a an image resampling function against the photo, we’ll get the same image tag, only to a new version of the image, to the size of our choice.

The following syntax will show the photo at a width of 600 pixels, with unconstrained height, reduced proportionately:

    $Photo.SetWidth(600)

It’s effectively the same as reducing a photo by dragging the corner box while holding the shift key in image editing software.

Does this seem like a lot of overhead to add to your templates? Most of the time, it’s almost nothing. Here’s how it works:

SilverStripe generates a sku for the resampled image based on the original filename, the resampling method, and the argument(s) passed to it. For example, given the filename “photo.jpeg”, the above function will generate an image like this:

**SetWidth600-photo.jpeg**

To avoid collisions and to simplify cleanup, all resampled images are placed in a **_resampled/** directory off the directory that contains the source image. In our case, the full path to this image would be:

**assets/travel-photos/_resampled/SetWidth600-photo.jpeg**

When the **SetWidth()** method is called, SilverStripe generates the file name, and checks to see if it exists. If it does, it renders the existing image. If not, it creates it, and returns the path to the new file. Either way, you still get your image tag, and the resampling is transparent to you.

The benefit of this approach is that it’s fantastically simple and declarative, but the downside of declarative programming is that it obscures the developer from what is really happening under the hood. In this case, the developer should be aware that the first page load after adding or modifying a resizing function will always be slower than subsequent page loads. How much slower depends on how many images you have. Most of the time, you’ll never notice, but it’s important to be aware that if you’re rendering a lot of new photos (say, 10 or more), you probably want to hit the page once to ensure that all those photos get cached. It is never a good idea to put hundreds of new photos on a page and attempt to resample them all in a single page load, as you’re likely to timeout your PHP process.

There are many image resampling functions that ship with the default install of SilverStripe. It’s also very easy to create your own, which will cover in another tutorial. Here are a few common methods you might find useful:

<div dir="ltr">

<table>

<tbody>

<tr>

<td>

$Image.SetWidth(width)

</td>

<td>

Resize the image proportionately to fit inside the given width

</td>

</tr>

<tr>

<td>

$Image.SetHeight(height)

</td>

<td>

Resize the image proportionately to fit inside the given height

</td>

</tr>

<tr>

<td>

$Image.SetSize(width, height)

</td>

<td>

Force the image to be a certain width and height. If one dimension falls short, add padding.

</td>

</tr>

<tr>

<td>

$Image.CroppedImage(width, height)

</td>

<td>

Resize to the given width and height, cropping it if necessary to maintain the aspect ratio.

</td>

</tr>

</tbody>

</table>

</div>

### Adding images to the template

Now that we understand how images work, this last step should be pretty straightforward. On **ArticleHolder.ss**, we see that the photos in list view are about **242x156** pixels. Let’s use **CroppedImage** for these, as more important that they maintain a uniform size than it is to show all their content.

Replace the placeholder image in the **<% loop $Children %>** with **$Photo.CroppedImage(242,156)**.

On **ArticlePage.ss**, the photo is larger, and it’s important that we show all of its content, since this is the detail view. Let’s use **SetWidth(750)** for this one.

Replace the placeholder image in `<div class="blog-main-image" />` with **$Photo.SetWidth(750)**.

Reload the page, and see that your images are displaying properly.

### Customising the image tag

As we’ve stated in previous lessons, the situations where SilverStripe does not give you full control over your HTML are few and far between. Image tags are no exception.

Let’s imagine that we need to add a custom class name to our image tag. Right now, our **SetWidth()** and **CroppedImage()** functions are outputting the entire string of HTML, so we have no control over that.

The good news is that these methods actually don’t return strings of text. They give us an Image object that contains all of the properties we would expect a file to have, such as **$URL**, **$Extension**, **$Size**, and anything we would expect an image to have, such as **$Width**, and **$Height**.

Let’s rewrite those template variables to output custom HTML.

```html
    <img class="my-custom-class" src="$Photo.SetWidth(750).URL" alt="" width="$Photo.SetWidth(750).Width" height="$Photo.SetWidth(750).Height" />
```

That gets a bit unwieldy, so let’s revisit that **<% with %>** block that we used earlier to clean things up a bit.
```html
    <% with $Photo.SetWidth(750) %>
    <img class="my-custom-class" src="$URL" alt="" width="$Width" height="”$Height”" />
    <% end_with %>
```
