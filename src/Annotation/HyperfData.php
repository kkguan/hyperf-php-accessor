<?php

declare(strict_types=1);

namespace Hyperf\PhpAccessor\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class HyperfData extends AbstractAnnotation
{
    public function __construct() {}
}
