<?php

class mdl_news extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    
	function get_news()
	{		
		$this->db->select('news_id,newstitle,news_headline,long_description,featured_image,is_featured,news_date,external_link,is_live,tags,cat_id');
		return $this->db->get_where('news', array('news_id > ' => 1))->result();
	}
	
	function get_news_details($aid) {
        $this->db->where('news_id', $aid);
        return $this->db->get('news')->row_array();
    }
	
	function insert_news() {
        
		$news_date = date("Y-m-d", strtotime($this->input->post('news_date')));
		
		//insert home page slider information 
		$config['upload_path'] = './uploads/news/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('featured_image');
		$file_data = $this->upload->data();
		$featured_image =  $file_data['file_name'];
		
        $data = array(
           
            'newstitle' 		=> $this->input->post('newstitle'),
            'news_headline' 	=> nl2br($this->input->post('news_headline')),
            'long_description' 	=> $this->input->post('long_description'),
			'featured_image'	=> $featured_image,
			'is_featured'		=> $this->input->post('is_featured'),
			'news_date'			=> $news_date,
            'external_link' 	=> $this->input->post('external_link'),
			'is_live'			=> $this->input->post('is_live'),
			'tags'				=> $this->input->post('tags'),
			'cat_id'			=> $this->input->post('news_category')
			
        );
		
		$this->db->insert('news', $data);
        $last_insert_id = $this->db->insert_id();
		
		$this->session->set_flashdata('msg_ok', 'News Created successfully.');
        redirect(site_url($this->uri->slash_segment(1) . 'news'));
    }
	
	function update_news($nid) {

		$news_date = date("Y-m-d", strtotime($this->input->post('news_date')));

		//update

		$structure = './uploads/news/';
		$config['upload_path'] = $structure;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;

		$this->load->library('upload');
		$this->upload->initialize($config);
		$this->upload->do_upload('featured_image');
		$file_data = $this->upload->data();
					
		if($file_data['file_name'])
		{
			$featured_image = $file_data['file_name'];
			@unlink($config['upload_path'].$this->input->post('prev_featured_image'));
		}
		else
		{
			$featured_image = $this->input->post('prev_featured_image');
		}

		$data = array(
           
            'newstitle' 		=> $this->input->post('newstitle'),
            'news_headline' 	=> nl2br($this->input->post('news_headline')),
            'long_description' 	=> $this->input->post('long_description'),
			'featured_image'	=> $featured_image,
			'is_featured'		=> $this->input->post('is_featured'),
			'news_date'			=> $news_date,
            'external_link' 	=> $this->input->post('external_link'),
			'is_live'			=> $this->input->post('is_live'),
			'tags'				=> $this->input->post('tags'),
			'cat_id'			=> $this->input->post('news_category')
			
        );
		
		$this->db->where('news_id', $nid);
        $this->db->update('news', $data);
        //echo $this->db->last_query();exit;
	}
	
	function delete_news($nid = '') {
        
		//$dirname =$_SERVER["DOCUMENT_ROOT"].'/uploads/news/';
		$this->db->where('news_id', $nid);
		$this->db->delete('news'); 
        
		redirect(site_url($this->uri->slash_segment(1).'news')); 
    }
	
	function update_is_featured($type, $aid)
    {
		
		$bigValue = $this->db->query("SELECT is_featured FROM aa_news where news_id = '".$aid."'");
		$bigValue = $bigValue->result();
			
		$fvalue = $bigValue[0]->is_featured;
      
       if($fvalue == 0){
                    $this->db->where('news_id', $aid);
                    $this->db->update('news',array('is_featured' => 1));
                    $msg = 'News flagged as featured';
        }
         if($fvalue == 1){
                    $this->db->where('news_id', $aid);
                    $this->db->update('news',array('is_featured' => 0));
                    $msg = 'News converted as non featured';
                    break;
        }
        return $msg;
    }

}