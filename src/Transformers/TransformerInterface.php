<?php

namespace Napp\Core\Api\Transformers;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Interface TransformerInterface
 * @package Napp\Core\Api\Transformers
 */
interface TransformerInterface
{
    /**
     * @param array|Arrayable $data
     * @return array
     */
    public function transformInput($data): array;

    /**
     * @param array|Arrayable $data
     * @return array
     */
    public function transformOutput($data): array;
}
