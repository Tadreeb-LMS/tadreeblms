<script>
    (function() {
        if (window.__pauseInactiveMediaRegistered) {
            return;
        }

        window.__pauseInactiveMediaRegistered = true;

        function pauseEmbeddedPlayer(frame) {
            if (!frame.contentWindow) {
                return;
            }

            try {
                frame.contentWindow.postMessage(JSON.stringify({
                    event: 'command',
                    func: 'pauseVideo',
                    args: []
                }), '*');

                frame.contentWindow.postMessage(JSON.stringify({
                    method: 'pause'
                }), '*');
            } catch (error) {
                // Ignore cross-provider iframe errors while the page is being hidden.
            }
        }

        function pauseInactiveMedia() {
            document.querySelectorAll('video').forEach(function(video) {
                if (!video.paused) {
                    video.pause();
                }
            });

            document.querySelectorAll('iframe').forEach(pauseEmbeddedPlayer);
        }

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                pauseInactiveMedia();
            }
        });

        window.addEventListener('pagehide', pauseInactiveMedia);
        window.addEventListener('beforeunload', pauseInactiveMedia);
    })();
</script>
