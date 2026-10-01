<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Format according to https://wwrof.org/cabrillo/cabrillo-qso-data/
class Cabrilloformat {

   public function header($contest_id, $callsign, $claimed_score,
      $operators, $club, $name, $address, $addresscity, $addressstateprovince, $addresspostalcode, $addresscountry, $soapbox, $gridlocator,
      $categoryoverlay, $categorytransmitter, $categorystation, $categorypower, $categorymode, $categoryband, $categoryassisted, $categoryoperator, $email,
      $location = '', $categorytime = '') {
      $cab_header = "";
      $cab_header .= "START-OF-LOG: 3.0" . "\r\n";
      $cab_header .= "CONTEST: " . $contest_id . "\r\n";
      $cab_header .= "CALLSIGN: " . $callsign . "\r\n";

      if (!empty($claimed_score)) {
         $cab_header .= "CLAIMED-SCORE: " . $claimed_score . "\r\n";
      }

      if (!empty($operators)) {
         $cab_header .= "OPERATORS: " . $operators . "\r\n";
      }

      if (!empty($club)) {
         $cab_header .= "CLUB: " . $club . "\r\n";
      }

      $cab_header .= "CATEGORY-OPERATOR: " . $categoryoperator . "\r\n";
      $cab_header .= "CATEGORY-BAND: " . $categoryband . "\r\n";
      $cab_header .= "CATEGORY-ASSISTED: " . $categoryassisted . "\r\n";
      $cab_header .= "CATEGORY-MODE: " . $categorymode . "\r\n";
      $cab_header .= "CATEGORY-POWER: " . $categorypower . "\r\n";
      $cab_header .= "CATEGORY-STATION: " . $categorystation . "\r\n";
      $cab_header .= "CATEGORY-TRANSMITTER: " . $categorytransmitter . "\r\n";

      if (!empty($categoryoverlay)) {
         $cab_header .= "CATEGORY-OVERLAY: " . $categoryoverlay . "\r\n";
      }

      if (!empty($categorytime)) {
         $cab_header .= "CATEGORY-TIME: " . $categorytime . "\r\n";
      }

      if (!empty($location)) {
         $cab_header .= "LOCATION: " . $location . "\r\n";
      }

      if (!empty($name)) {
         $cab_header .= "NAME: " . $name . "\r\n";
      }

      if (!empty($address)) {
         $cab_header .= "ADDRESS: " . $address . "\r\n";
      }

      if (!empty($addresscity)) {
         $cab_header .= "ADDRESS-CITY: " . $addresscity . "\r\n";
      }

      if (!empty($addressstateprovince)) {
         $cab_header .= "ADDRESS-STATE-PROVINCE: " . $addressstateprovince . "\r\n";
      }

      if (!empty($addresspostalcode)) {
         $cab_header .= "ADDRESS-POSTALCODE: " . $addresspostalcode . "\r\n";
      }

      if (!empty($addresscountry)) {
         $cab_header .= "ADDRESS-COUNTRY: " . $addresscountry . "\r\n";
      }

      if (!empty($email)) {
         $cab_header .= "EMAIL: " . $email . "\r\n";
      }

      if (!empty($soapbox)) {
         $cab_header .= "SOAPBOX: " . $soapbox . "\r\n";
      }

      if (!empty($gridlocator)) {
         $cab_header .= "GRID-LOCATOR: " . $gridlocator . "\r\n";
      }

      $cab_header .= "CREATED-BY: Cloudlog" . "\r\n";

      return $cab_header;

   }

   public function footer() {
      return "END-OF-LOG:";
   }

   public function qso($qso) {
      $freq =  substr($qso->COL_FREQ, 0, -3);
      if ($freq > 30000) {
         if ($freq > 250000000) {
            $freq = "LIGHT";
         }
         if ($freq >= 241000000 && $freq <= 250000000 ) {
            $freq = "241G";
         }
         if ($freq >= 134000000 && $freq <= 141000000 ) {
            $freq = "134G";
         }
         if ($freq >= 122250000 && $freq <= 123000000 ) {
            $freq = "122G";
         }
         if ($freq >= 75500000 && $freq <= 81500000 ) {
            $freq = "75G";
         }
         if ($freq >= 47000000 && $freq <= 47200000 ) {
            $freq = "47G";
         }
         if ($freq >= 24000000 && $freq <= 24250000 ) {
            $freq = "24G";
         }
         if ($freq >= 10000000 && $freq <= 10500000 ) {
            $freq = "10G";
         }
         if ($freq >= 5650000 && $freq <= 5850000 ) {
            $freq = "5.7G";
         }
         if ($freq >= 3400000 && $freq <= 3475000 ) {
            $freq = "3.4G";
         }
         if ($freq >= 2320000 && $freq <= 2450000 ) {
            $freq = "2.3G";
         }
         if ($freq >= 1240000 && $freq <= 1300000 ) {
            $freq = "1.2G";
         }
         if ($freq >= 902000 && $freq <= 928000 ) {
            $freq = "902";
         }
         if ($freq >= 430000 && $freq <= 440000 ) {
            $freq = "432";
         }
         if ($freq >= 222000 && $freq <= 225000 ) {
            $freq = "222";
         }
         if ($freq >= 144000 && $freq <= 146000 ) {
            $freq = "144";
         }
         if ($freq >= 70150 && $freq <= 70210 ) {
            $freq = "70";
         }
         if ($freq >= 50000 && $freq <= 52000 ) {
            $freq = "50";
         }
      }

      // Map ADIF modes to the five valid Cabrillo modes: CW, PH, FM, RY, DG
      $ph_modes = ['SSB', 'AM', 'LSB', 'USB', 'PH'];
      $ry_modes = ['RTTY', 'RY'];
      $fm_modes = ['FM'];
      $cw_modes = ['CW'];
      if (in_array($qso->COL_MODE, $cw_modes)) {
         $mode = "CW";
      } elseif (in_array($qso->COL_MODE, $ph_modes)) {
         $mode = "PH";
      } elseif (in_array($qso->COL_MODE, $fm_modes)) {
         $mode = "FM";
      } elseif (in_array($qso->COL_MODE, $ry_modes)) {
         $mode = "RY";
      } else {
         // All digital modes (FT8, FT4, PSK31, JS8, OLIVIA, etc.) -> DG
         $mode = "DG";
      }

      $time = substr($qso->COL_TIME_ON, 0, -3);

      $time = str_replace(":","",$time);

      $returnstring = "QSO: ".sprintf("%6s", $freq)." ".$mode." ".$time." ".sprintf("%-13s", $qso->station_callsign)." ".sprintf("%3s", $qso->COL_RST_SENT)." ";

      if ($qso->COL_STX != NULL) {
         $returnstring .= sprintf("%-6s", sprintf("%03d", $qso->COL_STX)) ." ";
      }

      if ($qso->COL_STX_STRING != "") {
         $returnstring .= $qso->COL_STX_STRING ." ";
      }

      $returnstring .= sprintf("%-13s", $qso->COL_CALL)." ".sprintf("%3s", $qso->COL_RST_RCVD)." ";

      if ($qso->COL_SRX != NULL) {
         $returnstring .= sprintf("%-6s", sprintf("%03d", $qso->COL_SRX)) ." ";
      } elseif ($qso->COL_STX != NULL) {
         // Serial exchange contest but no received serial logged — output placeholder for column alignment
         $returnstring .= sprintf("%-6s", "000") ." ";
      }

      if ($qso->COL_SRX_STRING != "") {
         $returnstring .= $qso->COL_SRX_STRING ." ";
      } elseif ($qso->COL_STX_STRING != "") {
         // String exchange contest but no received exchange logged — output placeholder for column alignment
         $returnstring .= sprintf("%-6s", "?") ." ";
      }

      $returnstring .= "0\n";

      return $returnstring;

   }
}
