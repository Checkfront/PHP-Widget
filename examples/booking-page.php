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
         'pipe_url'=>"{$schema}://{$_SERVER['HTTP_HOST']}/pipe.html", 
         'interface' =>'v2',
    )
);

// pipe_url -- pipe.html ^
// To help with sizing and caching, you need to install pipe.html somewhere on the server.
// The file must be located on the same domain of the booking page. You can rename it if 
// you like, but it must be accesable by browser at the supplied url. 
// pipe.html is included in this package.

// Let's build a basic web page, with the required javascript include 
echo '<html><head><script src="//' . $checkfront_host . '/lib/interface.js?v=3" type="text/javascript"></script></head><body>';

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
