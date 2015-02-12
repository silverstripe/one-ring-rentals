## Working with data relationships

So far, we've only been dealing with content that is directly associated with a page, but often times, pages are made up of content that is stored outside the page, and merely brought into the page through a data relationship. We saw a bit of this in Lesson #7 when we created `$has_one` relationships to `File` objects. All of the information about the file is stored on its own table, and the page merely references it. We're going to dig a bit deeper into relational data in this lesson by introducing some plural relationships with `$has_many` and `$many_many`.

### What we'll cover
* Creating a generic DataObject
* Setting up a `$has_many` relationship
* Introduction to the GridField
* Setting up a `$many_many` relationship
* Working with relational data on the template

### Creating a generic DataObject

Let's look at the new arrival to the `static/` directory in our theme, `regions-page.html`. Our designer has once again stripped away all the chrome and left us with just the `$Layout` section, so before we go any further, let's migrate this page into a SilverStripe template.

Copy the contents of `themes/one-ring/static/regions-page.html` into a new file, `themes/one-ring/templates/Layout/RegionsPage.ss`.

Then, create a new page type to go with it.

*mysite/code/RegionsPage.php*
```php
class RegionsPage extends Page {

}

class RegionsPage_Controller extends Page_Controller {

}
```

Run a `dev/build?flush`, and go into the CMS. Change the `Regions` page to type `RegionsPage` (on the Settings tab). Now go back to your website and click on "Regions." You should see the new layout.

All of this is static content, of course, and we need to start carving it up into something editable in the CMS. One of the most apparent features of this content is that it clearly belongs in a loop. We see the regions *Northeast*, *Southeast*, etc., all with the same structure and data. Each contains a title, a photo, and a short description.

We could hand this off to the rich text editor and create all this content in the `$Content` block, but that would be pretty ugly. Our content editor would have to take care to create very specific markup, and that would push the boundaries of WYSIWYG utility. We need to make each of these regions editable pieces of structured data.

Let's first create the `Region` data type. Just like we discussed in the previous lesson, generic, non-page content types should subclass `DataObject`.

*mysite/code/Region.php*
```php
class Region extends DataObject {
	
	private static $db = array (
		'Title' => 'Varchar',
		'Description' => 'Text',
	);

	private static $has_one = array (
		'Photo' => 'Image'
	);

	public function getCMSFields() {
		$fields = FieldList::create(
			TextField::create('Title'),
			TextareaField::create('Description'),
			$uploader = UploadField::create('Photo')
		);
		
		$uploader->setFolderName('region-photos');
		$uploader->getValidator()->setAllowedExtensions(array('png','gif','jpeg','jpg'));

		return $fields;
	}
}
```

You might have noticed that our `getCMSFields()` function looks a bit different. That's because we're not going to be using the typical page editing interface for this object, so we're not going to have the tabs that come with Page objects. We could very easily create one, but since this data type is so simple, we'll just leave it as a simple field list, and add all the form fields to the constructor.

### Setting up a $has_many relationship

Now that we have our standalone `Region` object, we need to relate it to our `RegionsPage`. We know from our design that the page can contain any number of Regions, so for this, we'll use the `$has_many` relationship.

*mysite/code/RegionsPage.php*
```php
class RegionsPage extends Page {

	private static $has_many = array (
		'Regions' => 'Region'
	);
}
```

This follows the same convention as the `$has_one` we used in a previous lesson. The key, "Regions" is the arbitrary name we'll give to the relationship. It's the method we'll use to get a list of all the related regions. The value, "Region" is the name of the related class.

Run a `dev/build` and see if you get any database changes. If you don't see any, **that is the expected result!** Why? Well, we're not quite done yet.

#### Reciprocating the $has_many

`$has_many` relationships are a bit of a special case because they have to be *reciprocated* by the related class. While every `RegionsPage` has many `Regions`, it is also true that every `Region` has *one* `RegionPage` that contains it. Regions cannot belong to any more than one region page.

This is important, because the database mutation happens at the `$has_one` level, not the `$has_many`. What will really bind these two objects together is a `Region` object providing one, and only one, `RegionsPageID`. Let's make that update now.

*mysite/code/Region.php*
```php
class Region extends DataObject {
	
        //...
	private static $has_one = array (
		'Photo' => 'Image',
		'RegionsPage' => 'RegionsPage'
	);

        //...
```

Typically, reciprocal `has_one`'s like this can just be named after the parent class.

Now let's run a `dev/build` and see that we get a new `RegionsPageID` field.

### Introduction to GridField

We've got our `$has_many` relationship defined, with a `$has_one` on the other side, and we're ready to start populating the relationship with data. For this, we'll need a need one of the workhorses of the CMS interface -- `GridField`.

GridField is a highly configurable form field that allows you to manage an arbitrary table of data. In its most primitive sense, you can think of it as an abstraction of a database table, but there's much more that you can do with it. To manage our `Region` objects, we'll want a tab on the `RegionsPage` that allows us to create, read, edit, and delete associated `Region` records.

Let's make the following update to our `RegionsPage` object.

```php
class RegionsPage extends Page {

	private static $has_many = array (
		'Regions' => 'Region'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Regions', GridField::create(
			'Regions',
			'Regions on this page',
			$this->Regions(),
			GridFieldConfig_RecordEditor::create()
		));

		return $fields;
	}
}
```

#### The GridField constructor

Let's take a look at the argument signature for `GridField`:
* **'Regions'**: A required, arbitrary name for the GridField. You'll need this if you ever want to make updates to your GridField after it's been added to the FieldList.
* **'Regions on this page'**: A title for the GridField. Should be user-friendly.
* **$this->Regions()**: This is the most substantial component of your GridField. It populates the grid with data. In this case, we're using the magic method created by our `$has_many` relationship to fill the grid with all the records that are currently associated with the page.
* **GridFieldConfig_RecordEditor::create()**: This is a bit more complex. It creates a object that contains a number of `GridFieldComponent` objects, which provide various UI tools to the grid, such as pagination, an "add new" button, delete/edit buttons, etc. These `GridFieldConfig` objects can be configured with any variety of components you like, but SilverStripe ships with a few common configurations that are often used. `GridFieldConfig_RecordEditor` is a great one, because it provides all the basic UI you'd expect to have for managing data.

Let's go back into the CMS and edit our "Regions" page. See that we have a tab now that contains a grid. Give it a try and add some test records.

#### Configuring the GridField

As stated earlier, `GridField` is highly configurable. One of the most common customisations you'll want to make to your grid is the columns that are displayed. In this case, there are only a handful of fields, so only showing `Title` and `Description` isn't so bad, but you can imagine that if the DataObject had 10 distinct fields, we would probably want to tighten that up a bit.

In our case, let's make a minor enhancement, and just add the `Photo` field to our list view. By default, only fields in the `$db` array get included. Since `Photo` is in the `$has_one`, we have to specifically include it.

Let's define a `$summary_fields` variable on our `Region` object.

```php
class Region extends DataObject {
        //...	
	private static $summary_fields = array (
		'Photo' => '',
		'Title' => 'Title of region',
		'Description' => 'Short description'
	);
        //...
}
```

This array maps the name of the field to the human-readable column name. We'll let the photo speak for itself and leave its column header empty.

Because we changed a private static variable, we need to run ?flush to see the update to the grid. How does it look? Pretty bad, right? The photo is not being resized to fit the grid. 

#### Using a custom getter

Fortunately, `$summary_fields` accepts more than just field names. We can provide any public method on our DataObject as a column value. Let's create a method that returns a resized photo.

```php
class Region extends DataObject {
        //...
	private static $summary_fields = array (
		'GridThumbnail' => '',
		'Title' => 'Title',
		'Description' => 'Description'
	);


	public function getGridThumbnail() {
		if($this->Photo()->exists()) {
			return $this->Photo()->SetWidth(100);
		}

		return "(no image)";
	}
        // ...
}
```
Run `?flush` again, and see that our grid looks much cleaner now.

We could have saved ourselves a lot of time by using a method that SilverStripe provides to all `Image` objects by default -- `CMSThumbnail()`. `Photo.CMSThumbnail` would have achieved a very similar result, without the fallback text *(no image)*.

#### Traversing fields

Another great feature of `$summary_fields` is that you can traverse relationships to get foreign fields, using a dot-separated syntax. Suppose we wanted to show the `Filename` field on the Photo:

```php
class Region extends DataObject {
        //...
	private static $summary_fields = array (
		'Photo.Filename' => 'Photo file name',
		'Title' => 'Title',
		'Description' => 'Description'
	);
        //...
}
```
This type of syntax becomes especially useful when formatting dates, or getting the title of a related `$has_one` rather than just showing its numeric ID.

### Setting up at $many_many relationship

Let's turn our focus back to the `ArticlePage` now and see that each article is associated with many categories. We can imagine that in the CMS, we want a list of selectable categories, perhaps checkboxes, that are offered to each article. The first thing we'll need to do is set up a place to manage the categories. There are several different ways you can do this. It really depends on what kind of user experience you want to create, but for now, let's stick them on the ArticleHolder object, so that, conceivably, another `ArticleHolder` page could provide its own set of distinct categories.

#### Managing the ArticleCategory objects

*mysite/code/ArticleHolder.php*
```php
class ArticleHolder extends Page {
        //...
	private static $has_many = array (
		'Categories' => 'ArticleCategory'
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Categories', GridField::create(
			'Categories',
			'Article categories',
			$this->Categories(),
			GridFieldConfig_RecordEditor::create()
		));

		return $fields;
	}
}
```

Next, let's create that `ArticleCategory` object. It's going to be really simple.

*mysite/code/ArticleCategory.php*
```php
class ArticleCategory extends DataObject {

	private static $db = array (
		'Title' => 'Varchar',
	);

	private static $has_one = array (
		'ArticleHolder' => 'ArticleHolder'
	);

	public function getCMSFields() {
		return FieldList::create(
			TextField::create('Title')
		);
	}
}
```
Notice once again that we have the reciprocal `$has_one` back to the `ArticleHolder`.

Run `dev/build` again and see that we get a new table. Edit the "Travel Guides" page in the CMS and add a few sample categories.

#### Relating Articles to Categories

Now that we have some categories to work with, let's relate them to the articles. Articles have many categories, as we can see on the template, so it's reasonable to assume we'll be using another `$has_many`, right?

In this case, a `$has_many` is not what we want. Remember that reciprocal `$has_one` we used with `$has_many`? That declares that each related object can only belong to one parent. Once that relation is created, it can't be used anywhere else. We don't want that behaviour with categories. Once a category is claimed by an article, it should still be available to other articles. Therefore, articles have many categories, and categories have many articles. This is a `$many_many` relationship.

*mysite/code/ArticlePage.php*
```php
class ArticlePage extends Page {
        //...
	private static $many_many = array (
		'Categories' => 'ArticleCategory'
	);
        //...
}
```
Run `dev/build` and see that we get a new table, `ArticlePage_Categories`.

#### Reciprocating the $many_many

Optional, but strongly recommended is a reciprocation of this relationship on the `ArticleCategory` object, using `$belongs_many_many`. This variable does not create any database mutations, but will provide an magic method to the object for getting its parent records. In this case, we know that we'll need any `ArticleCategory` object to get its articles, because our design includes a filter by category in the sidebar, so this is quite important.

*mysite/code/ArticleCategory.php*
```php
class ArticleCategory extends DataObject {
        //...
	private static $belongs_many_many = array (
		'Articles' => 'ArticlePage'
	);
        //...
}
```
We changed a static variable, so run `?flush`.

#### $many_many vs $belongs_many_many

So if both sides of the relationship have many associated records, how do you know which one gets the `$many_many` and which one is `$belongs_many_many`? Typically, the object that contains the interface gets the `$many_many`. In this case, we'll add categories to the articles using checkboxes, so that's where our `$many_many` goes. Again, the `$belongs_many_many` just provides the convenience of an accessor method for getting the articles from within a category.

#### Adding checkboxes

Speaking of interface, we need to add some to the `ArticlePage` object. Let's introduce `CheckboxSetField`.

*mysite/code/ArticlePage.php*
```php
class ArticlePage extends Page {
        //...
	public function getCMSFields() {
		$fields = parent::getCMSFields();
                //...
		$fields->addFieldToTab('Root.Categories', CheckboxSetField::create(
			'Categories',
			'Selected categories',
			$this->Parent()->Categories()->map('ID','Title')
		));
		return $fields;
	}
}
```
Let's take a look at the argument signature of `CheckboxSetField`:

* **'Categories'**: The name of the `$many_many` relation we're managing.
* **'Selected categories'**: A label for the checkboxes
* **$this->Parent()->Categories()**: The categories are stored on the parent `ArticleHolder` page, so we need to invoke `Parent()` first.
* **->map('ID', 'Title')**: Using the resulting list of categories, create an array that maps each category's ID to its Title. This tells the checkboxes to save the ID to the relation, but present the `Title` field as a label. Note that `Title` can be any public method executable on the object, which is useful if you want a computed value or concatenation of multiple fields. 99% of the time, you will want to use `ID`  as the first argument here, as relational data is all held together by unique identifiers.

Go into the CMS and edit an article under "Travel Guides." Check off some categories and make sure they save.

`CheckboxSetField` is a good go-to UI for most `$many_many` relations, but it doesn't scale very well. If we had 100 categories, this wouldn't be a pleasant experience for the user. For larger data sets there is also `ListboxField`, which provides a typeahead UI for associating records without displaying them all at once.

### Working with relational data on the template

Now that our relational data is all in place, it's time to display it on the template. This should be pretty straight forward. Let's start with `RegionsPage.ss`

*themes/one-ring/templates/Layout/RegionsPage.ss, line 9*
```html
	<div class="grid-style1 clearfix">
		<% loop $Regions %>
		<div class="item col-md-12"><!-- Set width to 4 columns for grid view mode only -->
			<div class="image image-large">
				<a href="#">
					<span class="btn btn-default"><i class="fa fa-file-o"></i> Read More</span>
				</a>
				$Photo.CroppedImage(720,255)
			</div>
			<div class="info-blog">
				<h3>
					<a href="#">$Title</a>
				</h3>
				<p>$Description</p>
			</div>
		</div>
		<% end_loop %>
	</div>
```
Notice that we're skipping over the links. We'll address that in a future lesson.

Now let's look at adding the comma-separated category list to the articles.

*themes/one-ring/templates/Layout/ArticlePage.ss, line 22*
```html
<li><i class="fa fa-tags"></i> 
     <% loop $Categories %>$Title<% if not $Last %>, <% end_if %><% end_loop %>
</li>
```
We can use the global template variable `$Last` to tell us whether we're in the last iteration of the loop, which will determine whether or not we show the comma.  Also available are `$First`, `$Even`, `$Odd`, and many others.

#### Using a custom getter

If we reload the page, this all looks great, but we're not done yet. The categories are also displayed on `ArticleHolder.ss` and `HomePage.ss`. This is a lot of template syntax to keep replicating. We could put this into an include, but it would be better if the `ArticlePage` objects could render a comma-separated list of categories themselves. Let's create a new method that does this.

*mysite/code/ArticlePage.php*
```php
class ArticlePage extends Page {
        //...
	public function CategoriesList() {
		if($this->Categories()->exists()) {
			return implode(', ', $this->Categories()->column('Title'));
		}
	}
}
```
We check the existence of categories with the `exists()` method. Simply checking the result of `Categories()` will not work, because it will at worst return an empty `DataList` object. It will never return false. We use `exists()` to check trueness.

Invoking `column()` on the list of `ArticleCategory` objects will get an array of all the values for the given column, which saves us the trouble of looping through the list just to get one field.

Now update `HomePage.ss`, `ArticleHolder.ss`, and `ArticlePage.ss` to use the `$CategoriesList` method.

*themes/one-ring/templates/Layout/ArticlePage.ss, line 22*
```html
<li><i class="fa fa-tags"></i> $CategoriesList</li>
``` 

*themes/one-ring/templates/Layout/ArticleHolder.ss, line 25*
```html
<li><i class="fa fa-tags"></i> $CategoriesList</li>
```

*themes/one-ring/templates/Layout/HomePage.ss, line 289*
```html
<li><i class="fa fa-tags"></i> $CategoriesList</li>
```
