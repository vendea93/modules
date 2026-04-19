<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Lesson Header -->
<div class="flexacademy-lesson-header">
    <div class="flexacademy-lesson-breadcrumb">
        <a href="<?php echo site_url('flexacademy/course/player/' . $course['slug']); ?>" class="flexacademy-breadcrumb-link">
            <i class="fa fa-arrow-left"></i> <?php echo htmlspecialchars($course['title']); ?>
        </a>
    </div>
    
    <div class="flexacademy-lesson-title-section">
        <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
        
        <div class="flexacademy-lesson-meta">
            <?php if ($lesson['duration'] > 0): ?>
                <span class="flexacademy-lesson-duration">
                    <i class="fa fa-clock-o"></i> 
                    <?php echo flexacademy_convert_duration_from_minutes($lesson['duration']); ?>
                </span>
            <?php endif; ?>
            
            <?php if ($lesson['is_free']): ?>
                <span class="flexacademy-lesson-free">
                    <i class="fa fa-unlock"></i> <?php echo _flexacademy_lang('free'); ?>
                </span>
            <?php endif; ?>
            
            <span class="flexacademy-lesson-type">
                <i class="fa fa-file-text"></i> <?php echo ucfirst($lesson['lesson_type']); ?>
            </span>
        </div>
    </div>
</div>

