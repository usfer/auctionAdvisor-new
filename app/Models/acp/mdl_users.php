<?php

namespace App\Models;

use CodeIgniter\Model;

class MdlUser extends Model
{
    public $pageLimit = 30;	

    function __construct() 
	{
        parent::__construct();
    }

	function get_users()
	{		
		$this->db->select('id, userId, user_type, show_in_frontend, firstname, lastname,accreditations');
		return $this->db->get_where('users', array('id > ' => 1))->result();
	}

	function get_user_details($userId)
	{
		return $this->db->get_where('users', array('userId' => $userId))->row_array();
	}

	function insert_users()
	{

		$user_check = $this->validate_user($this->input->post('username'));
		if($user_check == 'N')
		{
			$this->session->set_flashdata('msg_error', 'username already exists');
			redirect(site_url($this->uri->slash_segment(1).'users/add'));
		}
		// Desired folder structure
		$structure = './uploads/'.time();

		// To create the nested structure, the $recursive parameter 
		// to mkdir() must be specified.

		if (!mkdir($structure, 0755, true)) {
			die('Failed to create folders...');
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

		if($width > 250){
			$prop_height = round((250/$width)*$height);
			$prop_width = 250;
		}else{
			$prop_height = $height;
			$prop_width = $width;
		}

		$config['image_library'] = 'gd2';
		$config['source_image'] = $structure.'/'.$file_data['file_name'];		
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $prop_width;
		$config['height'] = $prop_height;
		$this->load->library('image_lib', $config);
		$this->image_lib->resize();               
               

		$data = array(
			'userId'			=> time(),
			'user_type'			=> $this->input->post('user_type'),
			'firstname'			=> $this->input->post('firstname'),
			'lastname'			=> $this->input->post('lastname'),
			'title'				=> $this->input->post('title'),
			'accreditations'                => $this->input->post('accreditations'),			
			'email'				=> $this->input->post('email'),
			'phone'				=> $this->input->post('phone'),
			'cell'				=> $this->input->post('cell'),
			'fax'				=> $this->input->post('fax'),
			'counties'			=> $this->input->post('counties'),
			'image'				=> $file_data['file_name'],
			'bio'				=> $_REQUEST['bio'],
			'username'			=> $this->input->post('username'),
			'pwd'				=> $this->encrypt->sha1($this->input->post('pass')),
			'show_in_frontend'              => $this->input->post('show_in_frontend'),
			'allow_admin_login'             => $this->input->post('allow_admin_login'),
			'auction_zip_user_id'           => $this->input->post('auction_zip_user_id'),
			'auction_zip_password'          => $this->input->post('auction_zip_password'),
			'status' => '1'
		);

		$this->db->insert('users', $data); 
		$this->session->set_flashdata('msg_ok', 'User created successfully');
		redirect(site_url($this->uri->slash_segment(1).'users/add'));
	}

	function update_user($uid)
	{
		// Desired folder structure
		$structure = './uploads/'.$uid;

		// To create the nested structure, the $recursive parameter 
		// to mkdir() must be specified.
		if(!file_exists($structure))
		{
			if(!mkdir($structure, 0755, true)) 
			{
				die('Failed to create folders...');
			}			
		}

		$config['upload_path'] = $structure;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = TRUE;
		$this->load->library('upload', $config);
                $this->upload->do_upload('file_upload');
                
                $file_data = $this->upload->data();
   
                $img = getimagesize($file_data['full_path']);

		$width = $img['0'];
		$height = $img['1'];

		if($width > 250){
			$prop_height = round((250/$width)*$height);
			$prop_width = 250;
		}else{
			$prop_height = $height;
			$prop_width = $width;

		}

		$config['image_library'] = 'gd2';
		$config['source_image'] = $structure.'/'.$file_data['file_name'];		
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $prop_width;
		$config['height'] = $prop_height;
		$this->load->library('image_lib', $config);

		$this->image_lib->resize();
      

		if($file_data['file_name'] <> '')
		{
			$upfile = $file_data['file_name'];

			if(file_exists($config['upload_path'].'/'.$this->input->post('pre_img')))
			{
				@unlink($config['upload_path'].'/'.$this->input->post('pre_img'));
			}
		}
		else
		{
			$upfile = $this->input->post('pre_img');
		}
		                
		$data = array(	
			'firstname'		=> $this->input->post('firstname'),
			'lastname'		=> $this->input->post('lastname'),
			'title'			=> $this->input->post('title'),
			'accreditations'        => $this->input->post('accreditations'),			
			'email'			=> $this->input->post('email'),
			'phone'			=> $this->input->post('phone'),
			'cell'			=> $this->input->post('cell'),
			'fax'			=> $this->input->post('fax'),
			'counties'		=> $this->input->post('counties'),
			'image'			=> $upfile,
			'bio'			=> $_REQUEST['bio'],
			'username'		=> $this->input->post('username'),
			'auction_zip_user_id'	=> $this->input->post('auction_zip_user_id'),
			'auction_zip_password'	=> $this->input->post('auction_zip_password')
		);		
               //allow user to update user permissions those who have below privilleges
               if ($this->session->userdata('role_type') == 'B' || $this->session->userdata('role_type') == 'A') { 
                           
                   $data['user_type']           = $this->input->post('user_type');
                   $data['show_in_frontend']    = $this->input->post('show_in_frontend');
                   $data['allow_admin_login']   = $this->input->post('allow_admin_login');
               }
                 
		if($this->input->post('pass'))
		{
			$data['pwd'] = $this->encrypt->sha1($this->input->post('pass'));
		}

		$this->db->where('userId', $uid);
		$this->db->update('users', $data);
		
		$this->session->set_flashdata('msg_ok', 'User updated successfully');
		redirect(site_url($this->uri->slash_segment(1).'users/edit/'.$uid));
	}

	function delete_user($uid)
	{
                $dirname =$_SERVER["DOCUMENT_ROOT"].'/orea/uploads/'.$uid.'/';
		$this->db->where('userId', $uid);
		$this->db->delete('users'); 
                $this->delete_directory($dirname);
		redirect(site_url($this->uri->slash_segment(1).'users'));
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

	function validate_user($uname)
	{
	  $this->db->where('username', $uname);
	  $cnt = $this->db->count_all_results('users');

	  if($cnt > 0)
	  {
	   return 'N';
	  }
	  else
	  {
	   return 'Y';
	  }
	 }
}