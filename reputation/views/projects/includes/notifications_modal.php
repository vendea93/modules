<?php 
  $id = '';
  $email = '';
  $frequency = 'every_hour';
  $frequency_day = 'every_hour';
  $frequency_day_of_week = '';
  $frequency_time = '';
  $visited = 'all';
  $sources = '';
  $sentiment = '';
  $tags = '';

  if(isset($notification)){
    $id = $notification->id;
    $email = $notification->email;
    $frequency = $notification->frequency;
    $frequency_day = $notification->frequency_day;
    $frequency_day_of_week = $notification->frequency_day_of_week;
    $frequency_time = $notification->frequency_time;
    $visited = $notification->visited;
    $sources = explode(',', $notification->sources);
    $sentiment = explode(',', $notification->sentiment);
    $tags = $notification->tags;
  }
?>


<?php echo form_hidden('id', $id); ?>
<?php echo render_input('email', 'email_address', $email, 'email'); ?>
<?php 
    $frequency_arr = [
      ['id' => 'every_hour', 'name' => _l('every_hour')],
      ['id' => 'every_6_hour', 'name' => _l('every_6_hour')],
      ['id' => 'every_12_hour', 'name' => _l('every_12_hour')],
      ['id' => 'once_a_day', 'name' => _l('once_a_day')],
      ['id' => 'every_week', 'name' => _l('every_week')],
      ['id' => 'every_month', 'name' => _l('every_month')],
    ];
    echo render_select('frequency', $frequency_arr, array('id', 'name'), 'frequency', $frequency, [], [], '', '', false);
    ?>
    
    <div class="frequency_day hide">
      <?php 
      $frequency_day_arr = [];
      for ($i=1; $i <= 31; $i++) { 
        $frequency_day_arr[] = ['id' => $i, 'name' => $i];
      } 

      $frequency_day_arr[] = ['id' => 'last', 'name' => _l('end_of_the_month')];
      ?>
      <?php echo render_select('frequency_day', $frequency_day_arr, array('id', 'name'), 'frequency_day', $frequency_day, [], [], '', '', false); ?>
    </div>
    <div class="frequency_day_of_week hide">
      <?php 
      $frequency_day_of_week_arr = [
      ['id' => 'monday', 'name' => _l('monday')],
      ['id' => 'tuesday', 'name' => _l('tuesday')],
      ['id' => 'wednesday', 'name' => _l('wednesday')],
      ['id' => 'thursday', 'name' => _l('thursday')],
      ['id' => 'friday', 'name' => _l('friday')],
      ['id' => 'saturday', 'name' => _l('saturday')],
      ['id' => 'sunday', 'name' => _l('sunday')],
    ];
     echo render_select('frequency_day_of_week', $frequency_day_of_week_arr, array('id', 'name'), 'frequency_day_of_week', $frequency_day_of_week, [], [], '', '', false); ?>
    </div>
    <div class="frequency_time hide">
      <?php echo render_input('frequency_time', 'frequency_time', $frequency_time, 'time'); ?>
    </div>

            <?php 
          $date_filter_visited = [
                  1 => ['id' => 'all', 'name' => _l('all')],
                  2 => ['id' => '1', 'name' => _l('only_visited')],
                  3 => ['id' => '0', 'name' => _l('only_not_visited')],
                 ];
          echo render_select('visited', $date_filter_visited, array('id', 'name'),'visited', $visited, array(), array(), '', '', false);
          ?>
      <?php 
        $sources_arr = [
          ['id' => 'x_twitter', 'name' => _l('x_twitter')],
          ['id' => 'google_news', 'name' => _l('google_news')],
          ['id' => 'youtube', 'name' => _l('youtube')],
          ['id' => 'facebook', 'name' => _l('facebook')],
          ['id' => 'instagram', 'name' => _l('instagram')],
        ];
        ?>
        <?php echo render_select('sources[]',$sources_arr,array('id','name'),'sources', $sources, array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>
      <?php 
      $date_filter_sentiment = [
              ['id' => 'Neutral', 'name' => _l('neutral')],
            ['id' => 'Positive', 'name' => _l('positive')],
              ['id' => 'Negative', 'name' => _l('negative')],
             ];
      ?>
        <?php echo render_select('sentiment[]',$date_filter_sentiment,array('id','name'),'sentiment', $sentiment, array('multiple' => true, 'data-actions-box' => true), array(), '', '', false); ?>


        <div class="form-group">
            <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
            <?php echo _l('tags'); ?></label>
            <input type="text" class="tagsinput" id="tags" name="tags"
            value="<?php echo e($tags); ?>"
            data-role="tagsinput">
        </div>