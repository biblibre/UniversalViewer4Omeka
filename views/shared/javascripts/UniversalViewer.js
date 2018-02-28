// Needed for UV
$ = jQuery;

(function() {
    window.addEventListener('uvLoaded', function (e) {
        new UV({
            target: '#uv',
            data: {
                root: '../../plugins/UniversalViewer/views/shared/javascripts/uv',
                iiifResourceUri: jQuery('#uv').attr('data-manifest'),
                configUri: '/plugins/UniversalViewer/views/public/universal-viewer/config.json',
                locales: [
                    {
                        name: 'en-GB'
                    }
                ]
            }
        });
    });
})();
