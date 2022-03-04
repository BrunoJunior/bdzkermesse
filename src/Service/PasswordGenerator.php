<?php

namespace App\Service;

use Exception;
use InvalidArgumentException;
use LogicException;

/**
 * Class PasswordGenerator
 * @package App\Service
 * @author bruno <bdesprez@thalassa.fr>
 */
class PasswordGenerator {

    const CHARSET_ALPHA_LOW = 'abcdefghijklmnopqrstuvwxyz';
    const CHARSET_ALPHA_UP = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARSET_NUM = '0123456789';
    const CHARSET_SPE = '*!-_';

    /**
     * Generate a random string
     *
     * @link https://paragonie.com/b/JvICXzh_jhLyt4y3
     *
     * @param int $length - How long should our random string be?
     * @param string $charset - A string of all possible characters to choose from
     * @return string
     * @throws Exception
     */
    public function randomStr(int $length = 32, string $charset = self::CHARSET_ALPHA_LOW): string
    {
        if ($length < 1) {
            // Just return an empty string. Any value < 1 is meaningless.
            return '';
        }

        // Remove duplicate characters from $charset
        $split = str_split($charset);
        $charset = implode('', array_unique($split));

        // This is the maximum index for all of the characters in the string $charset
        $charset_max = strlen($charset) - 1;
        if ($charset_max < 1) {
            // Avoid letting users do: random_str($int, 'a'); -> 'aaaaa...'
            throw new LogicException(
                'random_str - Argument 2 - expected a string that contains at least 2 distinct characters'
            );
        }
        // Now that we have good data, this is the meat of our function:
        $random_str = '';
        for ($i = 0; $i < $length; ++$i) {
            $r = random_int(0, $charset_max);
            $random_str .= $charset[$r];
        }
        return $random_str;
    }

    /**
     * Mélange une chaine
     * @param string $chaine
     * @return string
     * @throws Exception
     */
    public function shuffleStr(string $chaine): string {
        $array = str_split($chaine);
        $size = count($array);
        $keys = array_keys($array);
        for ($i = $size - 1; $i > 0; --$i) {
            $r = random_int(0, $i);
            if ($r !== $i) {
                $temp = $array[$keys[$r]];
                $array[$keys[$r]] = $array[$keys[$i]];
                $array[$keys[$i]] = $temp;
            }
        }
        // Reset indices:
        $array = array_values($array);
        return implode('', $array);
    }

    /**
     * Générer un mot de passe sécurisé avec au moins 6 caractères des minuscules, des majuscules, des chiffres et un caractère spécial
     * @param int $length
     * @return string
     * @throws Exception
     */
    public function generateSecuredPassword(int $length): string {
        if ($length < 6) {
            throw new InvalidArgumentException("generateSecuredPassword - Argument 1 - length must be at least 6 to generate a secured password!");
        }
        $nbChar = round(($length - 1) / 3);
        $charsets = [self::CHARSET_ALPHA_LOW, self::CHARSET_ALPHA_UP, self::CHARSET_NUM];
        $reste = $length;
        $password = '';
        foreach ($charsets as $charset) {
            $password .= $this->randomStr(min($nbChar, $reste), $charset);
            $reste -= $nbChar;
        }
        $password .= $this->randomStr(1, self::CHARSET_SPE);
        return $this->shuffleStr($password);
    }

    /**
     * Génère une clé sécurisé au format <random chaine>-<random chaine>-<random chaine>-<random chaine>
     * @param int $partialLentgh
     * @return string
     * @throws Exception
     */
    public function generateRandomKey(int $partialLentgh = 20): string {
        return implode('-', array_map(function () use ($partialLentgh) {
            return $this->randomStr(
                $partialLentgh,
                self::CHARSET_NUM . self::CHARSET_ALPHA_UP . self::CHARSET_ALPHA_LOW
            );
        }, ['','','','']));
    }
}
