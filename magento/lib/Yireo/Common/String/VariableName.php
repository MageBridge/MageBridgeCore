<?php

namespace Yireo\Common\String;

class VariableName
{
    private $string = '';

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function colonsToClassName()
    {
        $parts = explode(':', $this->string);
        $classNameParts = [];

        foreach ($parts as $part) {
            $classNameParts[] = (new self($part))->toCamelCase()->toString();
        }

        $this->string = implode('\\', $classNameParts);

        return $this;
    }

    public function toCamelCase($capitalize = true)
    {
        $string = str_replace(' ', '_', $this->string);

        $parts = explode('_', $string);
        $classNameParts = [];

        foreach ($parts as $part) {
            $classNameParts[] = ucfirst($part);
        }

        $string = implode('', $classNameParts);

        if ($capitalize !== true) {
            $string = lcfirst($string);
        }

        $this->string = $string;

        return $this;
    }

    public function toString()
    {
        return $this->string;
    }

    public function __toString()
    {
        return (string) $this->toString();
    }
}
