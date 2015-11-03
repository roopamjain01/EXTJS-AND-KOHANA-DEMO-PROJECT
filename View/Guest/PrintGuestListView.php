<?php defined('SYSPATH') or die('No direct script access.');
/**
  * @package 	guestPlus
  * @category	View	
  * @Date 		09-July-2015
  * @author    	Roopam
*/
$encrypt = new encryption();
$url_load_data_for_guest_grid = BASEURL."index.php".$encrypt->encode('guestPlus/guestentialIndex/load_data_for_guestential_list'); 
?>

<html>
<head>
<title><?php echo __('GUEST',array(),'',$language_file_path);?></title>

<?php   
 echo HTML::style('public/ext/resources/css/default/app.css');
 echo HTML::script('public/ext/ext-all.js');
?>
</head>
<script language="javascript">
Ext.onReady(function(){

   function complete_text_show(val)
   {
	  return '<div style="white-space:normal !important;">'+ val +'</div>';
   } 
 /*store start from here.*/
	var store = Ext.create('Ext.data.Store', {
					     autoLoad: false,
						 id : 'guestential_store',
						 remoteFilter :true,
					     fields:[
						         {name: 'notes', type: 'string'},
								 {name: 'name', type: 'string'},
						         {name: 'guest_type', type: 'string'},
								 {name: 'event_type', type: 'string'},
								 {name: 'access_level', type: 'string'},
								 {name: 'cell_phone', type: 'string'},
                                 {name: 'department_name', type: 'string'},
								 {name: 'email', type: 'string'},
								 {name: 'affiliation', type: 'string'},
								 {name: 'guestential_details', type: 'string'},
								 {name: 'status_details', type: 'string'}
								 ],	
						 filters:  [{
										 property: '<?php echo $search_type; ?>',
										 value: '<?php echo $search_value; ?>'
							      }],
						 proxy: {
							       type: 'ajax',
							       url:  '<?php echo $url_load_data_for_guest_grid;?>',
							       reader: {
											  type: 'json',
											  root: 'rows',
											  totalProperty: 'results'
							               }
					}
		});
		
	/*store load*/
	store.load({
				  params:{
				      'sort' : Ext.encode([{property: '<?php echo $sort_by;?>',direction: '<?php echo $direction;?>'}]),
					   start:0,
					   limit: 10000,
					  'cdp_filter': '<?php echo $cdp_filter;?>',
					  'status_filter': '<?php echo $status_filter;?>',
				  }
	});

	store.getProxy().extraParams = {
		'cdp_filter': '<?php echo $cdp_filter;?>',
		'status_filter': '<?php echo $status_filter;?>',
		'sort' : Ext.encode([{property: '<?php echo $sort_by;?>',direction: '<?php echo $direction;?>'}]),
	};
	
	/*Grid start from here.*/
	var grid = Ext.create('Ext.grid.Panel', {
					  store: store,
					  padding: '10 40 40 40',
					  renderTo: Ext.getBody(),
					  enableColumnHide:false,
					  enableColumnResize: false, 
					  flex:1,
					  columns:[
						<?php if(!in_array('col_guest_name',$hidden_col_array))	{?>	  
					    {
							text: '<?php echo __('NAME',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'name',
							align:'left',
							style: 'text-align:center',
							renderer :complete_text_show,
							flex:1,
							width:'9%'
						},
						<?php } if(!in_array('col_guest_department',$hidden_col_array))	{?>	                                                  {
							text: '<?php echo __('DEPARTMENT',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'department_name',
							align:'left',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'9%'
						},	
						<?php } if(!in_array('col_guest_guest_type',$hidden_col_array))	{?>	  
						{
							text: '<?php echo __('GUEST_TYPE',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'guest_type',
							align:'left',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'9%'
						},
						<?php } if(!in_array('col_guest_event_type',$hidden_col_array))	{?>	  
						{
							text: '<?php echo __('EVENT_TYPE',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'event_type',
							align:'left',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'9%'
						},
						<?php } if(!in_array('col_guest_access_level',$hidden_col_array))	{?>	  
						{
							text: '<?php echo __('ACCESS_LEVEL',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'access_level',
							align:'left',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'9%'
						},
                        <?php } if(!in_array('col_guest_cell_phone',$hidden_col_array))	{?>	  
						{
							text: '<?php echo __('CELL_PHONE',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'cell_phone',
							align:'center',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'9%'
						},
						<?php } if(!in_array('col_guest_email',$hidden_col_array))	{?>	  
						{
							text: '<?php echo __('EMAIL',array(),'',$language_file_path);?>',
							sortable: false,
                            dataIndex: 'email',
							align:'left',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'16%'
						},
						<?php } if(!in_array('col_guest_affiliation',$hidden_col_array))	{?>	  
						{
							text: '<?php echo __('AFFILIATION',array(),'',$language_file_path);?>',
							sortable: false,
                            dataIndex: 'affiliation',
							align:'left',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'7%'
						},
						<?php } if(!in_array('col_guest_guestential_details',$hidden_col_array)){?>	  
						{
							text: '<?php echo __('GRID_guestENTIAL',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'guestential_details',
							align:'center',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'8%'
						},
						<?php } if(!in_array('col_guest_submitted_by',$hidden_col_array)){?>	  
						{
							text: '<?php echo __('SUBMITTED_BY',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'submitted_by',
							align:'center',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'7%'
						},
						<?php } if(!in_array('col_guest_status_details',$hidden_col_array)) {?>	  
						{
						   
							text: '<?php echo __('STATUS',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'status_details',
							align:'center',
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'7%',
							
						},
						<?php } if(!in_array('col_guest_notes',$hidden_col_array)) {?>	  
						{
							text: '<?php echo __('NOTES',array(),'',$language_file_path);?>',
							sortable: false,
							dataIndex: 'notes',
							align:'left',
							flex: 1,
							style: 'text-align:center',
							renderer :complete_text_show,
							width:'10%',
						}
						<?php } ?>
						]
    });
	
	/*Print after store load.*/
	store.on( 'load', function(store) {
				      window.print();
	});  	
	
});
</script>