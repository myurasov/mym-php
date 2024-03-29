<?php

/**
 * String utils
 *
 * @copyright 2010-2011 Misha Yurasov
 * @package mym
 */

namespace mym\Util;

class Strings
{
  /**
   * Converts string to boolean
   * @param string $string
   * @return boolean
   */
  public static function str2Bool($string)
  {
    $string = strtolower($string);

    if ($string == 'y' || $string == 'yes' || $string == 't' || $string == 'true')
    {
      return true;
    }
    else if ($string == 'n' || $string == 'no' || $string == 'f' || $string == 'false')
    {
      return false;
    }
    else if (is_numeric($string))
    {
      if (floatval($string) == 0)
      {
        return false;
      }
      else
      {
        return true;
      }
    }
    else
    {
      return false;
    }
  }

  /**
   * Splits string into the array of strings with quotes consideration
   *
   * @param str $str
   * @param char $delimiter
   * @return array
   * @author Misha Yurasov
   */
  public static function explodeString($input, $delimiter = ' ')
  {
    $q1 = false;  // single quote level
    $q2 = false;  // double quote level
    $c = '';      // current char
    $w = '';      // current word
    $j = 0;       // index counter
    $n = false;   // next word flag

    $len = strlen($input);

    for ($i = 0; $i < $len; $i++)
    {
      $c = $input{$i}; // current char

      switch ($c)
      {
        case "'":
        {
          if ($q2 == false)
          {
            $q1 = !$q1;
          }

          break;
        }

        case '"':
        {
          if ($q1 == false)
          {
            $q2 = !$q2;
          }

          break;
        }

        case $delimiter:
        {
          if (!($q1 || $q2))
          {
            $n = true;
            $c = '';
          }

          break;
        }
      }

      $w .= $c;

      if ($n || $i == $len - 1)
      {
        if ($w{0} == "'" || $w{0} == '"')
        {
          $w = trim($w, $w{0});
        }

        $ww[$j++] = $w;
        $w = '';
        $n = false;
      }
    }

    return $ww;
  }

  /**
   * Converts time in seconds to human-redable string
   *
   * @param float $seconds
   * @param integer $precision
   * @param boolean $stripEmptyUnits
   * @param integer $unitsNamingLevel
   * @param boolean $twoDigitHMS
   * @param boolean $forceMinutes
   * @return string
   */
  public static function formatTime(
    $seconds, $precision = 0, $stripEmptyUnits = true,
    $unitsNamingLevel = 3, $twoDigitHMS = false,
    $forceMinutes = false)
  {
    $result = '';
    $prev_entry_present = false;
    $seconds = round($seconds, $precision);

    // Units' names

    switch ($unitsNamingLevel)
    {
      case 0:
      {
        $units = array(
          'd' => 'd',
          'dd' => 'd',
          'w' => 'w',
          'ww' => 'w'
        );

        break;
      }

      case 1:
      {
        $units = array(
          's' => 's',
          'ss' => 's',
          'm' => 'm',
          'mm' => 'm',
          'h' => 'h',
          'hh' => 'h',
          'd' => 'd',
          'dd' => 'd',
          'w' => 'w',
          'ww' => 'w'
        );

        break;
      }

      case 2:
      {
        $units = array(
          's' => ' sec',
          'ss' => ' sec',
          'm' => ' min',
          'mm' => ' min',
          'h' => ' hr',
          'hh' => ' hr',
          'd' => ' dy',
          'dd' => ' dy',
          'w' => ' wk',
          'ww' => ' wk'
        );

        break;
      }

      case 3:
      {
        $units = array(
          's' => ' second',
          'ss' => ' seconds',
          'm' => ' minute',
          'mm' => ' minutes',
          'h' => ' hour',
          'hh' => ' hours',
          'd' => ' day',
          'dd' => ' days',
          'w' => ' week',
          'ww' => ' weeks'
        );

        break;
      }
    }

    // Seconds

    $seconds_fraction = fmod($seconds, 60);

    if ($seconds_fraction > 0 || !$stripEmptyUnits || ($seconds < 1))
    {
      $result = $unitsNamingLevel > 0

      ? (($twoDigitHMS && $seconds_fraction < 10 && $seconds >= 60 ? '0' : '' /* zero padding */)
          . sprintf('%.' . $precision . 'f%s',
            $seconds_fraction, (floor($seconds_fraction) % 10 != 1)
              || ($precision > 0) ? $units['ss'] : $units['s']))

        : (($twoDigitHMS && $seconds_fraction < 10 && $seconds >= 60 ? '0' : '' /* zero padding */)
          . sprintf('%.' . $precision . 'f', $seconds_fraction));

      $prev_entry_present = true;
    }

    // Minutes

    if ($seconds >= 60 || $forceMinutes)
    {
      $minutes = floor($seconds / 60) % 60;
      //$prev_entry_present = $prev_entry_present || $seconds > 0;

      if ($prev_entry_present || $minutes > 0)
      {
        if ($seconds < 10 && $unitsNamingLevel == 0)
        {
          $result = '0' . $result;
        }

        $result = $unitsNamingLevel > 0

           ? sprintf($twoDigitHMS && $seconds >= 3600 ? '%02d%s' : '%d%s',
             $minutes, $minutes % 10 != 1 ? $units['mm'] : $units['m']) . ($prev_entry_present ? ' ' : '') . $result

          : sprintf('%02d',
            $minutes) . ($prev_entry_present ? ':' : '') . $result ;
      }
    }

    // Hours

    if ($seconds >= 3600)
    {
      $hours = floor($seconds / 3600) % 24;
      $prev_entry_present = $prev_entry_present || $minutes > 0;

      if ($prev_entry_present || $hours > 0)
      {
        $result = $unitsNamingLevel > 0

          ? sprintf($twoDigitHMS && $seconds >= 86400 ? '%02d%s' : '%d%s',
            $hours, $hours % 10 != 1 ? $units['hh'] : $units['h']) . ($prev_entry_present ? ' ' : '') . $result

          : sprintf('%02d',
            $hours) . ($prev_entry_present ? ':' : '') . $result;
      }
    }

    // Days

    if ($seconds >= 86400)
    {
      $days = floor($seconds / 86400) % 7;
      $prev_entry_present = $prev_entry_present || $hours > 0;
      //
      if ($prev_entry_present || $days > 0)
      {
        $result = sprintf('%d%s',
          $days, $days % 10 != 1 ? $units['dd'] : $units['d']) . ($prev_entry_present ? ' ' : '') . $result;
      }
    }

    // Weeks

    if ($seconds >= 604800)
    {
      $weeks = floor($seconds / 604800);
      $prev_entry_present = $prev_entry_present || $days > 0;
      //
      if ($prev_entry_present || $weeks > 0)
      {
        $result = sprintf('%d%s',
          $weeks, $weeks % 10 != 1 ? $units['ww'] : $units['w']) . ($prev_entry_present ? ' ' : '') . $result;
      }
    }

    return $result;
  }

  /**
   * Parse URI into components, according to RFC 2396
   * "Uniform Resource Identifiers (URI): Generic Syntax"
   *
   * @see http://www.ietf.org/rfc/rfc2396.txt
   *
   * To parse "authority" component (user:pass@host:port):
   *  ^(?:(.+?)(?::(.*))?@)?([A-Za-z0-9\-\.]+)(?::([0-9]+))?$
   *
   * @param string $uri
   * @return array
   */
  public static function parseUri($uri)
  {
    $m = array();

    if (preg_match('/^(?:([^:\/?#]+):)?(?:\/\/([^\/?#]*))?([^?#]*)(?:\?([^#]*))?(?:#(.*))?$/', $uri, $m))
    {
      return array(
        'uri'       => $m[0],
        'scheme'    => isset($m[1]) ? $m[1] : '',
        'authority' => isset($m[2]) ? $m[2] : '',
        'path'      => isset($m[3]) ? $m[3] : '',
        'query'     => isset($m[4]) ? $m[4] : '',
        'fragment'  => isset($m[5]) ? $m[5] : ''
      );
    }
    else
    {
      return false;
    }
  }

  public static function fixUriProtocol($uri)
  {
    $uri = \parse_url($url, \PHP_URL_SCHEME);
  }

  const ALPHABET_BINARY = '01';
  const ALPHABET_OCTAL = '01234567';
  const ALPHABET_HEXADEMICAL = '01234567890abcdef';
  const ALPHABET_ALPHANUMERICAL = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
  const ALPHABET_ALPHANUMERICAL_LOWCASE = '0123456789abcdefghijklmnopqrstuvwxyz';

  /**
   * Get number representation in the specified radix
   *
   * @param integer $number
   * @param string $alphabet
   * @return string
   */
  public static function convertToRadix($number, $alphabet)
  {
    if ($number == 0)
    {
      return substr($alphabet, 0, 1);
    }
    elseif ($number > 0)
    {
      $base = strlen($alphabet);
      $result = '';

      while ($number > 0)
      {
        $orderVal = $number % $base;
        $number = ($number - $orderVal) / $base;
        $result = substr($alphabet, $orderVal, 1) . $result;
      }
    }
    else // number < 0
    {
      throw new \Exception("\$number sould be positive integer");
    }

    return $result;
  }

  /**
   * Create string from random characters
   *
   * @param int $length
   * @param string $alphabet
   * @param int $bitStrength If $bitStrength is passed, $length is computed based on it
   * @return string
   */
  public static function createRandomString($length, $alphabet, $bitStrength = null)
  {
    // bit_strength = log2(alphabet_length) * num_chars
    // num_chars = bit_strength_required / log2(alphabet_lenght)

    $base = strlen($alphabet);

    if (!is_null($bitStrength))
      $length = (int) ceil($bitStrength / log($base, 2));

    $result = '';

    for ($i = 0; $i < $length; $i++)
    {
      $order = mt_rand(0, $base - 1);
      $result = substr($alphabet, $order, 1) . $result;
    }

    return $result;
  }

  /**
   * Convert file size given in bytes to human-redable string
   *
   * @param int $size
   * @param int $precision
   * @return str
   */
  public static function formatFileSize($size, $precision = 2)
  {
    if (round($size / 1024, $precision) < 1)
    {
      return sprintf('%d byte%s', $size, ($size == 1 ? '' : 's'));
    }
    elseif (round($size / 1024 / 1024, $precision) < 1)
    {
      return sprintf('%.' . $precision . 'f KB', $size / 1024);
    }
    elseif (round($size / 1024 / 1024 / 1024, $precision) < 1)
    {
      return sprintf('%.' . $precision . 'f MB', $size / 1024 / 1024);
    }
    elseif (round($size / 1024 / 1024 / 1024 / 1024, $precision) < 1)
    {
      return sprintf('%.' . $precision . 'f GB', $size / 1024 / 1024 / 1024);
    }
    else
    {
      return sprintf('%.' . $precision . 'f TB', $size / 1024 / 1024 / 1024 / 1024);
    }
  }
}