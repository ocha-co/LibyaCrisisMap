<?php

class Translate_Install {

	protected $field_name = "Translation";

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db =  new Database();
	}

	/**
	 * Creates the required database tables for my_plugin_name
	 */
	public function run_install(){

		//1. CREATE TRANSLATION FIELD IN USHAHIDI
		$form_table = Kohana::config('database.default.table_prefix')."form_field";
		$fn = addslashes($this->field_name);
		$field_exists = 0;
		$cnstr = "cnstr";
		$res = $this->db->query("SELECT COUNT(field_name) AS $cnstr FROM $form_table WHERE field_name = '$fn'");
		if($row=$res->result_array()){
			$field_exists = $row[0]->$cnstr;
		}
		if($field_exists==0){
			$this->db->query("INSERT INTO $form_table (field_name, field_position, form_id, field_type, field_width, field_height) VALUES ('$fn', 1, 1, 2, 45, 10)");
		}


		//2. CREATE TABLE FOR TRACKING WHO IS VIEWING
		$view_table = Kohana::config('database.default.table_prefix')."report_viewer";
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `$view_table`
			(
				`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
				`incident_id` INT NOT NULL COMMENT 'incident_id of the incident report being viewed',
				`user_id` INT NOT NULL COMMENT 'user_id of the user viewing the incident report',
				`last_viewed` BIGINT NOT NULL COMMENT 'unix timestamp of when the report was last viewed',
				`username` TEXT COMMENT 'redundant copy of username for quick access',
				PRIMARY KEY (id),
				INDEX indx_last_viewed (last_viewed),
				INDEX indx_user_id (user_id),
				INDEX indx_incident_id (incident_id)

			);");



	}

	/**
	 * Deletes the database tables for my_plugin_name
	 */
	public function uninstall(){
	}

}



?>