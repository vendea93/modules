<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php $this->load->view('partials/course-form', ['course' => $course, 'title' => $title, 'action' => $action]); ?>
    </div>
</div>
<?php init_tail(); ?>
