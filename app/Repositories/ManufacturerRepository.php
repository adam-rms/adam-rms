<?php

namespace App\Repositories;

use App\Interfaces\ManufacturerRepositoryInterface;
use App\Models\Manufacturer;

class ManufacturerRepository implements ManufacturerRepositoryInterface
{
    public function getAll() 
    {
        return Manufacturer::all();
    }

    public function getById($ManufacturerId) 
    {
        return Manufacturer::findOrFail($ManufacturerId);
    }

    public function delete($ManufacturerId) 
    {
        Manufacturer::destroy($ManufacturerId);
    }

    public function create(array $ManufacturerDetails) 
    {
        return Manufacturer::create($ManufacturerDetails);
    }

    public function update($ManufacturerId, array $newDetails) 
    {
        return Manufacturer::whereId($ManufacturerId)->update($newDetails);
    }

}