<?php
/***
 * Copyright (c) 2016 Alexey Kopytko
 * Released under the MIT license.
 */

namespace KigoBangoShiraberu;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertToHalfWidth()
    {
        $this->assertEquals('0123456789', Converter::fullToHalfWidth('０１２３４５６７８９'));
    }

    public function testConverter()
    {
        $converter = new Converter('14030', '12345671');
        $this->assertEquals('普通', $converter->getAccountType());
        $this->assertEquals('四〇八', $converter->getBranchName());
        $this->assertEquals('ヨンゼロハチ', $converter->getBranchNameKana());
        $this->assertEquals('1234567', $converter->getAccountNumber());

        $converter = new Converter('15150', '21111111');
        $this->assertEquals('普通', $converter->getAccountType());
        $this->assertEquals('五一八', $converter->getBranchName());
        $this->assertEquals('ゴイチハチ', $converter->getBranchNameKana());
        $this->assertEquals('2111111', $converter->getAccountNumber());

        $converter = new Converter('00580', '654321');
        $this->assertEquals('当座', $converter->getAccountType());
        $this->assertEquals('〇五九', $converter->getBranchName());
        $this->assertEquals('ゼロゴキユウ', $converter->getBranchNameKana());
        $this->assertEquals('0654321', $converter->getAccountNumber());

        $converter = new Converter('00160', '100001', '0');
        $this->assertEquals('当座', $converter->getAccountType());
        $this->assertEquals('〇一九', $converter->getBranchName());
        $this->assertEquals('ゼロイチキユウ', $converter->getBranchNameKana());
        $this->assertEquals('0100001', $converter->getAccountNumber());

        $this->assertEquals("銀行名	ゆうちょ銀行\n金融機関コード	９９００\n店番	０１９\n預金種目	当座\n店名	〇一九店（ゼロイチキユウ店）\n口座番号	０１００００１\n", (string) $converter);
    }

    public function testCheckDigit()
    {
        $this->assertEquals(8, Converter::calculateCheckDigit(14080, 12712721));
        $this->assertEquals(1, converter::calculateCheckDigit(10000, 81111111));
        $this->assertEquals(9, converter::calculateCheckDigit(10000, 80000001));
        $this->assertEquals(5, converter::calculateCheckDigit(16150, 12233411));
        $this->assertEquals(7, converter::calculateCheckDigit(10001, 10001));
    }

    /**
     * @expectedException \KigoBangoShiraberu\Exception
     * @expectedExceptionCode 1
     */
    public function testInvalidChecksum()
    {
        new Converter('10000', '80000001', '0');
    }

    /**
     * @expectedException \KigoBangoShiraberu\Exception
     * @expectedExceptionCode 2
     */
    public function testInvalidCode()
    {
        new Converter('200001', '80000001');
    }

    /**
     * @expectedException \KigoBangoShiraberu\Exception
     * @expectedExceptionCode 3
     */
    public function testInvalidNumber()
    {
        new Converter('15150', '211111111');
    }

    /**
     * @expectedException \KigoBangoShiraberu\Exception
     * @expectedExceptionCode 3
     */
    public function testInvalidGiroNumber()
    {
        new Converter('01110', '2111110');
    }

    /**
     * @expectedException \KigoBangoShiraberu\Exception
     * @expectedExceptionCode 4
     */
    public function testInvalidGiroCheckDigit()
    {
        new Converter('01110', '211111', '11');
    }

}
