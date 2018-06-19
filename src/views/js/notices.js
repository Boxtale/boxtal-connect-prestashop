(function () {
    var Components = {};

    Components.notices = {
        trigger: '.bw-notice',

        init: function() {
            const triggers = document.querySelectorAll(this.trigger);
            const self = this;

            if (triggers.length) {
                self.on("body", "click", ".bw-hide-notice", function() {
                    const httpRequest = new XMLHttpRequest();
                    const notice = this;
                    httpRequest.onreadystatechange = function(data) {
                        if (httpRequest.readyState === 4) {
                            if (httpRequest.status === 200) {
                                notice.closest(".bw-notice").style.display = 'none';
                            } else {
                                console.log("Error: " + httpRequest.status);
                            }
                        }
                    };
                    httpRequest.open("POST", ajaxurl);
                    httpRequest.setRequestHeader(
                        "Content-Type",
                        "application/x-www-form-urlencoded"
                    );
                    httpRequest.responseType = "json";
                    const noticeId = notice.getAttribute("rel");
                    httpRequest.send("action=hide_notice&notice_id=" + encodeURIComponent(noticeId) + "&security=" + encodeURIComponent(ajax_nonce));
                });

                self.on("body", "click", ".bw-pairing-update-validate", function() {
                    const httpRequest = new XMLHttpRequest();
                    const notice = this;
                    httpRequest.onreadystatechange = function() {
                        if (httpRequest.readyState === 4) {
                            if (httpRequest.status === 200) {
                                notice.closest(".bw-notice").style.display = 'none';
                            } else {
                                console.log("Error: " + httpRequest.status);
                            }
                        }
                    };
                    httpRequest.open("POST", ajaxurl);
                    httpRequest.setRequestHeader(
                        "Content-Type",
                        "application/x-www-form-urlencoded"
                    );
                    httpRequest.responseType = "json";
                    const approve = notice.getAttribute("bw-pairing-update-validate");
                    httpRequest.send("action=pairing_update_validate&approve=" + encodeURIComponent(approve) + "&security=" + encodeURIComponent(ajax_nonce));
                });
            }
        },

        on: function(elSelector, eventName, selector, fn) {
            const element = document.querySelector(elSelector);

            element.addEventListener(eventName, function(event) {
                const possibleTargets = element.querySelectorAll(selector);
                const target = event.target;

                for (let i = 0, l = possibleTargets.length; i < l; i++) {
                    let el = target;
                    const p = possibleTargets[i];

                    while(el && el !== element) {
                        if (el === p) {
                            return fn.call(p, event);
                        }

                        el = el.parentNode;
                    }
                }
            });
        }
    };

    document.addEventListener(
        "DOMContentLoaded", function() {
            Components.notices.init();
        }
    );

})();