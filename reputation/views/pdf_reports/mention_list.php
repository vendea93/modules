
<?php 
$statuses = [ 
            'Neutral' => [
                'id'             => 'Neutral',
                'color'          => '#64748b',
                'name'           => _l('neutral'),
                'order'          => 1,
                'filter_default' => true,
            ],
            'Positive' => [
                'id'             => 'Positive',
                'color'          => '#22c55e',
                'name'           => _l('positive'),
                'order'          => 2,
            ],
            'Negative' => [
                'id'             => 'Negative',
                'color'          => '#d9534f',
                'name'           => _l('negative'),
                'order'          => 3,
            ],
        ];
?>

<h3 class="bold"><?php echo _l('mention_list'); ?></h3>
<?php foreach ($mention_list as $key => $value) { 
  $label = 'default';
  if($value['sentiment'] == 'Positive'){
    $label = 'success';
  }

  if($value['sentiment'] == 'Negative'){
    $label = 'danger';
  }

  $status = $statuses[$value['sentiment']] ?? [
                'id'             => 'Neutral',
                'color'          => '#64748b',
                'name'           => _l('neutral'),
                'order'          => 1,
                'filter_default' => true,
            ];
  $tags = get_tags_in($value['id'], 'rep_mention');

  ?>
  <div class="comment-card" id="mention-card-<?php echo e($value['id']) ?>">
    <?php $this->load->view('mention_detail', ['mention' => $value]); ?>
  </div>
<?php } ?>
