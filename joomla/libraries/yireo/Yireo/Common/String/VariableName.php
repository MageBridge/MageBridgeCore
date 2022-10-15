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
        $this->string = str_replace(' ', '', ucwords(str_replace('-', ' ', $this->string)));

        if ($capitalize) {
            $this->string = ucfirst($this->string);
        }

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
