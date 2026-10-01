(function(factory) {
    if (typeof define === 'function' && define.amd) {
        define(['leaflet'], factory);
    } else if (typeof module !== 'undefined') {
        module.exports = factory(require('leaflet'));
    } else {
        factory(window.L);
    }
}(function(L) {
    if (!L) {
        return;
    }

    function toRadians(degrees) {
        return degrees * Math.PI / 180;
    }

    function toDegrees(radians) {
        return radians * 180 / Math.PI;
    }

    function normalizeLongitude(longitude) {
        var normalized = ((longitude + 180) % 360 + 360) % 360 - 180;
        return normalized;
    }

    function julianDate(date) {
        return date.getTime() / 86400000 + 2440587.5;
    }

    function solarPosition(date) {
        var jd = julianDate(date);
        var n = jd - 2451545.0;

        var Ls = (280.460 + 0.9856474 * n) % 360;
        var g = (357.528 + 0.9856003 * n) % 360;

        var lambda = Ls + 1.915 * Math.sin(toRadians(g)) + 0.020 * Math.sin(toRadians(2 * g));
        var epsilon = 23.439 - 0.0000004 * n;

        var lambdaRad = toRadians(lambda);
        var epsilonRad = toRadians(epsilon);

        var declination = Math.asin(Math.sin(epsilonRad) * Math.sin(lambdaRad));
        var rightAscension = Math.atan2(Math.cos(epsilonRad) * Math.sin(lambdaRad), Math.cos(lambdaRad));

        var gmst = 280.46061837 + 360.98564736629 * (jd - 2451545.0);
        var subsolarLongitude = normalizeLongitude(toDegrees(rightAscension) - gmst);

        return {
            declination: declination,
            subsolarLongitude: subsolarLongitude
        };
    }

    function terminatorLatitude(longitude, declination, subsolarLongitude) {
        var hourAngle = toRadians(longitude - subsolarLongitude);
        var tanDeclination = Math.tan(declination);

        if (Math.abs(tanDeclination) < 1e-10) {
            tanDeclination = tanDeclination >= 0 ? 1e-10 : -1e-10;
        }

        var latitude = Math.atan(-Math.cos(hourAngle) / tanDeclination);
        return toDegrees(latitude);
    }

    L.Terminator = L.Polygon.extend({
        options: {
            time: null,
            resolution: 1,
            weight: 1.5,
            color: '#4a6fa5',
            fillColor: '#001f3f',
            fillOpacity: 0.18,
            interactive: false,
            bubblingMouseEvents: false
        },

        initialize: function(options) {
            L.setOptions(this, options);
            this._time = this.options.time || new Date();
            this._setTerminatorLatLngs();
        },

        setTime: function(time) {
            this._time = time || new Date();
            this._setTerminatorLatLngs();
            return this;
        },

        _setTerminatorLatLngs: function() {
            var position = solarPosition(this._time);
            var declination = position.declination;
            var nightOverNorthPole = declination < 0;
            var resolution = Math.max(1, this.options.resolution || 2);

            var curve = [];
            for (var lon = -180; lon <= 180; lon += resolution) {
                curve.push([terminatorLatitude(lon, declination, position.subsolarLongitude), lon]);
            }

            if (curve[curve.length - 1][1] !== 180) {
                curve.push([terminatorLatitude(180, declination, position.subsolarLongitude), 180]);
            }

            // Build 3 copies at -360, 0, +360 so the overlay tiles across
            // multiple world copies when the map is zoomed out.
            var polygons = [];
            for (var offset = -360; offset <= 360; offset += 360) {
                var shiftedCurve = curve.map(function(pt) {
                    return [pt[0], pt[1] + offset];
                });
                var polygon;
                if (nightOverNorthPole) {
                    polygon = [[90, -180 + offset]].concat(shiftedCurve).concat([[90, 180 + offset]]);
                } else {
                    polygon = [[-90, -180 + offset]].concat(shiftedCurve).concat([[-90, 180 + offset]]);
                }
                polygons.push([polygon]);
            }

            this.setLatLngs(polygons);
        }
    });

    L.terminator = function(options) {
        return new L.Terminator(options);
    };

    return L.Terminator;
}));
