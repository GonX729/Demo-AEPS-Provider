<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the package/service check fails — i.e. the requested service
 * slug is missing or inactive for the user.
 */
class ServiceUnavailableException extends RuntimeException
{
}
