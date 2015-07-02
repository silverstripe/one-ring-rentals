In this tutorial, we're going to talk about adding non-database content to our templates.

## What we'll cover
* What are we working towards?
* Updating the template: Working backwards
* Updating the controller to use generic data

## What are we working towards?

Up until now, the data on our templates has been pretty one-sided. It's sourced from the database, and we render the fields from one or many returned records on the template. Often times, however, the template and the database are not so tightly coupled. There's actually no rule saying that all template data has to come from the database

Ultimately what we're teaching in this lesson is the concept of *composable UI elements*. As you may know, composable components are a rapidly accelerating trend in application development as developers and designers seek to maintain a high level of agility and reusability.

Being composable, these components are essentially "dumb" and only really know how to do one thing, which is render some UI based on the configuration that has been passed to them, which is what we'll call a *composition*.

In the context of our project, we'll be lighting up the search filter toggle buttons in the sidebar of our property search page. The purpose of these buttons is to show the user what search filters have been applied, and to offer an option to remove them and refresh the search page.

## Updating the template: Working backwards

A lot of developers, including myself, find it easier to work backwards with problems like this, which means starting from the template and adding the backend afterward. Let's look at these filter buttons and try to abtract them into something we can use.

As we can see, they're all statically contained in a `ul` tag at the moment.

```html
<ul class="chzn-choices">
   <li class="search-choice"><span>New York</span><a href="#" class="search-choice-close"></a></li>
   <li class="search-choice"><span>Residential</span><a href="#" class="search-choice-close"></a></li>
   <li class="search-choice"><span>3 bedrooms</span><a href="#" class="search-choice-close"></a></li>
   <li class="search-choice"><span>2 bathrooms</span><a href="#" class="search-choice-close"></a></li>
   <li class="search-choice"><span>Min. $150</span><a href="#" class="search-choice-close"></a></li>
   <li class="search-choice"><span>Min. $400</span><a href="#" class="search-choice-close"></a></li>
</ul>
```

### The wrong way to do it

One approach that may come to mind is using a long series of display logic to output all of the possible options, like so:

```html
<ul class="chzn-choices">
<% if $LocationFilter %>
   <li class="search-choice"><span>$LocationFilter</span><a href="#" class="search-choice-close"></a></li>
<% end_if %>

<% if $BedroomFilter %>
   <li class="search-choice"><span>$BedroomFilter bedrooms</span><a href="#" class="search-choice-close"></a></li>
<% end_if %>

<!-- etc... -->
</ul>
```

This might look reasonable at first, it's going to lead to nothing but problems. There are a number of things wrong with this approach.

* It pollutes your template with syntax, and a lot of repeated markup
* It pollutes your controller with a lot of repetative property assignments and/or methods
* It creates more parity between your controller and your template. If you ever want to add or remove a new search option, you have to remember to update the template.
* We have to repurpose the *value* of the filter as its *label*, e.g. `$BedroomFilter bedrooms`, and at some point that's just not going to work. Search filters are often not human-readable, such as IDs.

### A better approach

If the sight of `li` tags nested in a `ul` is becoming almost synonymous with the `<% loop %>` control to you, that's a good sign. We're definitely going to need a loop here. This will keep the UI much cleaner, and it will give us more control over the output, as we'll have a chance to *compose* each member of the loop. Let's add that now, and make up the rest as we go.

```html
<ul class="chzn-choices">
   <% loop $ActiveFilters %>
   		<li class="search-choice"><span>New York</span><a href="#" class="search-choice-close"></a></li>
   <% end_loop %>
</ul>
```

Make sense so far? Again, we're working backwards, so the `$ActiveFilters` piece is merely semantic right now. 

Let's now just go through brainstorm some property names for all the dynamic content.

```html
<ul class="chzn-choices">
   <% loop $ActiveFilters %>
   		<li class="search-choice"><span>$Label</span><a href="$RemoveLink" class="search-choice-close"></a></li>
   <% end_loop %>
</ul>
```

We've added the properties `$Label` and `$RemoveLink`, which we can assume are the only two distinguishing traits of each filter button.

## Updating the controller

Now that our template syntax is in place, we need to configure the controller to feed this data to the template. We could write a new method called `ActiveFilters()` (or `getActiveFilters()`) that inspects the request and returns something, but given that there's only one endpoint for our search page, I think it makes more sense at this point in the project to create the filter UI elements as they're being applied to the list.

### Creating an arbitrary list

In order to invoke the `<% loop %>` block, we of course will need some kind of iteratable list. So far, we've been using `DataList`, which represents a list of records associated with a database query. Since our filter UI elements are not coming from the database, we'll need something more primitive. In this case, `ArrayList` is an ideal choice.

At the top of our `index()` action, let's instantiate that list.

*mysite/code/PropertySearchPage.php*
```php
	public function index(SS_HTTPRequest $request) {
		$properties = Property::get();
		$filters = ArrayList::create();
	
		//...
	}
```
Now, we just need to fill our list with data.

### Remember ViewableData?
To populate the list, we'll revisit our old friend `ViewableData` from the previous tutorial. Just as a recap, `ViewableData` is a primitive object that is ready to be rendered on a template. One type of `ViewableData` is `DataObject`, which we've been using all along to render content from the database.

You will rarely need to use the `ViewableData` class itself, but its immediate descendant, `ArrayData` is very flexible and couldn't be simpler to implement. It's basically just a glorified array. All you have to do is instantiate it with an array of key/value pairs that will translate to `$Variable` template variables, and render their associated values.

Let's add the filter for the `Keywords` filter.

*mysite/code/PropertySearchPage.php*
```php
	public function index(SS_HTTPRequest $request) {
		
		//...
		
		if($search = $request->getVar('Keywords')) {
			$filters->push(ArrayData::create(array(
				'Label' => "Keywords: '$search'",
				'RemoveLink' => HTTP::setGetVar('Keywords', null)
			)));

			$properties = $properties->filter(array(
				'Title:PartialMatch' => $search
			));
		}
		
		//..

```

Using the `push()` method on `ArrayList`, we add `ArrayData` objects to it. Each one has `Label` and `RemoveLink` properties, as required by the template. The `RemoveLink` property implements an obscure utility method from the `HTTP` helper class. All it does is take the current URI and set a given query parameter to a given value. In this case, we're setting it to `null` to effectively remove the filter.

The next filter is for the availability date range. It actually doesn't offer a whole lot of utility to the user to display this as a toggleable filter, especially since it's actually a composite filter of `ArrivalDate` and `Nights`, so let's skip this one.

The next several are pretty straightforward. Let's add the filter UI elements for Bedrooms, Bathrooms, and Min/Max Price.

*mysite/code/PropertySearchPage.php*
```php
	public function index(SS_HTTPRequest $request) {
		
		//...

		if($bedrooms = $request->getVar('Bedrooms')) {
			$filters->push(ArrayData::create(array(
				'Label' => "$bedrooms bedrooms",
				'RemoveLink' => HTTP::setGetVar('Bedrooms', null)
			)));

			$properties = $properties->filter(array(
				'Bedrooms:GreaterThanOrEqual' => $bedrooms
			));
		}

		if($bathrooms = $request->getVar('Bathrooms')) {
			$filters->push(ArrayData::create(array(
				'Label' => "$bathrooms bathrooms",
				'RemoveLink' => HTTP::setGetVar('Bathrooms', null)
			)));

			$properties = $properties->filter(array(
				'Bathrooms:GreaterThanOrEqual' => $bathrooms
			));
		}

		if($minPrice = $request->getVar('MinPrice')) {
			$filters->push(ArrayData::create(array(
				'Label' => "Min. \$$minPrice",
				'RemoveLink' => HTTP::setGetVar('MinPrice', null)
			)));

			$properties = $properties->filter(array(
				'PricePerNight:GreaterThanOrEqual' => $minPrice
			));
		}

		if($maxPrice = $request->getVar('MaxPrice')) {
			$filters->push(ArrayData::create(array(
				'Label' => "Max. \$$maxPrice",
				'RemoveLink' => HTTP::setGetVar('MaxPrice', null)
			)));

			$properties = $properties->filter(array(
				'PricePerNight:LessThanOrEqual' => $maxPrice
			));
		}

		//...
	}

```

### Passing the filters to the template

Just like our custom variable `Results`, we'll pass the `ActiveFilters` list to the template through an array.

*mysite/code/PropertySearchPage.php*
```php
	public function index(SS_HTTPRequest $request) {
		
		//...

		$paginatedProperties = PaginatedList::create(
			$properties,
			$request
		)->setPageLength(15)
		 ->setPaginationGetVar('s');

		$data = array (
			'Results' => $paginatedProperties,
			'ActiveFilters' => $filters			
		);

		//...
	}
```

Reload the page, and you should have working filter buttons now!
