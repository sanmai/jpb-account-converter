<?php
/**
 * This code is licensed under the MIT License.
 *
 * Copyright (c) 2016 Alexey Kopytko
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace KigoBangoShiraberu;

/**
 * @covers \KigoBangoShiraberu\Converter
 */
class ConverterTest extends \LegacyPHPUnit\TestCase
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

    public function testInvalidChecksum()
    {
        $this->expectException(\KigoBangoShiraberu\Exception::class);
        $this->expectExceptionCode(1);

        new Converter('10000', '80000001', '0');
    }

    public function testInvalidCode()
    {
        $this->expectException(\KigoBangoShiraberu\Exception::class);
        $this->expectExceptionCode(2);

        new Converter('200001', '80000001');
    }

    public function testInvalidNumber()
    {
        $this->expectException(\KigoBangoShiraberu\Exception::class);
        $this->expectExceptionCode(3);

        new Converter('15150', '211111111');
    }

    public function testInvalidGiroNumber()
    {
        $this->expectException(\KigoBangoShiraberu\Exception::class);
        $this->expectExceptionCode(3);

        new Converter('01110', '2111110');
    }

    public function testInvalidGiroCheckDigit()
    {
        $this->expectException(\KigoBangoShiraberu\Exception::class);
        $this->expectExceptionCode(4);

        new Converter('01110', '211111', '11');
    }
}
