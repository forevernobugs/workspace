<?php
/**
 * Created by PhpStorm.
 * Index: xiangbohua
 * Date: 14/01/2018
 * Time: 13:46
 */

namespace App\Exceptions;

use Throwable;

class ApiParaException extends KnownLogicException
{

    private $paraName = '';
    public function getParaName()
    {
        return $this->paraName;
    }

    public function __construct($paraName, $message = "", $code = 0, Throwable $previous = null)
    {
        $this->paraName = $paraName;
        parent::__construct($message, $code, $previous);
    }

}