<div class="panel-group no-margin">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title" data-toggle="collapse" data-target="#collapse_node_<?php echo wa_html_entity_decode($nodeId); ?>" aria-expanded="true">
        <span class="text-success glyphicon glyphicon-log-in"> </span><span class="text-success"> <?php echo _l('flow_start').' '.$nodeId; ?></span>
      </h4>
    </div>
    <div id="collapse_node_<?php echo wa_html_entity_decode($nodeId); ?>" class="panel-collapse collapse in" aria-expanded="true">
      <div class="box" node-id="<?php echo wa_html_entity_decode($nodeId); ?>">

          <div class="form-group">
              <label for="trigger_type"><?php echo _l('wa_trigger_type'); ?></label><br />

              <?php 
                  $trigger_types = [
                    ['id' => 'user_action', 'name' => _l('wa_user_action')],
                    ['id' => 'automatic', 'name' => _l('wa_automatic')],
                  ];
              ?>

              <?php echo render_select('trigger_type['.$nodeId.']', $trigger_types, ['id', 'name'], '', 'user_action', ['df-trigger_type' => '', 'onchange' => 'trigger_type(this); return false;', 'data-nodeid' => $nodeId]); ?>
          </div>

          <div id="user_action_div">  
            <div class="form-group">
                <label for="data_type"><?php echo _l('wa_data_type'); ?></label><br />

                <?php 
                    $types = wa_data_type_list();
                ?>

                <?php echo render_select('data_type['.$nodeId.']', $types, ['id', 'name'], '', '', ['df-data_type' => '', 'onchange' => 'data_type(this); return false;', 'data-nodeid' => $nodeId]); ?>
              </div>


              <div class="form-group">
                <label for="start_when"><?php echo _l('wa_start_when'); ?></label><br />

                <?php 
                    $start_cases = wa_get_start_case_by_type($workflow->type);
                ?>

                <?php echo render_select('start_when['.$nodeId.']', $start_cases, ['id', 'name'], '', '', ['df-start_when' => '']); ?>
              </div>
          </div>
           <div id="automatic_div" class="hide">
                <label for="repeat_every"><?php echo _l('wa_repeat_every'); ?></label><br />

                <?php 
                    $repeat_everys = [
                      ['id' => 'day', 'name' => _l('wa_day')],
                      ['id' => 'week', 'name' => _l('wa_week')],
                      ['id' => 'month', 'name' => _l('wa_month')],
                      ['id' => 'no_repeat', 'name' => _l('wa_no_repeat')],
                    ];
                ?>

                <?php echo render_select('repeat_every['.$nodeId.']', $repeat_everys, ['id', 'name'], '', 'user_action', ['df-repeat_every' => '', 'onchange' => 'repeat_every(this); return false;', 'data-nodeid' => $nodeId]); ?>

                <div id="repeat_div_<?php echo wa_html_entity_decode($nodeId); ?>">
                    
                </div>

           </div>

      </div>
    </div>
  </div>
</div>
