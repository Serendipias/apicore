<?php 

namespace Napp\Core\Api\Tests\Transformers;

use Napp\Core\Api\Tests\Models\Variant;
use Napp\Core\Api\Transformers\ApiTransformer;

/**
 * Class VariantTransformer
 * @package Napp\Core\Api\Tests\Transformers
 */
class VariantTransformer extends ApiTransformer
{
    protected $strict = true;
    /**
     * @param Variant $variant
     */
    public function __construct(Variant $variant)
    {
        $this->setApiMapping($variant);
    }
}