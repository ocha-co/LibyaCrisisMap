<?php

//defined('SYSPATH') or die('No direct script access.');
//require_once(dirname(__FILE__)."../../../../index.php");

/**
 * Translate Hook - Load All Events
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

class Translate{
	protected $field_name = "Translation";

	protected $bing_app_key = "D9B52C8F6E76ACB98CB5E619F8BF542843818751"; //MAKE THIS YOUR BING bing_app_key
	protected $google_app_key = "ABQIAAAALd2FVd0aLtAI0NIjSsQbCxQiPnkLK3Pc1fTQUZPz8uq5mP5QrhRnQM8d4WYH01enHeUeOnBd8KL4hQ"; //MAKE THIS YOUR Google API Key (optional)
	protected $user_ip = ""; //Your IP address - optional Google parameter

	protected $lang_delim = "==== CODE"; //DELIMETER FOR SEPARATING LANGUAGES IN MESSAGES - 'CODE' will be replaced with the given language code
	protected $line_delim = "[\n\r]"; //Default delimeter (as a regular expression) to separate sentences or lines for translation. The empty string will not split;
	protected $per_line_lang_detect = 1; //Whether to perform language detection per-line (if messages contain more than one language). Default 0=no;

	protected $target_langs  = array('en', 'ar'); //LIST OF TARGET LANGUAGE CODES

	protected $bing_source_preference  = array('kr'); //LIST OF ALL LANGUAGE CODES FOR WHICH BING IS GIVEN PRIORITY OVER GOOGLE FOR SOURCE LANGUAGE
	protected $bing_target_preference  = array('kr'); //LIST OF ALL LANGUAGE CODES FOR WHICH BING IS GIVEN PRIORITY OVER GOOGLE FOR TARGET LANGUAGE
	protected $bing_detect_preference  = array('kr'); //LIST OF LANGUAGES FOR WHICH BING IS BETTER AT LANGUAGE DETECTION

	protected $bing_lang_detect_url  = "http://api.microsofttranslator.com/V2/Http.svc/Detect";
	protected $bing_translate_url  = "http://api.microsofttranslator.com/V2/Http.svc/Translate";

	protected $google_lang_detect_url  = "https://ajax.googleapis.com/ajax/services/language/detect";
	protected $google_translate_url  = "https://ajax.googleapis.com/ajax/services/language/translate";



	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		// Hook into routing
		$this->db =  new Database();

		if(defined('SYSPATH')){
			Event::add('system.pre_controller', array($this, 'add'));
		}
		else{
			$this->from_command(); //Behavour if run from command line (debugging)
		}

	}

	/**
	* function to perform when run from command line
	*/
	public function from_command(){
		$f = file_get_contents($GLOBALS['argv'][1]);
		$lines = preg_split("/\n/",$f);
		print $this->translate_subject_content($lines[0], $f);
	}


	public function add(){
		Event::add('ushahidi_action.message_email_add', array($this, 'translate_messages'));
		Event::add('ushahidi_action.message_sms_add', array($this, 'translate_messages'));
		Event::add('ushahidi_action.message_twitter_add', array($this, 'translate_messages'));

		Event::add('ushahidi_action.report_add', array($this, 'move_translation_to_field'));
		Event::add('ushahidi_action.report_edit', array($this, 'move_translation_to_field'));


		//HOOKS BELOW ARE FOR CONCURRENT USE
		//Event::add('ushahidi_action.admin_header_top_left', array($this, 'add_header_js'));
		//Event::add('ushahidi_action.report_form_admin', array($this, 'add_current_users'));

		//die('message received and translating');

	}

	/**
	*
	* lists the people currently editing this form
	*
	*/
	public function add_current_users(){
		$incident_id = Event::$data;
		print $this->get_current_user_html($incident_id, true);
	}

	/**
	* returns the HTML div of current users
	* @incident_id report id
	* @show_warning whether to print a warning that other people are editing the current report
	* @popup_warning whether to give the warning as a popup 'alert'. Prints to screen if false.
	*/
	public function get_current_user_html($incident_id, $show_warning=false, $popup_warning=true){
		$view_table = Kohana::config('database.default.table_prefix')."report_viewer";;

		$sql = "SELECT * FROM $view_table WHERE incident_id = '$incident_id'";
		$resa = $this->db->query($sql)->result_array();
		$ac = new Auth_Core();
		$usera = $ac->get_user()->as_array();
		$username = $usera['username'];

		$html = "\n".'<div id="current_editors">'."\n".'<h4>Users currently editing this report</h4>';

		$others = array();
		$all = array();
		foreach($resa as $user){
			$un = $user->username;
			$all[] = $un;
			if($un != $username){
				$others[] = $un;
				$all[count($all)-1] = '<font class="other_editor" style="color:red;">'.$all[count($all)-1].'</font>';
			}
		}
		$html .= implode(' | ', $all);

		if($show_warning && count($others) > 0){
			//Someone else is editing this report: give warning

			$warning = "Warning. Another user is editing this report: ";
			$alertjs = 'alert("'.$warning." \\n".implode(' | ', $others).'");';
			$alertns = '<font class="other_editor_warning" style="color:red;">'.$warning." <br />".implode(' | ', $others).'</font>';

			if($popup_warning){
				$html .= "\n".'<script type="text/javascript" charset="utf-8">'."\n";
				$html .= " $alertjs \n</script> \n<noscript>\n $alertns \n</noscript> \n";
			}
			else{
				$html .= "\n <br /> $alertns \n";
			}
		}

		$html .= "\n".'</div>'."\n";

		return $html;

	}



	/**
	*
	* adds the header info for this report
	*
	*/
	public function add_header_js(){
		print "<b>WHERE AM I</b>";
		print_r($_POST);
		$url = $_SERVER["REQUEST_URI"];
		print $url;

		/**
		* admin/reports/edit/16
		* admin/reports/edit?mid=16
		*
		*/

		if(preg_match("/admin.reports.edit/", $url)){
			$report_num = preg_replace("/.*?[^0-9]([0-9]*)[^0-9]*$/","$1",$url);
			print "\n".'<script type="text/javascript" charset="utf-8">';

			//TODO: change this to a curl within PHP for interfaces without scripts
			print '
				var oRequest = new XMLHttpRequest();
				var sURL  = "http://"+self.location.hostname+"/admin/translate_settings/report_status?rid='.$report_num.'";

				oRequest.open("GET",sURL,false); //TODO: change this to parallel process
				oRequest.setRequestHeader("User-Agent",navigator.userAgent);
				oRequest.send(null);

				if (oRequest.status==200){
					//alert(oRequest.responseText);
				}
				else{
					//alert("Error executing XMLHttpRequest call for "+sURL);
				}
			';

			print "</script>\n";
		}

		print '
			<script type="text/javascript" charset="utf-8">

			function addLoadEvent(func) {
				var oldonload = window.onload;
				if (typeof window.onload != \'function\') {
					window.onload = func;
				} else {
					window.onload = function() {
						if (oldonload) {
							oldonload();
						}
					func();
				}
			}

			function updateEditors() {

				var editorsURL = "http://"+self.location.hostname+"/admin/translate_settings/current_editors?rid='.$report_num.'";
				oRequest.open("GET",editorsURL,false); //TODO: change this to parallel process
				oRequest.setRequestHeader("User-Agent",navigator.userAgent);
				oRequest.send(null);

				var currEds = document.getElementById(\'current_editors\');

				//TODO add check the currEds found something
				var curHTML = currEds.innerHTML;


				if (oRequest.status==200){
					//TODO add compare if new is different to old
					var currEds = document.getElementById(\'current_editors\');
					currEds.innerHTML = oRequest.responseText;
				}
				setTimeout(\'updateEditors()\',5000);
			}

			addLoadEvent(updateEditors);

			</script>
		';
		// http://ec2-184-73-10-10.compute-1.amazonaws.com/admin/translate_settings/current_editors?rid=12



	}

	/**
	*
	*
	*
	*/
	public function move_translation_to_field(){
		$report = Event::$data;
		$id  = $report->__get('id');
		$desc = $report->__get('incident_description');

		$delimregex = preg_replace('/CODE/','[a-zA-Z]*',$this->lang_delim);

		if(preg_match("/$delimregex/",$desc)){
			$new_desc = preg_replace("/$delimregex.*/","",$desc);
			$report->__set('incident_description',$new_desc);
			$report->save();
			$trans = preg_replace("/.*?($delimregex.*)/","$1",$desc,1);

			$this->db =  new Database();
			$form_table = Kohana::config('database.default.table_prefix')."form_field";
			$fn = addslashes($this->field_name);
			$pk = "id";
			$res = $this->db->query("SELECT $pk FROM $form_table WHERE field_name = '$fn'");
			$fid = 0;
			if($row=$res->result_array()){
				$fid = $row[0]->$pk;
			}
			$frm = new Form_Response_Model();
			$frm->__set('form_field_id',$fid);
			$frm->__set('incident_id',$id);
			$frm->__set('form_response',$trans);
			$frm->save();


		}
	}


	/**
	* Returns the best-guess language of $test_str via Google with backoff to Bing
	* Assumes that if Bing is a preference for a given language as a source, then best-guess is Bing with back off to Google.
	*
	*/
	public function identify_language($test_str){
		//1. GOOGLE
		$url = $this->google_lang_detect_url ."?v=1.0&q=".urlencode($test_str);
		if($this->google_app_key != ''){
			$url .= "&key=".$this->google_app_key;
		}
		$ch = json_decode($this->get_remote_url($url), true);
		$glang = $ch['responseData']['language'];

		//2. BING
		$blang = "";
		if($this->bing_app_key != ''){
			//use bing lang ident
			$url = $this->bing_lang_detect_url ."?appId=".$this->bing_app_key."&text=".urlencode($test_str);
			$ch = $this->get_remote_url($url);
			$blang = trim(strip_tags($ch));
		}

		$lang = $glang;
		if($blang != "" && in_array($blang, $this->bing_detect_preference)){
			$lang = $blang;
		}

		return $lang;
	}


	/**
	* translate the given message using Bing
	*/
	public function bing_translate($to, $from, $mes){
		$url = $this->bing_translate_url ."?appId=".$this->bing_app_key."&text=".urlencode($mes)."&from=".$from."&to=".$to;
		$trans = $this->get_remote_url($url);
		if(preg_match("/.?.?.?html..?.?body..?.?.?h1..?Argument Exception..h1./i",$trans)){
			return $mes; //bing error
		}
		return preg_replace('/^\s*/', '', strip_tags($trans));
	}



	/**
	* translate the given message using Google
	*/
	public function google_translate($to, $from, $mes){
		$url = $this->google_translate_url ."?v=1.0&q=".urlencode($mes)."&langpair=".$from.urlencode('|').$to;
		if($this->google_app_key != ''){
			$url .= "&key=".$this->google_app_key;
		}
		if($this->user_ip != ''){
			$url .= "&userip=".$this->user_ip;
		}

		$jsona = json_decode($this->get_remote_url($url), true);
		$trans = $jsona['responseData']['translatedText'];
		return $trans;
	}


	/**
	 * Makes the translation of messages
	 */
	public function translate_messages(){
		try{

			$message = Event::$data;

			$message->save();
			$mes = $message->__get('message');
			$det = $message->__get('message_detail');

			$translation = $this->translate_subject_content($mes, $det);
			$final_det = $det."\n\n".$translation;

			$message->__set('message_detail',$final_det);
			$message->save();
		}
		catch(Exception $e){
			$this->error_messages .= 'Caught exception: '.$e->getMessage()."\n";
			//die($this->error_messages);
		}
	}



	 /**
	 * Translates the given subject and content
	 */
	public function translate_subject_content($subject, $content){
		try{
			$mes = $subject;
			$det = $content;

			$test_str = $mes;
			if($mes == ''){
				$test_str = $det;
			}
			if($test_str == ''){
				return; //no content!
			}

			//1. LANGUAGE IDENTIFICATION

			$lang = $this->identify_language($test_str);
			if($lang == ''){
				$lang = "en"; //default to English when no guess was possible
			}

			$lines = array($mes.".\n\n".$det);
			$ld = $this->line_delim;
			if($ld != ''){
				$lines = preg_split("/$ld/", $mes."\n\n".$det, -1 , PREG_SPLIT_DELIM_CAPTURE);
			}

			$translation = "";
			foreach($this->target_langs as $target_lang){
				$delim = preg_replace('/CODE/',$target_lang, $this->lang_delim);
				$translation .= $delim."\n";

				if($target_lang == $lang && $this->per_line_lang_detect == 0){
					$translation .= $mes."\n\n".$det;
					continue; //already in the target language
				}

				foreach($lines as $line){
					$trans = "";
					if(preg_match('/^[\s\r\n]*$/',$line)){
						$trans = $line;
					}
					else{
						$source_lang = $lang;
						if($this->per_line_lang_detect == 1){
							$source_lang = $this->identify_language($line);
							if($source_lang == ''){
								$source_lang = $lang; //default to global lang prediction
							}
						}

						$bf = 0; //if Bing first
						if(in_array($target_lang, $this->bing_target_preference) || in_array($lang, $this->bing_source_preference)){
							$bf = 1;
							$trans = $this->bing_translate($target_lang, $source_lang, $line);
						}
						if($trans == "" || $trans == $line){
							$trans = $this->google_translate($target_lang, $source_lang, $line);
						}
						if($bf == 0 && ($trans == "" || $trans == $line)){
							$trans = $this->google_translate($target_lang, $source_lang, $line);
						}
					}
					if(preg_match("/[\n\r]$/",$line) || !(preg_match("/[\n\r]$/",$trans))){
						$trans .= "\n"; //add newline char if the translation services removed it
					}
					$translation .= $trans;
				}


				$translation .= "\n";
			}
			$translation = preg_replace('/[\n\r][\n\r][\n\r]*/',"\n\n",$translation);
			return $translation;
		}
		catch(Exception $e){
			$this->error_messages .= 'Caught exception: '.$e->getMessage()."\n";
			//die($this->error_messages);
		}

	}


	public $error_messages = '';

	/**
	* Returns content from remote url
	* Attempts a post if the given url with get params is too long
	*/
	public function get_remote_url($url,$post_params=''){
		$ch = curl_init();

		if(strlen($url) > 512 && $post_params==''){
			$vals = preg_split('/\?/',$url);
			$post_params = $vals[1];
			$url = $vals[0];
			curl_setopt($ch, CURLOPT_URL, $url);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		if($post_params != ''){
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$post_params);
		}


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


	public function translate()
	{
		$st = new View("translate/translate");
		$st->render(TRUE);
	}
}
new translate;
