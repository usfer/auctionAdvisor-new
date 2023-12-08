<?php

class mdl_carousel extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    
    function get_carousel_list()
    {		
            $this->db->select('cid,title,image,description,status');
            return $this->db->get_where('carousel_images')->result();
    }
	
    function get_carousel_details($aid) {
        $this->db->where('cid', $aid);
        return $this->db->get('carousel_images')->row_array();
    }
	
    function insert_carousel() 
    {
        //insert  information 
        $config['upload_path'] = './uploads/carousels/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload');
        $this->upload->initialize($config);
        $this->upload->do_upload('carousel_image');
        $file_data = $this->upload->data();

        $carousel_image =  $file_data['file_name'];
		
        $data = array(
            'title' => $this->input->post('carousel_title'),
            'image'=> $carousel_image,
            'description' => $this->input->post('description')
        );
	
	
        $this->db->insert('carousel_images', $data);
        $last_insert_id = $this->db->insert_id();
		
        $this->session->set_flashdata('msg_ok', 'carousel Created successfully.');
        redirect(site_url($this->uri->slash_segment(1) . 'carousel'));
    }
	
    function update_carousel($cid) {

        $structure = './uploads/carousels/';
        $config['upload_path'] = $structure;
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload');
        $this->upload->initialize($config);
        $this->upload->do_upload('carousel_image');
        $file_data = $this->upload->data();
					
        if($file_data['file_name'])
        {
            $carousel_image 	= $file_data['file_name'];
            @unlink($config['upload_path'].$this->input->post('image'));
        }
        else
        {
            $carousel_image = $this->input->post('prev_carousel_image');
        }

        $data = array(
            'title' => $this->input->post('carousel_title'),
            'image' => $carousel_image,
            'description' => $this->input->post('description')
	);
	
	$this->db->where('cid', $cid);
        $this->db->update('carousel_images', $data);
    }
	
    function delete_carousel($cid = '') {
        
        $path = './uploads/carousels/';
        $this->db->select('image');
        $qry= $this->db->get_where('carousel_images',array('cid' => $this->input->post('cid')))->row();
        if($qry->image <>'' && file_exists($path.$qry->image))
        {
            unlink($path.$qry->image);
        }
        $this->db->where('cid IN ('.$cid.')');
        $this->db->delete('carousel_images');
        redirect(site_url($this->uri->slash_segment(1).'carousel')); 
    }
}