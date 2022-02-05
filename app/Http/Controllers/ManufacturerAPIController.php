<?php

namespace App\Http\Controllers;

use App\Interfaces\ManufacturerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ManufacturerAPIController extends Controller
{
    private ManufacturerRepositoryInterface $manufacturerRepository;

    public function __construct(ManufacturerRepositoryInterface $manufacturerRepository) 
    {
        $this->manufacturerRepository = $manufacturerRepository;
    }

    public function index(): JsonResponse 
    {
        return response()->json([
            'data' => $this->manufacturerRepository->getAll()
        ]);
    }

    public function store(Request $request): JsonResponse 
    {
        $orderDetails = $request->only([
            'client',
            'details'
        ]);

        return response()->json(
            [
                'data' => $this->manufacturerRepository->create($orderDetails)
            ],
            Response::HTTP_CREATED
        );
    }

    public function show(Request $request): JsonResponse 
    {
        $orderId = $request->route('id');

        return response()->json([
            'data' => $this->manufacturerRepository->getById($orderId)
        ]);
    }

    public function update(Request $request): JsonResponse 
    {
        $orderId = $request->route('id');
        $orderDetails = $request->only([
            'client',
            'details'
        ]);

        return response()->json([
            'data' => $this->manufacturerRepository->update($orderId, $orderDetails)
        ]);
    }

    public function destroy(Request $request): JsonResponse 
    {
        $orderId = $request->route('id');
        $this->manufacturerRepository->delete($orderId);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
