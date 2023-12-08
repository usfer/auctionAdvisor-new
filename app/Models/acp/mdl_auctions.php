<?php

class mdl_auctions extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function get_auctions($type, $key) {
        $this->db->select('*');
        switch ($type) {
            case 'search_auctioneer':
                $this->db->where('auction_user', $key);
                $this->db->or_where('additional_user', $key);
				$this->db->order_by('sort_order', 'ASC');
                break;
            case 'live':
                //$this->db->where('(auction_date >= "'.date('Y-m-d').'" OR tbd_flag = 1)');
                $this->db->where('is_property_for_sale', 0);
                $this->db->where('is_auction_live', 1);
				$this->db->order_by('sort_order', 'ASC');
                break;
            case 'past_auction':
                $this->db->where('(auction_date < "'.date('Y-m-d').'" OR tbd_flag = 1)');
                $this->db->where('is_property_for_sale', 0);
                $this->db->where('is_auction_live', 1);
				$this->db->order_by('sort_order', 'ASC');
                break;
            case 'not_live':
                $this->db->where('is_auction_live', 0);
                $this->db->where('is_property_for_sale', 0);
				$this->db->order_by('sort_order', 'ASC');
                break;
            case 'prop_for_sale':
                $this->db->where('is_property_for_sale', 1);
				$this->db->order_by('sort_order', 'ASC');
                break;
            case 'is_featured':
                $this->db->where('is_featured', 1);
				$this->db->order_by('featured_sort_order', 'ASC');
                break;
            case 'is_sold':
                $this->db->where('auction_sold', 1);
				$this->db->order_by('sort_order', 'ASC');
                break;
			case 'home_slider':
                $this->db->where('show_home_slider', 1);
				$this->db->order_by('sort_order', 'ASC');
                break;
            
        }
        $sess_user_id = $this->session->userdata('gblUserId');
        $sess_user_role = $this->session->userdata('role_type');
        if ($sess_user_role == 'U') {
            $this->db->where("(auction_user = '" . $sess_user_id . "' OR additional_user = '" . $sess_user_id . "')");
        }
        $this->db->where('is_active', 1);        
        //$this->db->order_by('auction_date', 'ASC');
		//$this->db->order_by('sort_order', 'ASC');
        $query = $this->db->get('auctions');
       
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return '';
        }
    }

    function get_image_by_auction_id($aid) {
        $this->db->order_by('sort_order');
        return $this->db->get_where('auction_images', array('auction_id' => $aid))->row();
    }

    function get_linkid_by_details($link_id) {
        return $this->db->get_where('auction_links', array('link_id' => $link_id))->row_array();
    }

    function get_auction_categories() {
        return $this->db->get_where('categories', array('status' => 1))->result();
    }

    function get_categoryid_by_auction($aid) {
        $cat_result = $this->db->get_where('auction_categories', array('auction_id' => $aid))->result();
        foreach ($cat_result as $cat_val) {
            $cat_arr[$cat_val->category_id] = $cat_val->category_id;
        }
        return $cat_arr;
    }

    function get_auction_details($aid) {
        $this->db->where('auction_id', $aid);
        return $this->db->get('auctions')->row_array();
    }

    function get_users_by_usertype($type) {
        $this->db->where("(user_type='U' OR user_type='B')");
        return $this->db->get('users')->result();
    }

    function get_all_users() {
        $user_arr = array();
        $this->db->select('userId,firstname,lastname');
        $result = $this->db->get('users')->result();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $user_arr[$row->userId] = $row->firstname . ' ' . $row->lastname;
            }
        }
        return $user_arr;
    }

    function get_all_state_by_name() {
        $state_arr = array();
        $result = $this->db->get('states')->result();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $state_arr[$row->state_id] = $row->state_code;
            }
        }
        return $state_arr;
    }

    function get_auction_images($aid) {
        $this->db->select('image_id,auction_id,sort_order,file_name');
        $this->db->order_by('sort_order');
        if ($aid != '') {
            $this->db->where('auction_id', $aid);
        }
        return $this->db->get('auction_images')->result();
    }

	function get_auction_slider_images() {
        $this->db->select('auction_id,slider_sort_order,slider_title,slider_image');
		$this->db->where('show_home_slider', 1);
        $this->db->order_by('slider_sort_order');
        return $this->db->get('auctions')->result();
    }

    function get_auction_links($aid) {
        if ($aid != '') {
            $this->db->where('auction_id', $aid);
        }
        $this->db->order_by('sort_order');
        return $this->db->get('auction_links')->result();
    }

    function get_auction_docs($aid) {
        if ($aid != '') {
            $this->db->where('auction_id', $aid);
        }

		$this->db->where('is_active', 1);
        $this->db->order_by('document_sort_order');
        return $this->db->get('auction_documents')->result();
    }

    function insert_auctions() {
        $auction_user = ($this->session->userdata('role_type') == 'U') ? $this->session->userdata('gblUserId') : $this->input->post('auction_user');

		//$q = $this->db->query("select MAX(sort_order) as sort_id from ".$this->db->dbprefix('auctions')." where auction_id > 0");
//        $data = $q->row_array();
//        $sort_order = $data['sort_id'] + 1;

        $data = array(
            'is_auction_live' => $this->input->post('is_auction_live'),
            'headline' => $this->input->post('headline'),
            'short_description' => nl2br($this->input->post('short_description')),
            'long_description' => $this->input->post('long_description'),
            'annual_taxes' => $this->input->post('annual_taxes'),
            'size_description' => $this->input->post('size_description'),
			'external_link' => $this->input->post('external_link'),
			'alias_link' => $this->input->post('alias_link'),
			'highrisehq_tag' => $this->db->escape_str($this->input->post('highrisehq_tag')),
			'tbd_flag' => $this->input->post('tbd_flag'),
			'tbd_text' => $this->input->post('tbd_text'),
            'is_tbd_start_flag' => $this->input->post('is_tbd_start_flag'),
            'inspections' => $this->input->post('inspections'),
            'style' => $this->input->post('style'),
			'property_type' => $this->input->post('property_type'),
            'property_address' => $this->input->post('property_address'),
            'property_address2' => $this->input->post('property_address2'),
			'is_alt_address' => $this->input->post('is_alt_address'),
            'property_city' => $this->input->post('property_city'),
            'property_state' => $this->input->post('property_state'),
            'property_zip' => $this->input->post('property_zip'),
            'is_auction_address_different' => $this->input->post('is_auction_address_different'),
            'show_map' => $this->input->post('show_map'),
            'category_description' => $this->input->post('category_description'),
            'auction_directions' => $this->input->post('auction_directions'),
            'additional_user' => $this->input->post('additional_user'),
            'auction_user' => $auction_user,
            'other_user_firstname' => $this->input->post('other_user_firstname'),
            'other_user_lastname' => $this->input->post('other_user_lastname'),
            'other_user_title' => $this->input->post('other_user_title'),
            'other_user_accreditations' => $this->input->post('other_user_accreditations'),
            'other_user_company' => $this->input->post('other_user_company'),
            'other_user_email' => $this->input->post('other_user_email'),
            'other_user_phone' => $this->input->post('other_user_phone'),
            'other_user_cell' => $this->input->post('other_user_cell'),
            'other_user_fax' => $this->input->post('other_user_fax'),
            'basic_terms' => serialize($this->input->post('basic_terms')),
            'closing' => $this->input->post('closing'),
            'realtors' => $this->input->post('realtors'),
            'is_property_for_sale' => $this->input->post('is_property_for_sale'),
            'property_sale_price' => $this->input->post('property_sale_price'),
            'auction_sold' => $this->input->post('auction_sold'),
            'auction_created_date' => date('Y-m-d'),
			'sort_order' => 1,
            'ohr'   =>  $this->input->post('ohr'),
			'show_home_slider' => $this->input->post('show_home_slider')
        );
		
		if($this->input->post('in_contract') == '1'){
				$data['in_contract'] = $this->input->post('in_contract');	
			}else{
				$data['in_contract'] =0;
		}
			

        if ($this->input->post('tbd_flag') <> 1) {
            $auction_time = $this->input->post('auction_time_hh') . $this->input->post('auction_time_mm') . ' ' . $this->input->post('auction_time_meridian');
            $data['auction_date'] = date("Y-m-d", strtotime($this->input->post('auction_date')));
            $data['auction_time'] = $auction_time;
	    $data['auction_time_zone'] = $this->input->post('auction_time_zone');
        }
        if ($this->input->post('style') == 2 || $this->input->post('style') == 3) {
            if ($this->input->post('is_tbd_start_flag') <> 1) {
                $auction_time = $this->input->post('auction_start_time_hh') . $this->input->post('auction_start_time_mm') . ' ' . $this->input->post('auction_start_time_meridian');
                $data['auction_start_date'] = date("Y-m-d", strtotime($this->input->post('auction_start_date')));
                $data['auction_start_time'] = $auction_time;
		$data['auction_start_time_zone'] = $this->input->post('auction_start_time_zone');
		
		
            }
			$data['online_auction_url'] = $this->input->post('online_auction_url');
        }
        if ($this->input->post('is_auction_address_different') <> 1) {
            $data['auction_address'] = $this->input->post('auction_address');
            $data['auction_address2'] = $this->input->post('auction_address2');
            $data['auction_city'] = $this->input->post('auction_city');
            $data['auction_state'] = $this->input->post('auction_state');
            $data['auction_zip'] = $this->input->post('auction_zip');
        }

		//changes made on Jan 18th 2013
		if ($this->input->post('is_auction_alt_address') == 1) {
			$data['is_auction_alt_address'] = $this->input->post('is_auction_alt_address');
			$data['auction_alt_address'] = $this->input->post('auction_alt_address');			
		}
		
		$this->db->query("update ".$this->db->dbprefix('auctions')." set sort_order = sort_order+1");
        $this->db->insert('auctions', $data);
        $last_insert_id = $this->db->insert_id();
        
        $structure = './uploads/auction_' . $last_insert_id . '/images';

        // To create the nested structure, the $recursive parameter 
        // to mkdir() must be specified.
        if (!file_exists($structure)) {
            if (!mkdir($structure, 0755, true)) {
                die('Failed to create folders...');
            }
        }

		//insert home page slider information 
		
		$config['upload_path'] = './uploads/auction_'.$last_insert_id.'/images/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('slider_image');
		$file_data = $this->upload->data();

		$slider_image_data = array(
			'slider_title' => $this->input->post('slider_title'),
			'slider_title2' => $this->input->post('slider_title2'),
			'slider_image' => $file_data['file_name'],
			'slider_image_link_type' => $this->input->post('slider_image_link_type'),
			'slider_url' => $this->input->post('slider_url')
		);

		$this->db->where('auction_id', $last_insert_id);
		$this->db->update('auctions', $slider_image_data);

		$q = $this->db->query("select MAX(slider_sort_order) as sort_id from ".$this->db->dbprefix('auctions')." where auction_id ='" . $last_insert_id . "'");
		$img_data = $q->row_array();
		if($img_data != '0') 
		{
			$sort_order = $img_data['sort_id'] + 1;
			$this->db->query("UPDATE ".$this->db->dbprefix('auctions')." SET slider_sort_order = '".$sort_order."' WHERE auction_id = '".$last_insert_id."'");
		}



        //insert auction category 
        if (!empty($_POST['category_id'])) {

            for ($i = 0; $i < count($_POST['category_id']); $i++) {

                $this->db->insert('auction_categories', array('auction_id' => $last_insert_id, 'category_id' => $_POST['category_id'][$i]));
            }
        }


        $this->session->set_flashdata('msg_ok', 'Auction Created successfully. Please upload the Images,Documents & Links for the auction.');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_details/' . $last_insert_id));
    }

    function update_auctions($aid) {

        $auction_user = ($this->session->userdata('role_type') == 'U') ? $this->session->userdata('gblUserId') : $this->input->post('auction_user');
        $data = array(
            'is_auction_live' => $this->input->post('is_auction_live'),
            'headline' => $this->input->post('headline'),
            'short_description' => nl2br($this->input->post('short_description')),
            'long_description' => $_REQUEST['long_description'],
            'annual_taxes' => $this->input->post('annual_taxes'),
            'size_description' => $this->input->post('size_description'),
	       'external_link' => $this->input->post('external_link'),
	       'highrisehq_tag' => $this->input->post('highrisehq_tag'),
            'tbd_flag' => $this->input->post('tbd_flag'),
	       'tbd_text' => $this->input->post('tbd_text'),
            'is_tbd_start_flag' => $this->input->post('is_tbd_start_flag'),
            'inspections' => $_REQUEST['inspections'],
            'style' => $this->input->post('style'),
	       'property_type' => $this->input->post('property_type'),
            'property_address' => $this->input->post('property_address'),
            'property_address2' => $this->input->post('property_address2'),
	       'is_alt_address' => $this->input->post('is_alt_address'),
            'property_city' => $this->input->post('property_city'),
            'property_state' => $this->input->post('property_state'),
            'property_zip' => $this->input->post('property_zip'),
            'is_auction_address_different' => $this->input->post('is_auction_address_different'),
	       'is_auction_alt_address' => $this->input->post('is_auction_alt_address'),
	       'auction_alt_address' => $this->input->post('auction_alt_address'),
            'show_map' => $this->input->post('show_map'),
            'category_description' => $this->input->post('category_description'),
            'auction_directions' => $_REQUEST['auction_directions'],
            'additional_user' => $this->input->post('additional_user'),
            'auction_user' => $auction_user,
            'other_user_firstname' => $this->input->post('other_user_firstname'),
            'other_user_lastname' => $this->input->post('other_user_lastname'),
            'other_user_title' => $this->input->post('other_user_title'),
            'other_user_accreditations' => $this->input->post('other_user_accreditations'),
            'other_user_company' => $this->input->post('other_user_company'),
            'other_user_email' => $this->input->post('other_user_email'),
            'other_user_phone' => $this->input->post('other_user_phone'),
            'other_user_cell' => $this->input->post('other_user_cell'),
            'other_user_fax' => $this->input->post('other_user_fax'),
            'basic_terms' => serialize($this->input->post('basic_terms')),
            'closing' => $_REQUEST['closing'],
            'realtors' => $_REQUEST['realtors'],
            'is_property_for_sale' => $this->input->post('is_property_for_sale'),
            'property_sale_price' => $this->input->post('property_sale_price'),
            'auction_sold' => $this->input->post('auction_sold'),
            'auction_last_edit_date' => date('Y-m-d'),
	       'show_home_slider' => $this->input->post('show_home_slider'),
            'ohr'   =>  $this->input->post('ohr'),
        );
		
		if($this->input->post('in_contract') == '1'){
				$data['in_contract'] = $this->input->post('in_contract');	
			}else{
				$data['in_contract'] =0;
		}
		
        if ($this->input->post('tbd_flag') <> 1) {
            $auction_time = $this->input->post('auction_time_hh') . $this->input->post('auction_time_mm') . ' ' . $this->input->post('auction_time_meridian');
            $data['auction_date'] = date("Y-m-d", strtotime($this->input->post('auction_date')));
            $data['auction_time'] = $auction_time;
	    $data['auction_time_zone'] = $this->input->post('auction_time_zone');
        }

        if ($this->input->post('style') == 2 || $this->input->post('style') == 3) {
            if ($this->input->post('is_tbd_start_flag') <> 1) {
                $auction_time = $this->input->post('auction_start_time_hh') . $this->input->post('auction_start_time_mm') . ' ' . $this->input->post('auction_start_time_meridian');
                $data['auction_start_date'] = date("Y-m-d", strtotime($this->input->post('auction_start_date')));
                $data['auction_start_time'] = $auction_time;
		$data['auction_start_time_zone'] = $this->input->post('auction_start_time_zone');
		
		
            }
			$data['online_auction_url'] = $this->input->post('online_auction_url');
        } else {
            $data['auction_start_date'] = '';
            $data['auction_start_time'] = '';
	    $data['auction_start_time_zone'] = '';
            $data['is_tbd_start_flag'] = 0;
			 
        }
        if ($this->input->post('is_auction_address_different') <> 1) {
            $data['auction_address'] = $this->input->post('auction_address');
            $data['auction_address2'] = $this->input->post('auction_address2');
            $data['auction_city'] = $this->input->post('auction_city');
            $data['auction_state'] = $this->input->post('auction_state');
            $data['auction_zip'] = $this->input->post('auction_zip');
        }	
		

		//insert home page slider information 

		$structure = './uploads/auction_' . $aid . '/images/';
		$config['upload_path'] = $structure;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('slider_image');
		$file_data = $this->upload->data();
					
		if($file_data['file_name'])
		{
			$slider_image = $file_data['file_name'];
			@unlink($config['upload_path'].$this->input->post('prev_slider_image'));
		}
		else
		{
			$slider_image = $this->input->post('prev_slider_image');
		}

		$slider_image_data = array(
			'slider_title' => $this->input->post('slider_title'),
			'slider_title2' => $this->input->post('slider_title2'),
			'slider_image' => $slider_image,
			'slider_image_link_type' => $this->input->post('slider_image_link_type'),
			'slider_url' => $this->db->escape_str($this->input->post('slider_url'))
		);

		$this->db->where('auction_id', $aid);
		$this->db->update('auctions', $slider_image_data);

		$q = $this->db->query("select MAX(slider_sort_order) as sort_id from ".$this->db->dbprefix('auctions')." where auction_id ='" . $aid . "'");
		$img_data = $q->row_array();
		if($img_data != '0') 
		{
			$sort_order = $img_data['sort_id'] + 1;
			$this->db->query("UPDATE ".$this->db->dbprefix('auctions')." SET slider_sort_order = '".$sort_order."' WHERE auction_id = '".$aid."'");
		}

        $this->db->where('auction_id', $aid);
        $this->db->update('auctions', $data);



        if (!empty($_POST['category_id'])) {
            $this->db->where('auction_id', $aid);
            $this->db->delete('auction_categories');
            for ($i = 0; $i < count($_POST['category_id']); $i++) {

                $this->db->insert('auction_categories', array('auction_id' => $aid, 'category_id' => $_POST['category_id'][$i]));
            }
        }

        $this->session->set_flashdata('msg_ok', 'Auction updated successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_details/' . $aid));
    }
    
    function change_auction_status($aid,$curr_filter)
    {      
        $this->db->where('auction_id', $aid);
        $this->db->update('auctions',array('is_active' => 0));
        
        $this->session->set_flashdata('msg_ok', 'Auction deleted successfully');
        if(!empty($curr_filter))
        {
            redirect(site_url($this->uri->slash_segment(1) . 'auctions/index/'.$curr_filter));
        }else{
            redirect(site_url($this->uri->slash_segment(1) . 'auctions'));
        }
    }

    // this function can delete all images, docs realted to auction
    function delete_entire_auction_info($aid) {
        $dirname =$_SERVER["DOCUMENT_ROOT"].'/uploads/auction_'.$aid.'/';
       
        $this->db->where('auction_id', $aid);
        $this->db->delete('auctions');
        
        $this->db->where('auction_id', $aid);
        $this->db->delete('auction_images');
        
        $this->db->where('auction_id', $aid);
        $this->db->delete('auction_links'); 
        
        $this->db->where('auction_id', $aid);
        $this->db->delete('auction_documents');   
     
        
        $this->delete_directory($dirname);
        
        $this->session->set_flashdata('msg_ok', 'Auction deleted successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions'));
    }

    function delete_directory($dirname) {
        
       if ($dir_handle  = @opendir($dirname)){
           
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file))
                    unlink($dirname . "/" . $file);
                else
                    $this->delete_directory($dirname . '/' . $file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        }
        
    }

    function upload_auction_images() {
        $last_id = $this->input->post('last_id');

        $structure = './uploads/auction_' . $last_id . '/images';

        // To create the nested structure, the $recursive parameter 
        // to mkdir() must be specified.
        if (!file_exists($structure)) {
            if (!mkdir($structure, 0755, true)) {
                die('Failed to create folders...');
            }
        }
           
        $config['upload_path'] = $structure;
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload', $config);

        $this->upload->initialize($config);
        $this->upload->do_upload('file_upload');
        $file_data = $this->upload->data();

        $img = getimagesize($file_data['full_path']);

        $width = $img['0'];
        $height = $img['1'];

        if ($width > 550) {
            $prop_height = round((100 / $width) * $height);
            $prop_width = 100;
        } else {
            $prop_height = $height;
            $prop_width = $width;
        }

        $config['image_library'] = 'gd2';
        $config['source_image'] = $structure . '/' . $file_data['file_name'];
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = FALSE;
        $config['width'] = $prop_width;
        $config['height'] = $prop_height;

        $this->load->library('image_lib', $config);

        $this->image_lib->resize();

        $image_data = array('file_name' => $file_data['file_name'],
            'auction_id' => $last_id
        );
        $this->db->insert('auction_images', $image_data);
        $last_insert_id = $this->db->insert_id();

        $q = $this->db->query("select MAX(sort_order) as sort_id from ".$this->db->dbprefix('auction_images')." where auction_id ='" . $last_id . "'");
        $data = $q->row_array();
        if ($data != '0') {
            $sort_order = $data['sort_id'] + 1;
            $this->db->query("UPDATE " . $this->db->dbprefix('auction_images') . " SET `sort_order` = $sort_order WHERE `image_id` = $last_insert_id");
        }
        $this->session->set_flashdata('msg_ok', 'Image uploaded successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_detail_images/' . $last_id));
    }

    function add_auction_links() {
        $last_id = $this->input->post('last_id');

        $q = $this->db->query("select MAX(sort_order) as sort_id from ".$this->db->dbprefix('auction_links')." where auction_id='" . $last_id . "'");
        $data = $q->row_array();
        $sort_order = $data['sort_id'] + 1;

        $link_data = array('link_display_text' => $this->input->post('link_desp_txt'),
            'link_url' => $this->input->post('link_url'),
            'auction_id' => $last_id,
            'sort_order' => $sort_order);

        $this->db->insert('auction_links', $link_data);
        $this->session->set_flashdata('msg_ok', 'Links created successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_detail_documents/' . $last_id));
    }

    function update_auction_links() {
        $last_id = $this->input->post('last_id');
        $link_id = $this->input->post('link_id');

        $update_data = array('link_display_text' => $this->input->post('link_desp_edit'),
            'link_url' => $this->input->post('link_url_edit')
        );
        $this->db->where('link_id', $link_id);
        $this->db->update('auction_links', $update_data);

        $this->session->set_flashdata('msg_ok', 'Link updated successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_detail_documents/' . $last_id));
    }

    function add_auction_documents() {

        $last_id = $this->input->post('last_id');

        $structure = './uploads/auction_' . $last_id . '/docs';

        if (!file_exists($structure)) {
            if (!mkdir($structure, 0755, true)) {
                die('Failed to create folders...');
            }
        }

        $config['upload_path'] = $structure;
        $config['allowed_types'] = 'docx|doc|pdf|rtf|txt|tiff|tif';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('document_file_name');
        $file_data = $this->upload->data();

        $q = $this->db->query("select MAX(document_sort_order) as sort_id from ".$this->db->dbprefix('auction_documents')." where auction_id='" . $last_id . "'");
        $data = $q->row_array();
        $document_sort_order = $data['sort_id'] + 1;

        $doc_data = array(
			'document_name' => $this->input->post('document_name'),
            'document_file_name' => $file_data['file_name'],
            'auction_id' => $last_id,
			'category' => $this->input->post('category'),
			'required_ca' => $this->input->post('required_ca'),
            'document_sort_order' => $document_sort_order
		);

		if($this->input->post('is_ca') == 1)
		{
			$doc_data['is_ca'] = $this->input->post('is_ca');

			$this->db->update('auction_documents', array('is_ca' => 0), array('auction_id' => $last_id));
		}

        $this->db->insert('auction_documents', $doc_data);

        $this->session->set_flashdata('msg_ok', 'Document uploaded successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_detail_documents/' . $last_id));
    }

    function delete_auction_images($aid, $img_id) {
        $this->db->select('file_name');
        $this->db->where('image_id', $img_id);
        $obj_row = $this->db->get('auction_images')->row();

        $structure = './uploads/auction_' . $aid . '/images';
        $config['upload_path'] = $structure;

        if ($obj_row->file_name <> '') {
            if (file_exists($config['upload_path'] . '/' . $obj_row->file_name)) {
                $split = explode('.', $obj_row->file_name);
                @unlink($config['upload_path'] . '/' . $obj_row->file_name);
                @unlink($config['upload_path'] . '/' . $split[0] . '_thumb.' . $split[1]);
                @unlink($config['upload_path'] . '/' . $split[0] . '_small_thumb.' . $split[1]);
            }
        }

        $this->db->where('image_id', $img_id);
        $this->db->delete('auction_images');
        $this->session->set_flashdata('msg_ok', 'Image deleted successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_detail_images/' . $aid));
    }

	function delete_slider_images($aid = 0)
	{
		$this->db->select('slider_image');
        $this->db->where('auction_id', $aid);
        $obj_row = $this->db->get('auctions')->row();

		$structure = './uploads/auction_' . $aid . '/images';
        $config['upload_path'] = $structure;

		@unlink($config['upload_path'] . '/' . $obj_row->slider_image);

		$data = array(
			'show_home_slider' => 0,
			'slider_title' => '',
			'slider_title2' => '',
			'slider_image' => '',
			'slider_image_link_type' => '',
			'slider_url' => '',
			'slider_sort_order' => 0
		);

		$this->db->update('auctions', $data, array('auction_id' => $aid));

		$this->session->set_flashdata('msg_ok', 'Image deleted successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_slider_images'));
	}

    function delete_auction_documents() {
        $doc_name = $this->input->get('doc_name');
        $doc_id = $this->input->get('doc_id');
        $aid = $this->input->get('aid');

        /*$structure = './uploads/auction_' . $aid . '/docs';
        $config['upload_path'] = $structure;
        if ($doc_name <> '') {
            if (file_exists($config['upload_path'] . '/' . $doc_name)) {
                //@unlink($config['upload_path'] . '/' . $doc_name);
            }
        }*/

        $this->db->where('document_id', $doc_id);
		$this->db->update('auction_documents', array('is_active' => 0));
        //$this->db->delete('auction_documents');

        $this->update_sort_order('auction_documents', $aid, 'document_sort_order', 'document_id');

        $this->session->set_flashdata('msg_ok', 'Document deleted successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_detail_documents/' . $aid));
    }

    function delete_auction_links() {
        $link_id = $this->input->get('link_id');
        $aid = $this->input->get('aid');

        $this->db->where('link_id', $link_id);
        $this->db->delete('auction_links');

        $this->update_sort_order('auction_links', $aid, 'sort_order', 'link_id');

        $this->session->set_flashdata('msg_ok', 'Link deleted successfully');
        redirect(site_url($this->uri->slash_segment(1) . 'auctions/auction_detail_documents/' . $aid));
    }

    function update_sort_order($tbl_name = '', $aid = '', $sort_field_name = '', $incid = '') {
        $this->db->where('auction_id', $aid);
        $this->db->from($tbl_name);
        $count = $this->db->count_all_results();

        if ($sort_field_name == 'sort_order') {
            $auction_result = $this->get_auction_links($aid);
            foreach ($auction_result as $row) {
                $link_id[] = $row->link_id;
            }
        } else {

            $auction_result = $this->get_auction_docs($aid);
            foreach ($auction_result as $row) {
                $link_id[] = $row->document_id;
            }
        }
        $j = 1;
        for ($i = 0; $i < $count; $i++) {
            $sort_data = array($sort_field_name => $j);

            $this->db->where('auction_id', $aid);
            $this->db->where($incid, $link_id[$i]);
            $this->db->update($tbl_name, $sort_data);
            $j++;
        }
    }

    function get_states($sid = '') {
        $this->db->select('state_id,state_name,state_code');
        $query = $this->db->get('states');
        foreach ($query->result() as $row) {
            if ($sid == $row->state_id) {
                $this->sname.='<option value=' . $row->state_id . ' selected>' . $row->state_code . ' - ' . $row->state_name . '</option>';
            } else {
                $this->sname.='<option value=' . $row->state_id . '>' . $row->state_code . ' - ' . $row->state_name . '</option>';
            }
        }
        return $this->sname;
    }
    
    function update_is_featured($type, $aid)
    {
		$bigValue = $this->db->query("SELECT MAX( featured_sort_order ) AS Highest FROM aa_auctions where is_featured = 1");
		$bigValue = $bigValue->result();
		
        switch ($type)
        {
            case 'feature':
                    $this->db->where('auction_id', $aid);
                    $this->db->update('auctions',array('is_featured' => 1,'featured_sort_order'=>$bigValue[0]->Highest+1));
                    $msg = 'Auction flagged as featured';
                    break;
            case 'notfeature':
                    $this->db->where('auction_id', $aid);
                    $this->db->update('auctions',array('is_featured' => 0));
                    $msg = 'Auction converted as non featured';
                    break;
        }
        return $msg;
    }

	function set_as_ca($docId = 0)
	{
		$this->db->update('auction_documents', array('is_ca' => 0), array('is_ca' => 1));
		$this->db->update('auction_documents', array('is_ca' => 1), array('document_id' => $docId));
	}

	function set_required_ca($docId = 0, $status = 0)
	{
		$this->db->update('auction_documents', array('required_ca' => $status), array('document_id' => $docId));
	}

	function get_document_categories()
	{
		$result = array();
		$this->db->where('category_id >', 0);
		$query = $this->db->get('documents_categories');

		if($query->num_rows() > 0)
		{
			$result = $query->result();
		}

		return $result;
	}

	function document_category_name($cat)
	{
		$returnVal = '';
		$this->db->where('category_id', $cat);
		$query = $this->db->get('documents_categories');

		if($query->num_rows() > 0)
		{
			$row = $query->row();
			$returnVal = $row->category_name;
		}

		return $returnVal;
	}

}