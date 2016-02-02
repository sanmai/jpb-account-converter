<?php
/***
 * Copyright (c) 2016 Alexey Kopytko
 * Released under the MIT license.
 */

namespace KigoBangoShiraberu;

class Converter
{
    const NORMAL_ACCOUNT_SUFFIX = '8';
    const GIRO_ACCOUNT_SUFFIX = '9';

    /**
     * Code number (always 5 digits) with an actual check digit on fourth position
     * @var string
     */
    private $kigou;

    /**
     * Check digit for giro accounts (always 1 digit) - basically ignored except for validation
     * @var string
     */
    private $naka;

    /**
     * Account number (up to 8 digits for integrated accounts, and up to 6 digits for giro accounts)
     * @var string
     */
    private $bangou;

    /**
     * Current account is integrated account
     * @var bool
     */
    private $isNormalAccount = false;

    /**
     * Branch number
     * @var string
     */
    private $branchNumber;

    /**
     *
     * Integrated account number format: Code number (5 digits, kigou) + account number (up to 8 digits, bangou)
     * E.g.: 10540-12045071
     *
     * Giro account number format: Code number (5 digits) + check digit (naka) + account number （up to 6 digits）
     * E.g.: 00160-0-100001
     *
     * @param string $kigou Code number
     * @param strin $bangou Account number
     * @param strin $naka Giro account check digit
     */
    public function __construct($kigou, $bangou, $naka = '')
    {
        // make sure we have half-width (normal) digits only
        $this->kigou = self::fullToHalfWidth($kigou);
        $this->naka = self::fullToHalfWidth($naka);
        $this->bangou = self::fullToHalfWidth($bangou);

        if (!preg_match('/^[01]\d{3}0$/', $this->kigou)) {
            throw new Exception('Code must be made of exactly five digits, starting with 1 or 0, ending with 0', Exception::INVALID_CODE);
        }

        // first digit is 1 for normal accounts
        $this->isNormalAccount = $this->kigou[0] == '1';

        $this->branchNumber = substr($this->kigou, 1, 2);
        // prefix depends on account type
        $this->branchNumber .= $this->isNormalAccount ? self::NORMAL_ACCOUNT_SUFFIX : self::GIRO_ACCOUNT_SUFFIX;

        if ($this->isNormalAccount) {
            if (!preg_match('/^\d{1,7}1$/', $this->bangou)) {
                throw new Exception('Normal account number may only contain up to eight digits, ending with 1', Exception::INVALID_NUMBER);
            }
            // we won't be checking if a branch number is valid for normal accounts
            // whole list of valid branch numbers:
            // 008 018 028 038 048 058 068 078 088 098 108 118 128 208 218 228 238 248 318 328 338 408 418 428 438 448 458 468 478 518 528 538 548 558 618 628 638 648 708 718 728 738 748 768 778 788 798 818 828 838 848 858 868 908 918 928 938 948 958 968 978 988 998 019 029 039 049 059 069 079 089 099 109 119 129 139 149 159 169 179 189 199 209 219 229 239 249 259 269 279 289
        } else {
            if (!preg_match('/^\d?$/', $this->naka)) {
                throw new Exception('Check digit of a giro account may only be one digit or none', Exception::INVALID_GIRO_CHECK_DIGIT);
            }

            if (!preg_match('/^\d{1,6}$/', $this->bangou)) {
                throw new Exception('Giro account number may only contain up to six digits', Exception::INVALID_NUMBER);
            }

            // from what we know, for giro accounts the number must be between 019 and 289
            // but we won't check this either - our info could be outdated
        }

        if (!$this->hasValidCheckDigit()) {
            throw new Exception("Invalid check digit. Proper: {$this->getCheckDigit()}", Exception::INVALID_CHECKSUM);
        }
    }

    public function getAccountType()
    {
        if ($this->isNormalAccount) {
            return '普通'; // both 普通 and 貯蓄 are valid for transfers
        } else {
            return '当座';
        }
    }

    private $vocKanji = ['〇', '一', '二', '三', '四', '五', '六', '七', '八', '九'];

    /**
     * Get branch name in kanji
     * @return string
     */
    public function getBranchName()
    {
        return join('', array_map(function ($digit) {
            return $this->vocKanji[$digit];
        }, str_split($this->branchNumber)));
    }

    private $vocKana = ['ゼロ', 'イチ', 'ニ', 'サン', 'ヨン', 'ゴ', 'ロク', 'ナナ', 'ハチ', 'キユウ'];

    /**
     * Get branch name in kana
     * @return string
     */
    public function getBranchNameKana()
    {
        return join('', array_map(function ($digit) {
            return $this->vocKana[$digit];
        }, str_split($this->branchNumber)));
    }

    const ACCOUNT_NUMBER_LENGTH = 7;

    /**
     * Get padded account number
     * @return string
     */
    public function getAccountNumber()
    {
        $accNumber = $this->bangou;

        if ($this->isNormalAccount) {
            // for normal accounts we need to get rid of the last digit
            $accNumber = substr($accNumber, 0, -1);
        }

        // resulting account number must be exactly 7 digits
        return str_pad($accNumber, self::ACCOUNT_NUMBER_LENGTH, '0', STR_PAD_LEFT);
    }

    public function __toString()
    {
        ob_start();
        echo "銀行名\tゆうちょ銀行\n";
        echo "金融機関コード\t9900\n";
        echo "店番\t{$this->branchNumber}\n";
        echo "預金種目\t{$this->getAccountType()}\n";
        echo "店名\t{$this->getBranchName()}店（{$this->getBranchNameKana()}店）\n";
        echo "口座番号\t{$this->getAccountNumber()}\n";
        // we'll convert to full-width to make it look like on their website
        return mb_convert_kana(ob_get_clean(), 'A');
    }

    private function getCheckDigit()
    {
        return self::calculateCheckDigit($this->kigou, $this->bangou);
    }

    private function hasValidCheckDigit()
    {
        return $this->getCheckDigit() == self::extractCheckDigit($this->kigou);
    }

    public static function extractCheckDigit($kigou)
    {
        return substr($kigou, 3, 1);
    }

    /**
     * Calculate a check digit
     * @param string $kigou
     * @param string $bangou
     * @return number
     */
    public static function calculateCheckDigit($kigou, $bangou)
    {
        $array = str_split($kigou);
        $array[3] = 0; // checksum digit

        $array = array_merge($array, str_split(str_pad($bangou, 8, '0', STR_PAD_LEFT)));

        $sum = 0; // every three items...
        for ($index = 0; $index < count($array)-2; $index += 3) {
            $sum += $array[$index]; // add item
            $sum += $array[$index+1] * 3; // add item * 3
            $val = $array[$index+2]; // and then this:
            $sum += ($val < 5) ? $val * 2 : $val * 2 - 9;
        }
        $sum += $array[count($array)-1];

        return $sum % 10;
    }

    /**
     * Converts "zen-kaku" alphabets and numbers to "han-kaku"
     * @param string $number
     * @return string
     */
    public static function fullToHalfWidth($number)
    {
        return mb_convert_kana($number, 'a');
    }
}