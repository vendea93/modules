<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
    <h4 class="tw-my-0 tw-font-semibold tw-text-lg">
        <?php echo _flexacademy_lang('instructors'); ?>
    </h4>
    <a href="#" class="btn btn-primary flexacademy-instructor-cta"
    data-title="<?php echo _flexacademy_lang('add-instructor') ?>"
    data-instructor-id="0"
    data-instructor-name=""
    data-instructor-email=""
    data-instructor-job-title=""
    data-instructor-bio=""
    data-instructor-image=""
    data-instructor-signature=""
    data-instructor-signature-url=""
    >
        <i class="fa-solid fa-plus tw-mr-1"></i>
        <?php echo _flexacademy_lang('add-instructor') ?>
    </a>
</div>
<div class="panel_s">
    <div class="panel-body panel-table-full">
        <table class="table dt-table" data-order-col="0" data-order-type="asc">
            <thead>
                <th>#</th>
                <th><?php echo _flexacademy_lang('image') ?></th>
                <th><?php echo _flexacademy_lang('name') ?></th>
                <th><?php echo _flexacademy_lang('email') ?></th>
                <th><?php echo _flexacademy_lang('job_title') ?></th>
                <th><?php echo _flexacademy_lang('actions') ?></th>
            </thead>
            <tbody>
                <?php $i = 1;
                foreach ($instructors as $instructor): ?>
                    <tr>
                        <td>
                            <?php
                            echo $i;
                            $i++;
                            ?>
                        </td>
                        <td>
                            <img src="<?php echo flexacademy_instructor_image($instructor); ?>" alt="<?php echo $instructor['name']; ?>" class="tw-w-10 tw-h-10 tw-rounded-full">
                        </td>
                        <td>
                            <?php echo $instructor['name']; ?>
                        </td>
                        <td>
                            <?php echo $instructor['email']; ?>
                        </td>
                        <td>
                            <?php echo $instructor['job_title']; ?>
                        </td>       
                        <td>
                            <a href="#" class="text-info flexacademy-instructor-cta"
                            data-title="<?php echo _flexacademy_lang('edit-instructor') ?>"
                            data-instructor-id="<?php echo $instructor['id']; ?>"
                            data-instructor-name="<?php echo $instructor['name']; ?>"
                            data-instructor-email="<?php echo $instructor['email']; ?>"
                            data-instructor-job-title="<?php echo $instructor['job_title']; ?>"
                            data-instructor-bio="<?php echo $instructor['bio']; ?>"
                            data-instructor-image="<?php echo $instructor['image']; ?>"
                            data-instructor-signature="<?php echo $instructor['signature']; ?>"
                            data-instructor-signature-url="<?php echo flexacademy_instructor_signature($instructor); ?>"> <?php echo _flexacademy_lang('edit') ?></a> |
                            <a href="<?php echo admin_url('flexacademy/delete_instructor/' . $instructor['id']); ?>" class="_delete text-danger"><?php echo _flexacademy_lang('delete') ?> </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $this->load->view('partials/instructor/add-edit', ['course' => $course]); ?>