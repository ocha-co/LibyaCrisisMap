<?php

class Bulkusers_Controller extends Admin_Controller
{
    function __construct()
    {  
        parent::__construct();

        $this->template->this_page = 'bulkusers';
    }


    function index($page = 1){
        // If user doesn't have access, redirect to dashboard
        if ( ! admin::permissions($this->user, "reports_upload"))
        {  
            url::redirect(url::site().'admin/dashboard');
        }

        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->template->content = new View('admin/bulkusers');
            //$this->template->content->mappings = $this->_get_mapping();
            $this->template->content->headers = $this->_get_headers($page);
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST') {


        }
    }

    // generates the headers 
    private function _get_headers()
    {  
        $headers = "<h2>" . Kohana::lang('omniimport.name') . "&nbsp;&nbsp;&nbsp;";
        $headers .= "<a href=\"" . url::site() . "admin/omniimport\">". Kohana::lang('ui_main.upload_reports') . "</a>&nbsp;&nbsp;&nbsp;";
        $headers .= "<a href=\"" . url::site() . "admin/omniimport/download\">". Kohana::lang('ui_main.download_reports') . "</a>&nbsp;&nbsp;&nbsp;";
        $headers .= "<a href=\"" . url::site() . "admin/omniimport/mapping\">". Kohana::lang('omniimport.new_mapping') . "</a>&nbsp;&nbsp;&nbsp;";
        $headers .= "</h2>";
        return $headers;
    }

}
?>
