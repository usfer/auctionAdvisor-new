<?php

class mdl_clients extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    
	function get_clients()
	{		
		$this->db->select('cid,client_logo,client_name,url,status');
		return $this->db->get_where('clients')->result();
	}
	
	function get_clients_details($aid) {
        $this->db->where('cid', $aid);
        return $this->db->get('clients')->row_array();
    }
	
	function insert_clients() {
        
		
		
		//insert  information 
		$config['upload_path'] = './uploads/clients/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('client_logo');
		$file_data = $this->upload->data();
		
		$client_logo =  $file_data['file_name'];
		
        $data = array(
           
            'client_name' 		=> $this->input->post('client_name'),
            'url' 				=> $this->input->post('url'),
			'client_logo'		=> $client_logo,
			'status'			=> $this->input->post('status')
		);
		
	
		$this->db->insert('clients', $data);
        $last_insert_id = $this->db->insert_id();
		
		$this->session->set_flashdata('msg_ok', 'Client Created successfully.');
        redirect(site_url($this->uri->slash_segment(1) . 'clients'));
    }
	
	function update_clients($cid) {

		$structure = './uploads/clients/';
		$config['upload_path'] = $structure;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('client_logo');
		$file_data = $this->upload->data();
					
		if($file_data['file_name'])
		{
			$client_logo 	= $file_data['file_name'];
			@unlink($config['upload_path'].$this->input->post('prev_client_logo'));
		}
		else
		{
			$client_logo = $this->input->post('prev_client_logo');
		}

		$data = array(
           
            'client_name' 		=> $this->input->post('client_name'),
            'url' 				=> $this->input->post('url'),
			'client_logo'		=> $client_logo,
			'status'			=> $this->input->post('status')
			
        );
		
		//print_r($data);exit;
		
		$this->db->where('cid', $cid);
        $this->db->update('clients', $data);
	}
	
	function delete_clients($cid = '') {
        
		$path = './uploads/clients/';
		$this->db->select('client_logo');
		$qry= $this->db->get_where('clients',array('cid' => $this->input->post('cid')))->row();
		if($qry->client_logo <>'' && file_exists($path.$qry->client_logo))
		{
			unlink($path.$qry->client_logo);
		}
		$this->db->where('cid IN ('.$cid.')');
		$this->db->delete('clients');
		
		//$dirname =$_SERVER["DOCUMENT_ROOT"].'/uploads/news/';
		//$this->db->where('cid', $cid);
		//$this->db->delete('clients'); 
        
		redirect(site_url($this->uri->slash_segment(1).'clients')); 
    }
}