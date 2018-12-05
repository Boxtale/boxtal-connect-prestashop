/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/afl-3.0.php
  * If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
                                                                  * needs please refer to http://www.prestashop.com for more information.
  *
* @author    Boxtal <api@boxtal.com>
*
* @copyright 2007-2018 PrestaShop SA / 2018-2018 Boxtal
*
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
  * International Registered Trademark & Property of PrestaShop SA
*/

(function () {
    var Components = {};

    Components.notices = {
        trigger: '.bx-notice',

        init: function() {
            const triggers = document.querySelectorAll(this.trigger);
            const self = this;

            if (triggers.length) {
                self.on("body", "click", ".bx-hide-notice", function() {
                    const httpRequest = new XMLHttpRequest();
                    const notice = this;
                    httpRequest.onreadystatechange = function(data) {
                        if (httpRequest.readyState === 4) {
                            if (httpRequest.status === 200) {
                                notice.closest(".bx-notice").style.display = 'none';
                            } else {
                                console.log("Error: " + httpRequest.status);
                            }
                        }
                    };
                    httpRequest.open("POST", ajaxLink);
                    httpRequest.setRequestHeader(
                        "Content-Type",
                        "application/x-www-form-urlencoded"
                    );
                    httpRequest.responseType = "json";
                    const noticeKey = notice.getAttribute("data-key");
                    const noticeShopGroupId = notice.getAttribute("data-shop-group-id");
                    const noticeShopId = notice.getAttribute("data-shop-id");
                    httpRequest.send("action=hideNotice&noticeKey=" + encodeURIComponent(noticeKey) + "&noticeShopGroupId="
                      + encodeURIComponent(noticeShopGroupId) + "&noticeShopId=" + encodeURIComponent(noticeShopId));
                });

                self.on("body", "click", ".bx-pairing-update-validate", function() {
                    const httpRequest = new XMLHttpRequest();
                    const notice = this;
                    httpRequest.onreadystatechange = function() {
                        if (httpRequest.readyState === 4) {
                            if (httpRequest.status === 200) {
                                notice.closest(".bx-notice").style.display = 'none';
                            } else {
                                console.log("Error: " + httpRequest.status);
                            }
                        }
                    };
                    httpRequest.open("POST", ajaxLink);
                    httpRequest.setRequestHeader(
                        "Content-Type",
                        "application/x-www-form-urlencoded"
                    );
                    httpRequest.responseType = "json";
                    const approve = notice.getAttribute("bx-pairing-update-validate");
                    httpRequest.send("action=pairingUpdateValidate&approve=" + encodeURIComponent(approve));
                });
            }
        },

        on: function(elSelector, eventName, selector, fn) {
            const element = document.querySelector(elSelector);

            element.addEventListener(eventName, function(event) {
                const possibleTargets = element.querySelectorAll(selector);
                const target = event.target;

                for (var i = 0, l = possibleTargets.length; i < l; i++) {
                    var el = target;
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
