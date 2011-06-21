<?php defined('SYSPATH') or die('No direct script access.');
/**
 * This is the controller for the main site.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Mapembed Controller
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */
//class Mapembed_Controller extends Template_Controller {
class Mapembed_Controller extends Controller {
	// Cache instance
	protected $cache;

	// Session instance
	protected $session;

	// Table Prefix
	protected $table_prefix;

	// Themes Helper
	protected $themes;



	public function __construct()
	{
		parent::__construct();

/*        $this->auth = new Auth();
        $this->session = Session::instance();
        $this->auth->auto_login();

        if ( ! $this->auth->logged_in('login'))
        {  
            url::redirect('login');
        }

*/
		Event::run('ushahidi_action.header_scripts');

		// Load cache
		$this->cache = new Cache;

		// Load Session
		$this->session = Session::instance();

		// Set Table Prefix
		$this->table_prefix = Kohana::config('database.default.table_prefix');

		// Themes Helper
		$this->themes = new Themes();
		$this->themes->api_url = Kohana::config('settings.api_url');



		// Retrieve Default Settings
		$site_name = Kohana::config('settings.site_name');
		// Prevent Site Name From Breaking up if its too long
		// by reducing the size of the font
		if (strlen($site_name) > 20)
		{
			$site_name_style = " style=\"font-size:21px;\"";
		}
		else
		{
			$site_name_style = "";
		}
		$this->template->header->site_name = $site_name;
		$this->template->header->site_name_style = $site_name_style;
		$this->template->header->site_tagline = Kohana::config('settings.site_tagline');

		$this->template->header->this_page = "";

		// Get tracking javascript for stats
		if(Kohana::config('settings.allow_stat_sharing') == 1){
			$this->template->footer->ushahidi_stats = Stats_Model::get_javascript();
		}else{
			$this->template->footer->ushahidi_stats = '';
		}

		// add copyright info
		$this->template->footer->site_copyright_statement = '';
		$site_copyright_statement = trim(Kohana::config('settings.site_copyright_statement'));
		if($site_copyright_statement != '')
		{
			$this->template->footer->site_copyright_statement = $site_copyright_statement;
		}

	}

	public function index()
	{
		$view = new View('mapembed/mapembed_view');

		// Cacheable Main Controller
		$this->is_cachable = TRUE;
		$view->width = (isset($_GET['width']) && is_numeric($_GET['width'])) ?
		$_GET['width'] : 573;

		$view->height = (isset($_GET['height']) && is_numeric($_GET['height'])) ?
		$_GET['height'] : 480;

		// Map and Slider Blocks
		//		$div_map = new View('mapembed/mapembed_view');
		//		$div_timeline = new View('main_timeline');
		// Filter::map_main - Modify Main Map Block
		Event::run('ushahidi_filter.map_main', $div_map);
		// Filter::map_timeline - Modify Main Map Block
		Event::run('ushahidi_filter.map_timeline', $div_timeline);
		//		$view->div_map = $div_map;
		//		$view->div_timeline = $div_timeline;

		// Check if there is a site message
		$view->site_message = '';
		$site_message = trim(Kohana::config('settings.site_message'));
		if($site_message != '')
		{
			$view->site_message = $site_message;
		}

		// Get locale
		$l = Kohana::config('locale.language.0');

		// Get all active top level categories
		$parent_categories = array();
		foreach (ORM::factory('category')
		->where('category_visible', '1')
		->where('parent_id', '0')
		->find_all() as $category)
		{
			// Get The Children
			$children = array();
			foreach ($category->children as $child)
			{
				// Check for localization of child category

				$translated_title = Category_Lang_Model::category_title($child->id,$l);

				if($translated_title)
				{
					$display_title = $translated_title;
				}
				else
				{
					$display_title = $child->category_title;
				}

				$children[$child->id] = array(
				$display_title,
				$child->category_color,
				$child->category_image
				);

				if ($child->category_trusted)
				{ // Get Trusted Category Count
					$trusted = ORM::factory("incident")
					->join("incident_category","incident.id","incident_category.incident_id")
					->where("category_id",$child->id);
					if ( ! $trusted->count_all())
					{
						unset($children[$child->id]);
					}
				}
			}

			// Check for localization of parent category

			$translated_title = Category_Lang_Model::category_title($category->id,$l);

			if($translated_title)
			{
				$display_title = $translated_title;
			}else{
				$display_title = $category->category_title;
			}

			// Put it all together
			$parent_categories[$category->id] = array(
			$display_title,
			$category->category_color,
			$category->category_image,
			$children
			);

			if ($category->category_trusted)
			{ // Get Trusted Category Count
				$trusted = ORM::factory("incident")
				->join("incident_category","incident.id","incident_category.incident_id")
				->where("category_id",$category->id);
				if ( ! $trusted->count_all())
				{
					unset($parent_categories[$category->id]);
				}
			}
		}
		$view->categories = $parent_categories;

		// Get all active Layers (KMZ/KML)
		$layers = array();
		$config_layers = Kohana::config('map.layers'); // use config/map layers if set
		if ($config_layers == $layers) {
			foreach (ORM::factory('layer')
			->where('layer_visible', 1)
			->find_all() as $layer)
			{
				$layers[$layer->id] = array($layer->layer_name, $layer->layer_color,
				$layer->layer_url, $layer->layer_file);
			}
		} else {
			$layers = $config_layers;
		}
		$view->layers = $layers;

		// Get all active Shares
		$shares = array();
		foreach (ORM::factory('sharing')
		->where('sharing_active', 1)
		->find_all() as $share)
		{
			$shares[$share->id] = array($share->sharing_name, $share->sharing_color);
		}
		$view->shares = $shares;

		// Get Reports
		// XXX: Might need to replace magic no. 8 with a constant
		$view->total_items = ORM::factory('incident')
		->where('incident_active', '1')
		->limit('8')->count_all();
		$view->incidents = ORM::factory('incident')
		->where('incident_active', '1')
		->limit('10')
		->orderby('incident_date', 'desc')
		->find_all();

		// Get Default Color
		$view->default_map_all = Kohana::config('settings.default_map_all');

		// Get Twitter Hashtags
		$view->twitter_hashtag_array = array_filter(array_map('trim',
		explode(',', Kohana::config('settings.twitter_hashtags'))));

		// Get Report-To-Email
		$view->report_email = Kohana::config('settings.site_email');

		// Get SMS Numbers
		$phone_array = array();
		$sms_no1 = Kohana::config('settings.sms_no1');
		$sms_no2 = Kohana::config('settings.sms_no2');
		$sms_no3 = Kohana::config('settings.sms_no3');
		if (!empty($sms_no1)) {
			$phone_array[] = $sms_no1;
		}
		if (!empty($sms_no2)) {
			$phone_array[] = $sms_no2;
		}
		if (!empty($sms_no3)) {
			$phone_array[] = $sms_no3;
		}
		$view->phone_array = $phone_array;

		// Get RSS News Feeds
		$view->feeds = ORM::factory('feed_item')
		->limit('10')
		->orderby('item_date', 'desc')
		->find_all();

		// Get The START, END and Incident Dates
		$startDate = "";
		$endDate = "";
		$display_startDate = 0;
		$display_endDate = 0;

		$db = new Database();
		// Next, Get the Range of Years
		$query = $db->query('SELECT DATE_FORMAT(incident_date, \'%Y-%c\') AS dates FROM incident WHERE incident_active = 1 GROUP BY DATE_FORMAT(incident_date, \'%Y-%c\') ORDER BY incident_date');

		$first_year = date('Y');
		$last_year = date('Y');
		$first_month = 1;
		$last_month = 12;
		$i = 0;

		foreach ($query as $data)
		{
			$date = explode('-',$data->dates);

			$year = $date[0];
			$month = $date[1];

			// Set first year
			if($i == 0)
			{
				$first_year = $year;
				$first_month = $month;
			}

			// Set last dates
			$last_year = $year;
			$last_month = $month;

			$i++;
		}

		$show_year = $first_year;
		$selected_start_flag = TRUE;
		while($show_year <= $last_year)
		{
			$startDate .= "<optgroup label=\"".$show_year."\">";

			$s_m = 1;
			if($show_year == $first_year)
			{
				// If we are showing the first year, the starting month may not be January
				$s_m = $first_month;
			}

			$l_m = 12;
			if($show_year == $last_year)
			{
				// If we are showing the last year, the ending month may not be December
				$l_m = $last_month;
			}

			for ( $i=$s_m; $i <= $l_m; $i++ ) {
				if ( $i < 10 )
				{
					// All months need to be two digits
					$i = "0".$i;
				}
				$startDate .= "<option value=\"".strtotime($show_year."-".$i."-01")."\"";
				if($selected_start_flag == TRUE)
				{
					$display_startDate = strtotime($show_year."-".$i."-01");
					$startDate .= " selected=\"selected\" ";
					$selected_start_flag = FALSE;
				}
				$startDate .= ">".date('M', mktime(0,0,0,$i,1))." ".$show_year."</option>";
			}
			$startDate .= "</optgroup>";

			$endDate .= "<optgroup label=\"".$show_year."\">";
			for ( $i=$s_m; $i <= $l_m; $i++ )
			{
				if ( $i < 10 )
				{
					// All months need to be two digits
					$i = "0".$i;
				}
				$endDate .= "<option value=\"".strtotime($show_year."-".$i."-".date('t', mktime(0,0,0,$i,1))." 23:59:59")."\"";

				if($i == $l_m AND $show_year == $last_year)
				{
					$display_endDate = strtotime($show_year."-".$i."-".date('t', mktime(0,0,0,$i,1))." 23:59:59");
					$endDate .= " selected=\"selected\" ";
				}
				$endDate .= ">".date('M', mktime(0,0,0,$i,1))." ".$show_year."</option>";
			}
			$endDate .= "</optgroup>";

			// Show next year
			$show_year++;
		}

		$view->startDate = $startDate;
		$view->endDate = $endDate;

		//		$view->div_timeline->startDate = $startDate;
		//		$view->div_timeline->endDate = $endDate;

		// Map Settings
		$clustering = Kohana::config('settings.allow_clustering');
		$marker_radius = Kohana::config('map.marker_radius');
		$marker_opacity = Kohana::config('map.marker_opacity');
		$marker_stroke_width = Kohana::config('map.marker_stroke_width');
		$marker_stroke_opacity = Kohana::config('map.marker_stroke_opacity');

		// pdestefanis - allows to restrict the number of zoomlevels available
		$numZoomLevels = Kohana::config('map.numZoomLevels');
		$minZoomLevel = Kohana::config('map.minZoomLevel');
		$maxZoomLevel = Kohana::config('map.maxZoomLevel');

		// pdestefanis - allows to limit the extents of the map
		$lonFrom = Kohana::config('map.lonFrom');
		$latFrom = Kohana::config('map.latFrom');
		$lonTo = Kohana::config('map.lonTo');
		$latTo = Kohana::config('map.latTo');

		// CDR: simply use the main application js, and it works fine
		$view->js = new View('main_js');
		$view->js->json_url = ($clustering == 1) ?
			"json/cluster" : "json";
		$view->js->marker_radius =
		($marker_radius >=1 && $marker_radius <= 10 ) ? $marker_radius : 5;
		$view->js->marker_opacity =
		($marker_opacity >=1 && $marker_opacity <= 10 )
		? $marker_opacity * 0.1  : 0.9;
		$view->js->marker_stroke_width =
		($marker_stroke_width >=1 && $marker_stroke_width <= 5 ) ? $marker_stroke_width : 2;
		$view->js->marker_stroke_opacity =
		($marker_stroke_opacity >=1 && $marker_stroke_opacity <= 10 )
		? $marker_stroke_opacity * 0.1  : 0.9;

		// pdestefanis - allows to restrict the number of zoomlevels available
		$view->js->numZoomLevels = $numZoomLevels;
		$view->js->minZoomLevel = $minZoomLevel;
		$view->js->maxZoomLevel = $maxZoomLevel;

		// pdestefanis - allows to limit the extents of the map
		$view->js->lonFrom = $lonFrom;
		$view->js->latFrom = $latFrom;
		$view->js->lonTo = $lonTo;
		$view->js->latTo = $latTo;

		// Javascript Header
		$this->themes->map_enabled = TRUE;
		$this->themes->main_page = TRUE;

		$view->theme_header = $this->themes->header_block();

		$view->js->default_map = Kohana::config('settings.default_map');
		$view->js->default_zoom = Kohana::config('settings.default_zoom');
		$view->js->latitude = Kohana::config('settings.default_lat');
		$view->js->longitude = Kohana::config('settings.default_lon');
		$view->js->default_map_all = Kohana::config('settings.default_map_all');

		$view->js->active_startDate = $display_startDate;
		$view->js->active_endDate = $display_endDate;

		$view->api_url = Kohana::config('settings.api_url');
		$view->map_container = 'map';
		$view->render(TRUE);
	}

} // End Main
