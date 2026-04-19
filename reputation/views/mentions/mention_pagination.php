<?php
$items_per_page = 10;
$total_pages = ceil($mention_total / $items_per_page);

$current_page = $page;
$current_page = max(1, min($total_pages, $current_page));

$visible_pages = 3;
$half = floor($visible_pages / 2);

$start = max(1, $current_page - $half);
$end = min($total_pages, $current_page + $half);

if ($end - $start + 1 < $visible_pages) {
    if ($start == 1) {
        $end = min($total_pages, $start + $visible_pages - 1);
    } elseif ($end == $total_pages) {
        $start = max(1, $end - $visible_pages + 1);
    }
} ?>

<div class="row pagination-card">
  <div class="col-md-4 no-padding">
    <div class="dataTables_info mtop5" role="status" aria-live="polite">
      <?php 
        $start_entry = ($current_page - 1) * $items_per_page + 1;
        $end_entry = min($mention_total, $current_page * $items_per_page);

        echo "Showing $start_entry to $end_entry of $mention_total entries";
      ?>
    </div>
  </div>
  <div class="col-md-8 dataTables_paging no-padding">
    <div id="colvis"></div>
    <div id="" class="dt-page-jump">
<?php
echo '<select onchange="load_more_mention_list(this.value)" class="dt-page-jump-select form-control no-margin-top">';
for ($i = 1; $i <= $total_pages; $i++) {
    $selected = $i == $current_page ? 'selected' : '';
    echo "<option value=\"$i\" $selected>$i</option>";
}
echo '</select>'; ?>


</div>
    <div class="dataTables_paginate paging_simple_numbers">

<?php
echo '<ul class="pagination no-margin pull-right">';

if ($current_page > 1) {
    echo '<li class="paginate_button previous"><a href="javascript:void(0)" onclick="load_more_mention_list(' . ($current_page - 1) . ')">'._l('dt_paginate_previous').'</a></li>';
} else {
    echo '<li class="paginate_button previous disabled"><a href="javascript:void(0)">'._l('dt_paginate_previous').'</a></li>';
}

if ($start > 1) {
    echo '<li class="paginate_button"><a href="javascript:void(0)" onclick="load_more_mention_list(1)">1</a></li>';
    if ($start > 2) echo '<li class="paginate_button disabled"><a href="javascript:void(0)">...</a></li>';
}

for ($i = $start; $i <= $end; $i++) {
  echo '<li class="paginate_button '.($current_page == $i ? 'active' : '').'"><a href="javascript:void(0)" onclick="load_more_mention_list('.$i.')">'.$i.'</a></li>';
}

if ($end < $total_pages) {
    if ($end < $total_pages - 1) echo '<li class="paginate_button disabled"><a href="javascript:void(0)">...</a></li>';
    echo '<li class="paginate_button"><a href="javascript:void(0)" onclick="load_more_mention_list('.$total_pages.')">'.$total_pages.'</a></li>';
}

if ($current_page < $total_pages) {
    echo '<li class="paginate_button"><a href="javascript:void(0)" onclick="load_more_mention_list('.($current_page + 1).')">'._l('dt_paginate_next').'</a></li>';
} else {
    echo '<li class="paginate_button next disabled"><a href="javascript:void(0)">'._l('dt_paginate_next').'</a></li>';
}



echo '</ul>';
  ?>
  </div>
  </div>
</div>