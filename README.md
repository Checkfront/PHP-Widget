![Checkfront](https://media.checkfront.com/images/brand/Checkfront-Logo-Tag-60.png)

Checkfront PHP Widget Class (v2.4)
==========================

The [Checkfront Widget Class](http://www.checkfront.com/developers/widget/) allows you 
quickly integrate Checkfront into a website and start taking bookings. The class
builds the code to render the booking portal, change the look and feel, include 
additional tracking, and takes care of sizing and caching.

If you require further customizations not available in the Widget Class, the [Checkfront API and PHP SDK](http://www.checkfront.com/developers/api/) 
should provide you with the tools needed to further extend the platform.


Except as otherwise noted, the Checkfront PHP SDK is licensed under the Apache Licence, Version 2.0
(http://www.apache.org/licenses/LICENSE-2.0.html)


Usage
-----

You'll need to include the CheckfrontWidget.php class, and call 
CheckfrontWidget::render() where you wish to display the booking portal.

*You must:*

**Include the Checkfront javascript library in your html head:**

```html
<html>
	<head>
		<script src="//media.checkfront.com/lib/interface--20.js" type="text/javascript"></script>
	</head>
</html>
```

**Install pipe.html on the server (included with this package):**

To help with sizing and caching, you need to install pipe.html somewhere on the server. It's a static
html file and can be hosted on any platform.

The file must be located on the same domain of the booking page. You can rename it if 
you like, but it must be accessible by browser at the supplied pipe_url (see below).


```php
<?
// apache - may require tweaking with nginx
// it's important it match with what the browser is on.
$schema = ($_SERVER['HTTPS'] != "on") ? 'http' : 'https'; 

// replace demo.checkfront.com with your checkfront host
$checkfront_host = 'demo.checkfront.com'; 

include_once('CheckfrontWidget.php');
$Checkfront = new CheckfrontWidget(
   array(
         'host'=>$checkfront_host, 
         'pipe_url'=>"/pipe.html", 
    )
);

// Let's build a basic web page, with the required javascript include 
echo '<html><head><script src="//' . $checkfront_host . '/lib/interface--20.js" type="text/javascript"></script></head><body>';

echo '<div style="width: 1000px; margin: 0 auto"><h1>Book Online!</h1>';

// Render the booking portal.
echo $Checkfront->render(
    array(
        'options'=>'tabs,compact', // use tab layout and compact layout (optional)
        'style'=>'background-color: #fff; color: #000; font-family: Arial',
        'tid'=>'homepage', // custom tracking id to attach to bookings
		'theme'=>'default',
		'layout'=>'D', // detail or list view for the booking page
		'width'=>'auto', // will assume the width of the parent by default
		'category_id'=>'', // numeric category_id filter
        'item_id'=>'', // numeric item_id filter, can include multipe, eg 1,4,2
        'date'=>'', // forward to a specific date YYYYMMDD
        'load_msg'=>'Searching', // Override default loading message
    )
);
echo '</div>';
echo '</body></html>';
?>
```


