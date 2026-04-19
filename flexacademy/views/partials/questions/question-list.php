     <table class="table table-bordered">
         <thead>
             <tr>
                 <th><?php echo _flexacademy_lang('question-type'); ?></th>
                 <th><?php echo _flexacademy_lang('question'); ?></th>
                 <th><?php echo _flexacademy_lang('correct-answer'); ?></th>
                 <th><?php echo _flexacademy_lang('actions'); ?></th>
             </tr>
         </thead>
         <tbody>
             <?php foreach ($questions as $question): ?>
                 <tr>
                     <td><?php echo $question['question_type']; ?></td>
                     <td><?php echo $question['question']; ?></td>
                     <td>
                        <?php if($question['question_type'] == "true-false"): ?>
                            <?php if($question['correct_answer'] == 1): ?>
                                <?php echo _flexacademy_lang('true'); ?>
                            <?php else: ?>
                                <?php echo _flexacademy_lang('false'); ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo $question['correct_answer']; ?>
                        <?php endif; ?>
                    </td>
                     <td>
                         <a href="javascript:void(0)"
                             data-question-id="<?php echo $question['id']; ?>"
                             data-question-type="<?php echo $question['question_type']; ?>"
                             data-question="<?php echo $question['question']; ?>"
                             data-correct-answer="<?php echo $question['correct_answer']; ?>"
                             data-options="<?php echo $question['options']; ?>"
                             class="btn btn-primary flexacademy-quiz-questions-cta"><i class="fa-solid fa-pencil"></i></a>
                         <a href="javascript:void(0)"
                             data-question-id="<?php echo $question['id']; ?>"
                             data-msg="<?php echo _flexacademy_lang('delete-question-msg'); ?>"
                             class="btn btn-danger flexacademy-quiz-questions-delete-question"><i class="fa-solid fa-trash"></i></a>
                     </td>
                 </tr>
             <?php endforeach; ?>
         </tbody>
     </table>