<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * Guest Add Form for Guest 
 * @package 	Guest
 * @category	View	
 * @Date 		    09-July-2015
 * @author 	    Roopam
 */
$session = Session::instance();
$encrypt = new encryption();
$url_for_guest_type_store = BASEURL . "index.php" . $encrypt->encode('Guest/GuestIndex/guest_type_list');
$url_sumbit_data = BASEURL . "index.php" . $encrypt->encode('Guest/GuestIndex/save_credential');
?>

<script language="javascript">
    var required = '<span style="color:red;font-weight:bold" data-qtip="<?php echo __('REQUIRED', array(), '', $language_file_path) ?>">*</span>';
    var loading_msg = "<?php echo __('LOADING', array(), '', $language_file_path)uest ?>";
    var add_guest_window = null;
 
    function save_guest_form(form, btn, btn_type)
    {
        /******************************** create array for department list *****************************/
        var department_list_array = new Array();
        var department_list_details = new Array();
        var department_list_ids = form.findField('department_list').getValue();
        var department_list_array = department_list_ids.split(",");
        for (var i = 0; i < department_list_array.length; i++)
        {
            Ext.getStore('department_store').each(function(items) {
                if (department_list_array[i] == items.get('department_id')) {
                    department_list_details.push({department_id: items.get('department_id'), department_name: items.get('department_name')})
                }
            });
        }
        var department_list_detail_value = Ext.encode(department_list_details);
        /************************End here *************************************/

        /****************************** Create Array For Access level Mapping Ids ******************************/
        var access_level_mapping_id_details = new Array();
        Ext.getStore('guest_access_details_store').each(function(items) {
            access_level_mapping_id_details.push(items.get('id'));
        });
        /***************************************** End here **************************************************/
            form.submit({
                url: '<?php echo $url_sumbit_data; ?>',
                params: {
                    department_list_details: department_list_detail_value,
                    access_level_mapping_id_details: access_level_mapping_id_details.toString(), },
                success: function(form, action) {
                    notification(action.result.message);
                    btn.up('window').close();
                    guest_popup_window.close();                    
                    Ext.getStore('guest_store').reload();                    
                },
                failure: function(form, action) {
                    notification(action.result ? action.result.message : '<?php echo __('UNABLE_TO_ADD', array(), '', $language_file_path) ?>', '<?php echo __('ERROR', array(), '', $language_file_path) ?>', 'ux-notification-icon-error');
                    btn.enable();
                }
            });/* form.sumbit end here. */
        }
    }

    Ext.onReady(function() {
        /* Store for guest type combo box. */
        var guest_type_store = Ext.create('Ext.data.Store', {
            autoLoad: false,
            fields: [{name: 'id', type: 'string'},
                {name: 'category_name', type: 'string'}],
            proxy: {
                type: 'ajax',
                url: "<?php echo $url_for_guest_type_store; ?>",
                reader: {
                    type: 'json',
                    root: 'rows'
                }
            }
        });

        /* Store for status type combo box. */
        var status_store = Ext.create('Ext.data.Store', {
            autoLoad: true,
            fields: [{name: 'id', type: 'string'},
                {name: 'name', type: 'string'}],
            proxy: {
                type: 'ajax',
                url: "<?php echo $url_for_status_store; ?>",
                reader: {
                    type: 'json',
                    root: 'rows'
                }
            }
        });

        /*Department store*/
        var department_store = Ext.create('Ext.data.Store', {
            id: 'department_store',
            autoLoad: department_store_load_flag,
            fields: [{name: 'department_id', type: 'string'},
                {name: 'department_name', type: 'string'},
            ],
            proxy: {
                type: 'ajax',
                url: '<?php echo $url_load_data_for_department_list; ?>',
                reader: {
                    type: 'json',
                    root: 'rows',
                }
            }
        });

        /*************************** Department Store Ended *********************************************/

        /* form start from here. */
		guest_add_form = Ext.create('Ext.form.Panel', {
				fieldDefaults: {
					msgTarget: 'side',
				},
				frame: false,
				bodyStyle: 'background:transparent',
				bodyBorder: false,
				border: false,
				autoScroll: true,
				height: getViewport()[0] - 52,
				maxHeight: 608,
				items: [{
                    xtype: 'panel',
                    layout: 'vbox',
                    margin: '0 -1 -1 -1',
                    width: '100%',
                    title: '<?php echo __('GUEST_DETAILS', array(), '', $language_file_path); ?>',
                    items: [{
                            xtype: 'fieldcontainer',
                            layout: 'hbox',
                            margin: '5 0 0 0',
                            width: '100%',
                            items: [
                                {
                                    xtype: 'textfield',
                                    name: 'first_name',
                                    vtype: 'ws',
                                    labelAlign: 'top',
                                    fieldLabel: '<?php echo __('FIRST_NAME', array(), '', $language_file_path); ?>',
                                    width: '20%',
                                    margin: '0 10 0 10',
                                    allowBlank: false,
                                    afterLabelTextTpl: required,
                                    blankText: '<?php echo __('BLANK_FIRST_NAME', array(), '', $language_file_path); ?>',
                                }, {
                                    xtype: 'textfield',
                                    name: 'last_name',
                                    vtype: 'ws',
                                    afterLabelTextTpl: required,
                                    labelAlign: 'top',
                                    fieldLabel: '<?php echo __('LAST_NAME', array(), '', $language_file_path); ?>',
                                    allowBlank: false,
                                    blankText: '<?php echo __('BLANK_LAST_NAME', array(), '', $language_file_path); ?>',
                                    width: '20%',
                                    margin: '0 10 0 0',
                                }, {
                                    xtype: 'combo',
                                    name: 'guest_type',
                                    labelAlign: 'top',
                                    valueField: 'id',
                                    displayField: 'category_name',
                                    editable: false,
                                    store: guest_type_store,
                                    valueNotFoundText: '<?php echo __('NOTHING_FOUND', array(), '', $language_file_path); ?>',
                                    fieldLabel: '<?php echo __('GUEST_TYPE', array(), '', $language_file_path); ?>',
                                    width: '20%',
                                    margin: '0 10 0 0',
                                    noDataText: 'No data',
                                },
                                {
                                    xtype: 'checkcombo',
                                    width: '20%',
                                    margin: '0 10 0 0',
                                    name: 'department_list',
                                    allowBlank: false,
                                    afterLabelTextTpl: required,
                                    valueField: 'department_id',
                                    displayField: 'department_name',
                                    queryMode: 'local',
                                    store: department_store,
                                    editable:false,
                                    labelAlign: 'top',
                                    emptyText: '<?php echo __('SELECT_DEPARTMENT', array(), '', $language_file_path); ?>',
                                    addAllSelector: true,
                                    labelWidth: 80,
                                    fieldLabel: '<?php echo __('DEPARTMENT', array(), '', $language_file_path); ?>',               
                                },
                                {
                                    xtype: 'textfield',
                                    name: 'guest_of',
                                    labelAlign: 'top',
                                    fieldLabel: '<?php echo __('GUEST_OF', array(), '', $language_file_path); ?>',
                                    width: '20%',
                                    margin: '0 10 0 0',
                                }]
                        }, {
                            xtype: 'fieldcontainer',
                            layout: 'hbox',
                            margin: '5 0 0 0',
                            width: '100%',
                            items: [{
                                    xtype: 'textfield',
                                    name: 'email',
                                    labelAlign: 'top',
                                    fieldLabel: '<?php echo __('E_MAIL', array(), '', $language_file_path); ?>',
                                    width: '25%',
                                    margin: '0 10 0 10',
                                    vtype: 'email'
                                }, {
                                    xtype: 'textfield',
                                    name: 'cell_phone',
                                    labelAlign: 'top',
                                    fieldLabel: '<?php echo __('CELL_PHONE', array(), '', $language_file_path); ?>',
                                    maxLength: 12,
                                    minLength: 10,
                                    maskRe: /^[0-9]\.?$/,
                                    width: '14%',
                                    margin: '0 10 0 0'
                                },{
                                    xtype: 'textfield',
                                    name: 'affiliation',
                                    labelAlign: 'top',
                                    fieldLabel: '<?php echo __('AFFILIATION', array(), '', $language_file_path); ?>',
                                    width: '21%',
                                    margin: '0 10 0 0',
                                }, {
                                    xtype: 'combo',
                                    name: 'status',
                                    labelAlign: 'top',
                                    fieldLabel: '<?php echo __('STATUS', array(), '', $language_file_path); ?>',
                                    emptyText: '<?php echo __('ENABLED', array(), '', $language_file_path); ?>',
                                    valueField: 'id',
                                    displayField: 'name',
                                    editable: false,
                                    store: status_store,
                                    valueNotFoundText: '<?php echo __('NOTHING_FOUND', array(), '', $language_file_path); ?>',
                                    width: '14.8%',
                                    margin: '0 10 0 0',
                                    listeners: {
                                        afterrender: function(combo) {
                                            combo.store.load({
                                                callback: function() {
                                                    if (status_store.find('id', status) > -1)
                                                    {
                                                       guest_add_form.getForm().findField('status').setValue(status);
                                                    }/* if end here. */
                                                }/* callback end here. */
                                            });/* combo end here. */
                                        }/* afterrender end here. */
                                    }/* listners end here. */												 
                                }, {
                                    xtype: 'filefield',
                                    name: 'user_img',
                                    margin: '19 10 0 0',
                                    emptyText: '<?php echo __('EMPTY_TEXT', array(), '', $language_file_path); ?>',
                                    blankText: '<?php echo __('EMPTY_TEXT', array(), '', $language_file_path); ?>',
                                    max_file_size: '20mb',
                                    buttonText: '',
                                    buttonConfig: {
                                        iconCls: 'x-icn-attachment'
                                    },
                                    width: '25%',                                 
                                }]
                        }, {
                            xtype: 'fieldcontainer',
                            layout: 'hbox',
                            margin: '5 10 0 10',
                            width: '100%',
                            items: [{
                                    xtype: 'textareafield',
                                    name: 'notes',
                                    labelAlign: 'top',
                                    rows: 3,
                                    vtype: 'ws',
                                    fieldLabel: '<?php echo __('NOTES', array(), '', $language_file_path); ?>',
                                    width: '75%',
                                }, ]
                        },
                        {
                            xtype: 'fieldcontainer',
                            layout: 'hbox',
                            margin: '5 10 5 10',
                            width: '100%',
                            items: [
                                {
                                    xtype: 'radiofield',
                                    fieldLabel: '<?php echo __('AUTO_DISABLED_GUEST', array(), '', $language_file_path); ?>',
                                    boxLabel: '<?php echo __('YES', array(), '', $language_file_path); ?>',
                                    name: 'auto_disable_flag',
                                    width: '24%',
                                    labelWidth: 140,
                                    inputValue: '1',
                                    handler: function(obj) {
                                        if (obj.getValue())
                                        {

                                            guest_add_form.getForm().findField('auto_date_time').setDisabled(false);
                                            guest_add_form.getForm().isValid();
                                        }
                                    }
                                }, {
                                    xtype: 'radiofield',
                                    width: '5%',
                                    boxLabel: '<?php echo __('NO', array(), '', $language_file_path); ?>',
                                    name: 'auto_disable_flag',
                                    checked: true,
                                    inputValue: '0',
                                    handler: function(obj) {
                                        if (obj.getValue())
                                        {
                                            guest_add_form.getForm().findField('auto_date_time').reset();
                                            guest_add_form.getForm().findField('auto_date_time').setDisabled(true);
                                            guest_add_form.getForm().isValid();
                                        }
                                    }
                                }, {
                                    xtype: 'datetimefield',
                                    disabled: true,
                                    vtype: 'datetimerange',
                                    allowBlank: false,
                                    editable: false,
                                    format: '<?php echo $session->get('date_format') ?>',
                                    submitFormat: 'Y-m-d H:i:s',
                                    minValue: new Date(),
                                    name: 'auto_date_time',
                                    listeners: {
                                        select: function() {
                                            guest_add_form.getForm().isValid();
                                        }
                                    }
                                },
                                {
                                    xtype: 'image',
                                    height: 16,
                                    width: 16,
                                    style: {cursor: 'pointer'},
                                    src: '<?php echo BASEURL; ?>' + 'public/media/common/images/question-frame.png',
                                    margin: '3px 0px 0px 8px',
                                    listeners: {
                                        render: function(cmp) {
                                            Ext.create('Ext.tip.ToolTip', {
                                                target: cmp.el,
                                                autoHide: true,
                                                hideDelay: 0,
                                                dismissDelay: 95000000,
                                                html: '<?php echo __('AUTOMATIC_DISABLE_GUEST_TOOL_TIP', array(), '', $language_file_path); ?>'
                                            });
                                        }
                                    }
                                }
                            ]
                        }

                    ]

                },
                access_details_grid, notification_setting_panel],
            buttons: [{
                    iconCls: 'x-icn-save',
                    text: '<?php echo __('SAVE', array(), '', $language_file_path); ?>',
                    tooltip: '<?php echo __('SAVE', array(), '', $language_file_path); ?>',
                    width: 80,
                    margin: '0 10 0 0',
                    formBind: true,
                    handler: function(btn) {                        
                            var form = guest_add_form.getForm();
                            save_guest_form(form, btn, 'save');                      
                    }/* handler function end here. */
                }, {
                    iconCls: 'x-icn-close',
                    text: '<?php echo __('CANCEL', array(), '', $language_file_path); ?>',
                    tooltip: '<?php echo __('CANCEL', array(), '', $language_file_path); ?>',
                    width: 80,
                    handler: function(btn) {
                        btn.up('window').close();
                    }
                }]/* Buttons End Here */
        });/* Form End Here */

        add_guest_window = Ext.create('Ext.window.Window', {
            title: '<?php echo __('ADD_GUEST', array(), '', $language_file_path); ?>',
            width: 900,
            autocreate: true,
            border: false,
            plain: true,
            modal: true,
            resizable: false,
            defaults: {
                border: false,
                bodyPadding: '0 1 0 0',
            },
            listeners: {
                'afterrender': function(window) {
                    window.center();
                    window.setPosition(window.getPosition()[0], -2)
                },
                'close': function() {
                    guest_add_form = undefined;
                    add_guest_window = undefined;
                },
            },
            items: guest_add_form
        }).show();/* Window End Here */
        guest_add_form.getForm().findField('first_name').focus(true, 200);

        Ext.create('Ext.tip.ToolTip', {
            target: add_guest_window.header.items.get(1).el,
            html: '<?php echo __('CLOSE', array(), '', $language_file_path); ?>'
        });

        var win_original_width = add_guest_window.width;
        var win_original_resolution = getViewport()[1];
        Ext.EventManager.onWindowResize(function() {
            if (typeof  guest_add_form != 'undefined' && typeof add_guest_window != 'undefined')
            {
                get_view_height_width_for_resolution(add_guest_window, guest_add_form, win_original_width, win_original_resolution, 100, 52)
            }
        }, Ext.getBody());        
    });/* onReady End Here. */
</script>
