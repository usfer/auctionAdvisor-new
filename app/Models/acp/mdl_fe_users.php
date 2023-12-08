<?php 
class mdl_fe_users extends CI_Model
{  
	public $pageLimit = 30;	

    function __construct() 
	{
        parent::__construct();
    }

	function get_users()
	{		
		$this->db->select('customer_id, first_name, last_name, email, phone_number');
		return $this->db->get_where('customers', array('customer_id > ' => 0))->result();
	}

	function get_user_details($userId)
	{
		return $this->db->get_where('customers', array('customer_id' => $userId))->row_array();
	}

	function insert_users()
	{
		$data = array(
			'user_name'     => $this->input->post('user_name'),
			'first_name'	=> $this->input->post('first_name'),
			'last_name'		=> $this->input->post('last_name'),
			'email'			=> $this->input->post('email'),
			'phone_number'  => $this->input->post('phone_number'),
			'pwd'			=> $this->encrypt->sha1($this->input->post('pass'))
		);

		$this->db->insert('customers', $data); 
		$this->session->set_flashdata('msg_ok', 'User created successfully');
		redirect(site_url($this->uri->slash_segment(1).'fe_users/add'));
	}

	function update_user($uid)
	{
		$data = array(
			'first_name'	=> $this->input->post('first_name'),
			'last_name'		=> $this->input->post('last_name'),
			'email'			=> $this->input->post('email'),
			'phone_number'  => $this->input->post('phone_number')
		);
                 
		if($this->input->post('pass'))
		{
			$data['pwd'] = $this->encrypt->sha1($this->input->post('pass'));
		}

		$this->db->where('customer_id', $uid);
		$this->db->update('customers', $data);
		
		$this->session->set_flashdata('msg_ok', 'User updated successfully');
		redirect(site_url($this->uri->slash_segment(1).'fe_users/edit/'.$uid));
	}

	function delete_user($uid)
	{
        $this->db->where('customer_id', $uid);
		$this->db->delete('customers'); 
		redirect(site_url($this->uri->slash_segment(1).'fe_users'));
	}  

	function validate_user($uname)
	{
		$this->db->where('user_name', $uname);
		$cnt = $this->db->count_all_results('customers');

		if($cnt > 0)
		{
			return 'N';
		}
		else
		{
			return 'Y';
		}
	}
}   