<?php
/***
 * Copyright (c) 2016 Alexey Kopytko
 * Released under the MIT license.
 */

namespace KigoBangoShiraberu;

class Exception extends \Exception
{
    const INVALID_CHECKSUM = 1;
    const INVALID_CODE = 2;
    const INVALID_NUMBER = 3;
    const INVALID_GIRO_CHECK_DIGIT = 4;
}