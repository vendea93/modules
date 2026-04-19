<div class="panel-group no-margin">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title" data-toggle="collapse" data-target="#collapse_node_<?php echo wa_html_entity_decode($nodeId); ?>" aria-expanded="true">
        <span class="text-danger glyphicon glyphicon-fullscreen"> </span><span class="text-danger"> <?php echo _l('condition'); ?></span>
      </h4>
    </div>
    <div id="collapse_node_<?php echo wa_html_entity_decode($nodeId); ?>" class="panel-collapse collapse in" aria-expanded="true">
      <div class="box" node-id="<?php echo wa_html_entity_decode($nodeId); ?>">
        <?php $checks = wa_get_check_by_type($workflow->type); ?>
          <?php echo render_select('check['.$nodeId.']',$checks, array('id', 'name'),'wa_check', '', ['df-check' => '', 'onchange' => 'check_change(this); return false;', 'data-nodeid' => $nodeId], [], '', '', true); ?>
          <?php $conditions = [
            ['id' => 'equal','name' => _l('wa_equal')],
            ['id' => 'not_equal','name' => _l('wa_not_equal')],
      
          ]; ?>
          
          <?php echo render_select('condition['.$nodeId.']',$conditions, array('id', 'name'),'wa_condition', '', [ 'df-condition' => '','onchange' => 'check_condition_change(this); return false;', 'data-nodeid' => $nodeId], [], '', '', true); ?>


          <div id="condition_variable_div_<?php echo wa_html_entity_decode($nodeId); ?>" class="hide">

            

          </div>
          
      </div>
    </div>
  </div>
</div>