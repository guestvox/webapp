<?php

defined('_EXEC') or die;

class Placeholders_vkye
{
    private $buffer;
    private $format;

    public function __construct($buffer)
    {
        $this->buffer = $buffer;
        $this->format = new Format();
    }

    public function run()
    {
        $this->buffer = $this->main_header();
        $this->buffer = $this->placeholders();

        return $this->buffer;
    }

    private function main_header()
    {
        return $this->format->include_file($this->buffer, 'header');
    }

    private function placeholders()
    {
        $replace = [
            
        ];

        return $this->format->replace($replace, $this->buffer);
    }
}
