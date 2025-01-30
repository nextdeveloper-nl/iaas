<?php

namespace NextDeveloper\IAAS\ResourceLimiters;

abstract class AbstractLimiter
{
    protected $cpu;

    protected $ram;

    protected $disk;

    protected $traffic;
}
