<?php

declare(strict_types=1);

namespace PhpAccessor\Attribute;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use PhpAccessor\Attribute\Map\AccessorType;
use PhpAccessor\Attribute\Map\NamingConvention;
use PhpAccessor\Attribute\Map\PrefixConvention;

/**
 * 替换PHP Accessor的Data注解,用于支持Hyperf的注解收集.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Data extends AbstractAnnotation
{
    /**
     * @see NamingConvention
     */
    private int $namingConvention;

    /**
     * @see AccessorType
     */
    private string $accessorType;

    /**
     * @see PrefixConvention
     */
    private int $prefixConvention;

    public function __construct(
        int $namingConvention = NamingConvention::NONE,
        string $accessorType = AccessorType::BOTH,
        int $prefixConvention = PrefixConvention::GET_SET
    ) {
        $this->namingConvention = $namingConvention;
        $this->accessorType = $accessorType;
        $this->prefixConvention = $prefixConvention;
    }
}
