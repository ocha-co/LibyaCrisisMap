<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This controller is used to list/ view and feeds reports
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Reports Controller  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Feeds_Controller extends Main_Controller {

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays all reports.
     */
    public function index() 
    {
        $this->template->header->this_page = Kohana::lang('ui_admin.feeds');
        $this->template->content = new View('feeds');
        
        // Pagination
        $pagination = new Pagination(array(
                      'query_string' => 'page',
                      'items_per_page' => (int) Kohana::config('settings.items_per_page'),
                      'total_items' => ORM::factory('feed_item')
                                       ->count_all()
                      ));

        $feeds = ORM::factory('feed_item')
                     ->orderby('item_date', 'desc')
                     ->find_all( (int) Kohana::config('settings.items_per_page'), 
                                 $pagination->sql_offset);
        
        $this->template->content->feeds = $feeds;
        
        //Set default as not showing pagination. Will change below if necessary.
        $this->template->content->pagination = ''; 
        
        // Pagination and Total Num of Report Stats
        if($pagination->total_items == 1)
        {
            $plural = '';
        }
        else
        {
            $plural = 's';
        }
        if ($pagination->total_items > 0)
        {
            $current_page = ($pagination->sql_offset/ (int) Kohana::config('settings.items_per_page')) + 1;
            $total_pages = ceil($pagination->total_items/ (int) Kohana::config('settings.items_per_page'));
            
            if($total_pages > 1)
            { // If we want to show pagination
                $this->template->content->pagination_stats = Kohana::lang('ui_admin.showing_page').' '.$current_page.' '.Kohana::lang('ui_admin.of').' '.$total_pages.' '.Kohana::lang('ui_admin.pages');
                
                $this->template->content->pagination = $pagination;
            }
            else
            { // If we don't want to show pagination
                $this->template->content->pagination_stats = $pagination->total_items.' '.Kohana::lang('ui_admin.feeds');
            }
        }
        else
        {
            $this->template->content->pagination_stats = $pagination->total_items.' '.Kohana::lang('ui_admin.feeds');
        }
        
		// Rebuild Header Block
        $this->template->header->header_block = $this->themes->header_block();

        /*$icon_html = array();
        $icon_html[1] = "<img src=\"".url::base()."media/img/image.png\">"; //image
        $icon_html[2] = "<img src=\"".url::base()."media/img/video.png\">"; //video
        $icon_html[3] = ""; //audio
        $icon_html[4] = ""; //news
        $icon_html[5] = ""; //podcast
        
        //Populate media icon array
        $this->template->content->media_icons = array();
        foreach($incidents as $incident)
        {
            $incident_id = $incident->id;
            if(ORM::factory('media')
               ->where('incident_id', $incident_id)->count_all() > 0)
            {
                $medias = ORM::factory('media')
                          ->where('incident_id', $incident_id)->find_all();
                
                //Modifying a tmp var prevents Kohona from throwing an error
                $tmp = $this->template->content->media_icons;
                $tmp[$incident_id] = '';
                
                foreach($medias as $media)
                {
                    $tmp[$incident_id] .= $icon_html[$media->media_type];
                    $this->template->content->media_icons = $tmp;
                }
            }
        }*/
    } 
    
} // End Reports
