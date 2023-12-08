<?php

class mdl_account extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function login() {
        global $row;

        $check_user = $this->db->query("SELECT username,pwd FROM " . $this->db->dbprefix('users') . " WHERE status = 1 AND allow_admin_login = 1 AND username = '" . $this->db->escape_str($this->input->post('lusername')) . "'");
        $row_user = $check_user->row();
		
        if ($check_user->num_rows() > 0) {
            $get_cookie = unserialize(get_cookie('remember'));
            if ($get_cookie['password'] !='' && $get_cookie['password'] == $row_user->pwd) {
                $pass = $get_cookie['password'];
            } else {
                $pass = $this->db->escape_str($this->encrypt->sha1($this->input->post('lpassword')));
            }
            $query = $this->db->query("SELECT id, firstname, lastname, email, user_type, userId, lastvisitDate FROM " . $this->db->dbprefix('users') . " WHERE status = 1 AND allow_admin_login = 1 AND username = '" . $this->db->escape_str($this->input->post('lusername')) . "' AND pwd = '" . $pass . "'");

            if ($query->num_rows() > 0) {
                $row = $query->row();

                if ($this->input->post('remember') == 1) {
                    
                    $cookie_arr = array('username' => $this->input->post('lusername'), 'password' =>  $pass);
                    $serialize = serialize($cookie_arr);
                    
                    /* Set cookie to last 1 week */
                    $set_cookie = array(
                        'name' => 'remember',
                        'value' => $serialize,
                        'expire' => time() + 60 * 60 * 24 * 7,
                        'domain' => '.auctionadvisors.com',
                        'path' => '/'
                        
                    );
                    $this->input->set_cookie($set_cookie);
                } else {
                    
                    delete_cookie('remember','.auctionadvisors.com','/');
                }
                $timestamp = time();
                $timezone = 'UP45';
                $daylight_saving = TRUE;

                $data = array(
                    'lastvisitDate' => gmt_to_local($timestamp, $timezone, $daylight_saving),
                    'total_login_count' => total_login_count+1
                );                
                

                $this->db->update('users', $data, array('id' => $row->id));
            }
        }

        return $row;
    }

    function check_user() {
        $query = $this->db->query("SELECT id, email, username, userId FROM " . $this->db->dbprefix('users') . " WHERE  username = '" . $this->db->escape_str($this->input->post('rusername')) . "' AND email = '" . $this->db->escape_str($this->input->post('remail')) . "'");

        if ($query->num_rows() > 0) {
            return $query->row();
        }
    }
    
    function get_user_details($userId)
    {
            $this->db->select('image');
            return $this->db->get_where('users', array('userId' => $userId))->row_array();
    }

    function update_user_pwd($strpwd, $uid) {
        $data = array('pwd' => $this->encrypt->sha1($strpwd));
        $this->db->update('users', $data, array('userId' => $uid));
    }

    function get_total_live_auctions() {

        $sess_user_id = $this->session->userdata('gblUserId');
        $sess_user_role = $this->session->userdata('role_type');
        if ($sess_user_role == 'U') {
            $this->db->where("(auction_user = '" . $sess_user_id . "' OR additional_user = '" . $sess_user_id . "')");
        }
        $this->db->where('(auction_date >= "' . date('Y-m-d') . '" OR tbd_flag = 1)');
        $this->db->where('is_auction_live', '1');
        $this->db->where('is_property_for_sale', '0');
        $this->db->where('is_active', '1');
        $this->db->from('auctions');
        return $this->db->count_all_results();
    }

    function get_total_propsale_auctions() {
        $sess_user_id = $this->session->userdata('gblUserId');
        $sess_user_role = $this->session->userdata('role_type');
        if ($sess_user_role == 'U') {
            $this->db->where("(auction_user = '" . $sess_user_id . "' OR additional_user = '" . $sess_user_id . "')");
        }
        $this->db->where('is_active', '1');
        $this->db->where('is_property_for_sale', '1');
        $this->db->from('auctions');
        return $this->db->count_all_results();
    }

}

