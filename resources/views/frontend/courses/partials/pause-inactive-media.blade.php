<script>
    (function() {
        if (window.__pauseInactiveMediaRegistered) {
            return;
        }

        window.__pauseInactiveMediaRegistered = true;
        window.__lessonExclusiveMediaPlayers = window.__lessonExclusiveMediaPlayers || [];

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

        function pauseMediaElement(mediaElement) {
            try {
                if (!mediaElement.paused) {
                    mediaElement.pause();
                }
            } catch (error) {
                // Ignore native media pause errors from detached elements.
            }
        }

        function pausePlayerInstance(playerInstance, activePlayer, activeMedia) {
            if (
                !playerInstance ||
                playerInstance === activePlayer ||
                playerInstance.media === activeMedia ||
                typeof playerInstance.pause !== 'function'
            ) {
                return;
            }

            try {
                playerInstance.pause();
            } catch (error) {
                // Ignore third-party player state errors while switching media.
            }
        }

        function pauseInactiveMedia(activeMedia, activePlayer) {
            window.__lessonExclusiveMediaPlayers.forEach(function(playerInstance) {
                pausePlayerInstance(playerInstance, activePlayer, activeMedia);
            });

            document.querySelectorAll('video, audio').forEach(function(mediaElement) {
                if (mediaElement !== activeMedia) {
                    pauseMediaElement(mediaElement);
                }
            });

            if (!activeMedia && !activePlayer) {
                document.querySelectorAll('iframe').forEach(pauseEmbeddedPlayer);
            }
        }

        function pauseOtherLessonMedia(activeMedia, activePlayer) {
            pauseInactiveMedia(activeMedia || null, activePlayer || null);
        }

        window.pauseOtherLessonMedia = pauseOtherLessonMedia;
        window.pauseInactiveLessonMedia = function() {
            pauseInactiveMedia();
        };
        window.registerExclusiveLessonMediaPlayer = function(playerInstance) {
            if (!playerInstance || window.__lessonExclusiveMediaPlayers.indexOf(playerInstance) !== -1) {
                return playerInstance;
            }

            window.__lessonExclusiveMediaPlayers.push(playerInstance);

            if (typeof playerInstance.on === 'function') {
                playerInstance.on('play', function() {
                    pauseOtherLessonMedia(playerInstance.media || null, playerInstance);
                });
            }

            return playerInstance;
        };

        document.addEventListener('play', function(event) {
            if (event.target && event.target.matches('video, audio')) {
                pauseOtherLessonMedia(event.target, null);
            }
        }, true);

        document.addEventListener('playing', function(event) {
            if (event.target && event.target.matches('video, audio')) {
                pauseOtherLessonMedia(event.target, null);
            }
        }, true);

        document.addEventListener('plyr:ready', function(event) {
            if (event.detail && event.detail.plyr) {
                window.registerExclusiveLessonMediaPlayer(event.detail.plyr);
            }
        });

        function pauseAllMedia() {
            window.__lessonExclusiveMediaPlayers.forEach(function(playerInstance) {
                pausePlayerInstance(playerInstance, null, null);
            });

            document.querySelectorAll('video, audio').forEach(function(mediaElement) {
                pauseMediaElement(mediaElement);
            });

            document.querySelectorAll('iframe').forEach(pauseEmbeddedPlayer);
        }
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                pauseAllMedia();
            }
        });

        window.addEventListener('pagehide', pauseAllMedia);
        window.addEventListener('beforeunload', pauseAllMedia);
    })();
</script>
