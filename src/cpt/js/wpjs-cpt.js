jQuery(document).ready(function($) {
    var file_frame_download_file;
    var file_frame_banner_low;
    var file_frame_banner_high;
    $('#upload-wp_juggler_plugin_download_file-button').on('click', function(e) {
        e.preventDefault();

        if (file_frame_download_file) {
            file_frame_download_file.open();
            return;
        }

        file_frame_download_file = wp.media.frames.file_frame = wp.media({
            title: 'Choose Plugin Package',
            button: {
                text: 'Choose Plugin Package'
            },
            multiple: false
        });

        file_frame_download_file.on('select', function() {
            var attachment = file_frame_download_file.state().get('selection').first().toJSON();
            $('#wp_juggler_plugin_download_file').val(attachment.id);
            $('#wp_juggler_plugin_download_file-preview').val(attachment.url);
        });

        file_frame_download_file.open();
    });

    $('#remove-wp_juggler_plugin_download_file-button').on('click', function(e) {
        e.preventDefault();
        $('#wp_juggler_plugin_download_file').val('');
        $('#wp_juggler_plugin_download_file-preview').val('');
    });

    $('#upload-wp_juggler_plugin_banner_low-button').on('click', function(e) {
        e.preventDefault();

        if (file_frame_banner_low) {
            file_frame_banner_low.open();
            return;
        }

        file_frame_banner_low = wp.media.frames.file_frame = wp.media({
            title: 'Choose Banner',
            button: {
                text: 'Choose Banner'
            },
            multiple: false
        });

        file_frame_banner_low.on('select', function() {
            var attachment = file_frame_banner_low.state().get('selection').first().toJSON();
            var thumbnail_url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            $('#wp_juggler_plugin_banner_low').val(attachment.id);
            $('#wp_juggler_plugin_banner_low-preview').attr('src', thumbnail_url);
        });

        file_frame_banner_low.open();
    });

    $('#remove-wp_juggler_plugin_banner_low-button').on('click', function(e) {
        e.preventDefault();
        $('#wp_juggler_plugin_banner_low').val('');
        $('#wp_juggler_plugin_banner_low-preview').attr('src', '');
    });

    $('#upload-wp_juggler_plugin_banner_high-button').on('click', function(e) {
        e.preventDefault();

        if (file_frame_banner_high) {
            file_frame_banner_high.open();
            return;
        }

        file_frame_banner_high = wp.media.frames.file_frame = wp.media({
            title: 'Choose Banner',
            button: {
                text: 'Choose Banner'
            },
            multiple: false
        });

        file_frame_banner_high.on('select', function() {
            var attachment = file_frame_banner_high.state().get('selection').first().toJSON();
            var thumbnail_url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            $('#wp_juggler_plugin_banner_high').val(attachment.id);
            $('#wp_juggler_plugin_banner_high-preview').attr('src', thumbnail_url);
        });

        file_frame_banner_high.open();
    });

    $('#remove-wp_juggler_plugin_banner_high-button').on('click', function(e) {
        e.preventDefault();
        $('#wp_juggler_plugin_banner_high').val('');
        $('#wp_juggler_plugin_banner_high-preview').attr('src', '');
    });
    
});