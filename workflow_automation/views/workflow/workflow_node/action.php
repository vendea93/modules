<div class="panel-group no-margin">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title" data-toggle="collapse" data-target="#collapse_node_<?php echo wa_html_entity_decode($nodeId); ?>" aria-expanded="true">
        <span class="text-info glyphicon glyphicon-retweet"> </span><span class="text-info"> <?php echo _l('action').' '.$nodeId; ?></span>
      </h4>
    </div>
    <div id="collapse_node_<?php echo wa_html_entity_decode($nodeId); ?>" class="panel-collapse collapse in" aria-expanded="true">
      <div class="box" node-id="<?php echo wa_html_entity_decode($nodeId); ?>">
        <?php $actions = wa_get_actions_by_type($workflow_type); 
        ?>
          <?php echo render_select('action['.$nodeId.']',$actions, array('id', 'name', 'group'),'action', '', ['df-action' => '', 'onchange' => 'action_change(this); return false;', 'data-nodeid' => $nodeId], [], '', '', true); ?>

          <div id="action_variable_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="task_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="project_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="lead_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="customer_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="proposal_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="estimate_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="invoice_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="credit_note_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="payment_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>


          <div id="purchase_order_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="purchase_request_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

           <div id="purchase_quotation_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="purchase_invoice_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>


          <div id="purchase_contract_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="expense_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="subscription_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>

          <div id="vendor_field_box_<?php echo wa_html_entity_decode($nodeId); ?>">
            
          </div>
       
      </div>
    </div>
  </div>
</div>
