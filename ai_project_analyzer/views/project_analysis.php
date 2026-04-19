<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="ai-max-w-6xl ai-mx-auto">
    <?php
    $this->load->view('ai_project_analyzer/analysis/analysis');
    $this->load->view('ai_project_analyzer/analysis/loading_modal');
    ?>
</div>
<?php
if (staff_can('create', 'ai_project_analyzer')) {
    $this->load->view('ai_project_analyzer/analysis/generate');
}
?>