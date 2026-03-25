@if($cookieConsentConfig['enabled'] && ! $alreadyConsentedWithCookies)

    @include('cookieConsent::dialogContents')

    <script>

        window.laravelCookieConsent = (function () {

            const COOKIE_VALUE = 1;
            const COOKIE_DOMAIN = '{{ config('session.domain') }}';
            const COOKIE_SECURE = {{ config('session.secure') ? 'true' : 'false' }};
            const COOKIE_SAME_SITE = '{{ config('session.same_site') }}';

            function resolveCookieDomain() {
                const host = window.location.hostname;
                const isIpAddress = /^(\d{1,3}\.){3}\d{1,3}$/.test(host);

                if (COOKIE_DOMAIN) {
                    return COOKIE_DOMAIN;
                }

                // Avoid explicit domain on localhost/IP to prevent invalid-domain warnings.
                if (host === 'localhost' || isIpAddress) {
                    return '';
                }

                return host;
            }

            function consentWithCookies() {
                setCookie('{{ $cookieConsentConfig['cookie_name'] }}', COOKIE_VALUE, {{ $cookieConsentConfig['cookie_lifetime'] }});
                hideCookieDialog();
            }

            function cookieExists(name) {
                return (document.cookie.split('; ').indexOf(name + '=' + COOKIE_VALUE) !== -1);
            }

            function hideCookieDialog() {
                const dialogs = document.getElementsByClassName('js-cookie-consent');

                for (let i = 0; i < dialogs.length; ++i) {
                    dialogs[i].style.display = 'none';
                }
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                const cookieParts = [
                    name + '=' + value,
                    'expires=' + date.toUTCString(),
                    'path=/'
                ];

                const domain = resolveCookieDomain();
                if (domain) {
                    cookieParts.push('domain=' + domain);
                }

                if (COOKIE_SECURE) {
                    cookieParts.push('secure');
                }

                if (COOKIE_SAME_SITE) {
                    cookieParts.push('samesite=' + COOKIE_SAME_SITE);
                }

                document.cookie = cookieParts.join(';');
            }

            if (cookieExists('{{ $cookieConsentConfig['cookie_name'] }}')) {
                hideCookieDialog();
            }

            const buttons = document.getElementsByClassName('js-cookie-consent-agree');

            for (let i = 0; i < buttons.length; ++i) {
                buttons[i].addEventListener('click', consentWithCookies);
            }

            return {
                consentWithCookies: consentWithCookies,
                hideCookieDialog: hideCookieDialog
            };
        })();
    </script>

@endif
