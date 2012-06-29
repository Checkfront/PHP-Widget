<?php 
/**
 * Checkfront Widget 
 * PHP 5 
 *
 * @package     CheckfrontWidget
 * @version     2.0
 * @author      Checkfront <code@checkfront.com>
 * @copyright   2008-2012 Checkfront Inc 
 * @license     http://opensource.org/licenses/bsd-license.php New BSD License
 * @link        http://www.checkfront.com/developers/
 * @link        https://github.com/Checkfront/PHP-Widget
 *
 *
 * This makes use of the Checkfront embeddable widgets. It allows you
 * to embed a booking widget into any website, allows you to customize 
 * the look and feel, and takes care of sizing, caching etc.
 *
 * If the booking page is hosted under SSL and e-commerce is enabled
 * the customer will stay on your site for the duration of the booking
 * unless your are using Paypal or Google Checkout.
 *
 *
 * This class is designed to provide a quick way of integrating Checkfront
 * into a website.  If you wish to further extend the platform, consider
 * using the Checkfront SDK and API instead:
 * https//www.checkfront.com/developers/api/ 
 *  
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * o Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * o The names of the authors may not be used to endorse or promote
 *   products derived from this software without specific prior written
 *   permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * @access public
 * @package Checkfront
*/
class CheckfrontWidget {

	public $lib_version = '2.1';
	public $interface_lib_version = '2.1';
	public $host= '';
	public $src = '';
	public $plugin_url = '';
	public $interface = 'v1'; //v2 recommended
	public $legacy_mode='inline';
	public $embed = false;

	public $widget = false;
	public $book_url = '';
	public $api_url = '';

	public $load_msg = 'Searching Availability';
	public $continue_msg = 'Continue to Secure Booking System';

	public $args = array();

	function __construct($cnf) {
		$this->set_host($cnf['host']);
		$this->set_pipe($cnf['pipe_url']);
		$this->set_plugin_url($cnf['plugin_url']);
		$this->interface = ($cnf['interface']) ? $cnf['interface'] : 'v1';
		$this->provider = $cnf['provider'];
		if($cnf['load_msg']) $this->load_msg = strip_tags($cnf['load_msg']);
		if($cnf['continue_msg']) $this->continue_msg = strip_tags($cnf['continue_msg']);
	}

    /**
     * set the url base for the widget.  should be an absolute url local to the site
     * eg: http://www.mysite.com/checkfront-plugin -- must also match the current schema (http:// | https://)
     * @param string $url
     * @return bool
    */
	function set_plugin_url($url) {
		$this->plugin_url = preg_replace('|/$|','',$url);
	}


    /**
     * set location of the pipe file.  most be located on the same server.
     * @param string $url
     * @return bool
    */
	function set_pipe($url) {
		$this->pipe_url = preg_replace('|/$|','',$url);
	}

    /**
     * get shortcode arg defaults
     *
     * @param void
     * @return bool
    */
	public function get_args() {
		$this->args['view']= get_option('checkfront_view');
		$this->args['theme']= get_option('checkfront_theme');
		$this->args['style_background-color']= get_option('checkfront_style_background-color');
		$this->args['style_color']= get_option('checkfront_style_color');
		$this->args['style_font-family']= get_option('checkfront_style_font-family');
		$this->args['options-tabs']= get_option('checkfront_options-tabs');
		$this->args['options-compact']= get_option('checkfront_options-compact');
		$this->args['shortcode']= get_option('checkfront_shortcode');
		if(!$this->args['shortcode']) $this->args['shortcode'] = '[checkfront]';
		return true;
	}

    /**
     * sets the checkfront host
     *
     * @param string $host
     * @return bool
    */
	private function set_host($host) {
		$this->host = $host;
		$this->url = "//{$this->host}";

		// v1 only
		$this->api_url = "{$this->url}/api/2.0";
		return true;
	}

    /**
     * set valid host
     *
     * @param string $host
     * @return string $host  
    */
	public function valid_host($value) {
		if(!preg_match('~^http://|https://~',$value)) $value = 'https://' . $value;
		if($uri = parse_url($value)) {
			if($uri['host']) {
				$host= $uri['host'];
			}
		}
		return $host;
	}


    /**
     * display error when plugin is not yet configured
     *
     * @param void
     * @return string $html formatted message
    */
	public function error_config() {
		if(is_admin()) {
			return '<p style="padding: .5em; border: solid 1px red;">' . __('Please configure the Checkfront plugin in the Wordpress Admin.') . '</p>';
		} else {
			return '<p style="padding: .5em; border: solid 1px red;">' . __('Bookings not yet available here.') .'</p>';
		}
	}

    /**
     * render booking widget
     *
     * @param array $cnf shortcode paramaters
     * @return string $html rendering code
    */
	public function render($cnf) {
		$cnf = $this->clean($cnf);
		if($this->interface == 'v1') {
			return $this->v1_interface($cnf);
		} else {
			return $this->v2_interface($cnf);
		}
	}

    /**
     * clean short code params
     *
     * @param array $cnf shortcode paramaters
     * @return array $cnf formatted paramaters
    */
	private function clean($cnf) {
		foreach($cnf as $cnf_id => $data) {
			$data = preg_replace("/\#|'/",'',strip_tags($data));
			$cnf[$cnf_id] = $data;
		}
		return $cnf;
	}

    /**
     * render v1 legacy mode
     *
     * @param array $cnf shortcode paramaters
     * @return string $html rendering code
    */
	private function v1_interface($cnf) {
		$set = array();
		if($cnf['item_id'] >0) {
			$set[] = "item_id: '{$cnf['item_id']}'";
		}
		if($cnf['category_id'] >0) {
			$set[] = "category_id: '{$cnf['category_id']}'";
		}
		if(empty($this->host)) return $this->error_config();
		if($this->legacy_mode == 'framed') {
			$html = '<iframe src="//' . $this->host . '/www/client/?wp" style="border: 0; width: 100%; height: 800px"></iframe>';
		} else {
			$html = $this->droplet($set);
		}
		return $html;
	}

    /**
     * set the category and item id filters
     *
     * @param strong $ids 
     * @return string $ids eg 1,2,5
    */
	private function set_ids($ids) {
		return preg_replace("/[^0-9,]/",'',$ids);
	}


    /**
     * render v2 interface
     *
     * @param array $cnf shortcode paramaters
     * @return string $html rendering code
    */
	private function v2_interface($cnf) {
		$cnf['widget_id'] = ($cnf['widget_id'] > 0) ? $cnf['widget_id'] : '01';
		$html = "\n<!-- CHECKFRONT BOOKING PLUGIN v{$this->lib_version}-->\n";
		$html .= '<div id="CHECKFRONT_WIDGET_' . $cnf['widget_id'] . '"><p id="CHECKFRONT_LOADER" style="background: url(\'//' . $this->host . '/images/loader.gif\') left center no-repeat; padding: 5px 5px 5px 20px">' . $this->load_msg . '...</p></div><noscript><a href="https://' . $this->host . '/reserve/" style="font-size: 16px">' . $this->continue_msg . ' &raquo;</a></noscript>';
		$html .= "\n<script type='text/javascript'>\nnew CHECKFRONT.Widget ({\n";
		$html .= "host: '{$this->host}',\n";
		$html .= "provider: '{$this->provider}',\n";
		$html .= "pipe: '{$this->pipe_url}',\n";
		$html .= "target: 'CHECKFRONT_WIDGET_{$cnf['widget_id']}',\n";
		// optional, or default items
		if($cnf['item_id']) {
			$this->cnf['item_id'] = $this->set_ids($this->cnf['item_id']);
			$html .= "item_id: '{$cnf['category_id']}',\n";
		}
		if($cnf['category_id']) {
			$this->cnf['category_id'] = $this->set_ids($this->cnf['category_id']);
			$html .= "category_id: '{$cnf['category_id']}',\n";
		}	
		if($cnf['theme']) $html .= "theme: '{$this->theme}',\n";
		if($cnf['width'] > 0)  $html .= "width: '{$cnf['width']}',\n";
		if($cnf['layout'])  $html .= "layout: '{$cnf['layout']}',\n";
		if($cnf['tid'])  $html .= "tid: '{$cnf['tid']}',\n";
		if($cnf['options'])  $html.= "options: '{$cnf['options']}',\n";
		if($cnf['style'])  $html .= "style: '{$cnf['style']}'";
		$html .="\n}).render();\n</script>\n";
		return $html;
	}	

    /**
     * legacy droplet settings
     *
     * @param array $set 
     * @return string $html 
    */
	public function droplet_set($set=array()) {
		if(count($set)) {
			$CF_set = '<input type="hidden" id="CF_set" value="{' . implode(',',$set) . '}" />';
		}
		if($this->book_url) {
			$CF_set .= '<input type="hidden" id="CF_src" value="' . $this->book_url .'" />';
		}
		return $CF_set;
	}


    /**
     * v1 droplet
     *
     * @param array $cff
     * @return string html id
    */
	function droplet($set) {
		return '<div id="CF" class="' . $this->host . '"><p id="CF_load" class="CF_load">' . __('Searching Availability...') . $this->droplet_set($set) . '</div>';
	}
}
?>
