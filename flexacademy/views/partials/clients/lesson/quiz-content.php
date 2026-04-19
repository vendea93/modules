<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (empty($lesson['quiz_data'])) {
    echo '<div class="alert alert-danger">' . _flexacademy_lang('quiz-not-found') . '</div>';
    return;
}

$quiz = $lesson['quiz_data']['quiz'];
$questions = $lesson['quiz_data']['questions'];
$attempts = $lesson['quiz_data']['attempts'];
$attempt_count = $lesson['quiz_data']['attempt_count'];
$best_score = $lesson['quiz_data']['best_score'];
$can_retake = $lesson['quiz_data']['can_retake'];
$active_attempt = $lesson['quiz_data']['active_attempt'];
$lesson_completed = isset($lesson['quiz_data']['lesson_completed']) ? $lesson['quiz_data']['lesson_completed'] : false;
?>

<div class="flexacademy-quiz-wrapper">
    <div class="flexacademy-quiz-container">
        <?php if ($active_attempt) { ?>
            <div class="flexacademy-quiz-header">
                <div><?php echo $quiz['title']; ?></div>
                <div class="flexacademy-quiz-header-right">
                    <?php if (!empty($quiz['time_limit']) && $quiz['time_limit'] > 0) { ?>
                        <div class="flexacademy-quiz-timer">
                            <i class="fa fa-clock"></i>
                            <span id="quiz-timer-display">--:--</span>
                        </div>
                    <?php } ?>
                    <div class="flexacademy-quiz-info">
                        <?php echo _flexacademy_lang('total-questions'); ?>: <?php echo count($questions); ?> | 
                        <?php echo _flexacademy_lang('total-marks'); ?>: <?php echo $quiz['total_marks']; ?>
                    </div>
                </div>
            </div>
            <div class="flexacademy-quiz-divider"></div>
            
            
            <form id="quiz-form" 
                  data-attempt-id="<?php echo $active_attempt['id']; ?>" 
                  data-quiz-id="<?php echo $quiz['id']; ?>"
                  data-start-time="<?php echo $active_attempt['start_time']; ?>"
                  data-time-limit="<?php echo $quiz['time_limit'] * 60; ?>"
                  data-time-limit-message="<?php echo _flexacademy_lang('time-is-up'); ?>"
                  data-confirm-message="<?php echo _flexacademy_lang('confirm-submit-quiz'); ?>"
                  data-submit-text="<?php echo _flexacademy_lang('submit-quiz'); ?>"
                  data-total-questions="<?php echo count($questions); ?>">
                <input type="hidden" name="attempt_id" value="<?php echo $active_attempt['id']; ?>">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                
                <div class="flexacademy-quiz-questions-container">
                    <?php foreach ($questions as $index => $question) { ?>
                        <div class="flexacademy-quiz-question-slide" data-question-index="<?php echo $index; ?>">
                            <div class="flexacademy-question-text">
                                <?php echo $question['question']; ?>
                            </div>
                            
                                <div class="flexacademy-question-options">
                                    <?php if ($question['question_type'] === 'multiple_choice') { ?>
                                        <?php 
                                        $options = explode('::', $question['options']);
                                        foreach ($options as $option) { 
                                            if (trim($option) !== '') {
                                        ?>
                                        <label class="flexacademy-option-label">
                                            <input type="radio" name="answer_<?php echo $question['id']; ?>" 
                                                   value="<?php echo htmlspecialchars(trim($option)); ?>"
                                                   class="flexacademy-option-radio">
                                            <span class="flexacademy-option-text"><?php echo htmlspecialchars(trim($option)); ?></span>
                                            </label>
                                            <?php } ?>
                                        <?php } ?>
                                        
                                    <?php } elseif ($question['question_type'] === 'true_false' || $question['question_type'] === 'true-false') { ?>
                                    <label class="flexacademy-option-label">
                                        <input type="radio" name="answer_<?php echo $question['id']; ?>" 
                                               value="True" class="flexacademy-option-radio">
                                        <span class="flexacademy-option-text"><?php echo _flexacademy_lang('true'); ?></span>
                                    </label>
                                    <label class="flexacademy-option-label">
                                        <input type="radio" name="answer_<?php echo $question['id']; ?>" 
                                               value="False" class="flexacademy-option-radio">
                                        <span class="flexacademy-option-text"><?php echo _flexacademy_lang('false'); ?></span>
                                        </label>
                                        
                                    <?php } else { ?>
                                    <input type="text" name="answer_<?php echo $question['id']; ?>" 
                                           class="flexacademy-short-answer-input" 
                                           placeholder="<?php echo _flexacademy_lang('your-answer'); ?>">
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="flexacademy-quiz-nav-buttons">
                    <button type="button" class="btn btn-default" id="quiz-back-btn">
                        <i class="fa fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn btn-primary flexacademy-btn-primary" id="quiz-next-btn">
                        Next <i class="fa fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-primary flexacademy-btn-primary flexacademy-quiz-submit-hidden" id="quiz-submit-btn">
                        <i class="fa fa-check"></i> <?php echo _flexacademy_lang('submit-quiz'); ?>
                    </button>
                </div>
            </form>
            
        <?php } elseif (empty($questions)) { ?>
            <div class="flexacademy-quiz-empty">
                <i class="fa fa-exclamation-triangle"></i>
                <p><?php echo _flexacademy_lang('no-questions-in-quiz'); ?></p>
            </div>
        
        <?php 
        } elseif ($lesson_completed) { 
        ?>
            <div class="flexacademy-quiz-empty flexacademy-quiz-completed">
                <i class="fa fa-check-circle text-success flexacademy-quiz-icon-large"></i>
                <h4 class="text-success"><?php echo _flexacademy_lang('quiz-completed'); ?></h4>
                <p><?php echo _flexacademy_lang('you-have-successfully-completed-this-quiz'); ?></p>
                <?php if ($best_score !== null) { ?>
                    <p class="flexacademy-quiz-best-score">
                        <?php echo _flexacademy_lang('your-score'); ?>: 
                        <strong class="text-success"><?php echo round($best_score, 2); ?>%</strong>
                    </p>
                <?php } ?>
                <?php if ($can_retake) { ?>
                    <p class="text-muted mtop15">
                        <small><?php echo _flexacademy_lang('you-can-retake-quiz-to-improve-score'); ?></small>
                    </p>
                    <button type="button" id="start-quiz-btn" class="btn btn-default mtop10"
                            data-error-start-quiz="<?php echo _flexacademy_lang('error-start-quiz'); ?>"
                            data-quiz-id="<?php echo $quiz['id']; ?>"
                            data-enrollment-id="<?php echo $enrollment->id; ?>">
                        <i class="fa fa-refresh"></i> <?php echo _flexacademy_lang('retake-quiz-optional'); ?>
                    </button>
                <?php } ?>
            </div>
            
        <?php } elseif (!$can_retake) { ?>
            <div class="flexacademy-quiz-empty">
                <i class="fa fa-lock"></i>
                <h4><?php echo _flexacademy_lang('max-attempts-reached'); ?></h4>
                <p><?php echo _flexacademy_lang('you-have-completed-all-attempts'); ?></p>
                <?php if ($best_score !== null) { ?>
                    <p class="flexacademy-quiz-best-score">
                        <?php echo _flexacademy_lang('your-best-score'); ?>: 
                        <strong><?php echo round($best_score, 2); ?>%</strong>
                    </p>
                <?php } ?>
            </div>
            
        <?php } else { ?>
            <div class="flexacademy-quiz-empty">
                <i class="fa fa-play-circle"></i>
                <h4><?php echo _flexacademy_lang('ready-to-start-quiz'); ?></h4>
                <p>
                    <?php echo _flexacademy_lang('quiz-has'); ?> <?php echo count($questions); ?> <?php echo _flexacademy_lang('questions'); ?>.
                    <?php echo _flexacademy_lang('time-limit'); ?>: <?php echo $quiz['time_limit']; ?> <?php echo _flexacademy_lang('minutes'); ?>.
                </p>
                
                <?php if (!empty($attempts)) { ?>
                    <p class="flexacademy-quiz-attempts">
                        <?php echo _flexacademy_lang('previous-attempts'); ?>: <?php echo $attempt_count; ?>
                        <?php if ($quiz['retake_limit'] > 0) { ?>
                            / <?php echo $quiz['retake_limit']; ?>
                        <?php } ?>
                        <?php if ($best_score !== null) { ?>
                            | <?php echo _flexacademy_lang('best-score'); ?>: <strong><?php echo round($best_score, 2); ?>%</strong>
                        <?php } ?>
                    </p>
                <?php } ?>
                
                <button type="button" id="start-quiz-btn" class="btn small btn-primary flexacademy-btn-primary" 
                        data-error-start-quiz="<?php echo _flexacademy_lang('error-start-quiz'); ?>"
                        data-quiz-id="<?php echo $quiz['id']; ?>"
                        data-enrollment-id="<?php echo $enrollment->id; ?>">
                    <i class="fa fa-<?php echo $attempt_count > 0 ? 'refresh' : 'play'; ?>"></i>
                    <?php echo $attempt_count > 0 ? _flexacademy_lang('retake-quiz') : _flexacademy_lang('start-quiz'); ?>
                </button>
            </div>
        <?php } ?>
    </div>
</div>



