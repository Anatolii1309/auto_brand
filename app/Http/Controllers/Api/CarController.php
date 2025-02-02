<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{

  /**
   * Index cars.
   *
   * @return mixed
   */
  public function index(Request $request)
  {
    $query = Car::query();

    // Handle sorting
    $sortField = $request->input('sort', 'year');  // Default sort by year
    $sortDirection = $request->input('direction', 'desc');  // Default direction

    if (in_array($sortField, ['year', 'brand'])) {
      $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
    }

    return $query->get();
  }

  /**
   * Store car.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return mixed
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'brand' => 'required|string|max:255',
      'model' => 'required|string|max:255',
      'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
      'price' => 'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $car = Car::create($request->all());
    Cache::forget('cars.all');

    return response()->json($car, 201);
  }

  /**
   * Show car.
   *
   * @param \App\Models\Car $car
   *
   * @return mixed
   */
  public function show(Car $car)
  {
    return response()->json($car);
  }

  /**
   * Update car.
   *
   * @param \Illuminate\Http\Request $request
   * @param \App\Models\Car $car
   *
   * @return mixed
   */
  public function update(Request $request, Car $car)
  {
    $validator = Validator::make($request->all(), [
      'brand' => 'required|string|max:255',
      'model' => 'required|string|max:255',
      'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
      'price' => 'required|numeric|min:0'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $car->update($request->all());
    Cache::forget('cars.all');

    return response()->json($car);
  }

  /**
   * Remove car.
   *
   * @param \App\Models\Car $car
   *
   * @return mixed
   */
  public function destroy(Car $car)
  {
    $car->delete();
    Cache::forget('cars.all');

    return response()->json(null, 204);
  }
}