<?php defined('SYSPATH') or die('No direct script access.');

/**
  * Guest Controller
  * This file contain guest index controller of Guest which communicate to the view and model of Guest
  * @package 	Guest
  * @category	Controller	
  * @Date 		10-July-2015
  * @author 	     Roopam
  */
  
   class Controller_Guest_GuestIndex extends Controller
  {
	/**
	 * @Constructor for the index controller
	 * @This will check wheather the user is logged in or not, if not logged in
	 * @redirect to Login Controller and load the login view 
	 */
	 public function __construct(Request $request, Response $response)
	 {
        parent::__construct($request,$response);
        $this->session= Session::instance();
        $this->user_id  = $this->session->get('user_id');
        $this->db = Database::instance();
		$this->common_function = new CommonFunction();
    	if (!Auth::instance()->logged_in())
		{
			$this->redirect("Main/Login/after_logout");
			exit(0);
		}
		$this->language_file_path = $this->session->get('accountlanguage').'-Guest-GuestListView';
		$this->notification_language_path = $this->session->get('accountlanguage').'-Guest-NotificationMessages';
		$this->config = Kohana::$config->load('Guest/config')->as_array();
		$this->log_in_user_first_last_name  = $this->session->get('firstname').' '.$this->session->get('lastname');
		$this->x_req  = $request;
		$this->x_resp = $response;
    }
	
	/**
	 * @index action of Guest GuestIndex controller
	 * @This action render the GuestListView of Guest to index view 
	 * @and return the Guest GuestListView in response to the ajax request  
	 */
	function action_index()
	{		
		$view_index                            = View::factory('Guest/Index');	
		$view_main_content                     = View::factory('Guest/GuestListView');
		$view_main_content->language_file_path = $this->language_file_path;
		
		/*records per page*/
		$settings = Model::factory('guest_QueueSettings')->get_user_settings($this->user_id, 'guest_grid')->as_array();		
		if($settings['id'] != '') 
		{
			 $itemsPerPage = $settings['max_records'];
		} 
		else
		{
			 $itemsPerPage = '20';
		}		
		/*records per page end here*/
		$view_main_content->itemsPerPage 	   = $itemsPerPage;		
		$config_details                        = Model::factory('Guest_CdpGridColumnSettings')->cdp_check_user_column_settings('guest_grid')->as_array();
		$view_main_content->grid_config        = $config_details['grid_config'];
		$view_index->content                   = $view_main_content->render();
		$this->response->body($view_index);
	}
	
	/**
	 * @load_add_guest_form action of Guest GuestIndex controller
	 * @This action render the AddGuestForm of Guest 
	 * @and return the Guest AddGuestForm in response to the ajax request  
	 */
	function action_load_add_guest_form()
	{        
        $view = View::factory('Guest/AddGuestForm');
		$view->language_file_path = $this->language_file_path;
		$this->response->body($view);	
	}
	
	/**
	 * @load_guest_details_form action of Guest GuestIndex controller
	 * @This action render the GuestDetailsForm of Guest 
	 * @and return the Guest GuestDetailsForm in response to the ajax request  
	 */
	function action_load_guest_details_form()
	{
	    $id = Arr::get($_POST,'guest_id');
        $details_page_store_flag = Arr::get($_POST,'details_page_store_flag');
		$view = View::factory('Guest/GuestDetailsForm');		
		$data = Model::factory('Guest_CdpGuests')->select_record_for_specific_id($id);				
	
		/*for language file*/
		$view->language_file_path = $this->language_file_path;				
		
		/*for all data*/
		$view->data = $data;
		
		/*for image*/
		$user_image = '';
		$minWidth = 0;
		$minHeight = 0;
		if($data['photo'] != NULL  && $data['photo'] != '')
		{
		    $image_url =  $this->config['USER__THUMB_IMG_PATH'].$data['photo'];
			if(@GETIMAGESIZE($image_url))
			{	
				list($width, $height, $type, $attr)   = getimagesize($image_url);
				if($width > 120)
				{
					$minWidth = 120;
				}
				else
				{
					$minWidth = $width;
				}
				if($height > 90)
				{
					$minHeight = 90;
				}
				else
				{
					$minHeight = $height;
				}				
			   $user_image = $image_url;
			}
			else
			{
			   $user_image = '';
			   $minWidth = 0;
			   $minHeight = 0;
			}
		}
		$view->user_image = $user_image;		
		$view->minWidth = $minWidth;
		$view->minHeight = $minHeight;				
		$this->response->body($view);	
	}
	
	/**
	 * @load_note_form action of Guest GuestIndex controller
	 * @This action render GuestNotesForm of Guest 
	 * @and return the Guest GuestNotesForm in response to the ajax request  
	 */
	function action_load_note_form()
	{
        $id = Arr::get($_POST,'id');
        $store_load_flag = Arr::get($_POST,'store_load_flag','0');
		$data =  Model::factory('Guest_CdpGuests')->get_record_for_specific_id($id);
		$view = View::factory('Guest/GuestNotesForm');
		$view->language_file_path = $this->language_file_path;
		$view->data = $data;
		$this->response->body($view);	
	}		
	
	/**
	 * @download_sample_excel_file action of guest index controller
	 * @download sample formate for insert date .
	 */
	public function action_download_sample_excel_file()
	{
		Download::download_file('Guest.xlsx');
	}
	
	/**
	 * @import_from_excel_view action of Guest GuestIndex controller
	 * @This action render the AddGuestForm of Guest
	 * @and return the Guest import_from_excel_view in response to the ajax request
	 */
	public function action_import_from_excel_view()
	{
		$import_location_view = View::factory('Guest/ImportGuestView');
		$import_location_view->language_file_path = $this->language_file_path;
		$this->response->body($import_location_view->render());
	}
	
	/**
	 * @this function will show import status window.
	 * this function is used to list all error log when data import.
	 */
	function action_import_status_window()
	{
		$import_status = View::factory('Guest/GuestImportStatus');
		$import_status->language_path = $this->language_file_path;
		$error_log = Arr::get($_POST,'data');
		$import_status->status_details = $error_log;
		$this->response->body($import_status->render());
	}
	
	/**
	 * @This action will save imported guest from excel.
	 * @and return the Guest import_from_excel_view in response to the ajax request
	 */
	public function action_import_guest_from_excel()
	{
		if (
				! Upload::valid($_FILES['guest']) OR
				! Upload::type($_FILES['guest'], array('xlsx','xls')))
		{
			echo "{'success':false,'message':'".__('NOT_EXCEL_FILE',array(),'',$this->language_file_path)."','invalidfile':1}";
			return false;
		}
		
		$imported_record = SpreadsheetReader::factory(
				array(
						'filename' => $_FILES['guest']['tmp_name'],
						'format' => 'Excel2007'
				), FALSE)
				->load()
				->read(array('number' => 'D'),array(1));
		$account_id = $this->session->get('account_id');
        $user_id = $this->session->get('user_id');
		$insert_array = array(); /*for insert data*/
		$flag_for_insert = false;/*flag for insert*/
		
		/************************ For guest type detail **********************************/
		$guest_type_detail_array = array();/*for store guest type details*/
		$guest_type_name_array = array(); /*for store all guest type*/
		$guest_type_list = Model::factory('Common_GuestType')->get_all_guest_categories('id','DESC',$account_id);
		$count = $guest_type_list['count'];
		$guest_type_array = $guest_type_list['data'];
		if($count > 0)
		{
			foreach($guest_type_array as $guest_type)
			{
				$guest_type_detail_array[$guest_type['category_name']] = $guest_type['id'];
				$guest_type_name_array[] = $guest_type['category_name'];
			}
		}
		/*****************************guest type detail end here. *****************************/		
		
        /*For Department Details*/
        $selected_department_id_array = array();
        $department_detail_array = array();/*for store department detail*/
		$department_name_array = array();/*for store department name*/
		$department_list = Model::factory('Guest_GuestDepartments')->get_user_department($account_id,$user_id);
		if(count($department_list) > 0)
		{	
			foreach($department_list as $department)
			{
				$department_name = stripslashes($department->{'department'});
				$department_detail_array[$department_name]  = $department->{'department_id'};
				$department_name_array[] = $department_name;
			}
		}
        /****************************Department detail end here***********************************/      
		
		$first_name = '';
		$last_name = '';
		$email = '';
		$cell_phone = '';
		$affiliation = '';
		$guest_type_id = '';
		$guest_type_name = '';
		$guest_of = '';
		$status = '';
		$notes = '';
		$department_string ='';         
	
		$inserted_row_count = 0;		
		$count_for_insert = 0;		
		$invalid_first_name = 0;
		$invalid_last_name = 0;
		$empty_department = 0;
		$invalid_email = 0;
		$invalid_cell_phone = 0;
		$invalid_status_option = 0;		
        $invalid_department = 0;
		$status_option = array('Enabled','Disabled');
		$error_log = array();
		
		if(count($imported_record) > 0)
		{	
				foreach($imported_record as $detail)
				{
					$count_for_insert = 0;
					
					/*for first name*/
					if(isset($detail['A'])){
						$trim_first_name = trim($detail['A']);
						if($trim_first_name != '')
						{	
							$first_name = $trim_first_name;
						    $count_for_insert++;
						} else {
							$first_name = '';
							$invalid_first_name++;
						}
					} else {
						$first_name = '';	
						$invalid_first_name++;
					}
		
					/*for last name*/
					if(isset($detail['B'])){
						$trim_last_name = trim($detail['B']);
						if($trim_last_name != '') {
							$last_name = $trim_last_name;
							$count_for_insert++;
						}else {
							$last_name = '';
							$invalid_last_name++;
						}
					} else {
						$last_name = '';
						$invalid_last_name++;
					}
					
				    /*Department */
                    $selected_department_id_array = array();
                    $selected_department_array = array();
				    if(isset($detail['C'])){                                      
				        $department_array =  explode(',', trim($detail['C']));
				   	    foreach($department_array as $department){
				   	  	  $department = trim($department);
				          if(in_array($department, $department_name_array))
				           {
				           	   $selected_department['department_id']   = $department_detail_array[$department];
                               $selected_department_id_array[] = $department_detail_array[$department];
			 	           	   $selected_department['department_name'] = $department;
                               $selected_department_array[]  =$selected_department;
				   	       }
				   	       else {
                                $invalid_department++;
				   	       }
					   	}/*foreach end here.*/
					   	if(count($selected_department_array) > 0)
					   	{
					   		$count_for_insert++;
					   	}
					   	$department_string = json_encode($selected_department_array);                                           
				    }
				    else
				    {
				       $department_string ='';
				   	   $empty_department++;
				    }/*Department end here.*/
                                		
				   	/*for E-Mail*/
					if(isset($detail['D'])){
						$trim_email = trim($detail['D']);
						if (filter_var($trim_email, FILTER_VALIDATE_EMAIL)) {
								$email = $trim_email;
						}else {
							  $email = '';
							  $invalid_email++;
						}
					} else {
						$email = '';
					}
					
					
					/*for Cell Phone*/
					if(isset($detail['E'])){
						$trim_cell_phone = trim($detail['E']);
						$trim_cell_phone = str_replace('-','',$trim_cell_phone);
						if((ctype_digit($trim_cell_phone)) && (strlen($trim_cell_phone) >= 10) && (strlen($trim_cell_phone) <= 12))
						{
						   $cell_phone = $trim_cell_phone;
						}	
						else {
							$cell_phone = '';
							$invalid_cell_phone++;
						}
					} else {
						$cell_phone = '';
					}
					
					/*for Afiliation*/
					if(isset($detail['F'])){
						$affiliation = trim($detail['F']);
					} else {
						$affiliation = '';
					}
					
					/*for guest type*/
					if(isset($detail['G'])){
						    if(in_array($detail['G'],$guest_type_name_array)){
						    	$guest_type_id = $guest_type_detail_array[trim($detail['G'])];
						    	$guest_type_name = trim($detail['G']);
						    }
							else {
								$invalid_guest_type++;
								$guest_type_id ='';
								$guest_type_name ='';
							}
					}/*if end here.*/
					else{
						$guest_type_id ='';
						$guest_type_name ='';
					}
					
					/*for guest of*/
					if(isset($detail['H'])){
						$guest_of = trim($detail['H']);
					} else {
						$guest_of = '';
					}
					
				  /*for status*/
					if(isset($detail['I'])){
						$trim_status = trim($detail['I']);
						if(in_array($trim_status,$status_option))
						{
							if($trim_status === 'Enabled') {
							   $status = '1';
							}   
							if($trim_status === 'Disabled') {
							   $status = '0';
							}
						} else {
							$invalid_status_option++;
							$status = '1';
						}	
					} else {
						$status = '1';
					}
								
				   
				   /*for notes*/
				   if(isset($detail['J'])){
				   	$notes = trim($detail['J']);
				   } else {
				   	$notes = '';
				   }/*end here*/
				   	
				   /*for insert data*/
				   if($count_for_insert == 3){
						$guest_id  = Model::factory('Guest_CdpGuests')->save_guest_details($first_name, $last_name, $guest_type_id, $guest_type_name, $email, $cell_phone, $affiliation, $guest_of, $notes, $status,$this->user_id, $this->log_in_user_first_last_name);
						$inserted_row_count++;
                        if($guest_id && !empty($selected_department_array))
						{
							$department_result = Model::factory('Guest_CdpGuestDepartments')->save_guest_department($guest_id, $department_string);
						}						
											
				   }/* if($flag_for_insert) end here.*/
			  }/*main foreach loop end here.*/

			  $error_log = array(
			  		'total_records'		             => count($imported_record),
			  		'inserted_row'                   => $inserted_row_count,
			  		'invalid_first_name'             => $invalid_first_name,
			  		'invalid_last_name'              => $invalid_last_name,
			  		'invalid_email'                  => $invalid_email,
			  		'invalid_cell_phone'             => $invalid_cell_phone,
			  		'invalid_status_option'          => $invalid_status_option,
                    'invalid_department'             => $invalid_department,			  		
			  		'empty_department'               => $empty_department,			  		
			  );
			  if($inserted_row_count > 0 )
			  {					  	
			      echo"{'success':true,'message':'".__('IMPORT_RECORDS',array(),'',$this->notification_language_path)."', 'error_log':'".json_encode($error_log)."'}";
			  }
			  else 
			  {					  	
			  	echo"{'success':false,'message':'".__('IMPORT_FAILED',array(),'',$this->notification_language_path)."', 'error_log':'".json_encode($error_log)."'}";
			  }	
		}/*main if end here.*/
		else 
		{			
			$error_log = array(
					'total_records'		             => count($imported_record),
					'inserted_row'                   => $inserted_row_count,
					'invalid_first_name'             => $invalid_first_name,
					'invalid_last_name'              => $invalid_last_name,
					'invalid_rfid'                   => $invalid_rfid,
					'invalid_email'                  => $invalid_email,
					'invalid_department'             => $invalid_department,
					'invalid_cell_phone'             => $invalid_cell_phone,
					'invalid_status_option'          => $invalid_status_option,
					'invalid_event_type'             => $invalid_event_type,
					'invalid_access_level'           => $invalid_access_level,
					'empty_department'               => $empty_department,
					'empty_event_type'               => $empty_event_type,
					'empty_access_level'             => $empty_access_level,
					'invalid_required_escort_option' => $invalid_required_escort_option,
					'invalid_can_escort'             => $invalid_can_escort
			);
	       echo"{'success':false,'message':'".__('IMPORT_FAILED',array(),'',$this->notification_language_path)."', 'error_log':'".json_encode($error_log)."'}";
		}  
	}/*function end here.*/

	
	/**
	 * @load_data_for_guest_list action of Guest GuestIndex controller
	 * @This action used to load guest list view(grid),
	 * @and return the json string in response.  
	 */
	function action_load_data_for_guest_list()
	{
		$field_value_array          = array(); /*to store guest details*/
		$filter_condition_array     = array(); /*to store filter condition */
		
		$search_value = '';
		$search_type = '';
		$account_id = '';
		$user_id = '';
		
		$limit                      = Arr::get($_GET,'limit');
	    $start                      = Arr::get($_GET,'start');		
		$sort                       = Arr::get($_GET,'sort','[{"property":"id","direction":"ASC"}]');
		$filter                     = Arr::get($_GET,'filter','');	
		$cdp_filter                 = Arr::get($_GET,'cdp_filter','');
	   
	   if($filter != ''){
		     $filter_array = json_decode($filter);	
		     $search_type = $filter_array[0]->{'property'}; 
			 $search_value = $filter_array[0]->{'value'}; 
		}	
		if($cdp_filter != '' && $cdp_filter != '[]')
		{
			$filter_condition_array = $this->create_filter_condition($cdp_filter);
		}
		
		$account_id         = $this->session->get('account_id');
		$user_id  			= $this->session->get('user_id');	;	
		$count              = Model::factory('Guest_CdpGuests')->get_count_for_guest_details_grid($search_type, $search_value,$filter_condition_array,$account_id,$user_id);
        guest_details = Model::factory('Guest_CdpGuests')->get_records_for_guest_details_grid($sort, $search_type, $search_value, $limit, $start,$filter_condition_array );
		
		if($count > 0)
		{ 
			$grid_data = array("sucess"=>true,"results"=>$count); 
		    foreach(guest_details as $row)
		    {			 
			  guest_details_array['id']                 = $row->{'id'};	
			  guest_details_array['notes']              = $this->common_function->remove_spaces($row->{'notes'});
			  guest_details_array['name']               = stripslashes($row->{'name'});
			  guest_details_array['guest_type']         = stripslashes($row->{'guest_type'});
			  guest_details_array['event_type']         = stripslashes($row->{'event_type'});
              guest_details_array['department_name']    = stripslashes($row->{'department_name'});
			  guest_details_array['access_level']       = stripslashes($row->{'access_level'});			 
			  guest_details_array['cell_phone']         = stripslashes($row->{'cell_phone'});			 
			  guest_details_array['affiliation']        = stripslashes($row->{'affiliation'});
			  guest_details_array['email']              = stripslashes($row->{'email'});	
			  guest_details_array['guest_details'] 		= stripslashes($row->{'guest_details'});	
			  guest_details_array['status_details']     = stripslashes($row->{'status_details'});		  
			  guest_details_array['submitted_by']       = stripslashes($row->{'submitted_by'});
			  guest_details_array['photo']              = stripslashes($row->{'photo'});	
			  $field_value_array[]                      = guest_details_array;
			}/*foreach end here.*/
		}/*if end here.*/
		$grid_data["rows"] = $field_value_array;
	    echo json_encode($grid_data);		   
	}
	
	/**
	 * @delete_guests action of guest index controller
	 * @This action is used to delete single or multiple guest	
	 */
	public function action_delete_guests()
	{
		$guests =  json_decode($_POST['guest_id']);
		$result =  Model::factory("Guest_CdpGuests")->delete_guest_details((array)$guests);
		if($result){             
            echo __('DELETE_GUEST',array(),'',$this->notification_language_path);         
		} else {
			echo __('FAILED_TO_DELETE',array(),'',$this->notification_language_path);
		}		
	}	

   /**
	 * @print_view action of guest index controller
	 * @This action is used to print guest	list view.
	 */
	public function action_print_view()
	{
		$hidden_col_array = array();
	    $sort_by       = Arr::get($_GET,'sort_by');
		$direction     = Arr::get($_GET,'direction');
		$search_value  = Arr::get($_GET,'search_value');
		$search_type   = Arr::get($_GET,'search_type');
		$cdp_filter    = Arr::get($_GET,'cdp_filter','');
		/*for get hidden column*/
		$hidden_col   = Arr::get($_GET,'hidden_col_string','');
		if(isset($hidden_col) && $hidden_col != '')
		{
			$hidden_col_array = explode(',', $hidden_col);
			
		}/*end here.*/
		
	    $view = View::factory('Guest/PrintGuestListView');
		$view->sort_by            = $sort_by;
		$view->search_value       = $search_value;
		$view->direction          = $direction;
		$view->search_type        = $search_type;
		$view->cdp_filter         = $cdp_filter;
		$view->hidden_col_array   = $hidden_col_array;
		$view->language_file_path = $this->language_file_path;
		$this->response->body($view);
	}
	
	/**
	 * @export_to_excel action of guest index controller
	 * @This action is used to generate excel report.
	 */
	public function action_export_to_excel()
	{
		$header_column_array = array();
    	$field_value_array = array();
    	$excel_meta_data_array = array();
		$event_name_id_array = array();
		$hidden_col_array = array();
		$total_hidden_column = 0;
		$account_id = '';
		$user_id = '';		
		$account_name = $this->session->get('account_name');
		
		$excel_meta_data_array['title']		   = __('GUEST_GUEST_LIST',array(),'',$this->language_file_path);
		$excel_meta_data_array['subject']	   = __('gUEST_GUEST_LIST',array(),'',$this->language_file_path);
		$excel_meta_data_array['description']  = 'File Contain all Guest details';
		$excel_meta_data_array['author']	   = $this->log_in_user_first_last_name;
		$sheetName    = __('GUEST_LIST',array(),'',$this->language_file_path);
		$excel_file_name = str_replace(" ","_",$account_name).'_'.__('GUEST',array(),'',$this->language_file_path);/*Excel_data_for_Guest_Details';
		
		/*for sorting */
	    $sort  = Arr::get($_POST,'sort_details');	    
		if($sort == '[]')
		{
		  $sort = '[{"property":"id","direction":"ASC"}]';
		}/*end here*/
		
		$search_value = Arr::get($_POST,'search_value');
		$search_type  = Arr::get($_POST,'search_type');
		
		/*for grid column filter*/
		$filter_condition_array = array();
		$cdp_filter   = Arr::get($_POST,'cdp_filter','');		
		if($cdp_filter != '' && $cdp_filter != '[]')
		{
			$filter_condition_array = $this->create_filter_condition($cdp_filter);
		}/*end here*/
		
		/*for get hidden column*/
		$hidden_col    = Arr::get($_POST,'hidden_col_string','');
		if(isset($hidden_col) && $hidden_col != '')
		{
			$hidden_col_array = explode(',', $hidden_col);
			$total_hidden_column = count($hidden_col_array);
		}/*end here.*/		
		
		$header_column_array = $this->create_header_for_excel_report($hidden_col_array); 
		
		$account_id         = $this->session->get('account_id');
		$user_id  			= $this->session->get('user_id');	
	    $count                = Model::factory('Guest_CdpGuests')->get_count_for_guest_details_grid($search_type, $search_value, $filter_condition_array, $account_id);
        guest_details   = Model::factory('Guest_CdpGuests')->get_records_for_guest_details_grid($sort, $search_type, $search_value,NULL,0, $filter_condition_array);
        
        $guest_list = $this->data_for_excel_report($count, guest_details, $header_column_array, $total_export_column);        
        
        $XLSX = new Spreadsheet($excel_meta_data_array);
        $XLSX->set_excel_file_data($header_column_array, $guest_list, $sheetName);
        $XLSX->get_excel_file(array('name'=>$excel_file_name));
	}	
	
	/**
	 * @create_header_for_export_to_excel function of Guest index controller
	 * @This action used to set create header for excel report.
	 * @return array of header field.
	 */
	function create_header_for_excel_report($hidden_col_array = array())
	{
		$header_field_array = array();
	    $column_key_value = 0;  		
		if(!in_array('col_guest_name', $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('NAME',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
			$column_key_value = $column_key_value + 1;				
		}
		if(!in_array('col_guest_department',  $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('DEPARTMENT',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
			$column_key_value = $column_key_value + 1;
		}
		if(!in_array('col_guest_guest_type',  $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('GUEST_TYPE',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
			$column_key_value = $column_key_value + 1;
		}				
		if(!in_array('col_guest_cell_phone',  $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('CELL_PHONE',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'NUMBER';
			$column_key_value = $column_key_value + 1;
		}		
		if(!in_array('col_guest_email',  $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('E_MAIL',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
			$column_key_value = $column_key_value + 1;
		}		
		if(!in_array('col_guest_affiliation',  $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('AFFILIATION',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
			$column_key_value = $column_key_value + 1;
		} 			
		if(!in_array('col_guest_guest_details',  $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('GRID_GUEST',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
			$column_key_value = $column_key_value + 1;
		}			
		if(!in_array('col_guest_status_details', $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('STATUS',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
			$column_key_value = $column_key_value + 1;
		}			
		if(!in_array('col_guest_notes', $hidden_col_array )){
			$header_field_array[$column_key_value]['value'] = __('NOTES',array(),'',$this->language_file_path);
			$header_field_array[$column_key_value]['width'] = '30';
			$header_field_array[$column_key_value]['type'] = 'TEXT';
		}		
		return  $header_field_array;
	}	
	
	/**
	 * @data_for_excel_report function of Guest index controller
	 * @This action used to create array for excel report.
	 * @return array of data.
	 */
	function data_for_excel_report($count = 0, guest_details= array(), $header_field_array = array(), $total_export_column =0)
	{
		$field_value_array = array();
		
		if($count > 0)
		{
			foreach(guest_details as $row)
			{
				guest_details_array= array();
				for($i=0; $i < $total_export_column; $i++)
				{
					switch($header_field_array[$i]['value'])
					{
						case __('NAME',array(),'',$this->language_file_path):
							guest_details_array[$i] 	   =  $row->{'name'};
						break;
                        case __('DEPARTMENT',array(),'',$this->language_file_path):
							guest_details_array[$i] 	    =  $row->{'department_name'};
						break;
						case __('GUEST_TYPE',array(),'',$this->language_file_path):
							guest_details_array[$i] 	    =  $row->{'guest_type'};
						break;						
						case __('CELL_PHONE',array(),'',$this->language_file_path):
						if($row->{'cell_phone'} != 0)	
						   guest_details_array[$i] 	=  $row->{'cell_phone'};
						else 
						   guest_details_array[$i] 	=  "";
						break;
						case __('E_MAIL',array(),'',$this->language_file_path):
							guest_details_array[$i] 	   =   $row->{'email'};
						break;
						case __('AFFILIATION',array(),'',$this->language_file_path):
							guest_details_array[$i] 	   =   $row->{'affiliation'};
						break;
						case __('GRID_GUEST',array(),'',$this->language_file_path):
							guest_details_array[$i] 	    =  $row->{'guest_details'};
						break;			
						case __('STATUS',array(),'',$this->language_file_path):
							guest_details_array[$i] 	    =  $row->{'status_details'};
						break;
						case __('NOTES',array(),'',$this->language_file_path):
							guest_details_array[$i] 	    =  $this->common_function->remove_spaces($row->{'notes'});
						break;
				   }/*switch case end here*/
		       }/*for loop end here.*/
		       $field_value_array[] = guest_details_array;
		   }/*foreach end here*/
	    }/*if end here.*/
	    return $field_value_array;
	}
	
	/**
	 * @save_guest action of guest index controller
	 * @this action used to save guest
	 * @Return message for success or failure.
	*/
	public function action_save_guest()
	{           
	    /*---uploading image*/
		$filename = NULL;
		$file_original_name = NULL;
        	if ($this->request->method() == Request::POST)
        	{
            	if (isset($_FILES['user_img']) && $_FILES['user_img']['name']!=NULL)
            	{
					$result = $this->common_function->check_img_size($_FILES['user_img']);
					if(!$result)
					{
						echo"{'success':false,'message':'".__('IMG_SIZE_INCORRECT',array(),'',$this->notification_language_path)."'}";
						return;
					}	
                	$filename = $this->common_function->_save_image($_FILES['user_img'],$this->config['USER_IMG_PATH']);
                	$file_original_name = $_FILES['user_img']['name'];
            	}
        	}
        	if (!$filename && $_FILES['user_img']['name']!=NULL)
        	{
				echo"{'success':false,'message':'".__('IMG_TYPE_INCORRECT',array(),'',$this->notification_language_path)."'}";
				return;
        	}
        /*----end image uploading	*/		
        $department_list_details = Arr::get($_POST,'department_list_details','');		
		$photo = isset($filename) ? $filename : '';			
		$guest_id  = Model::factory('Guest_CdpGuests')->save_guest_details_from_web($_POST, $photo, NULL, NULL, NULL, NULL,  NULL, '1', $this->user_id, $this->log_in_user_first_last_name, $file_original_name);
			
		if($guest_id)
		{   
		    if(!empty($department_list_details) && $department_list_details != '') {
				$department_list_result = Model::factory('Guest_CdpGuestDepartments')->save_guest_department($guest_id, $department_list_details);
		  }	
		}
		echo "{'success':true,'message':'".__('ADD_GUEST',array(),'',$this->notification_language_path)."','New_token':'".$token."'}";          
	}
	
	/**
	 * @update_note action of guest index controller
	 * @Used to update notes 
	 * @Return message for success or failure.
	*/
	public function action_update_note()
	{
	    $notes  = Arr::get($_POST,'notes');
		$id  = Arr::get($_POST,'id');
		$old_notes = Arr::get($_POST,'old_notes');
		$result = Model::factory('Guest_CdpGuests')->update_note_details($id, $notes);		
		if($result){			
			if($old_notes != "")
			{
			   echo"{'success':true,'message':'".__('UPDATE_NOTE',array(),'',$this->notification_language_path)."'}";
			}
			else 
			{
		       echo"{'success':true,'message':'".__('SAVE_NOTE',array(),'',$this->notification_language_path)."'}";
			}
		} else {
		    echo"{'success':false,'message':'".__('FAILED_TO_UPDATE',array(),'',$this->notification_language_path)."'}";
		}
	}		
	 
	/**
	 * @guest_type_list action of guest index controller
	 * @Return guest type list for dropdown.
	 */
	public function action_guest_type_list()
	{
	    $field_value_array = array();
	    $guest_type_list = Model::factory('Guest_GuestType')->get_all_guest_categories('category_name','ASC',$this->session->get('account_id'));
		$count = $guest_type_list['count'];
		$guest_type_array = $guest_type_list['data'];
		if($count > 0)
		{
			$guest_type_details['id'] = -1;
			$guest_type_details['category_name'] = __('SELECT_GUEST_TYPE',array(),'',$this->notification_language_path);
			$field_value_array[0] = $guest_type_details;
			foreach($guest_type_array as $guest_type)
			{
				$guest_type_details['id'] = $guest_type['id'];
				$guest_type_details['category_name'] = $guest_type['category_name'];
				$field_value_array[] = $guest_type_details;
			}	
		}
		$json_sting["rows"] = $field_value_array;
		echo json_encode($json_sting);
	}
	
    /**
	 * @status_list action of guest index controller
	 * @Return status list.
     */
    public function action_status_list()
    {
        $field_value_array         = array();
		$status_list_array['id']   = 0;
		$status_list_array['name'] = "Disabled";
		$field_value_array[]       = $status_list_array;
		$status_list_array['id']   = 1;
		$status_list_array['name'] = "Enabled";
		$field_value_array[]       = $status_list_array;
		$json_sting["rows"]        = $field_value_array;
	   	echo json_encode($json_sting);
    } 
	
	/**
	 * @update_guest action of guest index controller
	 * @Used to update guest.
	 * @Return message for success or failure.
	*/
	public function action_update_guest()
	{		
		$id        = Arr::get($_POST,'id');
		$data      = Model::factory('Guest_CdpGuests')->find_user($id);
		$account_id = $this->session->get('account_id');		
		$filename  = NULL;
		$old_filename = NULL;
		$file_original_name = NULL;
		$old_file_original_name = NULL;	
		$image_url = $this->config['USER__THUMB_IMG_PATH'].$data->{'photo'};
		
		/*for photo uplode*/
		if ($this->request->method() == Request::POST) {
			  if(isset($_FILES['photo']) && $_FILES['photo']['name']!=NULL) {
				    if(@GETIMAGESIZE($image_url)) {
					     /*$is_img_deleted = $this->common_function->unlink_image($data->{'photo'},$this->config['USER_IMG_PATH']);*/
				    }
				    $result = $this->common_function->check_img_size($_FILES['photo']);
				    if(!$result) {
					    echo"{'success':false,'message':'".__('IMG_SIZE_INCORRECT',array(),'',$this->notification_language_path)."'}";
					    return;
				    }	
				    $filename = $this->common_function->_save_image($_FILES['photo'], $this->config['USER_IMG_PATH']);
				    $file_original_name = $_FILES['photo']['name'];
					$imageDetailsHistory['new_image_name'] = $filename;
					$imageDetailsHistory['new_image_original_name'] = $file_original_name;
			}
		}
		if (!$filename && $_FILES['photo']['name']!= NULL) {
			echo"{'success':false,'message':'".__('IMG_TYPE_INCORRECT',array(),'',$this->notification_language_path)."'}";
			return;
		}
		
		if($_FILES['photo']['name']== NULL || $_FILES['photo']['name']== '')
		{		     
			 if(@GETIMAGESIZE($image_url))
			 {
			 	$old_filename = $data->{'photo'};
			 	$old_file_original_name = $data->{'photo_original_name'};
			 }
	 		 else
	 		 {
	 			$old_filename = '';
	 			$old_file_original_name = '';
	 		 }
		}		
		/*end here	*/	
		$department_details             = Arr::get($_POST,'department_details_value','');		
		$photo                          = isset($filename) ? $filename : $old_filename;	
		$photo_origional_name           = isset($file_original_name) ? $file_original_name : $old_file_original_name;		
		$old_department_id_array        = array();
        $new_department_id_array        = array();
		$old_department_array_diff      = array();
		$new_department_array_diff      = array();
		if(isset($id))
		{
			/************************************************************* Department**********************************************************/
		         $old_department_details = Model::factory('Guest_CdpGuestDepartments')->select_record_for_guest_id($id);
			     if(count($old_department_details) > 0) {
		               foreach($old_department_details as $department) {
		                    $old_department_id_array[] = $department->{'department_id'};
		               }
		        }
			   
			    $department_details_array = json_decode($department_details); 		
		        if(count($department_details_array) > 0){
			        foreach($department_details_array as $department) { 
				         $department_array  =  get_object_vars($department);
				         $new_department_id_array[] = $department_array['department_id'];
			        }
		        }
				 
               $old_department_array_diff = array_diff($old_department_id_array, $new_department_id_array);		
		       $new_department_array_diff = array_diff($new_department_id_array, $old_department_id_array);
                       
               if((!empty($old_department_array_diff)) &&(count($old_department_array_diff)>0)) {
					 $delete_result = Model::factory('Guest_CdpGuestDepartments')->delete_data($id,$old_department_array_diff);
			   }
			   if((!empty($new_department_array_diff)) && (count($new_department_array_diff) > 0)) {
					$insert_result = Model::factory('Guest_CdpGuestDepartments')->save_guest_department($id,$department_details,$new_department_array_diff);
			   }
			/********************************************************** End Here*******************************************************************************/
					
			$guest_id  = Model::factory('Guest_CdpGuests')->update_record_from_web($_POST, $photo, NULL, NULL, NULL, $photo_origional_name);
			/*If records updated successfully then insert history details*/			
			echo"{'success':true,'message':'".__('UPDATE_GUEST',array(),'',$this->notification_language_path)."','data':'".$scan_list_grid."'}";
        }
		else
		{
		   echo"{'success':false,'message':'".__('FAIL_TO_SAVE',array(),'',$this->notification_language_path)."'}";
		}
	} /*update guest end here.  */	
	
	/**
	 * @guest_type_list_for_filter action of guest index controller
	 * @Return guest type list for filter.
	 */
	public function action_guest_type_list_for_filter()
	{
		$field_value_array = array();
		$guest_type_list = Model::factory('Common_GuestType')->get_all_guest_categories('id','ASC',$this->session->get('account_id'));
		$count = $guest_type_list['count'];
		$guest_type_array = $guest_type_list['data'];
		if($count > 0)
		{
			$guest_type_details['id'] = '-1';
			$guest_type_details['category_name'] = 'All';
			$field_value_array[0] = $guest_type_details;
			foreach($guest_type_array as $guest_type)
			{
				$guest_type_details['id'] = $guest_type['id'];
				$guest_type_details['category_name'] = $guest_type['category_name'];
				$field_value_array[] = $guest_type_details;
			}	
		}
		$json_sting["rows"] = $field_value_array;
		echo json_encode($json_sting);
	}	
	
	/**
	 * @guest_type_list_for_filter action of guest index controller
	 * @Return guest type list for filter.
	 */
	public function action_guest_status_list_for_filter()
	{
		$field_value_array = array();
		
		guest_details['id'] = '-1';
		guest_details['guest_status'] = 'All';
		$field_value_array[0] = guest_details;
				
		guest_details['id'] = '1';
		guest_details['guest_status'] = 'Issued';
		$field_value_array[] = guest_details;
		
		guest_details['id'] = '0';
		guest_details['guest_status'] = 'Pending';
		$field_value_array[] = guest_details;
		
		$json_sting["rows"] = $field_value_array;
		echo json_encode($json_sting);
	}        
		
	/**
	 * @create_filter_condition action of guest index controller
	 * @Used to create filter condition.
	 * @Return filter array.
	 */
	function create_filter_condition($cdp_filter)
	{
		$filter_data = json_decode($cdp_filter);
		$arr_length = count($filter_data);
		for($i=0; $i < $arr_length; $i++)
		{
			switch($filter_data[$i]->{'field'})
			{
				case "department_name":
				$cdp_filter_value_array["department"] = $filter_data[$i]->{'value'};
				break;                            
                
				case "guest_type":
				$cdp_filter_value_array["guest_type"] = $filter_data[$i]->{'value'};
				break;
				
				case "email":
				$cdp_filter_value_array["email"] = $filter_data[$i]->{'value'};
				break;
				
				case "affiliation":
				$cdp_filter_value_array["affiliation"] = $filter_data[$i]->{'value'};
				break;
				
				case "guest_details":
				$cdp_filter_value_array["guest_details"] = $filter_data[$i]->{'value'};
				break;
								
				case "status_details":
				$cdp_filter_value_array["status_details"] = $filter_data[$i]->{'value'};
				break;				
		    }/*switch end here.*/
		}/*for end here.*/
		return $cdp_filter_value_array;
	}/*function end here.*/
	   
	/**
     * @guest_detail_pdf_view action of guest index controller
     * @used to generate pdf view.     
     */
    public function action_guest_detail_pdf_view() {
        $guest_id = Arr::get($_GET, 'guest_id');
        //Guests Details Page
        guest_details = Model::factory('Guest_CdpGuests')->select_record_for_pdf($guest_id);

        /*****Report Genrated Value******* */
        $report_generated_by = $this->session->get('firstname') . ' ' . $this->session->get('lastname');
        $report_generated_by = $this->db->mysqli_escape($report_generated_by);
        /* *****End here $report_generated_by*** */
       

        $pdf = view_mpdf::factory('Guest/GuestDetailPdfView');

        $config_array = array('format' => 'Letter', 'mgl' => 10, 'mgr' => 10, 'mgt' => 12, 'mgb' => 22, 'mgh' => 2, 'mgf' => 0, 'orientation' => 'L');

        $pdf->new_mpdf($config_array);

        $mpdf = $pdf->get_mpdf();

        $mpdf->showImageErrors = false; /* error reporting for problems with Images*/
        $mpdf->allow_charset_conversion = true; /*TRUE:Parse the character set of any input text from the HTML FALSE:Expect all text input as UTF-8 encoding*/
        $mpdf->allow_output_buffering = true; /*Ignores any content in the object buffer when outputting the PDF file.*/
        /*$mpdf->mirrorMargins = 1;	/* Use different Odd/Even headers and footers and mirror margins*/
        $mpdf->pagenumPrefix = 'Page '; /*page number prefix*/
        $mpdf->nbpgPrefix = ' of '; /*total page prefix generated by {nbpg}*/

        guest_details_main_footer = '<htmlpagefooter name="guest_details_footer">

		<div style="font-family:arial,sans-serif; font-size: 9pt;padding:3px;border-top:1px solid #000">
		<table width="100%" border="0" cellpadding="2" cellspacing="2" style="font-family:arial,sans-serif; color:#666; font-size: 9pt" >
		<tr>
		<td width="27%">' . __('CREATED_BY', array(), '', $this->language_file_path) . ': ' . $report_generated_by . '</td>
		<td width="26.5%">' . __('ACCESS_LEVEL', array(), '', $this->language_file_path) . ': ' . $access_level . '</td>
		<td width="28%">' . __('DATE_TIME', array(), '', $this->language_file_path) . ': ' . $current_time . '</td>
		<td width="20%">{PAGENO}{nbpg}</td>
		</tr>
                <tr>
                <td colspan="3"></td><td><img height="32" width="160" src="/public/media/common/images/event_summary_iss_logo.png"></td>
		</tr>
		</tbody>
		</table>
		</div>
		</htmlpagefooter>

		<sethtmlpagefooter name="guest_details_footer" page="ALL" value="on" />';


        //document footer ends
        $mpdf->WriteHTML(guest_details_main_footer);

        /* Main Html For Print PDF */
        $title_guest_details = ' <div style="display:row;width:100%;">
                                         <div style="width:16%;float:left;">
                                             <img src="public/media/common/images/login/loginLogo.jpg"  height="50px" width ="100px">
                                         </div>
                                        <div style="width:40%;font-size:20px;font-family: sans-serif;text-align:left; padding-top: 10px; vertical-align: middle;">' . __('GUEST_DETAILS', array(), '', $this->language_file_path) . '</div>
                                   </div>';
        $mpdf->WriteHTML($title_guest_details);
        /* End here */
        /* Guest Information and Photo Block */
        $created_on = explode(' ', $this->common_function->default_format_date(guest_details['created_on']));
        $created_on_value = $created_on[0];
        $auto_disable_on_value = "";
        if (guest_details['date_time'] != "0000-00-00 00:00:00") {
            $auto_disable_on = $this->common_function->default_format_date(guest_details['date_time']);
            $auto_disable_on_value = $auto_disable_on;
        }
        //$user_image Block
        $user_image = '';
        if ((guest_details['photo'] != NULL) && (guest_details['photo'] != '')) {
            $image_url = $this->config['USER__THUMB_IMG_PATH'] . guest_details['photo'];

            if (@GETIMAGESIZE($image_url)) {
                $user_image = BASEURL . 'application/classes/makethumb.php?pic=' . $image_url;
            } else {
                $user_image = '';
            }
        }
        //$user_image End Here
        $title_guest_info_photo_block = '
         <div style="width:100%;margin-top:20px;font-family: sans-serif;">

            <div style="width:50%;float:left;">
                    <div style="font-size:14px;">' . __('GUEST_INOF_PANEL', array(), '', $this->language_file_path) . '
                            <hr width="100%" style="height:1px;color:black;margin:0px;">
                    </div>

                    <div style="width:100%;font-size:11px;">  <!-- content div start -->
                            <!-- First Name row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                   <div style="width:57%;float:left;">' . __('FIRST_NAME', array(), '', $this->language_file_path) . ':' . '</div>';
        if ($guest_details['first_name'] != '') {

            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['first_name'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>
                            <!-- First Name row end -->

                            <!-- LAST_NAME row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                   <div style="width:57%;float:left;">' . __('LAST_NAME', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['last_name'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['last_name'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= ' </div>
                            <!-- LAST_NAME row end -->
                            <!-- GUEST_TYPE row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                   <div style="width:57%;float:left;">' . __('GUEST_TYPE', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['guest_type'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['guest_type'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>
                            <!-- GUEST_TYPE row end -->
                            <!-- DEPARTMENT row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                        <div style="width:57%;float:left;">' . __('DEPARTMENT', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['department_names'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['department_names'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>
                            <!-- DEPARTMENT row end -->

                            <!-- GUEST_OF row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                   <div style="width:57%;float:left;">' . __('GUEST_OF', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['guest_of']) {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['guest_of'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>
                            <!-- GUEST_OF row end -->
                            <!-- E_MAIL row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                   <div style="width:57%;float:left;">' . __('E_MAIL', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['email'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['email'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>
                            <!-- E_MAIL row end -->
                            <!-- CELL_PHONE row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                   <div style="width:57%;float:left;">' . __('CELL_PHONE', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['cell_phone'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['cell_phone'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.='</div>
                            <!-- CELL_PHONE row end -->
                            <!-- AFFILIATION row start -->
                                <div style="display:row;width:100%;margin-top:5px;" >
                                   <div style="width:57%;float:left;">' . __('AFFILIATION', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['affiliation'] != '') {
            $title_guest_info_photo_block.='<div style="width:42%;float:left;">' . guest_details['affiliation'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.='</div>
                            <!-- AFFILIATION row end -->

                            <!-- STATUS row start -->
                            <div style="display:row;width:100%;margin-top:5px;" >
                               <div style="width:57%;float:left;">' . __('STATUS', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['Status_detail'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['Status_detail'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>  <!-- STATUS row end -->

                            <!-- GRID_GUEST row start -->
                            <div style="display:row;width:100%;margin-top:5px;" >
                               <div style="width:57%;float:left;">' . __('GRID_GUEST', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['guest_status'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['guest_status'] . '</div>';
        } else {
            $title_guest_info_photo_block.='<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>  <!-- GRID_GUEST row end -->

                            <!-- SUBMITTED_BY row start -->
                            <div style="display:row;width:100%;margin-top:5px;" >
                               <div style="width:57%;float:left;">' . __('SUBMITTED_BY', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['submitted_by'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['submitted_by'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>  <!-- SUBMITTED_BY row end -->

                             <!-- APPROVED_BY row start -->
                            <div style="display:row;width:100%;margin-top:5px;" >
                               <div style="width:57%;float:left;">' . __('APPROVED_BY', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['created_by'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['created_by'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>  <!-- APPROVED_BY row end -->
                            <!-- DATE_APPROVED row start -->
                            <div style="display:row;width:100%;margin-top:5px;" >
                               <div style="width:57%;float:left;">' . __('DATE_APPROVED', array(), '', $this->language_file_path) . ':' . '</div>';
        if ($created_on_value != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . $created_on_value . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>
                            <!-- DATE_APPROVED row end -->
                             <!-- AUTO_DISABLED_GUESTS_STATUS row start -->
                            <div style="display:row;width:100%;margin-top:5px;" >
                               <div style="width:57%;float:left;">' . __('AUTO_DISABLED_GUESTS_STATUS', array(), '', $this->language_file_path) . ':' . '</div>';
        if (guest_details['auto_disable'] != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . guest_details['auto_disable'] . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>
                            <!-- AUTO_DISABLED_GUESTS_STATUS row end -->
                             <!-- AUTO_DISABLED_GUESTS_DATE row start -->
                            <div style="display:row;width:100%;margin-top:5px;" >
                               <div style="width:57%;float:left;">' . __('AUTO_DISABLED_GUESTS_DATE', array(), '', $this->language_file_path) . ':' . '</div>';
        if ($auto_disable_on_value != '') {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">' . $auto_disable_on_value . '</div>';
        } else {
            $title_guest_info_photo_block.= '<div style="width:42%;float:left;">---</div>';
        }
        $title_guest_info_photo_block.= '</div>  <!-- AUTO_DISABLED_GUESTS_DATE row end -->
                    </div>  <!-- content div end -->
             </div> <!-- Left End -->

<div style="width:50%;float:left;" >  <!-- Right Start -->

        <div style="width:100%;margin-left:20px;"> <!-- 1 start -->

		  		<div style="width:100%;font-size:14px;">' . __('PHOTO_PANEL', array(), '', $this->language_file_path) . '
		                             		    <hr width="100%" style="height:1px;color:black;margin:0px;">
                                </div>
                    <div style="width:100%;font-size:11px;">  <!-- content div start -->
                           <!-- PHOTO row start -->
                            <div style="display:row;width:100%;margin-top: 10px;" >';
        if ($user_image != '') {
            $title_guest_info_photo_block.='<div align="center" style="width:100%;"><img src=' . $user_image . '&w=275&sq=N&b=0></div>';
        }
        $title_guest_info_photo_block.= '</div>  <!-- PHOTO row end -->
                    ';


        $title_guest_info_photo_block.='</div>  <!-- content div end -->

      	</div>     <!-- 1 end -->

   	</div>   <!-- Right End -->
    </div>  <!-- Main End -->';
        $mpdf->WriteHTML($title_guest_info_photo_block);

        /* End here For  Guest Information And Photo section */

        /* Sart Of Notes Block */
        $title_notes_block = '
     <div style="width:100%;margin-top:10px;font-family: sans-serif;">
            <div style="font-size:14px;">' . __('NOTES', array(), '', $this->language_file_path) . '
          	<hr width="100%" style="height:1px;color:black;margin:0px;">
            </div>
            <table style="font-family: sans-serif;font-size:11px; width:100%">
		<tr>';
        if (guest_details['notes'] != '') {
            $title_notes_block.= '<td width="100%">' . guest_details['notes'] . '</td>';
        } else {
            $title_notes_block.= '<td width="100%" >' . __('NO_NOTES_ADDED', array(), '', $this->language_file_path) . '</td>';
        }
        $title_notes_block.= '</tr>
	</table>
    </div>';
        $mpdf->WriteHTML($title_notes_block);
        /* End Here Notes Block */       

        $report_name = __('GUEST_DETAILS', array(), '', $this->language_file_path);
        if (is_object($pdf)) {
            $this->response->headers('Content-Type', 'application/pdf');
            $pdf->download($report_name . '.pdf');
        } else {
            if (is_array($pdf)) {
                if (isset($pdf['message'])) {
                    echo $pdf['message'];
                }
            }
        }
    }
    //Function End Here
 }
?>
