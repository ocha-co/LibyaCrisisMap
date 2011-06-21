<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Private Ushahidi Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class privatize {
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
		// GC - should this be changed to !admin?
		// Only add the events if we are on that controller
		/*if (Router::$controller == 'main')
		{
		Event::add('ushahidi_action.header_scripts', array($this, 'privatize'));
		}*/
		Event::add('ushahidi_action.header_scripts', array($this, 'privatize'));
			
       }   	


	public function privatize()
	{
        $this->auth = new Auth();
        $this->session = Session::instance();
        $this->auth->auto_login();

        if ( ! $this->auth->logged_in('login'))
        {  
            url::redirect('login');
        }
	if(Router::$controller != "api"){ 
		$logout = new View("privatize/logout");
		$logout->render(TRUE);
	}

	}
}
new privatize;

