<?php

class mdl_linkedin extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    
    function get_linkedin_details($company_id)
    {		
    	$this->db->select('company_id, client_id, client_secret, redirect_url, access_token, token_gen_date');
       return $this->db->get_where('linkedin', array('company_id  ' => $company_id))->result();
       echo $this->db_last_query();
    }

}