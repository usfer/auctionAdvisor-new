<?php

class mdl_testimonials extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    
	function get_testimonials()
	{		
		$this->db->select('tid,member_name,member_image,description,member_company,status');
		return $this->db->get_where('testimonials')->result();
	}
	
	function get_testimonials_details($tid) {

        $this->db->where('tid', $tid);
        return $this->db->get('testimonials')->row_array();
    }
	
	function insert_testimonials() {
        
		
		
		//insert  information 
		$config['upload_path'] = './uploads/testimonials/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('member_image');
		$file_data = $this->upload->data();
		$member_image =  $file_data['file_name'];
		
        $data = array(
           
            'member_name' 		=> $this->input->post('member_name'),
            'member_image' 		=> $member_image,
			'description'		=> $this->input->post('description'),
			'member_company'	=> $this->input->post('member_company'),
			'status'			=> $this->input->post('status')
		);
		
		$this->db->insert('testimonials', $data);
        $last_insert_id = $this->db->insert_id();
		
		$this->session->set_flashdata('msg_ok', 'Client Created successfully.');
        redirect(site_url($this->uri->slash_segment(1) . 'testimonials'));
    }
	
	function update_testimonials($tid) {

		//update
		$structure = './uploads/testimonials/';
		$config['upload_path'] = $structure;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('member_image');
		$file_data = $this->upload->data();
					
		if($file_data['file_name'])
		{
			$member_image 	= $file_data['file_name'];
			@unlink($config['upload_path'].$this->input->post('member_image'));
		}
		else
		{
			$member_image = $this->input->post('prev_member_image');
		}
		
		$data = array(
            'member_name' 		=> $this->input->post('member_name'),
            'member_image' 		=> $member_image,
			'description'		=> $this->input->post('description'),
			'member_company'	=> $this->input->post('member_company'),
			'status'			=> $this->input->post('status')
			
        );
		
		$this->db->where('tid', $tid);
        $this->db->update('testimonials', $data);
	}
	
	function delete_testimonials($tid = '') {
        
		$path = './uploads/testimonials/';
		$this->db->select('member_image');
		$qry= $this->db->get_where('testimonials',array('tid' => $tid))->row();
		if($qry->member_image <>'' && file_exists($path.$qry->member_image))
		{
			unlink($path.$qry->member_image);
		}
		$this->db->where('tid IN ('.$tid.')');
		$this->db->delete('testimonials');	
		
		//$dirname =$_SERVER["DOCUMENT_ROOT"].'/uploads/news/';
		//$this->db->where('tid', $tid);
		//$this->db->delete('clients'); 
        
		redirect(site_url($this->uri->slash_segment(1).'testimonials')); 
    }
}