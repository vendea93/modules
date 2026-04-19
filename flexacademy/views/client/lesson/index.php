<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php $this->load->view('flexacademy/partials/course-player', [
    'lesson' => $lesson,
    'course' => $course,
    'sections' => $sections,
    'lesson_progress' => $lesson_progress,
    'enrollment' => $enrollment,
    'back_url' => isset($back_url) ? $back_url : site_url('flexacademy/my-courses'),
    'player_base_url' => isset($player_base_url) ? $player_base_url : site_url('flexacademy/course/player/'),
]); ?>
