<table class="table">
      <thead>
         <tr>
          <th><?php echo e($data['dimensionName']); ?></th>
         <?php foreach($metrics as $k => $metric){ 
            if($k >= 10){
                break;
            }
            ?>
            <th><?php echo _l($metric); ?></th>
         <?php } ?>
       </tr>
    </thead>
      <?php foreach($data['table_data'] as $value){ ?>
       <tr>
         <?php foreach($value as $key => $val){ 
            ?>
            <td><?php echo e($val); ?></td>
         <?php } ?>
      </tr>
      <?php } ?>
</table>
