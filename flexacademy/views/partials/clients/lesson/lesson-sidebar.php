<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Course Curriculum Sidebar -->
<div class="panel_s flexacademy-player-sidebar">
    <div class="flexacademy-sidebar-content">
        <!-- Progress Bar -->
        <?php
        $progress_percentage = isset($enrollment) ? round((float)$enrollment->progress, 2) : 0;
        $total_lessons = flexacademy_get_course_total_lessons($course['id']);
        $completed_lessons = 0;
        foreach ($sections as $section) {
            foreach ($section['lessons'] as $lesson_item) {
                if (!empty($lesson_item['progress'])) {
                    $completed_lessons++;
                }
            }
        }
        $base_player_url = isset($player_base_url) && $player_base_url !== null && $player_base_url !== ''
            ? rtrim($player_base_url, '/') . '/'
            : site_url('flexacademy/course/player/');
        ?>
        <div class="flexacademy-sidebar-progress">
            <div class="flexacademy-progress-bar-wrapper">
                <div class="flexacademy-progress-bar-bg">
                    <div class="flexacademy-progress-bar-green" style="width: <?php echo $progress_percentage; ?>%"></div>
                </div>
                <span class="flexacademy-progress-text">
                    <?php echo $progress_percentage; ?>% <?php echo _flexacademy_lang('completed'); ?> 
                    <?php echo $completed_lessons; ?>/<?php echo $total_lessons; ?>
                </span>
            </div>
        </div>
        
        <!-- Lessons List -->
        <div class="flexacademy-curriculum-list">
            <?php foreach ($sections as $index => $section): ?>
                <div class="flexacademy-curriculum-section">
                    <button class="flexacademy-section-header" data-section-id="<?php echo $section['id']; ?>">
                        <div class="flexacademy-section-title">
                            <i class="fa fa-chevron-down flexacademy-section-icon"></i>
                            <span><?php echo ($index + 1) . '. ' . htmlspecialchars($section['title']); ?></span>
                        </div>
                    </button>
                    
                    <div class="flexacademy-section-lessons">
                        <?php foreach ($section['lessons'] as $lesson_item): ?>
                            <?php
                            $is_completed = !empty($lesson_item['progress']);
                            $is_current = isset($lesson_item['is_current']) && $lesson_item['is_current'];
                            $lesson_url = site_url('flexacademy/course/player/' . $course['slug'] . '/' . $lesson_item['id']);
                            ?>
                            <div class="flexacademy-lesson-item-wrapper">
                                <label class="flexacademy-lesson-checkbox-wrapper">
                                    <input type="checkbox" 
                                           class="flexacademy-lesson-checkbox" 
                                           data-lesson-id="<?php echo $lesson_item['id']; ?>"
                                           <?php echo $is_completed ? 'checked' : ''; ?>>
                                    <span class="flexacademy-checkbox-custom">
                                        <i class="fa fa-check flexacademy-checkbox-icon"></i>
                                    </span>
                                </label>
                                
                                <a href="<?php echo $base_player_url . $course['slug'] . '/' . $lesson_item['id']; ?>" 
                                   class="flexacademy-lesson-item <?php echo $is_current ? 'flexacademy-lesson-current' : ''; ?>">
                                    <div class="flexacademy-lesson-icon">
                                        <i class="fa <?php echo flexacademy_lesson_type_icon($lesson_item); ?>"></i>
                                    </div>
                                    <div class="flexacademy-lesson-details">
                                        <div class="flexacademy-lesson-title">
                                            <?php echo htmlspecialchars($lesson_item['title']); ?>
                                        </div>
                                        <?php if ($lesson_item['duration'] > 0): ?>
                                            <div class="flexacademy-lesson-duration">
                                                <?php echo flexacademy_convert_duration_from_minutes($lesson_item['duration']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Finish Course Button
        <div class="flexacademy-sidebar-footer">
            <button class="btn btn-primary btn-lg flexacademy-btn-primary btn-block" id="flexacademy-finish-course-btn">
                <?php echo _flexacademy_lang('finish-course'); ?>
            </button>
        </div> -->
    </div>
</div>

