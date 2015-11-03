<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model for Database Table cdp_guest
 * @package 	Guest
 * @category	Model
 * @Date 			9-July-2015.
 * @author 		Roopam
 */

class Model_Guest_CdpGuests extends ORM {
    protected $_db_group = 'default';
    protected $_table_name = 'cdp_guests';

    /**
     * @get_count_for_guest_details_grid function
     * @param integer $account_id
     * @param string $search_type
     * @param string $search_value
     * @return count for  guest detail grid
     */
    public function get_count_for_guest_details_grid($search_type = NULL, $search_value = NULL, $cdp_filter_array = array(), $account_id = NULL)
	{
        $user_id = '';
        $common_function = new CommonFunction();
        $session = Session::instance();
        if ($account_id == NULL) {
            $account_id = $session->get('account_id');
            $user_id = $session->get('user_id');
        }       
        $all_dept_permission_id = array();
		$orm_obj = ORM::factory('Guest_CdpGuests');
        $orm_obj->join('cdp_guest_department', 'left')->on('guest_cdpguests.id', '=', 'cdp_guest_department.guest_id')->on('cdp_guest_department.account_id', '=', DB::expr("'" . $account_id . "'"));
        $orm_obj->where('guest_cdpguests.account_id', '=', $account_id);
        $orm_obj->and_where('guest_cdpguests.is_deleted', '=', '0');
        $guest_details_array = array();
        $depatment_array = array();
		
        if (!empty($cdp_filter_array)) {
            if (isset($cdp_filter_array['access_level'])) {
                $access_level_array = explode(',', $cdp_filter_array['access_level']);
                $orm_obj->join('cdp_guests_access', 'left')->on('guest_cdpguests.id', '=', 'cdp_guests_access.guest_id')->on('cdp_guests_access.account_id', '=', DB::expr("'" . $account_id . "'"));
            }
            if (isset($cdp_filter_array['guest_details'])) {
                $guest_details_array = explode(',', $cdp_filter_array['guest_details']);
                $orm_obj->join('cdp_issued_rfid', 'left')->on('guest_cdpguests.id', '=', 'cdp_issued_rfid.guest_id')->on('cdp_issued_rfid.account_id', '=', DB::expr("'" . $account_id . "'"));
            }
        }
        if ($search_type != '' && $search_type != NULL) {
            if ($search_type == 'first_name') {
                $orm_obj->and_where(DB::expr('CONCAT(guest_cdpguests.first_name,\' \',guest_cdpguests.last_name)'), 'like', '%' . $search_value . '%');
            } else {
                $orm_obj->and_where($search_type, 'like', '%' . $search_value . '%');
            }
        }
        if (!empty($guest_type_array)) {
            $orm_obj->and_where('guest_type_id', 'in', $guest_type_array);
        }
        if (!empty($depatment_array)) {
            $orm_obj->and_where('cdp_guest_department.department_id', 'in', $depatment_array);
        }
        if (isset($cdp_filter_array['affiliation']) && $cdp_filter_array['affiliation'] != '') {
            $orm_obj->and_where('affiliation', 'like', '%' . $cdp_filter_array['affiliation'] . '%');
        }
        if (isset($cdp_filter_array['email']) && $cdp_filter_array['email'] != '') {
            $orm_obj->and_where('email', 'like', '%' . $cdp_filter_array['email'] . '%');
        }
		
        $orm_obj->group_by('guest_cdpguests.id');
        $guest_detail = $orm_obj->find_all();
        $count = count($guest_detail);
        return $count;
    }

    /**
     * @get_records_for_guest_details_grid function
     * @param integer $account_id
     * @param integer $limit
     * @param integer $start
     * @param json object $sort
     * @param string $search_type   : name of column
     * @param string $search_value  : search value
     * @return guest details
     */
    public function get_records_for_guest_details_grid($sort = NULL, $search_type = NULL, $search_value = NULL, $limit = NULL, $start = 0, $cdp_filter_array = array()) {
        $session = Session::instance();
        $common_function = new CommonFunction();
        $account_id = $session->get('account_id');
        $user_id = $session->get('user_id');
        $orm_obj = ORM::factory('Guest_CdpGuests');
        $department_array = array();

        if (!empty($cdp_filter_array)) {
            if (isset($cdp_filter_array['guest_type']))
                $guest_type_array = explode(',', $cdp_filter_array['guest_type']);
            if (isset($cdp_filter_array['guest_details']))
                $guest_details_array = explode(',', $cdp_filter_array['guest_details']);
            if (isset($cdp_filter_array['department']))
                $department_array = explode(',', $cdp_filter_array['department']);
        }

        $orm_obj->join('cdp_guests_access', 'left')->on('guest_cdpguests.id', '=', 'cdp_guests_access.guest_id')->on('cdp_guests_access.account_id', '=', DB::expr("'" . $account_id . "'"))->on('cdp_guests_access.is_deleted', '=', DB::expr("'0'"));
        $orm_obj->join('cdp_guest_department', 'left')->on('guest_cdpguests.id', '=', 'cdp_guest_department.guest_id')->on('cdp_guest_department.account_id', '=', DB::expr("'" . $account_id . "'"));
        $orm_obj->select(array(DB::expr('CONCAT(guest_cdpguests.first_name," ",guest_cdpguests.last_name)'), 'name'));
        $orm_obj->select(array(DB::expr('CASE
		                          WHEN status = "1"
		                          THEN "Enabled"
		                          WHEN status = "0"
		                          THEN "Disabled"
								  END'), 'status_details'));
        $orm_obj->select(array(DB::expr("GROUP_CONCAT(distinct cdp_guest_department.department_name SEPARATOR ', ')"), 'department_name'));
       
        $orm_obj->where('guest_cdpguests.account_id', '=', $account_id);
        $orm_obj->and_where('guest_cdpguests.is_deleted', '=', '0');

        if ($search_type != '' && $search_type != NULL) {
            if ($search_type == 'first_name') {
                $orm_obj->and_where(DB::expr('CONCAT(guest_cdpguests.first_name,\' \',guest_cdpguests.last_name)'), 'like', '%' . $search_value . '%');
            } else {
                $orm_obj->and_where($search_type, 'like', '%' . $search_value . '%');
            }
        }
        /* for column filter */
        if (!empty($department_array)) {
            $orm_obj->and_where('cdp_guest_department.department_id', 'in', $department_array);
        }
        if (!empty($guest_type_array)) {
            $orm_obj->and_where('guest_type_id', 'in', $guest_type_array);
        }
        if (isset($cdp_filter_array['affiliation']) && $cdp_filter_array['affiliation'] != '') {
            $orm_obj->and_where('affiliation', 'like', '%' . $cdp_filter_array['affiliation'] . '%');
        }
        if (isset($cdp_filter_array['email']) && $cdp_filter_array['email'] != '') {
            $orm_obj->and_where('email', 'like', '%' . $cdp_filter_array['email'] . '%');
        }

       
        /* for limit */
        if ($limit != '' && $limit != NULL) {
            $orm_obj->limit($limit)->offset($start);
        }
        if ($sort != NULL) {
            $sort_array = json_decode($sort);
            $orm_obj->order_by($sort_array[0]->{'property'}, $sort_array[0]->{'direction'});
        }
        $orm_obj->group_by('guest_cdpguests.id');
        $guest_details = $orm_obj->find_all()->as_array();
        return $guest_details;
    }

    /**
     * @delete_guest_details to delete guest
     * @param array $guest_id_array
     * @return true or false
     */
    public function delete_guest_details($guest_id_array = array()) {
        $data = implode(",", $guest_id_array);
        if (!empty($guest_id_array)) {
            $update_query = "UPDATE cdp_guests SET is_deleted = '1',updated_on = '" . date('Y-m-d H:i:s') . "' where id IN (" . $data . ")";
            DB::query(Database::UPDATE, $update_query)->execute();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @delete_guest_details to delete guest
     * @param array $guest_id_array
     * @return true or false
     */
    public function change_guest_status_from_web($guest_id_array = array(), $flag = NULL) {
        $data = implode(",", $guest_id_array);
        $update_query = '';
        if (!empty($guest_id_array)) {
            if ($flag === 'enabled') {
                $update_query = "UPDATE cdp_guests SET status = '1',cron_update_flag= '0',updated_on = '" . date('Y-m-d H:i:s') . "' where id IN (" . $data . ")";
            }
            if ($flag === 'disabled') {
                $update_query = "UPDATE cdp_guests SET status = '0',updated_on = '" . date('Y-m-d H:i:s') . "' where id IN (" . $data . ")";
            }

            DB::query(Database::UPDATE, $update_query)->execute();
            return true;
        } else {
            return false;
        }
    }

    /**
     * @save_guest_details_from_web function
     * @used when guest is submiteed by add guest form
     * @param integer $account_id
     * @return guest id
     */
    public function save_guest_details_from_web($guest_detail, $photo = '', $account_id = NULL, $signature = NULL, $created_on = NULL, $updated_on = NULL, $temp_id = NULL, $submitted_via = NULL, $created_by_id = NULL, $created_by = NULL, $file_original_name = NULL) {
        $today = date('Y-m-d H:i:s');
        if ($account_id == NULL) {
            $session = Session::instance();
            $account_id = $session->get('account_id');
        }
        $orm_obj = ORM::factory('Guest_CdpGuests');
        $orm_obj->account_id = $account_id;
        $orm_obj->first_name = trim(Arr::get($guest_detail, 'first_name'));
        $orm_obj->last_name = trim(Arr::get($guest_detail, 'last_name'));
        if (Arr::get($guest_detail, 'guest_type') != -1) {
            $orm_obj->guest_type = trim(Arr::get($guest_detail, 'guest_type_name'));
            $orm_obj->guest_type_id = trim(Arr::get($guest_detail, 'guest_type'));
        } else {
            $orm_obj->guest_type = '';
            $orm_obj->guest_type_id = '';
        }
        $orm_obj->guest_of = trim(Arr::get($guest_detail, 'guest_of'));
        $orm_obj->email = trim(Arr::get($guest_detail, 'email'));
        $orm_obj->cell_phone = trim(Arr::get($guest_detail, 'cell_phone'));
        $orm_obj->affiliation = trim(Arr::get($guest_detail, 'affiliation'));
        $orm_obj->status = trim(Arr::get($guest_detail, 'status'));
        $orm_obj->notes = trim(Arr::get($guest_detail, 'notes'));
        $orm_obj->photo = trim($photo);
        $orm_obj->photo_original_name = trim($file_original_name);      
        $orm_obj->signature = $signature;
        if ($created_on == NULL) {
            $orm_obj->created_on = $today;
        } else {
            $orm_obj->created_on = $created_on;
        }
        if ($updated_on == NULL) {
            $orm_obj->updated_on = $today;
        } else {
            $orm_obj->updated_on = $updated_on;
        }
        if ($temp_id != NULL) {
            $orm_obj->device_unique_id = $temp_id;
        }
        $orm_obj->submitted_via = trim($submitted_via);
        $orm_obj->created_by_id = trim($created_by_id);
        $orm_obj->created_by = trim($created_by);        
        $orm_obj->auto_disable_flag = trim(Arr::get($guest_detail, 'auto_disable_flag', '0'));
        $orm_obj->date_time = trim(Arr::get($guest_detail, 'auto_date_time', ''));
        $result = $orm_obj->save()->id;
        return $result;
    }

    /**
     * @get_record_for_specific_id function
     * @param integer $account_id
     * @param integer $id
     */
    public function get_record_for_specific_id($id) {
        $session = Session::instance();
        $account_id = $session->get('account_id');
        $orm_obj = ORM::factory('Guest_CdpGuests');
        $orm_obj->where('id', '=', $id);
        $orm_obj->and_where('account_id', '=', $account_id);
        $guest_details = $orm_obj->find()->as_array();
        return $guest_details;
    }

    /**
     * @update_note_details function
     * @param integer $account_id
     * @param integer $id
     * @param string $notes
     */
    public function update_note_details($id = NULL, $notes = NULL) {
        $today = date('Y-m-d H:i:s');
        if ($id != NULL) {
            $session = Session::instance();
            $account_id = $session->get('account_id');
            $orm_obj = ORM::factory('Guest_CdpGuests');
            $orm_obj->where('id', '=', $id);
            $orm_obj->and_where('account_id', '=', $account_id);
            $sel_row = $orm_obj->find();
            if ($sel_row->loaded()) {
                $sel_row->notes = $notes;
                $sel_row->updated_on = $today;
                $sel_row->save();
            }
            return true;
        } else {
            return false;
        }
    }  

    /**
     * update_record_from_web function
     * @param integer $account_id
     * @param integer $id
     */
    public function update_record_from_web($guest_detail, $photo = '', $account_id = NULL, $signature = NULL, $updated_on = NULL, $file_original_name = NULL) {
        $today = date('Y-m-d H:i:s');
        if ($account_id == NULL) {
            $session = Session::instance();
            $account_id = $session->get('account_id');
        }
        $orm_obj = ORM::factory('Guest_CdpGuests')->where('id', '=', Arr::get($guest_detail, 'id'))->and_where('account_id', '=', $account_id)->find();
        if ($orm_obj->loaded()) {
            $orm_obj->account_id = $account_id;
            $orm_obj->first_name = trim(Arr::get($guest_detail, 'first_name'));
            $orm_obj->last_name = trim(Arr::get($guest_detail, 'last_name'));
            if (Arr::get($guest_detail, 'guest_type') != -1) {
                $orm_obj->guest_type = trim(Arr::get($guest_detail, 'guest_type_name'));
                $orm_obj->guest_type_id = trim(Arr::get($guest_detail, 'guest_type'));
            } else {
                $orm_obj->guest_type = '';
                $orm_obj->guest_type_id = '';
            }
            $orm_obj->guest_of = trim(Arr::get($guest_detail, 'guest_of'));
            $orm_obj->email = trim(Arr::get($guest_detail, 'email'));
            $orm_obj->cell_phone = trim(Arr::get($guest_detail, 'cell_phone'));
            $orm_obj->affiliation = trim(Arr::get($guest_detail, 'affiliation'));
            $orm_obj->status = trim(Arr::get($guest_detail, 'status'));
            if (Arr::get($guest_detail, 'status') == '1') {
                $orm_obj->cron_update_flag = '0';
            }
            $orm_obj->notes = trim(Arr::get($guest_detail, 'notes'));
            $orm_obj->photo = trim($photo);
            $orm_obj->photo_original_name = trim($file_original_name);
            if (Arr::get($guest_detail, 'template') != -1) {
                $orm_obj->template_id = trim(Arr::get($guest_detail, 'template'));
                $orm_obj->template_name = trim(Arr::get($guest_detail, 'template_name'));
            } else {
                $orm_obj->template_id = '';
                $orm_obj->template_name = '';
            }
            $orm_obj->signature = $signature;
            if ($updated_on == NULL) {
                $orm_obj->updated_on = $today;
            } else {
                $orm_obj->updated_on = $updated_on;
            }            
            $orm_obj->auto_disable_flag = trim(Arr::get($guest_detail, 'auto_disable_flag'));
            $orm_obj->date_time = trim(Arr::get($guest_detail, 'auto_date_time', ''));
            $orm_obj->save();
            return true;
        } else {
            return false;
        }
    }     
}