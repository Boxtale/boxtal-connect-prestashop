const bxParcelPoint = {
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
      self.on("body", "click", ".bx-parcel-point-button", function () {
        self.selectPoint(this.getAttribute("data-code"), this.getAttribute("data-label"), this.getAttribute("data-operator"))
          .then(function (label) {
            self.initSelectedParcelPoint();
            const target = document.querySelector(".bx-parcel-name");
            target.innerHTML = label;
            self.closeMap();
          })
          .catch(function (err) {
            self.showError(err);
          });
      });
      self.openMap();
      self.getPoints();
    });

    self.on("body", "change", self.getAllCarrierInputsSelector(), function () {
      self.initCarriers();
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
  },

  initCarriers: function() {
    const self = this;
    const carriers = self.getCarriers();
    const selectedCarrierId = self.getSelectedCarrier();
    for (let i = 0; i < carriers.length; i++) {
      const carrier = carriers[i];
      if (null === carrier.querySelector(".bx-extra-content")) {
        const el = document.createElement("div");
        el.setAttribute("class", "bx-extra-content");
        carrier.appendChild(el);
      }
      const extraContent = carrier.querySelector(".bx-extra-content");
      if (selectedCarrierId === self.getCarrierId(carrier)) {
        extraContent.innerHTML = self.getSelectedCarrierText(selectedCarrierId);
      } else {
        extraContent.innerHTML = "";
      }
    }
  },

  getCarriers: function() {
    // for 1.7
    let carriers = document.querySelectorAll(".delivery-option");

    // for 1.6
    if (null === carriers) {
      carriers = document.querySelectorAll(".delivery_option_radio");
    }

    // for 1.5
    if (null === carriers) {
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

  initSelectedParcelPoint: function () {
    const selectParcelPoint = document.querySelector(".bx-parcel-client");
    selectParcelPoint.innerHTML = bxTranslation.text.selectedParcelPoint + " ";
    const selectParcelPointContent = document.createElement("span");
    selectParcelPointContent.setAttribute("class", "bx-parcel-name");
    selectParcelPoint.appendChild(selectParcelPointContent);
  },

  getPoints: function () {
    const self = this;

    self.getParcelPoints().then(function (parcelPointResponse) {
      self.addParcelPointMarkers(parcelPointResponse['parcelPoints']);
      self.fillParcelPointPanel(parcelPointResponse['parcelPoints']);
      self.addRecipientMarker(parcelPointResponse['origin']);
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
          if (httpRequest.response.success === false) {
            reject(httpRequest.response.data.message);
          } else {
            resolve(httpRequest.response);
          }
        }
      };
      httpRequest.open("POST", ajaxurl);
      httpRequest.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
      );
      httpRequest.responseType = "json";
      httpRequest.send("action=get_points&carrier=" + encodeURIComponent(carrier));
    });
  },

  addParcelPointMarkers: function (parcelPoints) {
    for (let i = 0; i < parcelPoints.length; i++) {
      parcelPoints[i].index = i;
      this.addParcelPointMarker(parcelPoints[i]);
    }
  },

  addParcelPointMarker: function (point) {
    const self = this;
    let info = "<div class='bx-marker-popup'><b>" + point.label + '</b><br/>' +
      '<a href="#" class="bx-parcel-point-button" data-code="' + point.code + '" data-label="' + point.label + '" data-operator="' + point.operator + '"><b>' + bxTranslation.text.chooseParcelPoint + '</b></a><br/>' +
      point.address.street + ", " + point.address.postcode + " " + point.address.city + "<br/>" + "<b>" + bxTranslation.text.openingHours +
      "</b><br/>" + '<div class="bx-parcel-point-schedule">';

    for (let i = 0, l = point.schedule.length; i < l; i++) {
      const day = point.schedule[i];

      info += '<span class="bx-parcel-point-day">' + bxTranslation.day[day.weekday] + '</span>';

      for (let j = 0, t = day.timePeriods.length; j < t; j++) {
        const timePeriod = day.timePeriods[j];
        info += self.formatHours(timePeriod.openingTime) + '-' + self.formatHours(timePeriod.closingTime);
      }
      info += '<br/>';
    }
    info += '</div>';

    const el = document.createElement('div');
    el.className = 'bx-marker';
    el.style.backgroundImage = "url('" + imgDir + "markers/" + (point.index + 1) + ".png')";
    el.style.width = '28px';
    el.style.height = '35px';

    const popup = new mapboxgl.Popup({offset: 25})
      .setHTML(info);

    const marker = new mapboxgl.Marker({
      element: el
    })
      .setLngLat(new mapboxgl.LngLat(parseFloat(point.coordinates.longitude), parseFloat(point.coordinates.latitude)))
      .setPopup(popup)
      .addTo(self.map);

    self.markers.push(marker);

    self.addRightColMarkerEvent(marker, point.code);
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

  addRecipientMarker: function (latlon) {
    const self = this;

    const el = document.createElement('div');
    el.className = 'bx-marker-recipient';
    el.style.backgroundImage = "url('" + imgDir + "marker-recipient.png')";
    el.style.width = '30px';
    el.style.height = '35px';

    const marker = new mapboxgl.Marker({
      element: el,
    })
      .setLngLat(new mapboxgl.LngLat(parseFloat(latlon.longitude), parseFloat(latlon.latitude)))
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
      html += '<td><img src="' + imgDir + 'markers/' + (i + 1) + '.png" />';
      html += '<div class="bx-parcel-point-title"><a class="bx-show-info-' + point.code + '">' + point.label + '</a></div><br/>';
      html += point.address.street + '<br/>';
      html += point.address.postcode + ' ' + point.address.city + '<br/>';
      html += '<a class="bx-parcel-point-button" data-code="' + point.code + '" data-label="' + point.label + '" data-operator="' + point.operator + '"><b>' + bxTranslation.text.chooseParcelPoint + '</b></a>';
      html += '</td>';
      html += '</tr>';
    }
    html += '</tbody></table>';
    document.querySelector('#bx-pp-container').innerHTML = html;
  },

  selectPoint: function (code, label, operator) {
    const self = this;
    return new Promise(function (resolve, reject) {
      const carrier = self.getSelectedCarrier();
      if (!carrier) {
        reject(bxTranslation.error.carrierNotFound);
      }
      const setPointRequest = new XMLHttpRequest();
      setPointRequest.onreadystatechange = function () {
        if (setPointRequest.readyState === 4) {
          if (setPointRequest.response.success === false) {
            reject(setPointRequest.response.data.message);
          } else {
            resolve(label);
          }
        }
      };
      setPointRequest.open("POST", ajaxurl);
      setPointRequest.setRequestHeader(
        "Content-Type",
        "application/x-www-form-urlencoded"
      );
      setPointRequest.responseType = "json";
      setPointRequest.send("action=set_point&carrier=" + encodeURIComponent(carrier) + "&code=" + encodeURIComponent(code)
        + "&label=" + encodeURIComponent(label) + "&operator=" + encodeURIComponent(operator));
    });
  },

  clearMarkers: function () {
    for (let i = 0; i < this.markers.length; i++) {
      this.markers[i].remove();
    }
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

  getSelectedCarrierText: function(selectedCarrierId) {
    if (null === selectedCarrierId) {
      return "";
    }

    const getSelectedCarrierTextRequest = new XMLHttpRequest();
    getSelectedCarrierTextRequest.onreadystatechange = function () {
      if (getSelectedCarrierTextRequest.readyState === 4) {
        console.log(getSelectedCarrierTextRequest);
        if (200 !== getSelectedCarrierTextRequest.status) {
          return "";
        } else {
          return getSelectedCarrierTextRequest.response;
        }
      }
    };
    console.log(bxAjaxUrl);
    getSelectedCarrierTextRequest.open("POST", bxAjaxUrl);
    getSelectedCarrierTextRequest.setRequestHeader(
      "Content-Type",
      "application/x-www-form-urlencoded"
    );
    getSelectedCarrierTextRequest.responseType = "json";
    getSelectedCarrierTextRequest.send("route=getSelectedCarrierText&carrier=" + encodeURIComponent(selectedCarrierId));
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
