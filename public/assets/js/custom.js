/**
 * Display a listing of the resource.
 *
 * @return Toster
 */
function successToster(heading,text){
    $.toast({
        heading: heading,
        icon: 'success', // info success warning error
        text: text,
        position: 'top-right',// bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
        stack: false,
        allowToastClose:true,
        showHideTransition: 'slide', // fade|plain|slide
        hideAfter:5000, // false
        textAlign:"left",
        loader:true,
        loaderBg: '#ffffff',  // Background color of the toast loader
    })
}

function validToster(heading,text){
    $.toast({
        heading: heading,
        icon: 'error', // info success warning error
        text: text,
        position: 'top-right',// bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
        stack: false,
        allowToastClose:true,
        showHideTransition: 'plain', // fade|plain|slide
        hideAfter:10000, // false
        textAlign:"center",
        loader:true,
        loaderBg: '#ffffff',  // Background color of the toast loader
    })
}

function errorToster(heading,text){
    $.toast({
        heading: heading,
       // icon: 'error', // info success warning error
        text: text,
        position: 'top-right',// bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
        stack: false,
        allowToastClose:true,
        showHideTransition: 'plain', // fade|plain|slide
        hideAfter:10000, // false
        textAlign:"left",
        loader:true,
        loaderBg: '#ffffff',  // Background color of the toast loader
    })
}

function warningToster(heading,text){
    $.toast({
        heading: heading,
        icon: 'warning', // info success warning error
        text: text,
        position: 'top-right',// bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
        stack: false,
        allowToastClose:true,
        showHideTransition: 'plain', // fade|plain|slide
        hideAfter:10000, // false
        textAlign:"left",
        loader:true,
        loaderBg: '#ffffff',  // Background color of the toast loader
    })
}

/**
 * Display a listing of the resource.
 *
 * @return dataTable
 */

// onlyPrice: accepts positive numbers and decimals only (no letters, no negative)
$(document).on('keydown', '.onlyPrice', function (e) {
    var allowed = [8, 9, 13, 46, 37, 38, 39, 40]; // backspace, tab, enter, delete, arrows
    var isDecimalPoint = e.key === '.';
    var isDigit = e.key >= '0' && e.key <= '9';
    if (!isDigit && !isDecimalPoint && !allowed.includes(e.keyCode)) {
        e.preventDefault();
    }
});
$(document).on('input', '.onlyPrice', function () {
    var val = $(this).val();
    // Remove anything that's not a digit or decimal point
    val = val.replace(/[^0-9.]/g, '');
    // Allow only one decimal point
    var parts = val.split('.');
    if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
    if (parseFloat(val) < 0 || val === '-') val = '';
    $(this).val(val);
});

// notNegative: prevents negative values, still allows typing numbers freely
$(document).on('input', '.notNegative', function () {
    var val = parseFloat($(this).val());
    if (!isNaN(val) && val < 0) {
        $(this).val(0);
    }
});
$(document).on('keydown', '.notNegative', function (e) {
    if (e.key === '-') e.preventDefault();
});

$(".password-meter-input").on("keyup", function () {
    var e = 0,
        t = $(this).val(),
        c = new RegExp("[A-Z]"),
        o = new RegExp("[a-z]"),
        n = new RegExp("[0-9]"),
        r = new RegExp("^(?=.*?[#?!@$%^&*-]).{1,}$");
    7 < t.length && e++,
    0 < t.length && t.match(c) && e++,
    0 < t.length && t.match(o) && e++,
    0 < t.length && t.match(n) && e++,
    0 < t.length && t.match(r) && e++,
     $(".progress-bar-bar")[0].style.width = 20 * e + "%"
})

/**
 * Renders a 0-5 half-star rating widget into `container` and keeps it
 * synced with the hidden input referenced by `container`'s data-target.
 */
function initStarRatingInput(container, initialValue) {
    if (!container) return;

    var hiddenInput = document.querySelector(container.dataset.target);
    var valueLabel = container.parentElement.querySelector('.star-rating-value');
    var value = Math.min(5, Math.max(0, parseFloat(initialValue) || 0));

    function render(previewValue) {
        var v = previewValue === undefined ? value : previewValue;
        container.innerHTML = '';
        for (var i = 5; i >= 1; i--) {
            var full = v >= i;
            var half = !full && v >= (i - 0.5);
            var icon = document.createElement('i');
            icon.className = full ? 'fas fa-star' : (half ? 'fas fa-star-half-stroke' : 'far fa-star');
            icon.style.color = (full || half) ? '#f5a623' : '#dee2e6';
            icon.dataset.star = i;

            icon.addEventListener('mousemove', function (e) {
                var star = parseInt(this.dataset.star, 10);
                var isLeftHalf = (e.offsetX / this.offsetWidth) < 0.5;
                render(isLeftHalf ? star - 0.5 : star);
            });
            icon.addEventListener('mouseleave', function () {
                render();
            });
            icon.addEventListener('click', function (e) {
                var star = parseInt(this.dataset.star, 10);
                var isLeftHalf = (e.offsetX / this.offsetWidth) < 0.5;
                value = isLeftHalf ? star - 0.5 : star;
                if (hiddenInput) hiddenInput.value = value;
                if (valueLabel) valueLabel.textContent = value;
                render();
            });

            container.appendChild(icon);
        }
    }

    render();
    if (valueLabel) valueLabel.textContent = value;
}

/**
 * Injects the Google Maps JS API script once, on demand, and resolves once
 * `google.maps` is usable.
 *
 * The script used to be hardcoded in admin/layout/inc/_js.blade.php, which
 * meant all 182 admin views paid for ~250KB of Maps on every page load even
 * though only the two nearest_schools forms ever call initMapPicker. Loading
 * it here means a page that never opens a map picker never fetches it.
 *
 * `window.googleMapsKey` / `window.googleMapsMapId` are still printed by the
 * Blade layout — they're just config, not the library itself.
 */
var loadGoogleMaps = (function () {
    var promise = null;

    return function () {
        if (promise) return promise;

        promise = new Promise(function (resolve, reject) {
            if (window.google && window.google.maps) {
                resolve(window.google);
                return;
            }

            if (!window.googleMapsKey) {
                reject(new Error('GOOGLE_MAPS_API_KEY is not configured'));
                return;
            }

            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js'
                + '?key=' + encodeURIComponent(window.googleMapsKey)
                + '&libraries=places&loading=async&callback=Function.prototype';
            script.async = true;
            script.onerror = function () {
                // Allow a later attempt to retry rather than caching the failure.
                promise = null;
                reject(new Error('Failed to load the Google Maps JS API'));
            };
            document.head.appendChild(script);

            // The script loads async; poll briefly until google.maps appears.
            var triesLeft = 40;
            (function poll() {
                if (window.google && window.google.maps) {
                    resolve(window.google);
                } else if (triesLeft-- > 0) {
                    setTimeout(poll, 100);
                } else {
                    promise = null;
                    reject(new Error('Google Maps JS API did not become ready'));
                }
            })();
        });

        return promise;
    };
})();

/**
 * Renders a Google Map into `#{options.mapId}` letting the admin click/drag
 * a marker (or search an address via Places Autocomplete) to fill the
 * latitude/longitude inputs referenced by `options.latInputId`/`lngInputId`.
 *
 * Loads the Maps JS API on demand via googleMapsLoader() above.
 */
function initMapPicker(options) {
    var mapEl = document.getElementById(options.mapId);
    if (!mapEl) return;

    loadGoogleMaps().then(function () {
        renderMapPicker(options, mapEl);
    }).catch(function (err) {
        console.warn('[map-picker]', err.message);
        mapEl.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted small px-3 text-center">'
            + 'Google Maps API key is not configured (GOOGLE_MAPS_API_KEY).</div>';
    });
}

function renderMapPicker(options, mapEl) {
    var latInput = document.getElementById(options.latInputId);
    var lngInput = document.getElementById(options.lngInputId);
    var searchInput = options.searchId ? document.getElementById(options.searchId) : null;

    var defaultLat = options.lat || 24.7136;
    var defaultLng = options.lng || 46.6753;
    var hasInitialPosition = !!(options.lat && options.lng);

    var map = new google.maps.Map(mapEl, {
        center: { lat: defaultLat, lng: defaultLng },
        zoom: hasInitialPosition ? 14 : 6,
        mapId: (window.googleMapsMapId || undefined),
        streetViewControl: false,
        mapTypeControl: false,
    });

    var marker = new google.maps.Marker({
        map: map,
        position: { lat: defaultLat, lng: defaultLng },
        draggable: true,
    });

    function setPosition(lat, lng) {
        lat = parseFloat(lat.toFixed(7));
        lng = parseFloat(lng.toFixed(7));
        marker.setPosition({ lat: lat, lng: lng });
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
    }

    if (hasInitialPosition) {
        setPosition(defaultLat, defaultLng);
    }

    map.addListener('click', function (e) {
        setPosition(e.latLng.lat(), e.latLng.lng());
    });

    marker.addListener('dragend', function () {
        var pos = marker.getPosition();
        setPosition(pos.lat(), pos.lng());
    });

    // Fix map sizing when initialized inside a hidden/animated Bootstrap modal.
    setTimeout(function () {
        google.maps.event.trigger(map, 'resize');
        map.setCenter(marker.getPosition());
    }, 300);

    if (searchInput && google.maps.places) {
        var autocomplete = new google.maps.places.Autocomplete(searchInput, {
            fields: ['geometry'],
        });
        autocomplete.bindTo('bounds', map);

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;

            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();
            map.setCenter({ lat: lat, lng: lng });
            map.setZoom(15);
            setPosition(lat, lng);
        });
    }
}

// إصلاح: slideUp في pcoded بتحط height:0 بس مش display:none
// فالـ sub-submenu بيفضل visible ويطل على الـ parent
// نستخدم transitionend عشان لما الـ animation تخلص نضيف display:none
document.addEventListener('transitionend', function (e) {
    var el = e.target;
    if (
        e.propertyName === 'height' &&
        el.classList.contains('pc-submenu') &&
        el.style.height === '0px'
    ) {
        el.style.display = 'none';
    }
});

// Global Enter key → submit the active form
$(document).on('keydown', function (e) {
    if (e.key !== 'Enter') return;

    // Ignore if focus is on a textarea (allow newlines)
    if ($(e.target).is('textarea')) return;

    // Find the innermost visible open modal first, then fall back to any visible form
    var $form = null;

    var $modal = $('.modal.show').last();
    if ($modal.length) {
        $form = $modal.find('form:visible').first();
    }

    if (!$form || !$form.length) {
        $form = $('form:visible').first();
    }

    if (!$form || !$form.length) return;

    e.preventDefault();
    $form.find('[type="submit"]').first().trigger('click');
});
