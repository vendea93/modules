<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-md-8">

        <!-- Add Note -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-comment tw-mr-2"></i>
					<?php echo _l('add_note'); ?>
                </h4>

                <form id="add-note-form">
                    <div class="form-group">
                        <textarea class="form-control" name="description" rows="4"
                                  placeholder="<?php echo _l('add_note_placeholder'); ?>" required></textarea>
                    </div>
                    <div class="checkbox">
                        <input type="checkbox" name="visible_to_client" id="visible_to_client" value="1">
                        <label for="visible_to_client">
							<?php echo _l('visible_to_client'); ?>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo _l('save_note'); ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Notes Timeline -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-list tw-mr-2"></i>
					<?php echo _l('notes_activity'); ?>
                </h4>

                <div class="activity-feed" id="notes-feed">

					<?php if (empty($notes)): ?>
                        <div class="text-center text-muted" id="no-notes-message">
                            <i class="fa fa-comment-o fa-3x tw-mb-3"></i>
                            <p><?php echo _l('no_notes_yet'); ?></p>
                        </div>
					<?php else: ?>
						<?php $note_count = count($notes);
						$current = 0; ?>
						<?php foreach ($notes as $note): $current++; ?>
                            <div class="feed-item" data-note-id="<?php echo $note['id']; ?>">
                                <div class="media">
                                    <div class="media-left">
                                        <img src="<?php echo staff_profile_image_url($note['created_by'], 'thumb'); ?>"
                                             class="img-circle" width="40">
                                    </div>
                                    <div class="media-body">
                                        <div class="feed-header">
                                            <strong><?php echo $note['staff_name']; ?></strong>
                                            <span class="text-muted"><?php echo _l('added_a_note'); ?></span>
											<?php if ($note['visible_to_client']): ?>
                                                <span class="label label-success pull-right"><?php echo _l('client_visible'); ?></span>
											<?php else: ?>
                                                <span class="label label-default pull-right"><?php echo _l('internal'); ?></span>
											<?php endif; ?>
                                        </div>
                                        <div class="feed-time text-muted">
                                            <i class="fa fa-clock-o"></i> <?php echo _dt($note['created_at']); ?>
											<?php if ($note['updated_at']): ?>
                                                <span class="text-muted"> (<?php echo _l('updated'); ?>: <?php echo _dt($note['updated_at']); ?>)</span>
											<?php endif; ?>
                                        </div>
                                        <div class="feed-content mt-2">
                                            <p><?php echo nl2br(htmlspecialchars($note['description'])); ?></p>
                                        </div>
                                        <div class="feed-actions mt-2">
											<?php if ($note['created_by'] == get_staff_user_id() || is_admin()): ?>
                                                <button class="btn btn-default btn-xs edit-note" data-id="<?php echo $note['id']; ?>">
                                                    <i class="fa fa-pencil"></i> <?php echo _l('edit'); ?>
                                                </button>
                                                <button class="btn btn-danger btn-xs delete-note" data-id="<?php echo $note['id']; ?>">
                                                    <i class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                                                </button>
											<?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

							<?php if ($current < $note_count): ?>
                                <hr class="feed-divider">
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>

                </div>

            </div>
        </div>

    </div>

    <!-- Right Sidebar -->
    <div class="col-md-4">

        <!-- Activity Summary -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-bar-chart tw-mr-2"></i>
					<?php echo _l('activity_summary'); ?>
                </h5>
                <div class="tw-space-y-3" id="notes-stats">
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('total_notes'); ?>:</span>
                        <strong id="stat-total"><?php echo $notes_stats['total']; ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('internal_notes'); ?>:</span>
                        <strong id="stat-internal"><?php echo $notes_stats['internal']; ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('client_visible_notes'); ?>:</span>
                        <strong class="text-success" id="stat-client-visible"><?php echo $notes_stats['client_visible']; ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Note Templates -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-bookmark tw-mr-2"></i>
					<?php echo _l('note_templates'); ?>
                </h5>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action use-template"
                       data-template="<?php echo _l('note_template_client_followup'); ?>">
                        <i class="fa fa-phone"></i> <?php echo _l('client_followup'); ?>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action use-template"
                       data-template="<?php echo _l('note_template_kitchen_update'); ?>">
                        <i class="fa fa-cutlery"></i> <?php echo _l('kitchen_update'); ?>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action use-template"
                       data-template="<?php echo _l('note_template_logistics_update'); ?>">
                        <i class="fa fa-truck"></i> <?php echo _l('logistics_update'); ?>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action use-template"
                       data-template="<?php echo _l('note_template_payment_received'); ?>">
                        <i class="fa fa-money"></i> <?php echo _l('payment_received'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-filter tw-mr-2"></i>
					<?php echo _l('filter_activity'); ?>
                </h5>
                <div class="checkbox">
                    <input type="checkbox" id="filter_client_visible">
                    <label for="filter_client_visible"><?php echo _l('client_visible_only'); ?></label>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Edit Note Modal -->
<div class="modal fade" id="edit-note-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php echo _l('edit_note'); ?></h4>
            </div>
            <div class="modal-body">
                <form id="edit-note-form">
                    <input type="hidden" name="note_id" id="edit-note-id">
                    <div class="form-group">
                        <label><?php echo _l('description'); ?></label>
                        <textarea class="form-control" name="description" id="edit-note-description" rows="4" required></textarea>
                    </div>
                    <div class="checkbox">
                        <input type="checkbox" name="visible_to_client" id="edit-note-visible" value="1">
                        <label for="edit-note-visible">
							<?php echo _l('visible_to_client'); ?>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="save-note-btn">
                    <i class="fa fa-save"></i> <?php echo _l('save'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var eventId = <?php echo $event->eventid; ?>;

        $(function () {
            // Add note form
            $('#add-note-form').on('submit', function (e) {
                e.preventDefault();

                var description = $(this).find('[name="description"]').val();
                var visibleToClient = $(this).find('[name="visible_to_client"]').is(':checked') ? 1 : 0;

                $.post(admin_url + 'catering_management_module/events/add_note', {
                    event_id: eventId,
                    description: description,
                    visible_to_client: visibleToClient
                }).done(function (response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', '<?php echo _l('note_added_successfully'); ?>');
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_float('danger', response.message);
                    }
                }).fail(function () {
                    alert_float('danger', '<?php echo _l('something_went_wrong'); ?>');
                });
            });

            // Prevent edit note form submission
            $('#edit-note-form').on('submit', function (e) {
                e.preventDefault();
                $('#save-note-btn').trigger('click');
                return false;
            });

            // Edit note
            $('.edit-note').on('click', function () {
                var noteId = $(this).data('id');

                $.get(admin_url + 'catering_management_module/events/get_note/' + noteId)
                    .done(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            $('#edit-note-id').val(response.note.id);
                            $('#edit-note-description').val(response.note.description);
                            $('#edit-note-visible').prop('checked', response.note.visible_to_client == 1);
                            $('#edit-note-modal').modal('show');
                        } else {
                            alert_float('danger', response.message);
                        }
                    });
            });

            // Save edited note
            $('#save-note-btn').on('click', function (e) {
                e.preventDefault();

                var noteId = $('#edit-note-id').val();
                var description = $('#edit-note-description').val();
                var visibleToClient = $('#edit-note-visible').is(':checked') ? 1 : 0;

                // Validate
                if (!description.trim()) {
                    alert_float('warning', '<?php echo _l('please_enter_description'); ?>');
                    return;
                }

                $.post(admin_url + 'catering_management_module/events/update_note', {
                    note_id: noteId,
                    description: description,
                    visible_to_client: visibleToClient
                }).done(function (response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        alert_float('success', response.message);
                        $('#edit-note-modal').modal('hide');

                        setTimeout(function () {
                            location.reload();
                        }, 1500)
                    } else {
                        alert_float('danger', response.message);
                    }
                }).fail(function () {
                    alert_float('danger', '<?php echo _l('something_went_wrong'); ?>');
                });
            });

            // Delete note
            $(document).on('click', '.delete-note', function () {
                var noteId = $(this).data('id');
                var feedItem = $(this).closest('.feed-item');

                if (confirm('<?php echo _l('confirm_delete_note'); ?>')) {
                    $.post(admin_url + 'catering_management_module/events/delete_note/' + noteId)
                        .done(function (response) {
                            response = JSON.parse(response);
                            if (response.success) {
                                feedItem.fadeOut(function () {
                                    $(this).remove();
                                    updateStats();

                                    // Show no notes message if all notes deleted
                                    if ($('.feed-item').length === 0) {
                                        $('#notes-feed').html('<div class="text-center text-muted" id="no-notes-message"><i class="fa fa-comment-o fa-3x tw-mb-3"></i><p><?php echo _l('no_notes_yet'); ?></p></div>');
                                    }
                                });
                                alert_float('success', response.message);
                            } else {
                                alert_float('danger', response.message);
                            }
                        });
                }
            });

            // Use template
            $('.use-template').on('click', function (e) {
                e.preventDefault();
                var template = $(this).data('template');
                $('#add-note-form textarea[name="description"]').val(template);
                $('html, body').animate({
                    scrollTop: $('#add-note-form').offset().top - 100
                }, 500);
            });

            // Filter by client visible
            $('#filter_client_visible').on('change', function () {
                var clientVisibleOnly = $(this).is(':checked');

                $('.feed-item').each(function () {
                    if (clientVisibleOnly) {
                        if ($(this).find('.label-success').length === 0) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    } else {
                        $(this).show();
                    }
                });
            });

            // Update stats
            function updateStats() {
                $.get(admin_url + 'catering_management_module/events/get_event_notes/' + eventId)
                    .done(function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            $('#stat-total').text(response.stats.total);
                            $('#stat-internal').text(response.stats.internal);
                            $('#stat-client-visible').text(response.stats.client_visible);
                        }
                    });
            }
        });
    });
</script>

<style>
    .activity-feed {
        margin-top: 20px;
    }

    .feed-item {
        margin-bottom: 20px;
    }

    .feed-header {
        margin-bottom: 5px;
    }

    .feed-content {
        padding: 15px;
        background: #f9f9f9;
        border-left: 3px solid #ddd;
        border-radius: 4px;
    }

    .feed-divider {
        margin: 20px 0;
        border-color: #e5e5e5;
    }

    .feed-actions {
        margin-top: 10px;
    }

    .list-group-item-action:hover {
        background-color: #f5f5f5;
        cursor: pointer;
    }
</style>
