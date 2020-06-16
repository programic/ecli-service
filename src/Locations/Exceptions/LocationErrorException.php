<?php

namespace Programic\EcliService\Locations\Exceptions;

use Exception;

class LocationErrorException extends Exception
{
    protected $message;

    public function __construct($message)
    {
        parent::__construct();
        $this->message = $message;
    }

    public function __toString()
    {
        if (is_array($this->message)) {
            $this->message = implode("\n", $this->message);
        }
        return 'Location Exception with message: ' . $this->message;
    }
}
