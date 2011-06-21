<div class="bg">
    <?php echo $headers?>
    <!-- report-form -->
    <div class="report-form">
        <?php
        if (isset($form_error)) {        ?> 
            <!-- red-box -->
            <div class="red-box">
                <h3><?php echo Kohana::lang('ui_main.error');?></h3>
                <ul>
                <?php
                foreach ($errors as $error_item => $error_description)
                {
                    print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
                }
                ?>                </ul>
            </div>        <?php
        }
        ?>
        <!-- column -->
        <div class="upload_container">
        <p><?php echo Kohana::lang('omniimport.welcome');?>.</p>        <h3><?php echo Kohana::lang('omniimport.select_mapping');?></h3>
        <?php print form::open(NULL, array('id' => 'uploadForm', 'name' => 'uploadForm', 'enctype' => 'multipart/form-data')); ?>
                </table>
            </p>
            <p><h3><?php echo Kohana::lang('omniimport.select_file');?>
            <?php echo form::upload(array('name' => 'csvfile'), 'path/to/local/file'); ?></h3></p>
            <p></p>
            <button type="submit"><?php echo Kohana::lang('ui_main.upload');?></button>
            <?php print form::close(); ?>
        </div>
    </div>
i</div>
