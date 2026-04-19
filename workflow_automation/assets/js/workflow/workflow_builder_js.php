<script type="text/javascript">
    var timer = null;
	var id = document.getElementById("drawflow");
    var workflow_id = $('input[name="workflow_id"]').val();
    var workflow_type = $('input[name="workflow_type"]').val();
    const editor = new Drawflow(id);
    (function($) {
      "use strict";

    editor.reroute = true;
    editor.start();
    <?php if(isset($workflow->workflow) && $workflow->workflow != ''){ ?> 
    const dataToImport = <?php echo json_decode($workflow->workflow); ?>;
    editor.import(dataToImport);
    <?php } ?>
    <?php if(!isset($is_edit)){ ?>
        editor.editor_mode='fixed';
    <?php } ?>

    var elements = document.getElementsByClassName('drag-drawflow');
    for (var i = 0; i < elements.length; i++) {
      elements[i].addEventListener('touchend', drop, false);
      elements[i].addEventListener('touchmove', positionMobile, false);
      elements[i].addEventListener('touchstart', drag, false );
    }

    var mobile_item_selec = '';
    var mobile_last_move = null;

    $(document).on("keyup", "input[type=text]", function() { 
        clearTimeout(timer); 
        timer = setTimeout(workflow_input_change, 1000, $(this));
    });

    $(document).on("keyup", "input[type=number]", function() { 
        clearTimeout(timer); 
        timer = setTimeout(workflow_input_change, 1000, $(this));
    });

    $(document).on("change", "input.datetimepicker", function() { 
        clearTimeout(timer); 
        timer = setTimeout(workflow_input_change, 1000, $(this));
    });

    $(document).on("change", "input[type=time]", function() { 
        clearTimeout(timer); 
        timer = setTimeout(workflow_input_change, 1000, $(this));
    });

    $(document).on("change", "input[type=radio][name^=complete_action]", function() { 
        var parent = $(this).parents('.box');
        if(this.checked === true){
            parent.find('.div_complete_action_after').addClass('hide');
            parent.find('.div_complete_action_exact_time').addClass('hide');
            parent.find('.div_complete_action_exact_time_and_date').addClass('hide');

            parent.find('.div_complete_action_'+this.value).removeClass('hide');
        }
    });

    $(document).on("change", "select[name^=action]", function() {
        var parent = $(this).parents('.box');
        parent.find('.div_action_change_segments').addClass('hide');
        parent.find('.div_action_change_stages').addClass('hide');
        parent.find('.div_action_change_points').addClass('hide');
        parent.find('.div_action_point_action').addClass('hide');
        parent.find('.div_action_lead_status').addClass('hide');
        parent.find('.div_action_add_tags').addClass('hide');

        if (this.value == 'change_segments') {
            parent.find('.div_action_change_segments').removeClass('hide');
        }else if (this.value == 'change_stages') {
            parent.find('.div_action_change_stages').removeClass('hide');
        }else if (this.value == 'change_points') {
            parent.find('.div_action_change_points').removeClass('hide');
        }else if (this.value == 'point_action') {
            parent.find('.div_action_point_action').removeClass('hide');
        }else if (this.value == 'change_lead_status') {
            parent.find('.div_action_lead_status').removeClass('hide');
        }else if (this.value == 'add_tags') {
            parent.find('.div_action_add_tags').removeClass('hide');
        }
    });

    $(document).on("change", "input[type=radio][name^=lead_data_from]", function() { 
        var parent = $(this).parents('.box');
        if(this.checked === true){
            parent.find('.div_lead_data_from_segment').addClass('hide');
            parent.find('.div_lead_data_from_form').addClass('hide');

            parent.find('.div_lead_data_from_'+this.value).removeClass('hide');
        }

    });

    $(document).on("change", "input[type=radio][name^=client_data_from]", function() { 
        var parent = $(this).parents('.box');
        if(this.checked === true){
            parent.find('.div_client_data_from_segment').addClass('hide');
            parent.find('.div_client_data_from_group').addClass('hide');

            parent.find('.div_client_data_from_'+this.value).removeClass('hide');
        }

    });

    $(document).on("change", "input[type=radio][name^=data_type]", function() { 
        var parent = $(this).parents('.box');

        if(this.checked === true){
            parent.find('.div_data_type_lead').addClass('hide');
            parent.find('.div_data_type_customer').addClass('hide');

            parent.find('.div_data_type_'+this.value).removeClass('hide');
        }

    });

    $(document).on("change", "input[type=radio][name^=filter_type]", function() { 
        var parent = $(this).parents('.box');

        if(this.checked === true){
            parent.find('.div_filter_type_lead').addClass('hide');
            parent.find('.div_filter_type_customer').addClass('hide');

            parent.find('.div_filter_type_'+this.value).removeClass('hide');
        }

    });

    $(document).on("change", "input[type=radio][name^=action_segment_type]", function() { 
        var parent = $(this).parents('.box');

        if(this.checked === true){
            parent.find('.div_action_segment_type_lead').addClass('hide');
            parent.find('.div_action_segment_type_customer').addClass('hide');

            parent.find('.div_action_segment_type_'+this.value).removeClass('hide');
        }

    });


    $(document).on("change", "select[name^=action]", function() {
        var parent = $(this).parents('.box');
        parent.find('.div_action_change_segments').addClass('hide');
        parent.find('.div_action_change_stages').addClass('hide');
        parent.find('.div_action_change_points').addClass('hide');
        parent.find('.div_action_point_action').addClass('hide');
        parent.find('.div_action_lead_status').addClass('hide');
        parent.find('.div_action_add_tags').addClass('hide');

        if (this.value == 'change_segments') {
            parent.find('.div_action_change_segments').removeClass('hide');
        }else if (this.value == 'change_stages') {
            parent.find('.div_action_change_stages').removeClass('hide');
        }else if (this.value == 'change_points') {
            parent.find('.div_action_change_points').removeClass('hide');
        }else if (this.value == 'point_action') {
            parent.find('.div_action_point_action').removeClass('hide');
        }else if (this.value == 'change_lead_status') {
            parent.find('.div_action_lead_status').removeClass('hide');
        }else if (this.value == 'add_tags') {
            parent.find('.div_action_add_tags').removeClass('hide');
        }
    });

    $(document).on("change", "select", function() {
        var parent = $(this).parents('.box');
        var nodeId = parent.attr('node-id');
        var node = editor.getNodeFromId(nodeId);
        var data_node = node.data;
        var select_name = this.name.split("[");
        data_node[select_name[0]] = this.value;
        editor.updateNodeDataFromId(nodeId, data_node);
    });

    $(document).on("change", "input[id=tags]", function() {
        var parent = $(this).parents('.box');
        var nodeId = parent.attr('node-id');
        var node = editor.getNodeFromId(nodeId);
        var data_node = node.data;
        var select_name = this.name.split("[");
        data_node[select_name[0]] = this.value;
        editor.updateNodeDataFromId(nodeId, data_node);
    });

    $('input[type="time"]').datetimepicker({
        datepicker: false,
        format: 'H:i'
    });

    $( document ).ready(function() {
        $('input[type=radio][name^=data_type]').change();
        $('input[type=radio][name^=filter_type]').change();
        $('input[type=radio][name^=action_segment_type]').change();
        $('input[type=radio][name^=lead_data_from]').change();
        $('input[type=radio][name^=client_data_from]').change();
        $('input[type=radio][name^=complete_action]').change();
        $('select[name^=data_type]').change();
        $('select[name^=trigger_type]').change();
 
        $('select[name^=check]').change();
        $('select[name^=action]').change();

    });

    appValidateForm($('#workflow-form'), {
    },workflow_form_handler);

    })(jQuery);


   function positionMobile(ev) {
      "use strict";
     mobile_last_move = ev;
   }

   function allowDrop(ev) {
      "use strict";
      ev.preventDefault();
    }

    function drag(ev) {
      "use strict";
      if (ev.type === "touchstart") {
        mobile_item_selec = ev.target.closest(".drag-drawflow").getAttribute('data-node');
      } else {
      ev.dataTransfer.setData("node", ev.target.getAttribute('data-node'));
      }
    }

    function drop(ev) {
      "use strict";
      if (ev.type === "touchend") {
        var parentdrawflow = document.elementFromPoint( mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY).closest("#drawflow");
        if(parentdrawflow != null) {
          addNodeToDrawFlow(mobile_item_selec, mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY);
        }
        mobile_item_selec = '';
      } else {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("node");
        addNodeToDrawFlow(data, ev.clientX, ev.clientY);
      }

    }

    function addNodeToDrawFlow(name, pos_x, pos_y) {
      "use strict";
      if(editor.editor_mode === 'fixed') {
        return false;
      }
      pos_x = pos_x * ( editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)) - (editor.precanvas.getBoundingClientRect().x * ( editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)));
      pos_y = pos_y * ( editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)) - (editor.precanvas.getBoundingClientRect().y * ( editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)));


      switch (name) {
        case 'flow_start':
            $.post(admin_url + 'workflow_automation/get_workflow_node_html/'+workflow_id, {
                type: 'flow_start',
                nodeId: editor.nodeId,
                csrf_token_name: $('input[name=csrf_token_hash]').val()
            }).done(function (html) {

                if(editor.getNodesFromName('flow_start').length == 0){
                    editor.addNode('flow_start', 0,  1, pos_x, pos_y, 'flow_start', {}, html );
                    
                    init_selectpicker();
                }

            });

          break;
        case 'condition':
            $.post(admin_url + 'workflow_automation/get_workflow_node_html/'+workflow_id, {
                type: 'condition',
                nodeId: editor.nodeId,
                workflow_type: workflow_type,
                csrf_token_name: $('input[name=csrf_token_hash]').val()
            }).done(function (html) {
                editor.addNode('condition', 1, 2, pos_x, pos_y, 'condition', {}, html );
                
                init_selectpicker();
            });

          break;
        case 'action':

            $.post(admin_url + 'workflow_automation/get_workflow_node_html/'+workflow_id, {
                type: 'action',
                nodeId: editor.nodeId,
                workflow_type: workflow_type,
                csrf_token_name: $('input[name=csrf_token_hash]').val()
            }).done(function (html) {

                editor.addNode('action', 1, 1, pos_x, pos_y, 'action', {}, html );
                
                init_selectpicker();
                init_tags_inputs();
            });

          break;

        case 'break':

            $.post(admin_url + 'workflow_automation/get_workflow_node_html/'+workflow_id, {
                type: 'break',
                nodeId: editor.nodeId,
                workflow_type: workflow_type,
                csrf_token_name: $('input[name=csrf_token_hash]').val()
            }).done(function (html) {

                editor.addNode('break', 1, 1, pos_x, pos_y, 'break', {}, html );
                
                init_selectpicker();
                init_tags_inputs();
            });

          break;

        default:
      }

        

    }

  var transform = '';
  function showpopup(e) {
      "use strict";
    e.target.closest(".drawflow-node").style.zIndex = "9999";
    e.target.children[0].style.display = "block";

    transform = editor.precanvas.style.transform;
    editor.precanvas.style.transform = '';
    editor.precanvas.style.left = editor.canvas_x +'px';
    editor.precanvas.style.top = editor.canvas_y +'px';

    editor.editor_mode = "fixed";

  }

   function closemodal(e) {
      "use strict";
     e.target.closest(".drawflow-node").style.zIndex = "2";
     e.target.parentElement.parentElement.style.display  ="none";
     editor.precanvas.style.transform = transform;
       editor.precanvas.style.left = '0px';
       editor.precanvas.style.top = '0px';
      editor.editor_mode = "edit";
   }

    function changeModule(event) {
      "use strict";
      var all = document.querySelectorAll(".menu ul li");
        for (var i = 0; i < all.length; i++) {
          all[i].classList.remove('selected');
        }
      event.target.classList.add('selected');
    }

    function changeMode(option) {
      "use strict";

      if(option == 'lock') {
        lock.style.display = 'none';
        unlock.style.display = 'block';
      } else {
        lock.style.display = 'block';
        unlock.style.display = 'none';
      }

    }

    function save_workflow() {
      "use strict";
        $('input[name=workflow]').val('');
        $('#workflow-form').submit();
    }

    function builder() {
      "use strict";
        window.location.assign(admin_url + 'workflow_automation/workflow_builder/<?php echo html_entity_decode($workflow->id); ?>');
    }

    function workflow_input_change(input) {
      "use strict";
        var value = input.val();

        input.attr('value',value);

        var parent = input.parents('.box');
        var nodeId = parent.attr('node-id');
        var node = editor.getNodeFromId(nodeId);
        var data_node = node.data;
        var select_name = input.attr('name').split("[");
        if(select_name != ''){
            data_node[select_name[0]] = value;
            editor.updateNodeDataFromId(nodeId, data_node);
        }
    }


    function workflow_form_handler(form) {
        "use strict";

        var formURL = form.action;
        var formData = new FormData($(form)[0]);

        var workflow = new File([JSON.stringify(editor.export())], "workflow.txt", { type: "text/plain" });
        formData.append("workflow", workflow);

        $.ajax({
            type: $(form).attr('method'),
            data: formData,
            mimeType: $(form).attr('enctype'),
            contentType: false,
            cache: false,
            processData: false,
            url: formURL
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == 'true' || $.isNumeric(response.success)) {
              alert_float('success', response.message);
              if(response.url){
                window.location.assign(response.url);
              }
            }else {
              alert_float('danger', response.message);
              $('#btn-submit').attr('disabled', false);
            }
        }).fail(function(error) {
            alert_float('danger', JSON.parse(error.mesage));
            $('#btn-submit').attr('disabled', false);
        });

        return false;
    }


    function check_change(el){
        "use strict";

        var nodeId = $(el).data('nodeid');
        var check = $(el).val();
        var data = {};
        data.check = check;
        data.workflow_type = workflow_type;

        $.post(admin_url + 'workflow_automation/get_condition_for_check', data).done(function (response) { 
            response = JSON.parse(response);

            $('select[name="condition['+nodeId+']"]').html(response.condition_option);
            $('select[name="condition['+nodeId+']"]').selectpicker('refresh');

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;
            $('select[name="condition['+nodeId+']"]').val(data_node['condition']).change();

            editor.updateNodeDataFromId(nodeId, data_node);

        });

        load_variable(nodeId);

    }

    function check_condition_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        load_variable(nodeId);
    }

    function load_variable(nodeId){
        "use strict";

        var check = $('select[name="check['+nodeId+']"]').val();
        var condition = $('select[name="condition['+nodeId+']"]').val();

        var data = {};
        data.check = check;
        data.condition = condition;
        data.workflow_type = workflow_type;
        data.nodeid = nodeId;

        $.post(admin_url + 'workflow_automation/get_variable_condition_for_check', data).done(function (response) { 
            response = JSON.parse(response);

            $('#condition_variable_div_'+nodeId).html(response.html);
            $('#condition_variable_div_'+nodeId).removeClass('hide');
             $('select[name="condition_variable['+nodeId+']"]').selectpicker('refresh');
             init_datepicker();


            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;
            
            $('select[name="condition_variable['+nodeId+']"]').val(data_node['condition_variable']).change();
            $('input[name="condition_variable['+nodeId+']"]').val(data_node['condition_variable']);
            

        });


    }

    function change_load_data(){
        "use strict";
        $(document).on("change", "select", function() {
            var parent = $(this).parents('.box');
            var nodeId = parent.attr('node-id');
            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;
            var select_name = this.name.split("[");
            data_node[select_name[0]] = this.value;
            editor.updateNodeDataFromId(nodeId, data_node);
        });
    }

    /**
     * [action_change description]
     * @return {[type]} [description]
     */
    function action_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var action = $(el).val();
        var data = {};
        data.workflow_type = workflow_type;
        data.action = action;
        data.nodeid = nodeId;

        $.post(admin_url + 'workflow_automation/get_variable_for_action', data).done(function (response) { 
            response = JSON.parse(response);

            $('#action_variable_'+nodeId).html(response.html);
            $('#action_variable_'+nodeId).removeClass('hide');

            if(action != 'update_task_field'){
                $('#task_field_box_'+nodeId).html('');
                $('#task_field_box_'+nodeId).addClass('hide');
            }

            if(action != 'update_project_fields'){
                $('#project_field_box_'+nodeId).html('');
                $('#project_field_box_'+nodeId).addClass('hide');
            }


            if(action != 'update_expense_fields'){
                $('#expense_field_box_'+nodeId).html('');
                $('#expense_field_box_'+nodeId).addClass('hide');
            }

            if(action != 'update_customer_field'){
                $('#customer_field_box_'+nodeId).html('');
                $('#customer_field_box_'+nodeId).addClass('hide');
            }

            
            $('.selectpicker').selectpicker('refresh');
                

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(action == 'assign_to'){
            
                $('select[name="action_variable['+nodeId+']"]').val(data_node['action_variable']).change();
                $('input[name="action_variable['+nodeId+']"]').val(data_node['action_variable']);
            }else if(action == 'send_email' || action == 'send_email_default'){    
                $('select[name="send_email_to['+nodeId+']"]').val(data_node['send_email_to']).change();
                $('textarea[name="send_email_content['+nodeId+']"]').val(data_node['send_email_content']);
            }else if(action == 'add_a_comment' || action == 'add_comment'){  
                $('textarea[name="comment_content['+nodeId+']"]').val(data_node['comment_content']);
            }else if(action == 'create_task' || action == 'create_task_default'){  
                $('select[name="task_template['+nodeId+']"]').val(data_node['task_template']).change();
            }else if(action == 'assign_to_client' || action == 'assign_to_customer'){
                $('select[name="assign_to_client['+nodeId+']"]').val(data_node['assign_to_client']).change();
            }else if(action == 'add_tag'){
                $('input[name="tags['+nodeId+']"]').val(data_node['tags']);
            }else if(action == 'change_status'){
                $('select[name="task_status['+nodeId+']"]').val(data_node['task_status']).change();
                $('select[name="project_status['+nodeId+']"]').val(data_node['project_status']).change();
                 $('select[name="status['+nodeId+']"]').val(data_node['status']).change();
            }else if(action == 'change_priority'){
                $('select[name="task_priority['+nodeId+']"]').val(data_node['task_priority']).change();
                $('select[name="priority['+nodeId+']"]').val(data_node['priority']).change();
            }else if(action == 'update_task_field'){
                $('select[name="task_field['+nodeId+']"]').val(data_node['task_field']).change();
            }else if(action == 'update_project_fields'){
                $('select[name="project_field['+nodeId+']"]').val(data_node['project_field']).change();
            }else if(action == 'create_reminder_for_task' || action == 'create_reminder_for_lead' || action == 'create_reminder_for_proposal' || action == 'create_reminder_for_estimate' || action == 'create_reminder_for_invoice' || action == 'create_reminder_for_credit_note' || action == 'create_reminder_for_purchase_order'  || action == 'create_reminder_for_purchase_invoice' || action == 'create_reminder_for_purchase_contract' || action == 'create_reminder_for_expense' || action == 'create_reminder_for_customer'){
                $('select[name="reminder_to['+nodeId+']"]').val(data_node['reminder_to']).change();
                $('input[name="reminder_time['+nodeId+']"]').val(data_node['reminder_time']);
                $('select[name="reminder_time_type['+nodeId+']"]').val(data_node['reminder_time_type']).change();
                $('textarea[name="reminder_description['+nodeId+']"]').val(data_node['reminder_description']);

            }else if(action == 'change_status_project'){
    
                $('select[name="project_status['+nodeId+']"]').val(data_node['project_status']).change();
            }else if(action == 'add_note'){
    
                $('textarea[name="note['+nodeId+']"]').val(data_node['note']);
            }else if(action == 'change_status_lead'){
                $('select[name="lead_status['+nodeId+']"]').val(data_node['lead_status']).change();
            }else if(action == 'change_source_lead'){
               $('select[name="lead_source['+nodeId+']"]').val(data_node['lead_source']).change(); 
            }else if(action == 'assign_to_staff' || action == 'change_sale_agent'){
                $('select[name="staff['+nodeId+']"]').val(data_node['staff']).change();
            }else if(action == 'lead_field'){
                $('select[name="lead_field['+nodeId+']"]').val(data_node['lead_field']).change();
            }else if(action == 'update_proposal_field'){
                $('select[name="proposal_field['+nodeId+']"]').val(data_node['proposal_field']).change();
            }else if(action == 'change_project'){
                $('select[name="project['+nodeId+']"]').val(data_node['project']).change();
            }else if(action == 'update_estimate_field'){
                $('select[name="estimate_field['+nodeId+']"]').val(data_node['estimate_field']).change();
            }else if(action == 'create_note'){  
                $('textarea[name="note['+nodeId+']"]').val(data_node['note']);
            }else if(action == 'update_customer_field'){
                $('select[name="customer_field['+nodeId+']"]').val(data_node['customer_field']).change();
            }else if(action == 'change_status_invoice'){
                $('select[name="invoice_status['+nodeId+']"]').val(data_node['invoice_status']).change();
            }else if(action == 'update_invoice_field'){
                $('select[name="invoice_field['+nodeId+']"]').val(data_node['invoice_field']).change();
            }else if(action == 'update_credit_note_field'){
                $('select[name="credit_note_field['+nodeId+']"]').val(data_node['credit_note_field']).change();
            }else if(action == 'update_payment_field'){
                $('select[name="payment_field['+nodeId+']"]').val(data_node['payment_field']).change();
            }else if(action == 'update_purchase_order_field'){
                $('select[name="purchase_order_field['+nodeId+']"]').val(data_node['purchase_order_field']).change();
            }else if(action == 'change_approval_status'){
                $('select[name="approval_status['+nodeId+']"]').val(data_node['approval_status']).change();
            }else if(action == 'change_delivery_status'){
                $('select[name="delivery_status['+nodeId+']"]').val(data_node['delivery_status']).change();
            }else if(action == 'update_purchase_request_field'){
                $('select[name="purchase_request_field['+nodeId+']"]').val(data_node['purchase_request_field']).change();
            }else if(action == 'update_purchase_quotation_field'){
                $('select[name="purchase_quotation_field['+nodeId+']"]').val(data_node['purchase_quotation_field']).change();
            }else if(action == 'update_purchase_invoice_field'){
                $('select[name="purchase_invoice_field['+nodeId+']"]').val(data_node['purchase_invoice_field']).change();
            }else if(action == 'update_purchase_contract_field'){
                $('select[name="purchase_contract_field['+nodeId+']"]').val(data_node['purchase_contract_field']).change();
            }else if(action == 'create_onboarding'){
                $('select[name="type_of_training['+nodeId+']"]').val(data_node['type_of_training']).change();
                $('select[name="training_program['+nodeId+']"]').val(data_node['training_program']).change();
            }else if(action == 'layoff_checkist'){
                $('input[name="layoff_time['+nodeId+']"]').val(data_node['layoff_time']);
                $('select[name="layoff_time_type['+nodeId+']"]').val(data_node['layoff_time_type']).change();
            }else if(action == 'assign_training_program'){
                $('input[name="training_programs_name['+nodeId+']"]').val(data_node['training_programs_name']);
                $('input[name="training_places['+nodeId+']"]').val(data_node['training_places']);
                $('input[name="start_time['+nodeId+']"]').val(data_node['start_time']);
                $('textarea[name="training_results['+nodeId+']"]').val(data_node['training_results']);
                $('input[name="finish_time['+nodeId+']"]').val(data_node['finish_time']);

                $('select[name="start_time_type['+nodeId+']"]').val(data_node['start_time_type']).change();
                $('select[name="finish_time_type['+nodeId+']"]').val(data_node['finish_time_type']).change();
            }else if(action == 'update_expense_fields'){
                $('select[name="expense_field['+nodeId+']"]').val(data_node['expense_field']).change();
            }else if(action == 'update_subscription_field'){
                $('select[name="subscription_field['+nodeId+']"]').val(data_node['subscription_field']).change();
            }else if(action == 'change_campaign_status'){
                $('select[name="status['+nodeId+']"]').val(data_node['status']).change();

            }else if(action == 'send_email_to_candidate' || action == 'send_email_to_contact'){
                $('input[name="subject['+nodeId+']"]').val(data_node['subject']);
                $('textarea[name="content['+nodeId+']"]').val(data_node['content']);
            }else if(action == 'change_candidate_status'){
                $('select[name="candidate_status['+nodeId+']"]').val(data_node['candidate_status']).change();
            }else if(action == 'create_assignment' || action == 'change_driver'){
                $('select[name="driver['+nodeId+']"]').val(data_node['driver']).change();
            }else if(action == 'change_type'){
                $('select[name="type['+nodeId+']"]').val(data_node['type']).change();
            }else if(action == 'change_vehiche'){
                $('select[name="vehiche['+nodeId+']"]').val(data_node['vehiche']).change();

            }else if(action == 'change_vendor'){
                $('select[name="vendor['+nodeId+']"]').val(data_node['vendor']).change();

            }else if(action == 'change_working_hours'){
                $('select[name="working_hour['+nodeId+']"]').val(data_node['working_hour']).change();
            }else if(action == 'update_cost'){
                $('input[name="costs_hour['+nodeId+']"]').val(data_node['costs_hour']);
            }else if(action == 'update_note'){
                $('textarea[name="note['+nodeId+']"]').val(data_node['note']);
            }else if(action == 'change_work_center'){
                $('select[name="work_center['+nodeId+']"]').val(data_node['work_center']).change();
            }else if(action == 'update_description'){
                $('textarea[name="note['+nodeId+']"]').val(data_node['note']);
                $('textarea[name="description['+nodeId+']"]').val(data_node['description']);
            }else if(action == 'change_bom_type'){
                $('select[name="bom_type['+nodeId+']"]').val(data_node['bom_type']).change();
            }else if(action == 'change_ready_to_produce'){
                $('select[name="ready_to_produce['+nodeId+']"]').val(data_node['ready_to_produce']).change();
            }else if(action == 'change_consumption'){
                $('select[name="consumption['+nodeId+']"]').val(data_node['consumption']).change();
            }else if(action == 'change_unit_of_measure'){
                $('select[name="unit_id['+nodeId+']"]').val(data_node['unit_id']).change();
            }else if(action == 'change_payment_mode'){
                $('select[name="payment_mode['+nodeId+']"]').val(data_node['payment_mode']).change();
            }else if(action == 'update_amount'){
                $('input[name="amount['+nodeId+']"]').val(data_node['amount']);
            }else if(action == 'add_client_to_trade_discount'){
                $('select[name="client['+nodeId+']"]').val(data_node['client']).change();
            }else if(action == 'update_end_date'){
                $('input[name="end_time['+nodeId+']"]').val(data_node['end_time']);
                $('select[name="end_time_type['+nodeId+']"]').val(data_node['end_time_type']).change();
            }else if(action == 'update_formal'){
                $('select[name="formal['+nodeId+']"]').val(data_node['formal']).change();
            }else if(action == 'update_type'){
                $('select[name="type['+nodeId+']"]').val(data_node['type']).change();
            }else if(action == 'change_location'){
                $('select[name="location['+nodeId+']"]').val(data_node['location']).change();
            }else if(action == 'change_model'){
                $('select[name="model['+nodeId+']"]').val(data_node['model']).change();
            }else if(action == 'change_supplier'){
                $('select[name="supplier['+nodeId+']"]').val(data_node['supplier']).change();
            }else if(action == 'change_category'){
                $('select[name="category['+nodeId+']"]').val(data_node['category']).change();
            }else if(action == 'change_manufacturer'){
                $('select[name="manufacturer['+nodeId+']"]').val(data_node['manufacturer']).change();
            }else if(action == 'change_depreciation'){
                $('select[name="depreciation['+nodeId+']"]').val(data_node['depreciation']).change();
            }else if(action == 'payslip_template_apply_to_staff'){
                $('select[name="staff['+nodeId+']"]').val(data_node['staff']).change();
            }else if(action == 'payslip_template_except_for_staff'){
                $('select[name="except_staff['+nodeId+']"]').val(data_node['except_staff']).change();
            }else if(action == 'change_warehouse'){
                $('select[name="warehouse['+nodeId+']"]').val(data_node['warehouse']).change();
            }else if(action == 'change_commodity_type'){
                $('select[name="commodity_type['+nodeId+']"]').val(data_node['commodity_type']).change();
            }else if(action == 'change_unit'){
                $('select[name="unit['+nodeId+']"]').val(data_node['unit']).change();
            }else if(action == 'change_commodity_group'){
                $('select[name="commodity_group['+nodeId+']"]').val(data_node['commodity_group']).change();
            }else if(action == 'change_department'){
                $('select[name="department['+nodeId+']"]').val(data_node['department']).change();
            }else if(action == 'add_shipping_log'){
                $('textarea[name="wh_activity_textarea['+nodeId+']"]').val(data_node['wh_activity_textarea']);
            }else if(action == 'change_deliverer'){
                $('select[name="staff_id['+nodeId+']"]').val(data_node['staff_id']).change();
            }else if(action == 'change_group_customer'){
                $('select[name="client_group['+nodeId+']"]').val(data_node['client_group']).change();
            }else if(action == 'update_lead_field'){
                $('select[name="lead_field['+nodeId+']"]').val(data_node['lead_field']).change();
            }else if(action == 'create_proposal_default'){
                $('select[name="proposal_template['+nodeId+']"]').val(data_node['proposal_template']).change();
            }else if(action == 'create_estimate_default'){
                $('select[name="estimate_template['+nodeId+']"]').val(data_node['estimate_template']).change();
            }else if(action == 'create_invoice_default'){
                $('select[name="invoice_template['+nodeId+']"]').val(data_node['invoice_template']).change();
            }else if(action == 'create_manufacturing_order_default'){
                $('select[name="manufacturing_order_template['+nodeId+']"]').val(data_node['manufacturing_order_template']).change();
            }else if(action == 'create_purchase_request_default'){
                $('select[name="purchase_request_template['+nodeId+']"]').val(data_node['purchase_request_template']).change();
            }else if(action == 'create_purchase_order_default'){
                $('select[name="purchase_order_template['+nodeId+']"]').val(data_node['purchase_order_template']).change();
            }else if(action == 'assign_manager_default'){
                $('select[name="team_manage['+nodeId+']"]').val(data_node['team_manage']).change();
            }else if(action == 'create_inventory_receiving_voucher_default'){
                $('select[name="inventory_receiving_voucher_template['+nodeId+']"]').val(data_node['inventory_receiving_voucher_template']).change();
            }else if(action == 'create_inventory_delivery_voucher_default'){
                $('select[name="inventory_delivery_voucher_template['+nodeId+']"]').val(data_node['inventory_delivery_voucher_template']).change();
            }else if(action == 'change_status_credit_note'){
                $('select[name="credit_note_status['+nodeId+']"]').val(data_node['credit_note_status']).change();
            }else if(action == 'change_status_staff'){
                $('select[name="staff_status['+nodeId+']"]').val(data_node['staff_status']).change();
            }else if(action == 'create_manual_order_default'){
                $('select[name="manual_order_template['+nodeId+']"]').val(data_node['manual_order_template']).change();
            }else if(action == 'update_vendor_field'){
                $('select[name="vendor_field['+nodeId+']"]').val(data_node['vendor_field']).change();
            }else if(action == 'update_name'){
                $('input[name="routing_name['+nodeId+']"]').val(data_node['routing_name']);
            }else if(action == 'add_shipment_activity_log'){
                $('textarea[name="description['+nodeId+']"]').val(data_node['description']);
            }else if(action == 'update_discount'){
                $('input[name="discount['+nodeId+']"]').val(data_node['discount']);
            }else if(action == 'change_status_estimate'){
                $('select[name="estimate_status['+nodeId+']"]').val(data_node['estimate_status']).change();
            }else if(action == 'change_status_contract'){
                $('select[name="contract_status['+nodeId+']"]').val(data_node['contract_status']).change();
                
            }

            init_datepicker();

        });

    }

    /**
     * [data_type description]
     * @return {[type]} [description]
     */
    function data_type(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var type = $(el).val();
        var data = {};

        workflow_type = type;
        data.workflow_type = type;
        data.nodeid = nodeId;

        $.post(admin_url + 'workflow_automation/get_start_case_for_data_type', data).done(function (response) { 
            response = JSON.parse(response);

            $('select[name="start_when['+nodeId+']"]').html(response.html);
            $('select[name="start_when['+nodeId+']"]').selectpicker('refresh');


            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            $('select[name="start_when['+nodeId+']"]').val(data_node['start_when']).change();

        });

    }

    function trigger_type(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var type = $(el).val();

        if(type == 'automatic'){
            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            $('#collapse_node_'+nodeId+' #automatic_div').removeClass('hide');
            $('#collapse_node_'+nodeId+' #user_action_div').addClass('hide');
             $('select[name="repeat_every['+nodeId+']"]').val(data_node['repeat_every']).change();
        }else{
            $('#collapse_node_'+nodeId+' #user_action_div').removeClass('hide');
            $('#collapse_node_'+nodeId+' #automatic_div').addClass('hide');
        }

        
    }


    function repeat_every(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var repeat_every = $(el).val();
        var data = {};

        data.repeat_every = repeat_every;
        data.nodeid = nodeId;

        if(repeat_every != '' && repeat_every != undefined && repeat_every != null){
            $.post(admin_url + 'workflow_automation/get_repeat_every_html', data).done(function (response) { 
                response = JSON.parse(response);

                $('#repeat_div_'+nodeId).html(response.html);

                var node = editor.getNodeFromId(nodeId);
                var data_node = node.data;

                

                if(repeat_every == 'day'){

                    $('input[name="hour_of_day['+nodeId+']"]').val(data_node['hour_of_day']);

                }else if(repeat_every == 'week'){
                    $('select[name="day_of_week['+nodeId+']"]').selectpicker('refresh');
                    $('select[name="day_of_week['+nodeId+']"]').val(data_node['day_of_week']).change();
                    $('input[name="hour_of_day['+nodeId+']"]').val(data_node['hour_of_day']);

                }else if(repeat_every == 'month'){

                    $('input[name="day_of_month['+nodeId+']"]').val(data_node['day_of_month']);
                    $('input[name="hour_of_day['+nodeId+']"]').val(data_node['hour_of_day']);

                }else{

                    $('input[name="time['+nodeId+']"]').val(data_node['time']);
                    init_datepicker();
                }


                
            });
        }
    }

    /**
     * [task_field_change description]
     * @return {[type]} [description]
     */
    function task_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_task_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#task_field_box_'+nodeId).html(response.html);
            $('#task_field_box_'+nodeId).removeClass('hide');

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'name'){
                $('input[name="task_name['+nodeId+']"]').val(data_node['task_name']);
            }else if(field == 'startdate'){
                $('input[name="task_startdate['+nodeId+']"]').val(data_node['task_startdate']);
            }else if(field == 'duedate'){
                $('input[name="task_duedate['+nodeId+']"]').val(data_node['task_duedate']);
            }else if(field == 'hourly_rate'){
                $('input[name="task_hourly_rate['+nodeId+']"]').val(data_node['task_hourly_rate']);
            }else if(field == 'description'){
                $('textarea[name="task_description['+nodeId+']"]').val(data_node['task_description']);
            }

        });

    }

    /**
     * [project_field_change description]
     * @return {[type]} [description]
     */
    function project_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_project_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#project_field_box_'+nodeId).html(response.html);
            $('#project_field_box_'+nodeId).removeClass('hide');

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'name'){
                $('input[name="project_name['+nodeId+']"]').val(data_node['project_name']);
            }else if(field == 'hourly_rate'){
                $('input[name="project_hourly_rate['+nodeId+']"]').val(data_node['project_hourly_rate']);
            }else if(field == 'description'){
                $('textarea[name="project_description['+nodeId+']"]').val(data_node['project_description']);
            }

        });

    }

    /**
     * [lead_field_change description]
     * @return {[type]} [description]
     */
    function lead_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_lead_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#lead_field_box_'+nodeId).html(response.html);
            $('#lead_field_box_'+nodeId).removeClass('hide');

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'name'){
                $('input[name="name['+nodeId+']"]').val(data_node['name']);
            }else if(field == 'address'){
                $('input[name="address['+nodeId+']"]').val(data_node['address']);
            }else if(field == 'position'){
                $('input[name="position['+nodeId+']"]').val(data_node['position']);
            }else if(field == 'description'){
                $('textarea[name="description['+nodeId+']"]').val(data_node['description']);
            }

        });
    }

    /**
     * [customer_field_change description]
     * @return {[type]} [description]
     */
    function customer_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_customer_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#customer_field_box_'+nodeId).html(response.html);
            $('#customer_field_box_'+nodeId).removeClass('hide');

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'company'){
                $('input[name="company['+nodeId+']"]').val(data_node['company']);
            }else if(field == 'vat'){
                $('input[name="vat['+nodeId+']"]').val(data_node['vat']);
            }else if(field == 'phonenumber'){
                $('input[name="phonenumber['+nodeId+']"]').val(data_node['phonenumber']);
            }else if(field == 'website'){
                $('input[name="website['+nodeId+']"]').val(data_node['website']);
            }

        });
    }

    /**
     * [proposal_field_change description]
     * @return {[type]} [description]
     */
    function proposal_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_proposal_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#proposal_field_box_'+nodeId).html(response.html);
            $('#proposal_field_box_'+nodeId).removeClass('hide');

            if(field == 'assigned'){
                $('select[name="assigned['+nodeId+']"]').selectpicker('refresh');
            }

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'subject'){
                $('input[name="subject['+nodeId+']"]').val(data_node['subject']);
            }else if(field == 'assigned'){
                $('select[name="assigned['+nodeId+']"]').val(data_node['assigned']).change();
            }else if(field == 'proposal_to'){
                $('input[name="proposal_to['+nodeId+']"]').val(data_node['proposal_to']);
            }else if(field == 'email'){
                $('input[name="email['+nodeId+']"]').val(data_node['email']);
            }

        });
    }


    /**
     * [estimate_field_change description]
     * @return {[type]} [description]
     */
    function estimate_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_estimate_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#estimate_field_box_'+nodeId).html(response.html);
            $('#estimate_field_box_'+nodeId).removeClass('hide');

            if(field == 'assigned'){
                $('select[name="assigned['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'sale_agent'){
                $('select[name="sale_agent['+nodeId+']"]').selectpicker('refresh');
            }

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'reference_no'){
                $('input[name="reference_no['+nodeId+']"]').val(data_node['reference_no']);
            }else if(field == 'sale_agent'){
                $('select[name="sale_agent['+nodeId+']"]').val(data_node['sale_agent']).change();
            }else if(field == 'adminnote'){
                $('textarea[name="adminnote['+nodeId+']"]').val(data_node['adminnote']);
            }else if(field == 'clientnote'){
                $('textarea[name="clientnote['+nodeId+']"]').val(data_node['clientnote']);
            }else if(field == 'terms'){
                $('textarea[name="terms['+nodeId+']"]').val(data_node['terms']);
            }

        });
    }


    /**
     * [invoice_field_change description]
     * @return {[type]} [description]
     */
    function invoice_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_invoice_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#invoice_field_box_'+nodeId).html(response.html);
            $('#invoice_field_box_'+nodeId).removeClass('hide');

            if(field == 'assigned'){
                $('select[name="assigned['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'sale_agent'){
                $('select[name="sale_agent['+nodeId+']"]').selectpicker('refresh');
            }

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'sale_agent'){
                $('select[name="sale_agent['+nodeId+']"]').val(data_node['sale_agent']).change();
            }else if(field == 'adminnote'){
                $('textarea[name="adminnote['+nodeId+']"]').val(data_node['adminnote']);
            }else if(field == 'clientnote'){
                $('textarea[name="clientnote['+nodeId+']"]').val(data_node['clientnote']);
            }else if(field == 'terms'){
                $('textarea[name="terms['+nodeId+']"]').val(data_node['terms']);
            }

        });
    }

    /**
     * [credit_note_field_change description]
     * @return {[type]} [description]
     */
    function credit_note_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_credit_note_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#credit_note_field_box_'+nodeId).html(response.html);
            $('#credit_note_field_box_'+nodeId).removeClass('hide');

            if(field == 'assigned'){
                $('select[name="assigned['+nodeId+']"]').selectpicker('refresh');
            }

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'reference_no'){
                $('input[name="reference_no['+nodeId+']"]').val(data_node['reference_no']).change();
            }else if(field == 'adminnote'){
                $('textarea[name="adminnote['+nodeId+']"]').val(data_node['adminnote']);
            }else if(field == 'clientnote'){
                $('textarea[name="clientnote['+nodeId+']"]').val(data_node['clientnote']);
            }else if(field == 'terms'){
                $('textarea[name="terms['+nodeId+']"]').val(data_node['terms']);
            }

        });
    }


    /**
     * [payment_field_change description]
     * @return {[type]} [description]
     */
    function payment_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_payment_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#payment_field_box_'+nodeId).html(response.html);
            $('#payment_field_box_'+nodeId).removeClass('hide');

            if(field == 'assigned'){
                $('select[name="assigned['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'paymentmode'){
                $('select[name="paymentmode['+nodeId+']"]').selectpicker('refresh');
            }

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'amount'){
                $('input[name="amount['+nodeId+']"]').val(data_node['amount']);
            }else if(field == 'paymentmethod'){
                $('select[name="paymentmethod['+nodeId+']"]').val(data_node['paymentmethod']).change();
            }else if(field == 'paymentmode'){
                $('select[name="paymentmode['+nodeId+']"]').val(data_node['paymentmode']).change();
            }else if(field == 'transactionid'){
                $('input[name="transactionid['+nodeId+']"]').val(data_node['transactionid']);
            }else if(field == 'note'){
                $('textarea[name="note['+nodeId+']"]').val(data_node['note']);
            }

        });
    }


    /**
     * [purchase_order_field_change description]
     * @return {[type]} [description]
     */
    function purchase_order_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_purchase_order_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#purchase_order_field_box_'+nodeId).html(response.html);
            $('#purchase_order_field_box_'+nodeId).removeClass('hide');

            if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'buyer'){
                $('select[name="buyer['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'type'){
                $('select[name="type['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'project'){
                $('select[name="project['+nodeId+']"]').selectpicker('refresh');
            }

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'purchase_order_description'){
                $('input[name="pur_order_name['+nodeId+']"]').val(data_node['pur_order_name']);
            }else if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').val(data_node['vendor']).change();
            }else if(field == 'buyer'){
                $('select[name="buyer['+nodeId+']"]').val(data_node['buyer']).change();
            }else if(field == 'type'){
                $('select[name="type['+nodeId+']"]').val(data_node['type']).change();
            }else if(field == 'vendornote'){
                $('textarea[name="vendornote['+nodeId+']"]').val(data_node['vendornote']);
            }else if(field == 'terms'){
                $('textarea[name="terms['+nodeId+']"]').val(data_node['terms']);
            }else if(field == 'project'){
                $('select[name="project['+nodeId+']"]').val(data_node['project']).change();
            }

        });
    }


    /**
     * [purchase_request_field_change description]
     * @return {[type]} [description]
     */
    function purchase_request_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_purchase_request_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#purchase_request_field_box_'+nodeId).html(response.html);
            $('#purchase_request_field_box_'+nodeId).removeClass('hide');

            if(field == 'requester'){
                $('select[name="requester['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'type'){
                $('select[name="type['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'project'){
                $('select[name="project['+nodeId+']"]').selectpicker('refresh');
            }

            if(field == 'startdate' || field == 'duedate'){
                init_datepicker();
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'pur_rq_name'){
                $('input[name="pur_rq_name['+nodeId+']"]').val(data_node['pur_rq_name']);
            }else if(field == 'requester'){
                $('select[name="requester['+nodeId+']"]').val(data_node['requester']).change();
            }else if(field == 'type'){
                $('select[name="type['+nodeId+']"]').val(data_node['type']).change();
            }else if(field == 'project'){
                $('select[name="project['+nodeId+']"]').val(data_node['project']).change();
            }else if(field == 'description'){
                $('textarea[name="description['+nodeId+']"]').val(data_node['description']);
            }

        });
    }

    /**
     * [purchase_quoation_field_change description]
     * @return {[type]} [description]
     */
    function purchase_quotation_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_purchase_quotation_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#purchase_quotation_field_box_'+nodeId).html(response.html);
            $('#purchase_quotation_field_box_'+nodeId).removeClass('hide');

            if(field == 'buyer'){
                $('select[name="buyer['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').selectpicker('refresh');
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

           if(field == 'buyer'){
                $('select[name="buyer['+nodeId+']"]').val(data_node['buyer']).change();
            }else if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').val(data_node['vendor']).change();
            }

        });
    }


    /**
     * [purchase_invoice_field_change description]
     * @return {[type]} [description]
     */
    function purchase_invoice_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_purchase_invoice_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#purchase_invoice_field_box_'+nodeId).html(response.html);
            $('#purchase_invoice_field_box_'+nodeId).removeClass('hide');

            if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').selectpicker('refresh');
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'vendor_invoice_number'){
                $('input[name="vendor_invoice_number['+nodeId+']"]').val(data_node['vendor_invoice_number']);
            }else if(field == 'transactionid'){
                $('input[name="transactionid['+nodeId+']"]').val(data_node['transactionid']);
            }else if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').val(data_node['vendor']).change();
            }else if(field == 'vendornote'){
                $('textarea[name="vendornote['+nodeId+']"]').val(data_node['vendornote']);
            }else if(field == 'terms'){
                $('textarea[name="terms['+nodeId+']"]').val(data_node['terms']);
            }else if(field == 'adminnote'){
                $('textarea[name="adminnote['+nodeId+']"]').val(data_node['adminnote']);
            }

        });
    }


    /**
     * [purchase_contract_field_change description]
     * @return {[type]} [description]
     */
    function purchase_contract_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_purchase_contract_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#purchase_contract_field_box_'+nodeId).html(response.html);
            $('#purchase_contract_field_box_'+nodeId).removeClass('hide');

            if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').selectpicker('refresh');
            }else if(field == 'department'){
                $('select[name="department['+nodeId+']"]').selectpicker('refresh');
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'service_category'){
                $('input[name="service_category['+nodeId+']"]').val(data_node['service_category']);
            }else if(field == 'contract_value'){
                $('input[name="contract_value['+nodeId+']"]').val(data_node['contract_value']);
            }else if(field == 'vendor'){
                $('select[name="vendor['+nodeId+']"]').val(data_node['vendor']).change();
            }else if(field == 'department'){
                $('select[name="department['+nodeId+']"]').val(data_node['department']).change();
            }

        });
    }


    /**
     * [vendor_field_change description]
     * @return {[type]} [description]
     */
    function vendor_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_vendor_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#vendor_field_box_'+nodeId).html(response.html);
            $('#vendor_field_box_'+nodeId).removeClass('hide');

            if(field == 'country'){
                $('select[name="country['+nodeId+']"]').selectpicker('refresh');
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'company'){
                $('input[name="company['+nodeId+']"]').val(data_node['company']);
            }else if(field == 'vat'){
                $('input[name="vat['+nodeId+']"]').val(data_node['vat']);
            }else if(field == 'phone'){
                $('input[name="phone['+nodeId+']"]').val(data_node['phone']);
            }else if(field == 'website'){
                $('input[name="website['+nodeId+']"]').val(data_node['website']);
            }else if(field == 'address'){
                $('textarea[name="address['+nodeId+']"]').val(data_node['address']);
            }else if(field == 'city'){
                $('input[name="city['+nodeId+']"]').val(data_node['city']);
            }else if(field == 'state'){
                $('input[name="state['+nodeId+']"]').val(data_node['state']);
            }else if(field == 'zip'){
                $('input[name="zip['+nodeId+']"]').val(data_node['zip']);
            }else if(field == 'country'){
                $('select[name="country['+nodeId+']"]').val(data_node['country']).change();
            }

        });
    }


    /**
     * [expense_field_change description]
     * @return {[type]} [description]
     */
    function expense_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_expense_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#expense_field_box_'+nodeId).html(response.html);
            $('#expense_field_box_'+nodeId).removeClass('hide');

            if(field == 'clientid'){
                $('select[name="clientid['+nodeId+']"]').selectpicker('refresh');
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'name'){
                $('input[name="name['+nodeId+']"]').val(data_node['name']);
            }else if(field == 'amount'){
                $('input[name="amount['+nodeId+']"]').val(data_node['amount']);
            }else if(field == 'note'){
                $('textarea[name="note['+nodeId+']"]').val(data_node['note']);
            }else if(field == 'clientid'){
                $('select[name="clientid['+nodeId+']"]').val(data_node['clientid']).change();
            }

        });
    }


    /**
     * [subscription_field_change description]
     * @return {[type]} [description]
     */
    function subscription_field_change(el){
        "use strict";
        var nodeId = $(el).data('nodeid');
        var field = $(el).val();
        var data = {};

        data.nodeid = nodeId;
        data.field = field;

        $.post(admin_url + 'workflow_automation/get_subscription_field', data).done(function (response) { 
            response = JSON.parse(response);

            $('#subscription_field_box_'+nodeId).html(response.html);
            $('#subscription_field_box_'+nodeId).removeClass('hide');

            if(field == 'clientid'){
                $('select[name="clientid['+nodeId+']"]').selectpicker('refresh');
            }

            var node = editor.getNodeFromId(nodeId);
            var data_node = node.data;

            if(field == 'name'){
                $('input[name="name['+nodeId+']"]').val(data_node['name']);
            }else if(field == 'description'){
                $('textarea[name="description['+nodeId+']"]').val(data_node['description']);
            }else if(field == 'clientid'){
                $('select[name="clientid['+nodeId+']"]').val(data_node['clientid']).change();
            }

        });
    }
    
    



</script>