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
            $isDone = $request->query('is_done');
            if ($isDone !== null) {
                $isDone = filter_var($isDone, FILTER_VALIDATE_BOOLEAN);
            }

            $data = $this->service->getAll(
                $search,
                $kategori,
                $isDone,
            );
            
            return ApiResponse::success($data, 'get data komplain success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function dashboard()
    {
        try {

            $data = $this->service
                ->getDashboardCount();

            return ApiResponse::success(
                $data,
                'get data dashboard success'
            );

        } catch (\Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                500
            );
        }
    }
}
