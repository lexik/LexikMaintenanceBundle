<?php

namespace Lexik\Bundle\MaintenanceBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The server is currently unavailable (because it is overloaded or down for maintenance)
 *
 * @package LexikMaintenanceBundle
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
class ServiceUnavailableException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string    $message  The internal exception message
     * @param Exception $previous The previous exception
     * @param integer   $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(503, $message, $previous, array(), $code);
    }
}
