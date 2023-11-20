<?php

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class TrackerChartFactory
{
    protected $root_path;

    public function __construct(
        #[Autowire(env: 'tracker_chart_root_path')]
        string $root_path)
    {
        $this->root_path = $root_path;
    }

    public function create()
    {
        return new TrackerChart($this->root_path);
    }
}
