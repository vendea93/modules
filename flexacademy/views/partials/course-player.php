<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$backLink = isset($back_url) && $back_url ? $back_url : site_url('flexacademy/my-courses');
$basePlayerUrl = isset($player_base_url) && $player_base_url ? rtrim($player_base_url, '/') . '/' : site_url('flexacademy/course/player/');
?>

<div class="flexacademy-player-layout">
    <!-- Left: Video Player & Content -->
    <div class="panel_s tw-flex-1">
        <!-- Course Title Header -->
        <div class="mtop10 tw-pl-4 mbot10">
            <a href="<?php echo htmlspecialchars($backLink); ?>" class="btn btn-link tw-text-lg">
                <i class="fa fa-arrow-left"></i>
            </a>
            <span class="tw-text-lg tw-font-semibold">
                <?php echo htmlspecialchars($course['title']); ?>
            </span>
        </div>

        <!-- Video/Content Player -->
        <div class="flexacademy-lesson-content-wrapper">
            <?php $this->load->view('flexacademy/partials/clients/lesson/lesson-content', [
                'lesson' => $lesson,
                'enrollment' => isset($enrollment) ? $enrollment : null,
            ]); ?>
        </div>

        <!-- Tabs: Summary, Forum, Review -->
        <div class="flexacademy-player-tabs">
            <button id="summary-tab" class="flexacademy-tab-btn flexacademy-tab-active" data-tab="summary">
                <?php echo _flexacademy_lang('summary'); ?>
            </button>
            <button id="certificate-tab" class="flexacademy-tab-btn" data-tab="certificate">
                <?php echo _flexacademy_lang('certificate'); ?>
            </button>
        </div>

        <!-- Tab Content -->
        <div class="flexacademy-player-tab-content">
            <div class="flexacademy-tab-pane flexacademy-tab-pane-active" id="summary-pane">
                <?php if (!empty($lesson['summary'])) { ?>
                    <div class="flexacademy-summary-content">
                        <?php echo $lesson['summary']; ?>
                    </div>
                <?php } else { ?>
                    <p class="text-muted"><?php echo _flexacademy_lang('no-summary-available'); ?></p>
                <?php } ?>
            </div>

            <div class="flexacademy-tab-pane" id="certificate-pane">
                <?php $this->load->view('flexacademy/partials/course_player/certificate-tab', [
                    'enrollment' => $enrollment,
                    'course' => $course,
                    'certificate' => isset($certificate) ? $certificate : null,
                    'certificate_url' => isset($certificate_url) ? $certificate_url : null,
                ]); ?>
            </div>
        </div>
    </div>


    <?php $this->load->view('flexacademy/partials/clients/lesson/lesson-sidebar', [
        'lesson' => $lesson,
        'course' => $course,
        'sections' => $sections,
        'lesson_progress' => $lesson_progress,
        'enrollment' => $enrollment,
        'player_base_url' => $basePlayerUrl,
    ]); ?>
</div>
