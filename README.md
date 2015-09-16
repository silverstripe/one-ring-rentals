## Lesson 17: Ajax Behaviour and ViewableData

In this tutorial, we're going to add some Ajax behaviour to our site.

## Writing the Javascript

In the last tutorial, we added pagination to our list of search results. Let's now enhance the user experience a bit by adding Ajax to the pagination links.

Before we do anything, we'll need to add some JavaScript that will add this functionality. We'll do this in our catch-all JavaScript file, `scripts.js`.

*themes/one-ring/js/scripts.js*
```js
// Pagination
if ($('.pagination').length) {
	$('.main').on('click','.pagination a', function (e) {
	    e.preventDefault();
	    var url = $(this).attr('href');
	    $.ajax(url)
	        .done(function (response) {
	            $('.main').html(response);
	        })
	        .fail (function (xhr) {
	            alert('Error: ' + xhr.responseText);
	        });
	});
}

```

This is pretty specific to this use-case. Further down the track, we may find that we're adding a lot of Ajax events that closely resemble this, so we may want to make it more reusable at some point, but for now, let's just get this working.

Let's give this a try. Click on a link in the pagination and see if it works.

It kind of works, right? But we've still got a way to go. The controller is returning the entire page -- from `<html>` to </html`> into our `.main` div. Not good, but it is the expected result. The Ajax URL is just the `href` attribute, so anything different would be unusual.

So what do we do? Change the URL in our Javascript to use something other than `href`? We could use an alternative URL in something like `data-ajax-url`. That's actually not necessary. We always aim to keep things tidy with single endpoints. The controller ideally know as little about the UI as possible, and setting up a separate endpoint for Ajax requests in this case would be antithetical to that. We'll keep the same endpoint, and we'll just assign the controller the ability to detect Ajax requests.

## Detecting Ajax in a controller

Let's update `PropertySearchPage.php` to detect Ajax.

*mysite/code/PropertySearchPage.php*
```php
public function index(SS_HTTPRequest $request) {
	
	//...

	if($request->isAjax()) {
		return "Ajax response!";
	}
	
	return array (
		'Results' => $paginatedProperties
	);
}
```

Now give the link a try, and see what we get. You should see your custom Ajax response. Now we just need to return some partial content. Before we do that, let's talk a bit about a key player in SilverStripe Framework called `ViewableData`.

## An overview of ViewableData

To establish a basis for the next section of this lesson, we'll need to know more about how `ViewableData` objects work. `ViewableData` is a primitive class in SilverStripe that essentially allows its public properties and methods to render content to a template. The most common occurance of `ViewableData` objects is in `DataObject` instances, which we've been working with on templates exclusively. But templates are capable of rendering much more than database content. You just need to go further up the inheritance chain, above `DataObject` to `ViewableData`, or a subclass thereof.

Let's look at a simple example of `ViewableData`.

```php
class Address extends ViewableData {
	
	public $Street = '123 Main Street';

	public $City = 'Compton';

	public $Zip = '90210';

	public $Country = 'US';

	public function Country() {
		return MyGeoLibrary::get_country_name($this->Country);
	}

	public function getFullAddress() {
		return sprintf(
			'%s<br>%s %s<br>%s'
			$this->Street,
			$this->City,
			$this->Zip,
			$this->Country() 
		);
	}
}
```

Now let's create a template to render our `Address` object.

*AddressTemplate.ss*
```html
<p>I live on $Street in $City.</p>
<p>My full address is $FullAddress.</p>
```

As you can see, we're rendering data using a combination of both methods and properties. `ViewableData` has a very specific way of resolving the template variables on the object:

* Check if there public method on the object called [VariableName]
* If not, check if a method called "get[VariableName]" exists
* If not, check if there is a public property named [VariableName]
* Otherwise, call "getField([VariableName])"

`getField()` is a fallback method. For the base `ViewableData` class, it simply returns `$this->$VariableName`. The idea is that subclasses can invoke their own handlers for this. For example, in `DataObject`, `getField()` looks to the `$db` array.

All `ViewableData` objects know how to render themselves on templates. To do that, simply invoke `renderWith($templateName)` on the object, and the template variables will be scoped to that object.

```php
$myViewableData = Address::create();
echo $myViewableData->renderWith('AddressTemplate');
```

Another really useful feature of `ViewableData` is that the object itself can be called on a template and render itself. If we were to simply call `$MyAddressObject` on a template, SilverStripe would attempt to invoke a method called `forTemplate()` on the object to render it as a string. In our example address object, that might look like this:

```php
class Address extends ViewableData {
	
	//...	

	public function forTemplate() {
		return $this->getFullAddress();
	}
}
```

A great example of this is SilverStripe's `Image` class. When you call `$MyImage` on a template, it invokes its `forTemplate()` method, which returns a string of HTML representing an `<img />` tag with all the correct attributes and values.

## Rendering a partial template

So now that we have a good understanding of `ViewableData`, let's play around with some of its features. Right now, we're just returning a string to the template for our Ajax response. Let's instead return a partial template.

At the centre of dealing with Ajax responses is the use of includes in your Layout template. Let's take everything in the `.main` div, and export it to an include called `PropertySearchResults`.

*themes/one-ring/templates/Includes/PropertySearchResults.ss*
```html
<!-- BEGIN MAIN CONTENT -->
<div class="main col-sm-8">
	<% include PropertySearchResults %>				
</div>	
<!-- END MAIN CONTENT -->
```

Reload the page with `?flush` to get the new template.

Now, returning an Ajax response is trivial. Simply render the include.

```php
class PropertySearchPage_Controller extends Page_Controller {


	public function index(SS_HTTPRequest $request) {

		//...
		
		if($request->isAjax()) {
			return $this->renderWith('PropertySearch');
		}
		
		//..
	}
}
```

Let's try it out. It's not quite working right. We're getting a "no results" message when we paginate. That's because the `$Results` variable is not exposed to the template through `renderWith()`. It's just a local variable in our `index()` method. We have two choices here:

* Assign `$paginatedProperties` to a public property on the controller
* Explicitly pass it to the template using `customise()`.

Of these two options, the latter is much more favourable. There are cases where the first option makes more sense, but in this case, explicitly passing the list makes our `PropertySearchResults` template more reusable, and assigning a new member property would pollute our controller unnecessarily. Let's make that update now.

```php
class PropertySearchPage_Controller extends Page_Controller {


	public function index(SS_HTTPRequest $request) {

		//...
		
		if($request->isAjax()) {
			return $this->customise(array
				'Results' => $paginatedResults
			))->renderWith('PropertySearchResults');
		}

		return array (
			'Results' => $paginatedProperties
		);
	}
}
```
We now have repeated our array of data, so let's clean that up a bit.

```php
class PropertySearchPage_Controller extends Page_Controller {


	public function index(SS_HTTPRequest $request) {

		//...
		
		$data = array (
			'Results' => $paginatedProperties
		);

		if($request->isAjax()) {
			return $this->customise($data)
						 ->renderWith('PropertySearchResults');
		}

		return $data;
	}
}
```

Try it now. It's looking much better!

## Adding some UX enhancements

There are two major shortcomings of this user experience:
* The scroll stays fixed to the bottom of the results, leaving the user with little indication that the content has been updated
* The URL is not updated, so a page refresh after paginating will take the user back to the first page

Let's clean up both of these things now, with some updates to our Javascript.

*themes/one-ring/js/scripts.js*
```js
// Pagination
if ($('.pagination').length) {
    var paginate = function (url) {
        $.ajax(url)
            .done(function (response) {
                $('.main').html(response);
                $('html, body').animate({
                    scrollTop: $('.main').offset().top
                });
                window.history.pushState(
                    {url: url},
                    document.title,
                    url
                );    
            })
            .fail(function (xhr) {
                alert('Error: ' + xhr.responseText);
            });

    }
    $('.main').on('click','.pagination a', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        paginate(url);
    });
    
    window.onpopstate = function(e) {
        if (e.state.url) {
            paginate(e.state.url);
        }
        else {
            e.preventDefault();
        }
    };        
}

```
First, we'll add an `animate()` method that will handle the automatic scrolling. Then, we'll push some state to the browser history using `pushState`.

Lastly, we make export the `.ajax()` call to a function, so that both the pagination links and the browser back button will be able to invoke it when we add an `onpopstate` event.
