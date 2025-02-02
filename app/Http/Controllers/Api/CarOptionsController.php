<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class CarOptionsController extends Controller
{
  public function getMakes()
  {
    $response = Http::get('https://vpic.nhtsa.dot.gov/api/vehicles/getallmakes?format=json');
    return $response->json();
  }

  public function getModels($make)
  {
    $response = Http::get("https://vpic.nhtsa.dot.gov/api/vehicles/getmodelsformake/{$make}?format=json");
    return $response->json();
  }
}