# Japan Post Bank Account Number Converter

[![Build Status](https://travis-ci.org/sanmai/jpb-account-converter.svg?branch=master)](https://travis-ci.org/sanmai/jpb-account-converter)
[![Coverage Status](https://coveralls.io/repos/github/sanmai/jpb-account-converter/badge.svg?branch=master)](https://coveralls.io/github/sanmai/jpb-account-converter?branch=master)

This library lets you convert a specific to Japan Post Bank account code into a familiar branch name and account number pair.

## Installation

As simple as this:

    composer require sanmai/jpb-account-converter

## Usage

    $converter = new \KigoBangoShiraberu\Converter('14030', '12345671');
    var_dump($converter->getAccountType());
    var_dump($converter->getBranchName());
    var_dump($converter->getBranchNameKana());
    var_dump($converter->getAccountNumber());
    echo "\n$converter\n";

Sample output:

	string(6) "普通"
	string(9) "四〇八"
	string(18) "ヨンゼロハチ"
	string(7) "1234567"
	
	銀行名	ゆうちょ銀行
	金融機関コード	９９００
	店番	４０８
	預金種目	普通
	店名	四〇八店（ヨンゼロハチ店）
	口座番号	１２３４５６７

You can confirm its correctness with [the original tool from the Japan Post Bank](http://www.jp-bank.japanpost.jp/kojin/sokin/furikomi/kj_sk_fm_furikomi.html).

Only difference from the original tool is that this library doesn't check a branch number against [the list of known branch numbers](http://www.jp-bank.japanpost.jp/kojin/sokin/furikomi/pdf/tenbangou_tenmei.pdf). Every other check is in place. [See the tests.](tests/KigoBangoShiraberu/ConverterTest.php)

