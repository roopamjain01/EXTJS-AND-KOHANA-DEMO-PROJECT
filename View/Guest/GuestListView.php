<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * Main Content Page for  guest information
 * This file contain basic information about guest
 * @package 	Guest 
 * @category	View
 * @Date 		    09-July-2015
 * @author 	    Roopam
 */
 $url_add_guest = BASEURL . "index.php" . $encrypt->encode('Guest/GuestIndex/load_add_guest_form');
 $url_details_guest = BASEURL . "index.php" . $encrypt->encode('Guest/GuestIndex/load_guest_details_form');
 $url_delete_guest = BASEURL . "index.php" . $encrypt->encode('Guest/GuestIndex/delete_guests');
 $url_export_to_excel = BASEURL . 'index.php/Guest/GuestIndex/export_to_excel';
 $url_for_print = BASEURL . 'index.php/Guest/GuestIndex/print_view';
?>

 <script language="javascript">
	var filters = {
					ftype: 'filters',
                    updateBuffer :2000,
                    encode:true,
                    menuFilterText : 'Filter',
                    paramPrefix : 'cdp_filter',                  
    }; /* end here. */
	
Ext.onReady(function() {

        Ext.QuickTips.init();
        Ext.apply(Ext.QuickTips.getQuickTip(), {
            dismissDelay: 0,
            showDelay: 100
        });

	Ext.create('Ext.container.Container', {
                    padding: '0 0 0 0',
					renderTo:'navigation',
					plugins : [Ext.create('public.ext.src.ux.FitToParent')],
					layout: {
						type: 'vbox',
						align: 'stretch'
					},
					items: [{
							xtype: 'toolbar',
							ui: 'action',
							height: 35,
							width:500,
							padding: '0 20 0 40',
							defaults:{
							margins: '0 20 0 0',
							},
							items: [
									'&rsaquo;&rsaquo;&nbsp;&nbsp;' + '<?php echo Database::instance()->mysqli_escape(($tiles['guest_details']) ? $tiles['guest'] : __('GUESTL', array(), '', $language_file_path)); ?>',
									'->', 
									{
									iconCls: 'x-icn-print',
									tooltip:'<?php echo __('PRINT', array(), '', $language_file_path); ?>',
									handler:function(){
									var store_count = Ext.getStore('guest_store').getCount();
											if (store_count <= 0)
									{
									Ext.Msg.alert('Alert', '<?php echo __('NO_RECORD_FOUND', array(), '', $language_file_path); ?>');
									}/* if end here. */
									else
									{
									var cdp_filter = create_filter_format_data(grid);
											var sort_params = store.getSorters();
											var sort_by = 'id';
											var direction = 'ASC';
											if (sort_params.length > 0)
									{
									sort_by = sort_params[0].property;
											direction = sort_params[0].direction;
									}
									/* for get grid config */
									var new_grid_config_data = getGridConfig();
									new_grid_config_data = new_grid_config_data.replace(/^\s\s*/, '');
									new_grid_config_data = new_grid_config_data.replace(/\s\s*$/, '');
									var hidden_col_string_val = Ext.state.Manager.getProvider().initState([{'name':'guest_grid', 'value':new_grid_config_data}]);
									body = Ext.getBody();
									var windowUrl = '<?php echo BASEURL ?>index.php/Guest/GuestIndex/print_view?sort_by=' + sort_by + '&direction=' + direction + '&search_value=' + search_textfield_value + '&search_type=' + search_option + '&cdp_filter=' + cdp_filter + '&hidden_col_string=' + hidden_col_string_val + '&status_filter=' + global_status_filter_str;
									var uniqueName = new Date();
									var windowName = 'Print' + uniqueName.getTime();
									var printWindow = window.open(windowUrl, windowName, 'left=0,top=0,width=750,height=450,scrollbars=yes');
									printWindow.focus();
									}/* else end here */
								}/* handler end here. */
							},
							{
								iconCls: 'x-icn-excel',
								tooltip:'<?php echo __('EXPORT', array(), '', $language_file_path); ?>',
								handler:function(btn){
								var store_count = Ext.getStore('guest_store').getCount();
								if (store_count <= 0)
								{
									Ext.Msg.alert('Alert', '<?php echo __('NO_RECORD_FOUND', array(), '', $language_file_path); ?>');
								}/* if end here. */
								else
								{
									var cdp_filter = create_filter_format_data(grid);
									var sort_details = "";
									sort_details = Ext.encode(Ext.getStore(guest_store').getSorters());
									var new_grid_config_data = getGridConfig();
									new_grid_config_data = new_grid_config_data.replace(/^\s\s*/, '');
									new_grid_config_data = new_grid_config_data.replace(/\s\s*$/, '');
									var hidden_col_string_val = Ext.state.Manager.getProvider().initState([{'name':'guest_grid', 'value':new_grid_config_data}]);
									body = Ext.getBody();
									/* create the downloadform at the init of your app */
									this.downloadForm = body.createChild({
									tag: 'form',
											cls: 'x-hidden',
											id: 'form',
											target: 'iframe'
									});
									Ext.Ajax.request({
												url:'<?php echo $url_export_to_excel; ?>',
												form: this.downloadForm,
												isUpload: true,
												params:{
												sort_details:sort_details,
														search_value:search_textfield_value,
														search_type:search_option,
														cdp_filter:cdp_filter,
														status_filter: global_status_filter_str,
														hidden_col_string : hidden_col_string_val,
												},
												success: function(response){
												alert(response.responseText);
											}
										});
									}/* else end here */
								}/* handler end here */
							},
							{
								icon:  '<?php echo BASEURL; ?>' + 'public/media/Guest/table-import.png',
								tooltip:'<?php echo __('IMPORT_GUESTt', array(), '', $language_file_path); ?>',							
								handler: function(btn) {
									var me = Ext.get('popupbox'); ;
									me.load({
									url: '<?php echo $url_load_import_excel_sheet; ?>',
											scripts:true,
											text: "Updating..."
									});
									me.show();
							}
						}]
					}]
    });

	var store = Ext.create('Ext.data.Store', {
			autoLoad: true,
			id : 'guest_store',
			remoteFilter :true,
			remoteSort:true,
			fields:[{name: 'id', type: 'int'},
					{name: 'notes', type: 'string'},
					{name: 'name', type: 'string'},
					{name: 'guest_type', type: 'string'},
					{name: 'event_type', type: 'string'},
					{name: 'access_level', type: 'string'},
					{name: 'department_name', type: 'string'},
					{name: 'cell_phone', type: 'string'},
					{name: 'email', type: 'string'},
					{name: 'affiliation', type: 'string'},
					{name: 'credential_details', type: 'string'},
					{name: 'status_details', type: 'string'}],
			pageSize: itemsPerPage,
			proxy: {
			type: 'ajax',
					url:  '<?php echo $url_load_data_for_guest_grid; ?>',
					extraParams:{
					status_filter : global_status_filter_str,
							limit: itemsPerPage,
					},
					reader: {
					type: 'json',
							root: 'rows',
							totalProperty: 'results'
					}
			}
	});
	/*******End Store Here********/



	 grid = Ext.create('Ext.ux.SearchPanelGuest', {
                            title:  '<?php echo __('Guest_INFO', array(), '', $language_file_path); ?>',
                            store: store,
                            padding: '10 40 0 40',
                            plugins : [Ext.create('public.ext.src.ux.FitToParent')],
                            renderTo:'guest_content',
                            enableColumnHide : true,
                            stateId: 'guest_grid',
                            features: [filters],
                            stateful: true,
                            stateEvents: ['columnresize', 'columnmove', 'show', 'hide', 'sortchange'],//operation performed by grid
                            selModel: selModel, /* to give checkbox in every row */
                            height: getViewport()[0] - heightNeedToSub,
                            viewConfig: {
									emptyText: '<div align="center"  style="padding-top:200px;padding-bottom:5px; color:red;" ><b><?php echo					__('NO_RECORD_FOUND', array(), '', $language_file_path); ?></b> </div>',
                            },
                            listeners:{
										afterrender:function(){
											loadMask.hide();
										}
                            },                           
                            columns:[{
										xtype:'actioncolumn',
										id: 'col_guest_notes',
										width	: 40,
										tdCls:'x-vertical-align',
										align:'left',
										menuDisabled: true,
										hideable : false,
										resizable : false,
										dataIndex: 'notes',
										items: [{
											  icon   	: '<?php echo BASEURL; ?>' + 'public/media/common/images/edit_notes.png',
											  getClass: function (val, meta, rec) {/* Used to give different color icon for note */
														var data = rec.get('notes').replace(/</g, '&lsaquo;');
														data = data.replace(/>/g, '&rsaquo;');
														if (data == '' || data == null) {
														this.items[0].tooltip = "";
														eturn 'x-hide-display';
														} else {
																this.items[0].tooltip = data.replace(/"/g, '&rdquo;');
																return 'x-grid-left-icon x-display-cursor';
														}
											},
											handler: function(grid, rowIndex, colIndex) {                                            
                                            var rec = store.getAt(rowIndex);
                                                    var id = rec.get('id');
                                                    var me = Ext.get('popupbox'); /* Open new popup to add guest note */
                                                    me.load({
                                                    url: '<?php echo $url_load_notes_form; ?>',
                                                            scripts:true,
                                                            params: { id: id },
                                                    });                                         
                                            },/* handler end here. */											
											},
											{
											icon:'<?php echo BASEURL; ?>' + 'public/media/common/images/add_note.png',
                                            getClass: function(value, metadata, rec){
													var data = rec.get('notes');
                                                    if (data != '' && data != null) {
														 this.items[1].tooltip = data.replace(/"/g, '&rdquo;');
														 return 'x-hide-display';
													} else {
														 this.items[1].tooltip = '<?php echo __('ADD_NOTES', array(), '', $language_file_path); ?>';
														 return 'x-grid-left-icon x-display-cursor';
                                                    }
                                            },
                                            handler: function(grid, rowIndex, colIndex) {
													var rec = store.getAt(rowIndex);
                                                    var id = rec.get('id');
                                                    var me = Ext.get('popupbox');/* Open new popup to add guest note */
                                                    me.load({
                                                    url: '<?php echo $url_load_notes_form; ?>',
                                                            scripts:true,
                                                            params: { id: id },
                                                    });                                            
                                            }, /* handler end here. */										
                                   }]/* items end here */
                            },/* notes column end here. */
							{
									text: '<?php echo __('NAME', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'name',
                                    id : 'col_guest_name',
                                    align:'left',
                                    flex: 1,
                                    style: 'text-align:center',
                                    renderer:tooltip,
                                    overCls:'header-class-mouse-over',
                                    width:'9%',
							},
							{
									text: '<?php echo __('DEPARTMENT', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'department_name',
                                    align:'left',
                                    id : 'col_guest_department',
                                    flex: 1,
                                    renderer:tooltip,
                                    width:'9%',
                                    filter: {
										type: 'list',
										labelField: 'department_name', /* override default of 'text' */
										store: department_filter,
										phpMode: true,                                          
                                    }
                            },
							{
									text: '<?php echo __('GUEST_TYPE', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'guest_type',
                                    id : 'col_guest_type',
                                    align:'left',
                                    flex: 1,
                                    style: 'text-align:center',
                                    overCls:'header-class-mouse-over',
                                    renderer:tooltip,
                                    width:'10%',
                                    filter: {
											type: 'list',
                                            labelField: 'category_name', /* override default of 'text' */
                                            store: guest_type_filter,
                                            phpMode: true                                       
                                    }
                            },
                            {
									text: '<?php echo __('EVENT_TYPE', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'event_type',
                                    id: 'col_guest_event_type',
                                    align:'left',
                                    flex: 1,
                                    style: 'text-align:center',
                                    overCls:'header-class-mouse-over',
                                    renderer: tooltip,
                                    filter: {
											type: 'list',
                                            labelField: 'event_type', /* override default of 'text' */
                                            store: event_type_filter,
                                            phpMode: true                                          }
                                    }
                            },
                            {
									text: '<?php echo __('ACCESS_LEVEL', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'access_level',
                                    id: 'col_guest_access_level',
                                    align:'left',
                                    flex: 1,
                                    style: 'text-align:center',
                                    overCls:'header-class-mouse-over',
                                    renderer:tooltip,
                                    filter: {
										    type: 'list',
                                            labelField: 'access_level', /* override default of 'text' */
                                            store: access_level_filter,
                                            phpMode: true                                           
                                    }
                            },
                            {
									text: '<?php echo __('CELL_PHONE', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'cell_phone',
                                    id :'col_guest_cell_phone',
                                    renderer:tooltip,
                                    align:'center',
                                    flex: 1,
                                    tdCls   :'x-vertical-align',
                                    style: 'text-align:center',
                                    overCls:'header-class-mouse-over',
                            },
                            {
									text: '<?php echo __('E_MAIL', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'email',
                                    id: 'col_guest_email',
                                    align:'left',
                                    renderer:tooltip,
                                    flex: 2,
                                    filter : true,
                                    tdCls   :'x-vertical-align',
                                    style: 'text-align:center',
                                    overCls:'header-class-mouse-over',                                  
                            },
							{
									text: '<?php echo __('AFFILIATION', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    dataIndex: 'affiliation',
                                    id: 'col_guest_affiliation',
                                    align:'left',
                                    renderer:tooltip,
                                    flex: 1,
                                    filter : true,
                                    style: 'text-align:center',
                                    overCls:'header-class-mouse-over',
                                    width:'9%'
                            },
                            {
									xtype:'actioncolumn',
                                    text: '<?php echo __('STATUS', array(), '', $language_file_path); ?>',
                                    sortable: true,
                                    tdCls:'x-vertical-align',
                                    dataIndex: 'status_details',
                                    id: 'col_guest_status_details',
                                    align:'center',
                                    menuDisabled: true,
                                    hideable : false,
                                    resizable : false,
                                    style: 'text-align:center',
                                    overCls:'header-class-mouse-over',
                                    width:50,
                                    renderer: function (value, meta, record) {
                                    var max = 110;
                                            qtip = value.replace(/'/g, '');
                                            qtip = qtip.replace(/"/g, '');
                                            meta.tdAttr = 'data-qtip="' + qtip + '"';
                                    },
                                    items: [{
                                    icon: '<?php echo BASEURL; ?>' + 'public/media/common/images/statuson.png',
                                            getClass: function(value, metadata, record){
												var status = record.get('status_details');
												if(status == "Disabled") {
													return 'x-hide-display';
												} else {
													return 'x-grid-center-icon';
												}
											},                                         
										}, {
											icon:'<?php echo BASEURL; ?>' + 'public/media/common/images/statusoff.png',
                                            getClass: function(value, metadata, record){
												var status = record.get('status_details');
                                                if (status == "Enabled") {
													return 'x-hide-display';
												} else {
													return 'x-grid-center-icon';
												}
                                            },
                                   
									}],
							},{
                                xtype:'actioncolumn',
								sortable:false,
								text: '<?php echo __('EDIT', array(), '', $language_file_path); ?>',
								tdCls:'x-vertical-align',
								fixed:true,
								align:'center',
								style: 'text-align:center',
								menuDisabled: true,
								hideable : false,
								resizable : false,
								width:45,
								items: [{
								icon:  '<?php echo BASEURL; ?>' + 'public/media/common/images/Iconedit.png',
										iconCls :'x-grid-center-icon',
										tooltip:'<?php echo __('EDIT', array(), '', $language_file_path); ?>',
										handler: function(grid, rowIndex, colIndex) {
										var me = Ext.get('popupbox');
												var rec = grid.getStore().getAt(rowIndex);
												me.load({
												url:'<?php echo $url_details_guest; ?>',
														params: {
															guest_id : rec.get('id'),
														},
														scripts:true,
														text: '<?php echo __('UPDATING', array(), '', $language_file_path); ?>',
												});
										}/* handler end here. */
								}]/* edit items end here */
						},{
                                xtype:'actioncolumn',
								sortable:false,
								text:'<?php echo __('DELETE', array(), '', $language_file_path); ?>',
								menuDisabled: true,
								resizable : false,
								tdCls:'x-vertical-align',
								hideable : false,
								align:'center',
								style: 'text-align:center',
								fixed:true,
								width:50,
								items: [{
										icon: '<?php echo BASEURL; ?>' + 'public/media/common/images/Icondelete.png', /* Use a URL in the icon config */
										iconCls :'x-grid-center-icon',
										tooltip:'<?php echo __('DELETE', array(), '', $language_file_path); ?>',
										handler: function(master_users_grid, rowIndex, colIndex) {
										var rec = grid.getStore().getAt(rowIndex);
												Ext.Msg.confirm('Confirm Message', ('<?php echo __('DELETE_CONFIRM', array(), '', $language_file_path); ?>'),
														function (id){
														if (id == 'yes')
														{
														Ext.Ajax.request({
														url:'<?php echo $url_delete_guest ?>',
																params: {
																guest_id: Ext.JSON.encode(rec.get('id')),
																},
																success: function(response){
																var message = response.responseText;
																		notification(message);
																		var store = Ext.getStore('guest_store');
																		if (store.getCount() == 1 && store.currentPage != 1){
																store.previousPage();
																}
																else{
																store.reload();
																}
																}/* success end here. */
														}); /* ajax request end here. */
														}/* if end here */
														}); /* confirmation end here. */
										}/* end of handler. */
									}]/* end of items */
                                }/* end of delete. */
						],	
						 dockedItems: [{
									xtype: 'pagingtoolbar',
                                    store: store,
                                    dock: 'bottom',
                                    displayInfo: true,
                                    items: [
                                            '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', '->', , '->', '->',
                                         {
											 xtype         : 'combobox',
                                            valueField	  : 'records_per_page',
                                            displayField  : 'records_per_page',
                                            id			  : 'rpp',
                                            name		  : 'rpp',
                                            fieldLabel    : '<?php echo __('RECORDS_PER_PAGE', array(), '', $language_file_path); ?>',
                                            emptyText  : '<?php echo __('SELECT_RECORDS_PER_PAGE', array(), '', $language_file_path); ?>',
                                            store           :guest_list_view_rpp_list,
                                            editable       : false,
                                            multiSelect   : false,
                                            selectOnFocus : false,
                                            addAllSelector: true,
                                            allowBlank    : true,
                                            queryMode     : 'local',
                                            value         : '<?php echo $itemsPerPage; ?>',
                                            width         : 155,
                                            labelWidth	  : 102,
                                            listeners     : {
                                            select:function(combo) {
                                            Ext.Ajax.request({
                                            url: '<?php echo BASEURL ?>index.php/Guest/CommonFunctions/grid_check_and_get_rpp',
                                                    params: {
															 records_per_page : combo.getValue(),
                                                            setting_type     : 'guest_grid',
                                                    },
                                                    success: function(response){
                                                            var text = Ext.decode(response.responseText);
                                                            if (text)
															{
																 store.proxy.extraParams = {limit : combo.getValue()};
																store.pageSize = combo.getValue();
																store.loadPage(1);
															}
                                                    }, /* success end here */
                                            }); /* ajax request end here */
                                      }/* select end here */
                             },/* listeners end here */
                      }],
            });
    });/*OnReady End Here.*/
</script>