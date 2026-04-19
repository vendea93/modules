<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Lesson Content -->
<?php if ($lesson['lesson_type'] === 'file'): ?>
    <?php
    $file_source = isset($lesson['file_source']) ? $lesson['file_source'] : '';
    $file_url = isset($lesson['file_url']) ? $lesson['file_url'] : '';
    ?>
    
    <?php if ($file_source === 'youtube' && strpos($file_url, 'youtube.com/watch?v=') !== false): ?>
        <!-- YouTube Video -->
        <?php $youtube_video_id = explode('=', $file_url)[1]; 
        //if starts with &t=1110s, remove it
        if (strpos($youtube_video_id, '&t') !== false) {
            $youtube_video_id = explode('&t', $youtube_video_id)[0];
        } ?>
        
        <div class="flexacademy-video-embed">
            <iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_id; ?>" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    referrerpolicy="strict-origin-when-cross-origin" 
                    allowfullscreen>
            </iframe>
        </div>
    <?php elseif ($file_source === 'vimeo' && strpos($file_url, 'vimeo.com') !== false): ?>
        <?php $vimeo_video_id = explode('vimeo.com/', $file_url)[1]; ?>
        <!-- Vimeo Video -->
        <div class="flexacademy-video-embed">
            <iframe src="https://player.vimeo.com/video/<?php echo $vimeo_video_id; ?>" 
                    frameborder="0" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen>
            </iframe>
        </div>
    <?php elseif ($file_source === 'external-link' && !empty($file_url)): ?>
        <!-- External Video Link -->
        <div class="flexacademy-video-embed">
            <iframe src="<?php echo $file_url; ?>" 
                    frameborder="0" 
                    allowfullscreen>
            </iframe>
        </div>
    <?php elseif ($file_source === 'iframe' && !empty($file_url)): ?>
        <!-- Custom Iframe Embed -->
        <div class="flexacademy-video-embed">
            <?php echo $file_url; ?>
        </div>
    <?php elseif ($file_source === 'upload-file' && !empty($lesson['file_path'])): ?>
        <!-- Uploaded Video File -->
         <?php if (strpos($lesson['file_path'], '.jpg') !== false || strpos($lesson['file_path'], '.jpeg') !== false || strpos($lesson['file_path'], '.png') !== false || strpos($lesson['file_path'], '.gif') !== false || strpos($lesson['file_path'], '.bmp') !== false || strpos($lesson['file_path'], '.tiff') !== false || strpos($lesson['file_path'], '.ico') !== false || strpos($lesson['file_path'], '.webp') !== false): ?>
            <div class="flexacademy-media-embed">
                <img src="<?php echo flexacademy_media_url($lesson['file_path']); ?>" class="img img-responsive" alt="<?php echo $lesson['title']; ?>">
            </div>
         <?php elseif (strpos($lesson['file_path'], '.mp4') !== false || strpos($lesson['file_path'], '.mov') !== false || strpos($lesson['file_path'], '.avi') !== false || strpos($lesson['file_path'], '.wmv') !== false || strpos($lesson['file_path'], '.flv') !== false || strpos($lesson['file_path'], '.mpeg') !== false || strpos($lesson['file_path'], '.mpg') !== false): ?>
            <div class="flexacademy-media-embed">
                <embed controls class="flexacademy-media-player" id="flexacademy-lesson-video" src="<?php echo flexacademy_media_url($lesson['file_path']); ?>" type="video/mp4">
            </div>
         <?php elseif (strpos($lesson['file_path'], '.pdf') !== false): ?>
            <div class="flexacademy-media-embed">
               <iframe src="<?php echo flexacademy_media_url($lesson['file_path']); ?>" class="flexacademy-media-player" frameborder="0"></iframe>
            </div>
         <?php else: ?>
            <div class="flexacademy-media-embed">
                <embed controls class="flexacademy-media-player" id="flexacademy-lesson-video" src="<?php echo flexacademy_media_url($lesson['file_path']); ?>">
            </div>
         <?php endif; ?>
    <?php else: ?>
        <!-- Fallback: Download File -->
        <div class="flexacademy-file-placeholder">
            <div class="text-center p-5">
                <i class="fa fa-file-o fa-4x text-muted mb-3"></i>
                <h4><?php echo _flexacademy_lang('download-file'); ?></h4>
                <?php if (!empty($lesson['file_path'])): ?>
                    <a href="<?php echo flexacademy_media_url($lesson['file_path']); ?>" 
                       class="btn btn-primary flexacademy-btn-primary mt-3" 
                       download>
                        <i class="fa fa-download"></i> <?php echo _flexacademy_lang('download'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
<?php elseif ($lesson['lesson_type'] === 'text' && !empty($lesson['text_lesson'])): ?>
    <!-- Text Lesson -->
    <div class="flexacademy-text-lesson-content">
        <?php echo $lesson['text_lesson']; ?>
    </div>

<?php elseif ($lesson['lesson_type'] === 'quiz'): ?>
    <!-- Quiz Lesson -->
    <?php $this->load->view('flexacademy/partials/clients/lesson/quiz-content', [
        'lesson' => $lesson,
        'enrollment' => isset($enrollment) ? $enrollment : null
    ]); ?>
    
<?php else: ?>
    <!-- Placeholder -->
    <div class="flexacademy-content-placeholder">
        <div class="text-center p-5">
            <i class="fa fa-file-text-o fa-4x text-muted mb-3"></i>
            <h4><?php echo _flexacademy_lang('lesson-content'); ?></h4>
            <p class="text-muted"><?php echo _flexacademy_lang('lesson-content-coming-soon'); ?></p>
        </div>
    </div>
<?php endif; ?>

