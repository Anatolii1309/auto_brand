<?php

namespace Tests\Feature;

use App\Models\Car;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CarApiTest extends TestCase
{

  use RefreshDatabase;

  public function test_can_create_car()
  {
    $carData = [
      'brand' => 'Toyota',
      'model' => 'Camry',
      'year' => 2023,
      'price' => 35000
    ];

    $response = $this->postJson('/api/cars', $carData);

    $response->assertStatus(201)
      ->assertJsonFragment($carData);
  }

  public function test_can_get_cars_list()
  {
    Car::factory()->count(3)->create();

    $response = $this->getJson('/api/cars');

    $response->assertStatus(200)
      ->assertJsonCount(3);
  }

  public function test_can_delete_car()
  {
    $car = Car::factory()->create();

    $response = $this->deleteJson("/api/cars/{$car->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('cars', ['id' => $car->id]);
  }
}