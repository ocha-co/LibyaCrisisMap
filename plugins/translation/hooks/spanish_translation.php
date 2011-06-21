<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Spanish Translation Ushahidi Hook - Load All Events
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

class spanish_translation {

	protected $langdelim = "\n\n====\n"; //DELIMETER FOR SEPARATING LANGUAGES
	protected $appid = "D9B52C8F6E76ACB98CB5E619F8BF542843818751"; //MAKE THIS YOUR BING AppID

	protected $langidurl = "http://api.microsofttranslator.com/V2/Http.svc/Detect";
	protected $langtrurl = "http://api.microsofttranslator.com/V2/Http.svc/Translate";


	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		// Hook into routing
		//die('message received and translating');

		Event::add('system.pre_controller', array($this, 'add'));

	}

/*
message_email_add 	 Action gives you access to the just added incoming email message 	 $message object
message_sms_add 	Action gives you access to the just added incoming email message 	$message object
message_twitter_add 	Action gives you access to the just added incoming twitter message 	$message object


*/

	public function add(){
		Event::add('ushahidi_action.message_email_add', array($this, 'translate'));
		Event::add('ushahidi_action.message_sms_add', array($this, 'translate'));
		Event::add('ushahidi_action.message_twitter_add', array($this, 'translate'));

		//die('message received and translating');

	}


	/**
	 * Makes the translation of messages
	 */
	public function translate()
	{
		try{

			$message = Event::$data;

			$message->save();
			$mes = $message->__get('message');
			$det = $message->__get('message_detail');

			$lurl = $this->langidurl."?appId=".$this->appid."&text=".urlencode($mes);
			$ch = $this->get_remote_url($lurl);
			$lang = trim(strip_tags($ch));

			if($lang == ''){
				$lang = "en";
			}

			$from = $lang;

			$final_det = "$lang:\n".$det;

			if($lang != 'es'){
				$to = "es";
				$lurl = $this->langtrurl."?appId=".$this->appid."&text=".urlencode($mes)."&from=".$from."&to=".$to;

				$ch = $this->get_remote_url($lurl);
				$final_det .= $this->langdelim.$to.":\n\n".trim(strip_tags($ch));

				if($det != ''){
					$lurl = $this->langtrurl."?appId=".$this->appid."&text=".urlencode($det)."&from=".$from."&to=".$to;
					$ch = $this->get_remote_url($lurl);
					$final_det .= "\n\n".trim(strip_tags($ch));;
				}
			}

			if($lang != 'en'){
				$to = "en";

				$lurl = $this->langtrurl."?appId=".$this->appid."&text=".urlencode($mes)."&from=".$from."&to=".$to;
				$ch = $this->get_remote_url($lurl);
				$final_det .= $this->langdelim.$to.":\n\n".trim(strip_tags($ch));

				if($det != ''){
					$lurl = $this->langtrurl."?appId=".$this->appid."&text=".urlencode($det)."&from=".$from."&to=".$to;
					$ch = $this->get_remote_url($lurl);
					$final_det .= "\n\n".trim(strip_tags($ch));;
				}
			}

		}
		catch(Exception $e){
			$this->error_messages .= 'Caught exception: '.$e->getMessage()."\n";
			die($this->error_messages);
		}

		$message->__set('message_detail',$final_det);
		$message->save();



	}


	public $error_messages = '';

	//returns content from remote url
	public function get_remote_url($url,$post_params=''){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		/*
		if($post_params != ''){
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$post_params);
		}
		*/

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');

		$this->error_messages .= "processing $url\n";

		if($ch){
			$ret = curl_exec($ch);
		}
		else{
			die("bad curl\n".$this->error_messages);
		}

		curl_close($ch);
		return $ret;

	}


	public function spanish_translation()
	{
		$st = new View("spanish_translation/spanish_translation");
		$st->render(TRUE);
	}
}
new spanish_translation;
