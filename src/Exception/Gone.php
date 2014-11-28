<?php namespace Phrest\Exception;

use Symfony\Component\HttpFoundation\Response;

class Gone extends Exception
{
    /**
     * @param integer $code
     * @param array $errors
     * @param string $message
     * @param \Exception $previous
     */
    public function __construct($code = 0,
                                array $errors = [],
                                $message = 'Gone',
                                \Exception $previous = null)
    {
        parent::__construct($message, $code, Response::HTTP_GONE, $errors, $previous);
    }
}