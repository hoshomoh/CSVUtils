<?php
/**
 * Copyright (c) 2021 - 2022 Media.net Advertising FZ-LLC.
 * All Rights Reserved.
 * Created by PhpStorm.
 * User: danish.f
 * Date: 08-09-2021
 * Time: 12:04 PM.
 */

namespace Oshomo\CsvUtils\Helpers;

trait ExtractsAttributeSizeAndType
{
    protected function getSize($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        return mb_strlen($value);
    }

    protected function getType($value): string
    {
        if (is_numeric($value)) {
            return 'numeric';
        }

        return 'string';
    }
}
