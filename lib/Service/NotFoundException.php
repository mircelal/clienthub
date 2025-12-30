<?php

namespace OCA\DomainControl\Service;

use Exception;

class NotFoundException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message === "" ? 'Could not find item' : $message);
    }
}
