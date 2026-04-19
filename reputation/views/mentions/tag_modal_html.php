<?php echo form_hidden('mention_id', $mention_id); ?>
<div class="form-group">
    <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
        <?php echo _l('tags'); ?></label>
    <input type="text" class="tagsinput" id="tags" name="tags"
        value="<?php echo(prep_tags_input(get_tags_in($mention_id, 'rep_mention'))); ?>"
        data-role="tagsinput">
</div>