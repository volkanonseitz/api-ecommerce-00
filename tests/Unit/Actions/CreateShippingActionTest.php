<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateShippingAction;
use App\Models\Shipping;
use App\Modules\Shipping\DTO\ShippingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateShippingActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_shipping_record()
    {
        // Arrange
        $data = new ShippingData(
            title: 'Standard Shipping',
            description: 'Standard shipping via regular mail.',
            price: 10.00,
            is_global: false,
            type: 'pickup',
            language: 'en',
            shop_id: 1, // Assuming a shop_id exists
        );

        // Mock the Shipping model to prevent actual database interaction during unit test
        // and assert that the create method is called with the correct data
        $shippingMock = $this->mock(Shipping::class);
        $shippingMock->shouldReceive('create')
            ->once()
            ->with($data->toArray())
            ->andReturn(new Shipping($data->toArray())); // Return a new Shipping instance

        $action = new CreateShippingAction();

        // Act
        $shipping = $action->execute($data);

        // Assert
        $this->assertInstanceOf(Shipping::class, $shipping);
        $this->assertEquals($data->title, $shipping->title);
        $this->assertEquals($data->description, $shipping->description);
        $this->assertEquals($data->price, $shipping->price);
        $this->assertEquals($data->is_global, $shipping->is_global);
        $this->assertEquals($data->type, $shipping->type);
        $this->assertEquals($data->language, $shipping->language);
        $this->assertEquals($data->shop_id, $shipping->shop_id);
    }
}
