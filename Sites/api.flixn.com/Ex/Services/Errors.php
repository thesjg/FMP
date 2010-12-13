<?php

class ExServicesErrors {

    const INVALID_REQUEST         = '0001';
    const INVALID_SERVICE_TYPE    = '0002';
    const INVALID_ARGUMENT        = '0003';

    const UNSUPPORTED_API_VERSION = '0200';

    const UNKNOWN_METHOD          = '0300';
    const UNDEFINED_METHOD_RETURN = '0301';

    const INTERNAL_ERROR          = '0500';

    private $definitions = array();

    public function __construct() {
        $errors = array(
            self::INVALID_REQUEST         => "The request you have made is syntactically invalid.",
            self::INVALID_SERVICE_TYPE    => "Invalid service type.",
            self::INVALID_ARGUMENT        => "One of the supplied arguments is invalid, or a required argument is missing.",
            self::UNSUPPORTED_API_VERSION => "Unsupported API version.",
            self::UNKNOWN_METHOD          => "Unknown method.",
            self::UNDEFINED_METHOD_RETURN => "The method had an undefined return name.",
            self::INTERNAL_ERROR          => "An unknown internal error has occurred."
        );

        $this->definitions = $errors;
    }

    public function getPrintableError($code) {
        if (isset($this->definitions[$code]))
            return $this->definitions[$code];

        return $this->definitions[self::INTERNAL_ERROR];
    }

    protected function addError($code, $error) {
        $this->definitions[$code] = $error;
    }
}