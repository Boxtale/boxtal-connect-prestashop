/**
 * 2007-2019 PrestaShop
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
 * @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
 *
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

const bxParcelPoint = {
  trigger: '.bx-select-parcel',
  initialized: false,
  mapContainer: null,
  map: null,
  markers: [],

  init: function () {
    const self = this;
    if (self.initialized) {
      return;
    }
    self.initialized = true;
    self.mapContainer = document.querySelector('#bx-map');
    if (!self.mapContainer) {
      self.initMap();
    }
    self.initCarriers();

    self.on("body", "click", self.trigger, function () {
      self.openMap();
      self.getPoints();
    });

    self.on("body", "change", self.getAllCarrierInputsSelector(), function () {
      self.initCarriers();
    });

    self.on("body", "click", ".bx-parcel-point-button", function (e) {
      self.selectPoint(this.getAttribute("data-code"),
                      unescape(this.getAttribute("data-name")),
                      this.getAttribute("data-network"),
                      unescape(this.getAttribute("data-street")),
                      unescape(this.getAttribute("data-zipcode")),
                      unescape(this.getAttribute("data-city")),
                      unescape(this.getAttribute("data-country")),
                      unescape(this.getAttribute("data-openinghours")))
        .then(function () {
          self.initCarriers();
          self.closeMap();
        })
        .catch(function (err) {
          self.showError(err);
        });
    });
  },

  initMap: function () {
    const self = this;
    const mapClose = document.createElement("div");
    mapClose.setAttribute("class", "bx-close");
    mapClose.setAttribute("title", bxTranslation.text.closeMap);
    mapClose.addEventListener("click", function () {
      self.closeMap()
    });

    const mapCanvas = document.createElement("div");
    mapCanvas.setAttribute("id", "bx-map-canvas");

    const mapContainer = document.createElement("div");
    mapContainer.setAttribute("id", "bx-map-container");
    mapContainer.appendChild(mapCanvas);

    const mapPPContainer = document.createElement("div");
    mapPPContainer.setAttribute("id", "bx-pp-container");

    const mapInner = document.createElement("div");
    mapInner.setAttribute("id", "bx-map-inner");
    mapInner.appendChild(mapClose);
    mapInner.appendChild(mapContainer);
    mapInner.appendChild(mapPPContainer);

    self.mapContainer = document.createElement("div");
    self.mapContainer.setAttribute("id", "bx-map");
    self.mapContainer.appendChild(mapInner);
    document.body.appendChild(self.mapContainer);

    mapboxgl.accessToken = 'whatever';
    self.map = new mapboxgl.Map({
      container: 'bx-map-canvas',
      style: bxMapUrl,
      zoom: 14
    });
    self.map.addControl(new mapboxgl.NavigationControl());

    const logoImg = document.createElement("img");
    logoImg.setAttribute("src", bxMapLogoImageUrl);
    const logoLink = document.createElement("a");
    logoLink.setAttribute("href", bxMapLogoHrefUrl);
    logoLink.setAttribute("target", "_blank");
    logoLink.appendChild(logoImg);
    const logoContainer = document.createElement("div");
    logoContainer.setAttribute("id", "bx-boxtal-logo");
    logoContainer.appendChild(logoLink);

    const mapTopLeftCorner = document.querySelector(".mapboxgl-ctrl-top-left");
    if (mapTopLeftCorner) {
      mapTopLeftCorner.appendChild(logoContainer);
    }
  },

  initCarriers: function() {
    const self = this;
    const carriers = self.getCarriers();
    const selectedCarrierId = self.getSelectedCarrier();
    for (let i = 0; i < carriers.length; i++) {
      const carrier = carriers[i];
      if (null === carrier.querySelector(".bx-extra-content")) {
        const el = document.createElement("div");
        el.setAttribute("class", "col-sm-12 bx-extra-content");
        carrier.appendChild(el);
      }
      const extraContent = carrier.querySelector(".bx-extra-content");
      if (selectedCarrierId === self.getCarrierId(carrier)) {
        self.getSelectedCarrierText(selectedCarrierId, extraContent);
      } else {
        extraContent.innerHTML = "";
      }
    }
  },

  getCarriers: function() {
    // for 1.7
    let carriers = document.querySelectorAll(".delivery-option");

    // for 1.6
    if (carriers.length === 0) {
      carriers = document.querySelectorAll("td.delivery_option_radio");
    }

    // for 1.5
    if (carriers.length === 0) {
      carriers = document.querySelectorAll(".delivery_option");
    }
    return carriers;
  },

  openMap: function () {
    this.mapContainer.classList.add("bx-modal-show");
    let offset = window.pageYOffset + (window.innerHeight - this.mapContainer.offsetHeight) / 2;
    if (offset < window.pageYOffset) {
      offset = window.pageYOffset;
    }
    this.mapContainer.style.top = offset + 'px';
    this.map.resize();
  },

  closeMap: function () {
    this.mapContainer.classList.remove("bx-modal-show");
    this.clearMarkers();
  },

  getPoints: function () {
    const self = this;

    self.getParcelPoints().then(function (parcelPointResponse) {
      self.addParcelPointMarkers(parcelPointResponse['nearbyParcelPoints']);
      self.fillParcelPointPanel(parcelPointResponse['nearbyParcelPoints']);
      self.addRecipientMarker(parcelPointResponse['searchLocation']);
      self.setMapBounds();
    }).catch(function (err) {
      self.showError(err);
    });
  },

  getParcelPoints: function () {
    const self = this;
    return new Promise(function (resolve, reject) {
      const carrier = self.getSelectedCarrier();
      if (!carrier) {
        reject(bxTranslation.error.carrierNotFound);
      }
      const httpRequest = new XMLHttpRequest();
      httpRequest.onreadystatechange = function () {
        if (httpRequest.readyState === 4) {
          if (200 !== httpRequest.status) {
            reject();
          } else {
            const response = typeof httpRequest.response === 'object' && httpRequest.response !== null ? httpRequest.response : JSON.parse(httpRequest.response);
            resolve(response);
          }
        }
      };
      httpRequest.open("POST", bxAjaxUrl);
      httpRequest.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
      );
      httpRequest.responseType = "json";
      httpRequest.send("route=getPoints&carrier=" + encodeURIComponent(carrier)
        + "&cartId=" + encodeURIComponent(bxCartId) + "&token=" + encodeURIComponent(bxToken));
    });
  },

  addParcelPointMarkers: function (parcelPoints) {
    for (let i = 0; i < parcelPoints.length; i++) {
      parcelPoints[i].index = i;
      this.addParcelPointMarker(parcelPoints[i]);
    }
  },

  generateParcelPointTagData: function(parcelpoint) {
      return ' data-code="'    + parcelpoint.code + '" ' +
              'data-name="'    + escape(parcelpoint.name) + '" ' +
              'data-network="' + parcelpoint.network + '" ' +
              'data-zipcode="' + escape(parcelpoint.location.zipCode) + '" ' +
              'data-country="' + escape(parcelpoint.location.country) + '" ' +
              'data-city="'    + escape(parcelpoint.location.city) + '" ' +
              'data-street="'  + escape(parcelpoint.location.street) + '" ' +
              'data-openinghours="'  + escape(JSON.stringify(parcelpoint.openingDays)) + '" ';
  },

  formatOpeningDays(openingDays) {
      var parsedDays = [];

      for (var i = 0; i < openingDays.length; i++) {
          var openingDay = openingDays[i];

          if (openingDay.weekday) {
              var parsedDay = openingDay.weekday[0] + ' ';
              var openingPeriods = openingDay.openingPeriods;
              var parsedPeriods = [];

              for (var j = 0; j < openingPeriods.length; j++) {
                  var openingPeriod = openingPeriods[j];
                  var open = openingPeriod.openingTime === undefined ? '' : openingPeriod.openingTime;
                  var close = openingPeriod.closingTime === undefined ? '' : openingPeriod.closingTime;

                  if (open !== '' && close !== '') {
                      parsedPeriods.push(open + '-' + close);
                  }
              }

              while (parsedPeriods.length < 2) {
                parsedPeriods.push(bxTranslation.text.closedLabel);
              }

              parsedDay += parsedPeriods.join(' ');

              if (i % 2 === 1) {
                parsedDay = '<span style="background-color: #d8d8d8;">' + parsedDay + '</span>';
              }

              parsedDays.push(parsedDay);
          }
      }

      return parsedDays.join("\n");
  },

  addParcelPointMarker: function (point) {
    const self = this;
    let info = "<div class='bx-marker-popup'><b>" + point.parcelPoint.name + '</b><br/>' +
      point.parcelPoint.location.street + "<br/>" +
      point.parcelPoint.location.zipCode + " " + point.parcelPoint.location.city + "<br/>" +
      '<a href="#" class="bx-parcel-point-button" ' + this.generateParcelPointTagData(point.parcelPoint) + '><b>' + bxTranslation.text.chooseParcelPoint + '</b></a>' +
      '<pre class="bx-parcel-point-schedule">' +
      this.formatOpeningDays(point.parcelPoint.openingDays) +
      '</pre>';

    const el = this.getMarkerHtmlElement(point.index + 1);

    const popup = new mapboxgl.Popup({offset: 25})
      .setHTML(info);

    const marker = new mapboxgl.Marker({
      element: el,
      anchor: 'bottom'
    })
      .setLngLat(new mapboxgl.LngLat(parseFloat(point.parcelPoint.location.position.longitude), parseFloat(point.parcelPoint.location.position.latitude)))
      .setPopup(popup)
      .addTo(self.map);

    self.markers.push(marker);

    self.addRightColMarkerEvent(marker, point.parcelPoint.code);
  },

  addRightColMarkerEvent: function (marker, code) {
    this.on("body", "click", ".bx-show-info-" + code, function () {
      marker.togglePopup();
    });
  },

  formatHours: function (time) {
    const explode = time.split(':');
    if (3 === explode.length) {
      time = explode[0] + ':' + explode[1];
    }
    return time;
  },

  addRecipientMarker: function (location) {
    const self = this;

    const el = document.createElement('div');
    el.className = 'bx-marker-recipient';

    const marker = new mapboxgl.Marker({
      element: el,
      anchor: 'bottom'
    })
      .setLngLat(new mapboxgl.LngLat(parseFloat(location.position.longitude), parseFloat(location.position.latitude)))
      .addTo(self.map);

    self.markers.push(marker);
  },

  setMapBounds: function () {

    let bounds = new mapboxgl.LngLatBounds();

    for (let i = 0; i < this.markers.length; i++) {
      const marker = this.markers[i];
      bounds = bounds.extend(marker.getLngLat());
    }

    this.map.fitBounds(
      bounds,
      {
        padding: 30,
        linear: true
      }
    );
  },

  fillParcelPointPanel: function (parcelPoints) {
    let html = '';
    html += '<table><tbody>';
    for (let i = 0; i < parcelPoints.length; i++) {
      const point = parcelPoints[i];
      html += '<tr>';
      html += '<td>' + this.getMarkerHtmlElement(i+1).outerHTML;
      html += '<div class="bx-parcel-point-title"><a class="bx-show-info-' + point.parcelPoint.code + '">' + point.parcelPoint.name + '</a></div><br/>';
      html += point.parcelPoint.location.street + '<br/>';
      html += point.parcelPoint.location.zipCode + ' ' + point.parcelPoint.location.city + '<br/>';
      html += '<a class="bx-parcel-point-button" ' + this.generateParcelPointTagData(point.parcelPoint) + '"><b>' + bxTranslation.text.chooseParcelPoint + '</b></a>';
      html += '</td>';
      html += '</tr>';
    }
    html += '</tbody></table>';
    document.querySelector('#bx-pp-container').innerHTML = html;
  },

  getMarkerHtmlElement: function(index) {
    const el = document.createElement('div');
    el.className = 'bx-marker';
    el.innerHTML = index;
    return el;
  },

  selectPoint: function (code, name, network, address, zipcode, city, country, openingHours) {
    const self = this;
    return new Promise(function (resolve, reject) {
      const carrier = self.getSelectedCarrier();
      if (!carrier) {
        reject(bxTranslation.error.carrierNotFound);
      }
      const setPointRequest = new XMLHttpRequest();
      setPointRequest.onreadystatechange = function () {
        if (setPointRequest.readyState === 4) {
          if (200 !== setPointRequest.status) {
            reject(bxTranslation.error.couldNotSelectPoint);
          } else {
            resolve();
          }
        }
      };
      setPointRequest.open("POST", bxAjaxUrl);
      setPointRequest.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
      );
      setPointRequest.responseType = "json";
      setPointRequest.send("route=setPoint"
            + "&carrier=" + encodeURIComponent(carrier)
            + "&code=" + encodeURIComponent(code)
            + "&name=" + encodeURIComponent(name)
            + "&address=" + encodeURIComponent(address)
            + "&zipcode=" + encodeURIComponent(zipcode)
            + "&city=" + encodeURIComponent(city)
            + "&country=" + encodeURIComponent(country)
            + "&openingHours=" + encodeURIComponent(openingHours)
            + "&network=" + encodeURIComponent(network)
            + "&cartId=" + encodeURIComponent(bxCartId)
            + "&token=" + encodeURIComponent(bxToken)
        );
    });
  },

  clearMarkers: function () {
    for (let i = 0; i < this.markers.length; i++) {
      this.markers[i].remove();
    }
    this.markers = [];
  },

  getUniqueCarrier: function() {
    return document.querySelector('input[type="hidden"].shipping_method');
  },

  hasUniqueCarrier: function() {
    return null !== this.getUniqueCarrier();
  },

  getCarrierId: function(carrier) {
    if (this.hasUniqueCarrier()) {
      const uniqueCarrier = this.getUniqueCarrier();
      return uniqueCarrier.getAttribute("value");
    } else {
      const input = carrier.querySelector("input[type=radio]");
      return input.getAttribute("value");
    }
  },

  getSelectedCarrier: function() {
    let carrier;
    if (this.hasUniqueCarrier()) {
      const uniqueCarrier = this.getUniqueCarrier();
      carrier = uniqueCarrier.getAttribute('value');
    } else {
      const selectedCarrier = this.getSelectedInput();
      carrier = selectedCarrier.getAttribute('value');
    }
    return carrier;
  },

  getSelectedCarrierText: function(selectedCarrierId, extraContent) {
    if (null === selectedCarrierId) {
      return "";
    }

    const getSelectedCarrierTextRequest = new XMLHttpRequest();
    getSelectedCarrierTextRequest.onreadystatechange = function () {
      if (getSelectedCarrierTextRequest.readyState === 4) {
        if (200 !== getSelectedCarrierTextRequest.status) {
          extraContent.innerHTML = "";
        } else {
          const response = typeof getSelectedCarrierTextRequest.response === 'object'
            && getSelectedCarrierTextRequest.response !== null ?
            getSelectedCarrierTextRequest.response : JSON.parse(getSelectedCarrierTextRequest.response);
          extraContent.innerHTML = response.text;
        }
      }
    };
    getSelectedCarrierTextRequest.open("POST", bxAjaxUrl);
    getSelectedCarrierTextRequest.setRequestHeader(
      "Content-Type",
      "application/x-www-form-urlencoded"
    );
    getSelectedCarrierTextRequest.responseType = "json";
    getSelectedCarrierTextRequest.send("route=getSelectedCarrierText&carrier=" + encodeURIComponent(selectedCarrierId)
      + "&cartId=" + encodeURIComponent(bxCartId) + "&token=" + encodeURIComponent(bxToken));
  },

  getSelectedInput: function() {
    // for 1.7
    var input = document.querySelector(".delivery-option input[type='radio']:checked");

    // for 1.6
    if (null === input) {
      input = document.querySelector(".delivery_option_radio input[type='radio']:checked");
    }

    // for 1.5
    if (null === input) {
      input = document.querySelector(".delivery_option input[type='radio']:checked");
    }
    return input;
  },

  getAllCarrierInputsSelector : function() {
    // for 1.7
    let inputs = ".delivery-option input[type='radio']";

    // for 1.6
    inputs += ", .delivery_option_radio input[type='radio']";

    // for 1.5
    inputs += ", .delivery_option input[type='radio']";

    return inputs;
  },

  showError: function (error) {
    this.closeMap();
    alert(error);
  },

  on: function (elSelector, eventName, selector, fn) {
    const element = document.querySelector(elSelector);

    element.addEventListener(eventName, function (event) {
      const possibleTargets = element.querySelectorAll(selector);
      const target = event.target;

      for (let i = 0, l = possibleTargets.length; i < l; i++) {
        let el = target;
        const p = possibleTargets[i];

        while (el && el !== element) {
          if (el === p) {
            return fn.call(p, event);
          }

          el = el.parentNode;
        }
      }
    });
  }
};
