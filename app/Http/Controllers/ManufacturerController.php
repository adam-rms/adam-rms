<?php

namespace App\Http\Controllers;

use App\Interfaces\ManufacturerRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ManufacturerController extends Controller
{
    private ManufacturerRepositoryInterface $manufacturerRepository;

    public function __construct(ManufacturerRepositoryInterface $manufacturerRepository) 
    {
        //$this->middleware('auth');
        $this->manufacturerRepository = $manufacturerRepository;
    }

    public function index(): View
    {
        return view('templates/template', [
            "manufacturers" => $this->manufacturerRepository->getAll()
        ]);
    }

    public function show(Request $request): JsonResponse 
    {
        $orderId = $request->route('id');

        return response()->json([
            'data' => $this->manufacturerRepository->getById($orderId)
        ]);
    }
}
