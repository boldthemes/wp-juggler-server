jQuery(document).ready(function($) {
    var file_frame;
    $('#upload-wp_juggler_plugin_download_file-button').on('click', function(e) {
        e.preventDefault();

        if (file_frame) {
            file_frame.open();
            return;
        }

        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Choose Plugin Package',
            button: {
                text: 'Choose Plugin Package'
            },
            multiple: false
        });

        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            console.log(attachment);
            $('#wp_juggler_plugin_download_file').val(attachment.id);
            $('#wp_juggler_plugin_download_file-preview').text(attachment.url);
        });

        file_frame.open();
    });

    $('#remove-wp_juggler_plugin_download_file-button').on('click', function(e) {
        e.preventDefault();
        $('#wp_juggler_plugin_download_file').val('');
        $('#wp_juggler_plugin_download_file-preview').text('');
    });
});