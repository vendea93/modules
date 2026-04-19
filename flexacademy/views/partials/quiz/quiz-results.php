<div class="table-responsive">
    <table class="table table-striped table-borderless">
        <thead>
            <tr>
                <th><?php echo _flexacademy_lang('student'); ?></th>
                <th><?php echo _flexacademy_lang('score'); ?></th>
                <th><?php echo _flexacademy_lang('status'); ?></th>
                <th><?php echo _flexacademy_lang('actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ([] as $result): ?>
                <tr>
                    <td><?php echo $result['student_name']; ?></td>
                    <td><?php echo $result['score']; ?></td>
                    <td><?php echo $result['status']; ?></td>
                    <td>
                        <a href="<?php echo admin_url('flexacademy/quiz_results/' . $result['quiz_id']); ?>" class="btn btn-link btn-sm flexacademy-quiz-results" title="<?php echo _flexacademy_lang('quiz-results'); ?>">
                            <?php echo _flexacademy_lang('quiz-results'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>