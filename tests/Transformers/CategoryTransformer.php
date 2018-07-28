<?php 

namespace Napp\Core\Api\Tests\Transformers;

use Napp\Core\Api\Tests\Models\Category;
use Napp\Core\Api\Transformers\ApiTransformer;

/**
 * Class CategoryTransformer
 * @package Napp\Core\Api\Tests\Transformers
 */
class CategoryTransformer extends ApiTransformer
{
    protected $strict = true;
    /**
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->setApiMapping($category);
    }
}