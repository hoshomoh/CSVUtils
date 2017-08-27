<?php
/**
 * Created by PhpStorm.
 * User: oshomo.oforomeh
 * Date: 27/08/2017
 * Time: 9:26 PM
 */

namespace Oshomo\CsvUtils\Exceptions;


use Exception;

class InvalidRuleDeclarationException extends Exception
{
    /**
     * Json encodes the message and calls the parent constructor.
     *
     * @param null $message
     * @internal param int $code
     * @internal param Exception|null $previous
     */
    public function __construct($message = null)
    {
        parent::__construct(json_encode($message));
    }

}