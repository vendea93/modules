<ul id="flexacademy-lesson-order-list" class="flexacademy-order-list" data-type="lesson" data-success="<?php echo _flexacademy_lang('order-success'); ?>">
    <?php foreach ($lessons as $lesson): ?>
        <li class="flexacademy-order-item" data-id="<?php echo $lesson['id']; ?>"><span><i class="fa-solid fa-grip-vertical tw-mr-1"></i></span> <?php echo $lesson['title']; ?></li>
    <?php endforeach; ?>
</ul>