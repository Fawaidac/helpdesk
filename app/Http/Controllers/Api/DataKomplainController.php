<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\DataKomplainService;
use Illuminate\Http\Request;

class DataKomplainController extends Controller
{
    protected DataKomplainService $service;

    public function __construct()
    {
        $this->service = new DataKomplainService();
    }

    public function index(Request $request)
    {
        try {
            $search = $request->query('search');
            $kategori = $request->query('kategori');

            $data = $this->service->getAll($search, $kategori);

            return ApiResponse::success($data, 'get data komplain success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
