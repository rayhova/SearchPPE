(function($) {

    // Is sync running?
    UPT.get_progress = function() {
        $.post(ajaxurl, {
            action: 'upt_heartbeat',
            nonce: UPT.nonce
        }, function(response) {

            // Remove extra spaces added by some themes
            var response = response.trim();

            if ('-1' == response) {
                $('.upt-response').html(UPT.i18n['Sync complete']);
                UPT.is_syncing = false;
            }
            else if ($.isNumeric(response)) {
                $('.upt-response').html(UPT.i18n['Syncing'] + '... ' + response + '%');
                $('.upt-response').addClass('visible');
                setTimeout(function() {
                    UPT.get_progress();
                }, 5000);
            }
            else {
                $('.upt-response').html(response);
                UPT.is_syncing = false;
            }
        });
    }

    $(document).on('click', '.upt-sync', function() {
        if (UPT.is_syncing) {
            return;
        }

        UPT.is_syncing = true;

        $.post(ajaxurl, { action: 'upt_sync', nonce: UPT.nonce });
        $('.upt-response').html(UPT.i18n['Syncing'] + '...');
        $('.upt-response').addClass('visible');
        setTimeout(function() {
            UPT.get_progress();
        }, 5000);
    });

    $(document).on('click', '.upt-save', function() {
        $('.upt-response').html(UPT.i18n['Saving'] + '...');
        $('.upt-response').addClass('visible');

        var data = {
            'to_sync': $('.upt-to-sync').val() || []
        };

        $.post(ajaxurl, {
            action: 'upt_save',
            nonce: UPT.nonce,
            data: JSON.stringify(data)
        }, function(response) {
            $('.upt-response').html(response.message);
        }, 'json');
    });

    $(document).on('click', '.upt-reset', function() {
        if (confirm('This will remove all UPT data. Continue?')) {
            $('.upt-response').html(UPT.i18n['Resetting'] + '...');
            $('.upt-response').addClass('visible');

            $.post(ajaxurl, {
                action: 'upt_reset',
                nonce: UPT.nonce
            }, function(response) {
                $('.upt-response').html(response.message);
            }, 'json');
        }
    });

    $(function() {
        $('.upt-to-sync').fSelect({
            placeholder: UPT.i18n['Choose some user data']
        });

        UPT.get_progress();
    });

})(jQuery);
