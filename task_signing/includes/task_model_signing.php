<div class="clearfix"></div>

<div style="float: right">

    <a style="cursor: pointer; "  onclick="$('#task_signatura_all_content').toggle(); "> <?php echo _l('ts_show_hide')?> </a>

    <?php $task_sign_icon = site_url('modules/task_signing/includes/signature_icon.png') ?>

    <img width="32px" style="width: 32px; height: 32px;" src="<?php echo $task_sign_icon?>">

</div>

<div id="task_signatura_all_content" style="display: none">


    <div class="clearfix"></div>

    <?php

    $task_client_id     = ts_get_task_client_id( $taskid );

    $client_sign_info   = ts_get_task_client_signature( $taskid , $task_client_id );

    if ( empty( $client_sign_info ) ) { ?>

        <div>
            <a class="btn btn-primary" onclick="request_customer_signature( <?php echo $task->id?> ); return false;" > <i class="fa fa-envelope-open-text"></i> <?php echo _l('ts_send_request_for_signature')?></a>
        </div>

    <?php } ?>



    <table class="table table-responsive table-striped">

        <thead>
            <tr>
                <th><?php echo _l('ts_staff')?></th>
                <th><?php echo _l('ts_task_sign')?></th>
            </tr>
        </thead>


        <tbody>
            <?php if ( !empty( $task_staff_lists ) ) : ?>

                <?php foreach ( $task_staff_lists as $task_assignee ) { ?>

                    <tr>
                        <td>
                            <?php echo $task_assignee->firstname.' '.$task_assignee->lastname ?>

                            <?php if ( !empty( $task_assignee->assignee_sign ) ) {

                                $task_sign_info = get_instance()->db->select('*')
                                                            ->from(db_prefix().'task_signature_info')
                                                            ->where('task_id',$taskid)
                                                            ->where('staff_id',$task_assignee->staffid)
                                                            ->get()
                                                            ->row();


                                if ( !empty( $task_sign_info ) )
                                {
                                    echo "<br/> <br/>";
                                    echo "<span> "._l('ts_signature_date').' : '._dt($task_sign_info->datetime)." </span>";
                                    echo "<br/>";
                                    echo "<span> "._l('ts_ip_address')." : ".$task_sign_info->ip_address." </span>";
                                }

                            } ?>

                        </td>
                        <td>
                            <?php if ( !empty( $task_assignee->assignee_sign ) ) {

                                $assignee_sign_image = $task_assignee->assignee_sign;

                                echo "<img style='width: 200px' src='$assignee_sign_image' />";

                                if ( is_admin() ) { ?>

                                    <div  >
                                        <?php echo form_open( admin_url()."task_signing/remove_sign/$taskid/".$task_assignee->staffid ); ?>
                                        <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" class="btn btn-danger btn-sm">
                                            <i class="fa fa-remove"></i>  <?php echo _l('ts_remove_sign')?>
                                        </button>
                                        <?php echo form_close(); ?>
                                    </div>

                                <?php }

                            } else { ?>

                                <?php if ( $task_assignee->staffid == get_staff_user_id() && $task_assignee->can_sign ) { ?>

                                    <a href="#" class="mbot10 btn btn-primary btn-sm" onclick="fnc_toogle_sign_box();">
                                        <i class="fa fa-pen"></i> <?php echo _l('ts_sign_task')?>
                                    </a>

                                <?php } else {

                                    echo _l('ts_task_unsigned');

                                } ?>


                            <?php } ?>
                        </td>
                    </tr>

                <?php } ?>

            <?php endif; ?>

            <?php if ( !empty( $client_sign_info ) ) : ?>

                <tr>
                    <td>
                        <?php echo _l('ts_customer_sign')?>

                        <?php if ( $client_sign_info->signed == 1 )
                        {

                            echo "<br/> <br/>";
                            echo "<span> "._l('ts_task_signed_by')." : ".get_contact_full_name( $client_sign_info->contact_id )." </span>";

                            echo "<br/>  ";
                            echo "<span> "._l('ts_signature_date').' : '._dt($client_sign_info->signature_date)." </span>";

                            echo "<br/>";
                            echo "<span> "._l('ts_ip_address')." : ".$client_sign_info->ip_address." </span>";

                        } ?>

                    </td>
                    <td>
                        <?php if ( $client_sign_info->signed == 1 ) {
                            echo "<img style='width: 200px' src='".site_url( $client_sign_info->signature )."' />";
                        } else {
                            echo _l('ts_task_unsigned');
                        } ?>
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

    </table>

    <div class="clearfix"></div>

    <?php if( $has_need_signature ) { ?>

        <div id="div_sign_box" style="display: none">

            <?php echo form_open( admin_url()."task_signing/sign_task/".$taskid , array( 'id'=>'taskSignForm' , 'class'=>'form-horizontal' ) ); ?>

            <canvas id="signature" height="150" width="500" style="border: 1px solid; "></canvas>

            <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" name="signature" id="signatureInput">


            <div class="dispay-block">

                <button type="button" class="btn btn-default btn-sm clear" tabindex="-1" data-action="clear" onclick="signatureClear()"><?php echo _l('clear'); ?></button>

                <button type="button" class="btn btn-default btn-sm" tabindex="-1" data-action="undo" onclick="signatureUndo()" ><?php echo _l('undo'); ?></button>

                <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" data-form="#taskSignForm" class="btn btn-success"><?php echo _l('e_signature_sign'); ?></button>

            </div>

            <?php echo form_close(); ?>

        </div>


        <div class="clearfix"></div>


        <script type="text/javascript" id="signature-pad" src="<?php echo site_url()?>assets/plugins/signature-pad/signature_pad.min.js"></script>

        <script>

            function fnc_toogle_sign_box()
            {
                $('#div_sign_box').toggle();
            }


            var canvas = document.getElementById("signature");


            var signaturePad = new SignaturePad(canvas, {

                maxWidth: 2,

                onEnd:function(){

                    signaturePadChanged();

                }

            });


            SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {

                var canvas = this._ctx.canvas;

                // First duplicate the canvas to not alter the original

                var croppedCanvas = document.createElement('canvas'),

                    croppedCtx = croppedCanvas.getContext('2d');



                croppedCanvas.width = canvas.width;

                croppedCanvas.height = canvas.height;

                croppedCtx.drawImage(canvas, 0, 0);

                // Next do the actual cropping

                var w = croppedCanvas.width;

                var h = croppedCanvas.height;

                var pix = {

                    x: [],

                    y: []

                };

                var imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height);

                var x;
                var y;
                var index;



                for (y = 0; y < h; y++) {

                    for (x = 0; x < w; x++) {

                        index = (y * w + x) * 4;

                        if (imageData.data[index + 3] > 0) {

                            pix.x.push(x);

                            pix.y.push(y);



                        }

                    }

                }

                pix.x.sort(function(a, b) {

                    return a - b

                });

                pix.y.sort(function(a, b) {

                    return a - b

                });

                var n = pix.x.length - 1;



                w = pix.x[n] - pix.x[0];

                h = pix.y[n] - pix.y[0];

                var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);



                croppedCanvas.width = w;

                croppedCanvas.height = h;

                croppedCtx.putImageData(cut, 0, 0);



                return croppedCanvas.toDataURL();

            };



            $('#taskSignForm').submit(function() {

                signaturePadChanged();

            });



            function signatureClear()
            {

                signaturePad.clear();

                signaturePadChanged();
            }

            function signatureUndo()
            {

                var data = signaturePad.toData();

                if (data) {

                    data.pop(); // remove the last dot or line

                    signaturePad.fromData(data);

                    signaturePadChanged();

                }

            }

            function signaturePadChanged() {


                var input = document.getElementById('signatureInput');

                var $signatureLabel = $('#signatureLabel');

                $signatureLabel.removeClass('text-danger');



                if (signaturePad.isEmpty()) {

                    $signatureLabel.addClass('text-danger');

                    input.value = '';

                    return false;

                }



                $('#signatureInput-error').remove();

                var partBase64 = signaturePad.toDataURLAndRemoveBlanks();

                partBase64 = partBase64.split(',')[1];

                input.value = partBase64;

            }


        </script>

    <?php } ?>

</div>
<div class="clearfix"></div>
<div>  <hr /> </div>

<?php if ( !$all_staff_signed ) {

    $ts_complete_task_without_sign = get_option('ts_complete_task_without_sign');

    if ( $ts_complete_task_without_sign == 0 )
    {

        // close complete button

        ?>

        <script>

            $(document).ready(function (){

                $("#task-single-mark-complete-btn").prop("disabled", true).addClass("disabled").css("pointer-events", "none");

            })

        </script>

        <?php

    }

} ?>
