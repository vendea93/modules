
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

  $label = 'default';
  if($mention['sentiment'] == 'Positive'){
    $label = 'success';
  }

  if($mention['sentiment'] == 'Negative'){
    $label = 'danger';
  }

  $status = $statuses[$mention['sentiment']];
 

  switch ($mention['platform']) {
    case 'facebook':
      $ima_url = site_url('modules/reputation/assets/images/facebook_icon.png');
      $site = _l('facebook');
      $title = $mention['author_name'];
      break;
    case 'youtube':
      $ima_url = site_url('modules/reputation/assets/images/youtube_icon.png');
      $site = $mention['author_name'];
      $title = $mention['title'];
      break;

    case 'x_twitter':
      $ima_url = site_url('modules/reputation/assets/images/twitter_icon.png');
      $site = _l('twitter');
      $title = $mention['author_name'];
      break;
    
    default:
      $ima_url = site_url('modules/reputation/assets/images/Google_News_icon.png');
      $site = $mention['site'];
      $title = $mention['title'];
      break;
  }

  
  $tags = get_tags_in($mention['id'], 'rep_mention');

  ?>
<?php 
  if($mention['visit'] == 1){
  ?>

    <div class="wrap">
        <div class="ribbon ribbon-top-left info"><span><i class="fa fa-eye"></i></span></div>
    </div>
    <?php 
  }

  $view = '';
  if($mention['pageviews'] != '' && $mention['pageviews'] > 0){
    $view = ' · '.$mention['pageviews'].' '. _l('views');
  }

  $like = '';
  if($mention['likes'] != '' && $mention['likes'] > 0){
    $like = ' · '.$mention['likes'].' '. _l('likes');
  }

  $comment = '';
  if($mention['comments'] != '' && $mention['comments'] > 0){
    $comment = ' · '.$mention['comments'].' '. _l('comments');
  }

  $share = '';
  if($mention['shares'] != '' && $mention['shares'] > 0){
    $share = ' · '.$mention['shares'].' '. _l('shares');
  }

  ?>
  <div class="card-header">
    <div class="site-info">

      <img src="<?php echo e($ima_url); ?>" alt="Logo" class="site-logo">
      <div>
        <div class="site-title"><?php echo e($title) ?></div>
        <div class="site-meta"><?php echo e($site) ?> <?php echo e($view.$like.$comment.$share) ?> · <i class="fa fa-calendar"></i> <?php echo e($mention['time']) ?></div>
      </div>
    </div>
    <span class="label border_1_solid" style="color:<?php echo e($status['color']); ?>;border-color: <?php echo adjust_hex_brightness($status['color'], 0.4); ?>;background: <?php echo adjust_hex_brightness($status['color'], 0.04); ?>;" task-status-table="<?php echo e($mention['status']); ?>">
      <?php echo e($status['name']); ?>
      <div class="dropdown inline-block mleft5 table-export-exclude">
        <a href="javascript:void(0)" class="dropdown-toggle text-dark rep_mention_sentiment_btn" id="tableTaskStatus-<?php echo e($mention['id']); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span data-toggle="tooltip" title="<?php echo _l('ticket_single_change_status'); ?>"><i class="fa-solid fa-chevron-down tw-opacity-70"></i></span>
        </a>

        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableTaskStatus-<?php echo e($mention['id']); ?>">
          <?php foreach ($statuses as $key => $taskChangeStatus) {
            if ($mention['sentiment'] != $taskChangeStatus['id']) { ?>
              <li>
                <a href="javascript:void(0)" onclick="mention_sentiment_mark_as('<?php echo e($taskChangeStatus['id']); ?>','<?php echo e($mention['id']); ?>'); return false;">
                  <?php echo e(_l('task_mark_as', $taskChangeStatus['name'])); ?>
                </a>
              </li>
            <?php  }
          } ?>
        </ul>
      </div>
    </span>
  </div>

  <div class="card-description">
    <?php echo html_entity_decode($mention['content']) ?>
  </div>

  <div class="card-tags mtop10">
    <?php if($mention['add_to_pdf'] == 1){ ?>
    <span class="label pdf-label-tag tag-id-1"><?php echo _l('pdf_mentions'); ?></span>
    <?php } ?>
    <?php echo render_tags($tags); ?>
  </div>
  <div class="card-footer">
    <span><a href="javascript:void(0)" class="btn text-dark" onclick="visit_mention('<?php echo e($mention['id']); ?>', '<?php echo e($mention['link']) ?>'); return false;"><i class="fa fa-link"></i> <?php echo _l('visit'); ?></a></span>
    
    <span><a href="javascript:void(0)" class="btn text-dark" onclick="add_tags('<?php echo e($mention['id']); ?>'); return false;"><i class="fa fa-tag"></i> <?php echo _l('tags'); ?></a></span>

    <span><a href="javascript:void(0)" class="btn text-dark" onclick="delete_mention('<?php echo e($mention['id']); ?>'); return false;"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a></span>

    <?php if($mention['add_to_pdf'] == 1){ ?>
      <span><a href="javascript:void(0)" class="btn text-dark" onclick="remove_from_pdf_report('<?php echo e($mention['id']); ?>'); return false;"><i class="fa fa-file-text"></i> <?php echo _l('remove_from_pdf_report'); ?></a></span>
    <?php }else{ ?>
      <span><a href="javascript:void(0)" class="btn text-dark" onclick="add_to_pdf_report('<?php echo e($mention['id']); ?>'); return false;"><i class="fa fa-file-text"></i> <?php echo _l('add_to_pdf_report'); ?></a></span>
    <?php } ?>

    <span><a href="javascript:void(0)" class="btn text-dark hide" onclick="mute_site('<?php echo e($mention['id']); ?>'); return false;"><i class="fa fa-volume-off"></i> <?php echo _l('mute_site'); ?></a></span>
  </div>
