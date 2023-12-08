<?php

class mdl_reports extends CI_Model {

    function __construct() 
	{
        parent::__construct();
    }

    function get_reports($aid)
	{	
		$where = ' C.customer_id = CD.customer_id';
		
		if($aid)
		{
			$where .= " AND CD.auction_id = '".$aid."'";
		}

		if(($this->uri->segment(5)) && ($this->uri->segment(5) <> 'All'))
		{
			$where .= " AND first_name LIKE '".$this->uri->segment(5)."%'";
		}

		$query = $this->db->query("SELECT C.user_name, C.first_name, C.last_name, C.email, C.phone_number, CD.customer_id, C.register_date, document_id, auction_id, download_date, company_name, CD.address, CD.city, CD.state, CD.zip, CD.ip_address, CD.is_ca_accepted FROM ".$this->db->dbprefix('customers')." C, ".$this->db->dbprefix('customer_docs_download')." CD WHERE ".$where." GROUP BY CD.customer_id ORDER BY download_date DESC");
		
		return $query->result();
	}

	function get_report_details($cid, $did, $aid = 0)
	{
		$where = '1';

		if($cid)
		{
			if($aid)
			{
				$where .= " AND auction_id = '".$aid."'";
			}

			$where .= " AND customer_id = '".$cid."'";
		}

		if($did)
		{
			$where .= " AND document_id = '".$did."'";
		}

		$query = $this->db->query("SELECT * FROM ".$this->db->dbprefix('customer_docs_download')." WHERE ".$where." ORDER BY download_date DESC");
		return $query->result();
	}

	function get_auctions($type = 'ALL')
	{
		if($type == 'UPCOMING')
		{
			$this->db->where('auction_date >= ', date('Y-m-d'));
			$this->db->or_where('tbd_flag', 1);
		}

		if($type == 'PAST')
		{
			$this->db->where('auction_date < ', date('Y-m-d'));
		}

		$this->db->select('auction_id, headline, property_address, property_city, property_state');
		$this->db->order_by('headline', 'ASC');
		$query = $this->db->get('auctions');
		return $query->result();
	}

	function get_customer_name($id)
	{
		$this->db->select('first_name, last_name');
		$this->db->where('customer_id', $id);
		$query = $this->db->get('customers');
		$row = $query->row();
		return $row->first_name.' '.$row->last_name;
	}

	function get_document_name($id)
	{
		$this->db->select('document_name');
		$this->db->where('document_id', $id);
		$query = $this->db->get('auction_documents');
		$row = $query->row();
		return $row->document_name;
	}

	function get_auction_name($id)
	{
		$this->db->select('headline');
		$this->db->where('auction_id', $id);
		$query = $this->db->get('auctions');
		$row = $query->row();
		return $row->headline;
	}

	function get_auction_address($id)
	{
		$this->db->select('property_address, property_address2');
		$this->db->where('auction_id', $id);
		$query = $this->db->get('auctions');
		$row = $query->row();
		return $row->property_address.' '.$row->property_address2;
	}

	function get_auction_city($id)
	{
		$this->db->select('property_city');
		$this->db->where('auction_id', $id);
		$query = $this->db->get('auctions');
		$row = $query->row();
		return $row->property_city;
	}

	function get_auction_state($id)
	{
		$this->db->select('property_state');
		$this->db->where('auction_id', $id);
		$query = $this->db->get('auctions');
		$row = $query->row();
		return $row->property_state;
	}

	function get_all_states() {
        $state_arr = array();
        $result = $this->db->get('states')->result();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $state_arr[$row->state_id] = $row->state_name;
            }
        }
        return $state_arr;
    }

	function get_state_name($id) {

		$this->db->select('state_name');
		$this->db->where('state_id', $id);
		$query = $this->db->get('states');
		$row = $query->row();
		return $row->state_name;
    }
}