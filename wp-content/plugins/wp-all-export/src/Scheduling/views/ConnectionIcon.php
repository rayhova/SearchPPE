<?php
$scheduling = \Wpae\Scheduling\Scheduling::create();
?>
<span class="wpai-no-license" <?php if ($scheduling->checkLicense()) { ?> style="display: none;" <?php } ?> >

    <a href="#" style="z-index: 1000; top: -4px; position: absolute; left: 0;" class="help_scheduling tipsy"
       title="Automatic Scheduling is a paid service from Soflyy. Click for more info.">
        <img style="width: 16px;"
                    src="<?php echo PMXE_ROOT_URL; ?>/static/img/s-question.png"/>
    </a>
</span>


<span class="wpai-license" <?php if (!$scheduling->checkLicense()) { ?> style="display: none;" <?php } ?> >
    <?php if ( $scheduling->checkConnection() ) {
        ?>
        <span class="wpallexport-help" title="Connection to WP All Export servers is stable and confirmed"
              style="background-image: none; width: 20px; height: 20px;;">
        <img src="<?php echo PMXE_ROOT_URL; ?>/static/img/s-check.png" style="width: 16px;"/>
    </span>
        <?php
    } else  { ?>
        <img src="<?php echo PMXE_ROOT_URL; ?>/static/img/s-exclamation.png" style="width: 16px;"/>

        <?php
    }
    ?>
</span>
