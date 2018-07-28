<?php

namespace Napp\Core\Api\Tests\Unit;

use Napp\Core\Api\Tests\Models\Category;
use Napp\Core\Api\Tests\Models\Product;
use Napp\Core\Api\Tests\Transformers\ProductTransformer;
use Napp\Core\Api\Transformers\ApiTransformer;
use Napp\Core\Api\Tests\TestCase;

class ApiTransformerTest extends TestCase
{
    /**
     * @var ApiTransformer
     */
    protected $transformer;

    public function setUp()
    {
        parent::setUp();

        $apiMapping = [
            'id' => ['newName' => 'id', 'dataType' => 'int'],
            'name' => ['newName' => 'companyName', 'dataType' => 'string'],
            'has_access' => ['newName' => 'hasAccess', 'dataType' => 'bool'],
            'categories' => ['newName' => 'cats', 'dataType' => 'array'],
        ];

        $this->transformer = new ApiTransformer();
        $this->transformer->setApiMapping($apiMapping);
    }

    public function test_input_transforming()
    {
        $input = [
            'id' => 1,
            'companyName' => 'Wayne Industries',
            'hasAccess' => 0,
            'someAdditionalField' => 'someAdditionalValue',
            'cats' => ['foo' => 'bar']
        ];

        $expectedInput = [
            'id' => 1,
            'name' => 'Wayne Industries',
            'has_access' => 0,
            'someAdditionalField' => 'someAdditionalValue',
            'categories' => ['foo' => 'bar']
        ];
        $transformedInput = $this->transformer->transformInput($input);

        $this->assertEquals($expectedInput, $transformedInput);
    }

    public function test_strict_output_transforming()
    {
        $reflection = new \ReflectionProperty(\get_class($this->transformer), 'strict');
        $reflection->setAccessible(true);
        $reflection->setValue($this->transformer, true);

        $output = [
            'id' => 1,
            'name' => 'Wayne Industries',
            'has_access' => 0,
            'some_additional_field' => 'some_additional_value'
        ];

        $expectedOutput = [
            'id' => 1,
            'companyName' => 'Wayne Industries',
            'hasAccess' => false,
        ];

        $transformedOutput = $this->transformer->transformOutput($output);
        $this->assertEquals($expectedOutput, $transformedOutput);
        $this->assertArrayNotHasKey('some_additional_field', $transformedOutput);
    }

    public function test_output_transforming()
    {
        $output = [
            'id' => 1,
            'name' => 'Wayne Industries',
            'has_access' => 0,
            'some_additional_field' => 'some_additional_value'
        ];

        $expectedOutput = [
            'id' => 1,
            'companyName' => 'Wayne Industries',
            'hasAccess' => false,
            'some_additional_field' => 'some_additional_value'
        ];

        $transformedOutput = $this->transformer->transformOutput($output);

        $this->assertEquals($expectedOutput, $transformedOutput);
    }

    public function test_the_datatype_is_nullable()
    {
        $this->transformer->setApiMapping([
            'price' => ['newName' => 'price', 'dataType' => 'nullable|int']
        ]);

        $input = [
            'price' => '100'
        ];

        $expectedOutput = [
            'price' => 100
        ];

        $this->assertSame($expectedOutput, $this->transformer->transformOutput($input));

        $input = [
            'price' => 0
        ];

        $expectedOutput = [
            'price' => 0
        ];

        $this->assertSame($expectedOutput, $this->transformer->transformOutput($input));

        $this->transformer->setApiMapping([
            'description' => ['newName' => 'description', 'dataType' => 'array|nullable']
        ]);

        $input = [
            'description' => []
        ];

        $expectedOutput = [
            'description' => null
        ];

        $this->assertSame($expectedOutput, $this->transformer->transformOutput($input));
    }

    public function test_arguments_can_be_passed_to_the_datatype()
    {
        $this->transformer->setApiMapping([
            'price' => ['newName' => 'price', 'dataType' => 'float:2']
        ]);

        $input = [
            'price' => '100.5542'
        ];

        $expectedOutput = [
            'price' => 100.55
        ];

        $this->assertSame($expectedOutput, $this->transformer->transformOutput($input));
    }

    public function test_transform_model_hasMany_relation_returns_transformed_relation_with_it()
    {
        /** @var Category $category */
        $category = Category::create(['title' => 'Electronics']);
        $category->products()->create(['name' => 'iPhone', 'price'=> 100.0]);
        $category->load('products');
        $result = $category->getTransformer()->transformOutput($category);

        $this->assertArrayHasKey('products', $result);
        $this->assertEquals('iPhone', $result['products'][0]['title']);
    }

    public function test_empty_relation_returns_only_transformed_base_model()
    {
        /** @var Category $category */
        $category = Category::create(['title' => 'Electronics']);
        $category->load('products');
        $result = $category->getTransformer()->transformOutput($category);

        $this->assertArrayNotHasKey('products', $result);
    }

    public function test_without_relation_loaded_returns_only_transformed_base_model()
    {
        /** @var Category $category */
        $category = Category::create(['title' => 'Electronics']);
        $result = $category->getTransformer()->transformOutput($category);

        $this->assertArrayNotHasKey('products', $result);
    }

    public function test_transform_collection_with_belongsTo_relation_transforms()
    {
        $category = Category::create(['title' => 'Electronics']);
        $category->products()->create(['name' => 'iPhone', 'price'=> 100.0]);
        $category->products()->create(['name' => 'Google Pixel', 'price'=> 80.0]);
        $category->products()->create(['name' => 'Samsung Galaxy 9', 'price'=> 110.0]);

        $products = Product::with('category')->get();
        $result = app(ProductTransformer::class)->transformOutput($products);

        $this->assertEquals('iPhone', $result[0]['title']);
        $this->assertEquals('Electronics', $result[0]['category']['name']);
        $this->assertEquals('Electronics', $result[1]['category']['name']);
        $this->assertEquals('Electronics', $result[2]['category']['name']);

        $category->load('products');
        $result = $category->getTransformer()->transformOutput($category);
        $this->assertCount(3, $result['products']);
    }

    public function test_transform_deeply_nested_relationships()
    {
        $category = Category::create(['title' => 'Electronics']);
        $category->products()->create(['name' => 'iPhone', 'price'=> 100.0])->variants()->create(['name' => 'iPhone 8', 'sku_id' => 'IPHONE2233']);
        $category->load(['products', 'products.variants']);
        $result = $category->getTransformer()->transformOutput($category);

        $this->assertEquals('iPhone 8', $result['products'][0]['variants'][0]['title']);
    }
}
