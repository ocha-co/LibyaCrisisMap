<?php defined('SYSPATH') or die('No direct script access.');
/**
 * CSV Download - downloads the reports in csv format
 *
 * @author	   George Chamales
 * @package	   CSV Download
 */

class csvdownload {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
	
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		//Just in case we need this
//		Event::add('ushahidi_action.nav_main_top', array($this, '_add_csv_download_tab'));	
	}
	
	
	//adds a tab for the report download on the front end
	public function _add_csv_download_tab()
	{
		$this_page = Event::$data;
		
		$menu = "";
		$menu .= "<li><a href=\"".url::site()."reports_download\" ";
		$menu .= ($this_page == 'bigmap') ? " class=\"active\"" : "";
		$menu .= ">Download Reports</a></li>";
		echo $menu;
	}
	

	
}//end class

new csvdownload;
