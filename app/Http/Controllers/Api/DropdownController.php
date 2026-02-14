<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\DropdownRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    /**
     * Get dropdown options by type
     * POST /dropdown?type=users&q=search
     */
    public function index(Request $request, DropdownRepository $repository): JsonResponse
    {
        $type = $request->input('type');
        $keyword = $request->input('q') ?? $request->input('keyword') ?? $request->input('search');
        $data = $request->all();

        $options = $repository->fetchOptions($type, $keyword, $data);

        return response()->json($options);
    }
}
