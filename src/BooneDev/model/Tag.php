<?php

namespace BooneDev\model;

class Tag {
    private $name;
    private $formattedTag;

    public function __construct(string $name, string $formattedTag) {
        $this->name = $name;
        $this->formattedTag = $formattedTag;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getFormattedTag(): string {
        return $this->formattedTag;
    }
}