<?php

$choices = UPT()->helper->get_user_choices();
$settings = UPT()->helper->get_settings();
$nonce = wp_create_nonce( 'upt_nonce' );

$i18n = [
    'Saving' => __( 'Saving', 'upt' ),
    'Syncing' => __( 'Syncing', 'upt' ),
    'Resetting' => __( 'Resetting', 'upt' ),
    'Sync complete' => __( 'Sync complete', 'upt' ),
    'Choose some user data' => __( 'Choose some user data', 'upt' )
];

?>

<script>

window.UPT = window.UPT || {
    nonce: '<?php echo $nonce; ?>',
    i18n: <?php echo json_encode( $i18n ); ?>,
    is_syncing: false
};

</script>
<script src="<?php echo UPT_URL; ?>/assets/js/admin.js?ver=<?php echo UPT_VERSION; ?>"></script>
<script src="<?php echo UPT_URL; ?>/assets/lib/fSelect/fSelect.js?ver=<?php echo UPT_VERSION; ?>"></script>
<link href="<?php echo UPT_URL; ?>/assets/lib/fSelect/fSelect.css?ver=<?php echo UPT_VERSION; ?>" rel="stylesheet">
<link href="<?php echo UPT_URL; ?>/assets/css/admin.css?ver=<?php echo UPT_VERSION; ?>" rel="stylesheet">

<div class="wrap">
    <h1>UPT <?php _e( 'Settings', 'upt' ); ?></h1>
    <p>(Optional) copy extra data into <code>postmeta</code> during sync:</p>
    <p>
        <select class="upt-to-sync" multiple="multiple">
            <?php foreach ( $choices as $key => $choice ) : ?>
                <?php $selected = in_array( $key, $settings['to_sync'] ) ? ' selected' : ''; ?>
            <option value="<?php echo esc_attr( $key ); ?>"<?php echo $selected; ?>><?php echo esc_html( $choice ); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="button upt-save"><?php _e( 'Save', 'upt' ); ?></button>
    </p>
    <p>
        <button class="button button-primary upt-sync"><?php _e( 'Sync now', 'upt' ); ?></button>
        <span class="upt-reset"><?php _e( 'Reset', 'upt' ); ?></span>
        <div class="upt-response"></div>
    </p>
</div>
