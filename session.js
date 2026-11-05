(function () {
    'use strict';

    var script = document.currentScript;
    var logoutUrl = script.dataset.logoutUrl;
    var pingUrl = script.dataset.pingUrl;
    var loginUrl = script.dataset.loginUrl;
    var timeout = Number(script.dataset.timeoutMs);
    var lastActivity = Date.now();
    var logoutStarted = false;

    function recordActivity() {
        lastActivity = Date.now();
    }

    function endSession() {
        if (logoutStarted) {
            return;
        }
        logoutStarted = true;

        var form = document.createElement('form');
        form.method = 'post';
        form.action = logoutUrl + '?expired=1';
        document.body.append(form);
        form.submit();
    }

    ['pointerdown', 'keydown', 'scroll', 'touchstart'].forEach(function (eventName) {
        document.addEventListener(eventName, recordActivity, { passive: true });
    });

    window.setInterval(function () {
        if (Date.now() - lastActivity >= timeout) {
            endSession();
        }
    }, 1000);

    window.setInterval(function () {
        if (Date.now() - lastActivity < timeout && document.visibilityState === 'visible') {
            fetch(pingUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (response) {
                if (response.redirected || response.status === 401 || response.status === 403) {
                    window.location.assign(loginUrl);
                }
            }).catch(function (error) {
                console.error('Unable to verify the active session.', error);
            });
        }
    }, 60000);
}());
