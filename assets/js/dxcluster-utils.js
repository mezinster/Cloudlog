/**
 * DX Cluster shared utility functions.
 * Used by the DX Cluster page (dxcluster/index.php) and the
 * QSO entry DX Cluster tab (qso/index.php).
 *
 * Exposed as: DXCluster.isRbnSpot(), DXCluster.getBandFromFrequency(),
 *             DXCluster.detectModeFromFrequency(), DXCluster.detectMode()
 */
var DXCluster = (function () {
    'use strict';

    /**
     * Returns true if the spotter callsign is an RBN (Reverse Beacon Network) skimmer.
     * RBN skimmers always end with -# (literal hash character).
     */
    function isRbnSpot(spotter) {
        if (!spotter) return false;
        return /\-#+$/.test(spotter.toString().trim());
    }

    /**
     * Returns the amateur band name for a given frequency in kHz,
     * or 'unknown' if it falls outside any recognised band.
     */
    function getBandFromFrequency(freqKhz) {
        var freq = parseFloat(freqKhz);
        if (freq >= 1800   && freq <= 2000)   return '160m';
        if (freq >= 3500   && freq <= 4000)   return '80m';
        if (freq >= 5250   && freq <= 5450)   return '60m';
        if (freq >= 7000   && freq <= 7300)   return '40m';
        if (freq >= 10100  && freq <= 10150)  return '30m';
        if (freq >= 14000  && freq <= 14350)  return '20m';
        if (freq >= 18068  && freq <= 18168)  return '17m';
        if (freq >= 21000  && freq <= 21450)  return '15m';
        if (freq >= 24890  && freq <= 24990)  return '12m';
        if (freq >= 28000  && freq <= 29700)  return '10m';
        if (freq >= 50000  && freq <= 54000)  return '6m';
        if (freq >= 70000  && freq <= 71000)  return '4m';
        if (freq >= 144000 && freq <= 148000) return '2m';
        if (freq >= 420000 && freq <= 450000) return '70cm';
        if (freq >= 1240000 && freq <= 1300000) return '23cm';
        if (freq >= 1300000) return 'ghz';
        return 'unknown';
    }

    /**
     * Estimates the operating mode from a frequency in kHz using:
     *  1. Known FT8/FT4 spot frequencies (±2 kHz tolerance)
     *  2. IARU Region 1 band-plan segments
     * Returns 'cw', 'digi', or 'ssb'.
     */
    function detectModeFromFrequency(freqKhz) {
        // Known FT8 / FT4 spot frequencies (kHz)
        var ft8ft4Freqs = [
            1836,    // FT8 160m
            1840,    // FT8 160m
            1844,    // FT4 160m
            3567,    // FT8 80m
            3573,    // FT8 80m
            3575.5,  // FT4 80m
            5357,    // FT8 60m
            7047.5,  // FT4 40m
            7074,    // FT8 40m
            10130,   // FT8 30m
            10136,   // FT8 30m
            10140,   // FT8 30m
            14074,   // FT8 20m
            14080,   // FT4 20m
            14090,   // FT8 20m
            18095,   // FT8 17m
            18100,   // FT8 17m
            18104,   // FT4 17m
            21074,   // FT8 15m
            21090,   // FT8 15m
            21140,   // FT4 15m
            24911,   // FT8 12m
            24915,   // FT8 12m
            24919,   // FT4 12m
            28074,   // FT8 10m
            28090,   // FT8 10m
            28095,   // FT8 10m
            28180,   // FT4 10m
            50313,   // FT8 6m
            50318,   // FT4 6m
            50323,   // FT8 6m
            70154,   // FT8 4m
            144174,  // FT8 2m
            144170,  // FT4 2m
            432174   // FT8 70cm
        ];
        var tolerance = 2; // kHz
        for (var i = 0; i < ft8ft4Freqs.length; i++) {
            if (Math.abs(freqKhz - ft8ft4Freqs[i]) <= tolerance) return 'digi';
        }

        // 160m
        if (freqKhz >= 1800  && freqKhz < 1840)  return 'cw';
        if (freqKhz >= 1840  && freqKhz < 1843)  return 'digi';
        if (freqKhz >= 1843  && freqKhz <= 2000) return 'ssb';
        // 80m
        if (freqKhz >= 3500  && freqKhz < 3570)  return 'cw';
        if (freqKhz >= 3570  && freqKhz < 3600)  return 'digi';
        if (freqKhz >= 3600  && freqKhz <= 4000) return 'ssb';
        // 60m
        if (freqKhz >= 5351  && freqKhz < 5354)  return 'cw';
        if (freqKhz >= 5354  && freqKhz < 5358)  return 'digi';
        if (freqKhz >= 5358  && freqKhz <= 5366) return 'ssb';
        // 40m
        if (freqKhz >= 7000  && freqKhz < 7040)  return 'cw';
        if (freqKhz >= 7040  && freqKhz < 7060)  return 'digi';
        if (freqKhz >= 7060  && freqKhz <= 7300) return 'ssb';
        // 30m — CW + digi only
        if (freqKhz >= 10100 && freqKhz < 10130)  return 'cw';
        if (freqKhz >= 10130 && freqKhz <= 10150) return 'digi';
        // 20m
        if (freqKhz >= 14000 && freqKhz < 14070) return 'cw';
        if (freqKhz >= 14070 && freqKhz < 14112) return 'digi';
        if (freqKhz >= 14112 && freqKhz <= 14350) return 'ssb';
        // 17m
        if (freqKhz >= 18068 && freqKhz < 18095) return 'cw';
        if (freqKhz >= 18095 && freqKhz < 18110) return 'digi';
        if (freqKhz >= 18110 && freqKhz <= 18168) return 'ssb';
        // 15m
        if (freqKhz >= 21000 && freqKhz < 21070) return 'cw';
        if (freqKhz >= 21070 && freqKhz < 21151) return 'digi';
        if (freqKhz >= 21151 && freqKhz <= 21450) return 'ssb';
        // 12m
        if (freqKhz >= 24890 && freqKhz < 24915) return 'cw';
        if (freqKhz >= 24915 && freqKhz < 24930) return 'digi';
        if (freqKhz >= 24930 && freqKhz <= 24990) return 'ssb';
        // 10m
        if (freqKhz >= 28000 && freqKhz < 28070) return 'cw';
        if (freqKhz >= 28070 && freqKhz < 28190) return 'digi';
        if (freqKhz >= 28190 && freqKhz <= 29700) return 'ssb';
        // 6m
        if (freqKhz >= 50000 && freqKhz < 50100)  return 'cw';
        if (freqKhz >= 50100 && freqKhz < 50300)  return 'ssb';
        if (freqKhz >= 50300 && freqKhz <= 50500) return 'digi';
        if (freqKhz > 50500  && freqKhz <= 54000) return 'ssb';
        // 4m
        if (freqKhz >= 70000 && freqKhz < 70100)  return 'cw';
        if (freqKhz >= 70100 && freqKhz < 70200)  return 'digi';
        if (freqKhz >= 70200 && freqKhz <= 70500) return 'ssb';
        // 2m
        if (freqKhz >= 144000 && freqKhz < 144173) return 'cw';
        if (freqKhz >= 144170 && freqKhz < 144176) return 'digi';
        if (freqKhz >= 144176 && freqKhz <= 148000) return 'ssb';
        // 70cm
        if (freqKhz >= 432000 && freqKhz < 432170) return 'cw';
        if (freqKhz >= 432170 && freqKhz < 432175) return 'digi';
        if (freqKhz >= 432175 && freqKhz <= 450000) return 'ssb';
        // Other VHF/UHF/SHF — default to SSB
        return 'ssb';
    }

    /**
     * Detects the mode of a DX cluster spot.
     * Priority: comment keywords → RBN skimmer → frequency band-plan.
     * Returns 'cw', 'digi', or 'ssb'.
     */
    function detectMode(spot) {
        var comment = ((spot.comment || '') + '').toUpperCase();

        // Digital modes — checked first so RTTY/FT8 etc. are not misclassified as CW/SSB
        var digiKeywords = ['FT8', 'FT4', 'JS8CALL', 'JS8', 'WSPR', 'JT65', 'JT9',
                            'PSK31', 'PSK63', 'PSK', 'RTTY', 'BPSK', 'QPSK',
                            'OLIVIA', 'MFSK', 'MT63', 'FREEDV', 'WINLINK', 'VARA',
                            'PACKET', 'APRS', 'SSTV', 'DIGI', 'DIGITAL'];
        for (var i = 0; i < digiKeywords.length; i++) {
            if (comment.indexOf(digiKeywords[i]) !== -1) return 'digi';
        }

        if (comment.indexOf('CW') !== -1 || comment.indexOf('MORSE') !== -1) return 'cw';

        if (comment.indexOf('SSB')   !== -1 || comment.indexOf('LSB')   !== -1 ||
            comment.indexOf('USB')   !== -1 || comment.indexOf(' AM ')  !== -1 ||
            comment.indexOf('PHONE') !== -1 || comment.indexOf('FM ')   !== -1 ||
            comment.indexOf(' FM')   !== -1) return 'ssb';

        // RBN skimmer spots are always CW
        if (isRbnSpot(spot.spotter)) return 'cw';

        // Fall back to frequency band-plan detection
        return detectModeFromFrequency(parseFloat(spot.frequency));
    }

    return {
        isRbnSpot:              isRbnSpot,
        getBandFromFrequency:   getBandFromFrequency,
        detectModeFromFrequency: detectModeFromFrequency,
        detectMode:             detectMode
    };
}());
