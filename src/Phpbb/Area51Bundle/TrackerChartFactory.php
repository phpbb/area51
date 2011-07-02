<?php

namespace Phpbb\Area51Bundle;

class TrackerChartFactory
{
    protected $root_path;

    public function __construct($root_path)
    {
        $this->root_path = $root_path;
    }

    public function create()
    {
        return new TrackerChart($this->root_path);
    }
}
