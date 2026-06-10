<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\RevenueService;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    protected RevenueService $service;

    public function __construct()
    {
        $this->service = new RevenueService();
        $this->middleware('api.auth');
        $this->middleware('superadmin')->only(['store', 'years', 'showByYear']);
    }

    /**
     * @OA\Get(
     * path="/api/revenue",
     * operationId="getRevenueDashboard",
     * tags={"Revenue / Pendapatan"},
     * summary="Get Laporan Realisasi Dashboard",
     * description="Mengambil rangkuman hitungan data realisasi pendapatan horizontal dan vertikal sesuai tabel format Excel berdasarkan tahun.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="tahun",
     * in="query",
     * description="Tahun laporan pendapatan yang ingin dicari",
     * required=true,
     * @OA\Schema(type="integer", example=1900)
     * ),
     * @OA\Response(
     * response=200,
     * description="Get data berhasil",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Get data berhasil"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="tahun", type="integer", example=1900),
     * @OA\Property(property="target_tahunan", type="object", example={"UMUM": 2803200000, "BPJS KESEHATAN": 284856000000}),
     * @OA\Property(property="total_target_tahunan", type="number", example=330000000000),
     * @OA\Property(property="target_bulanan", type="object", example={"UMUM": 233600000, "BPJS KESEHATAN": 23738000000}),
     * @OA\Property(property="total_target_bulanan", type="number", example=27500000000),
     * @OA\Property(property="realisasi", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="bulan", type="integer", example=1),
     * @OA\Property(property="bulan_name", type="string", example="Januari"),
     * @OA\Property(property="categories", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="category", type="string", example="UMUM"),
     * @OA\Property(property="amount", type="number", example=2000000000),
     * @OA\Property(property="percentage", type="string", example="856.16")
     * )
     * ),
     * @OA\Property(property="total_bulan", type="number", example=14800000000),
     * @OA\Property(property="total_percentage", type="string", example="57.09")
     * )
     * ),
     * @OA\Property(property="summary", type="object",
     * @OA\Property(property="total_per_kategori", type="object", example={"UMUM": 24000000000}),
     * @OA\Property(property="persentase_tahun", type="object", example={"UMUM": "856.16"}),
     * @OA\Property(property="grand_total_realisasi", type="number", example=114738116031),
     * @OA\Property(property="grand_persentase_tahun", type="string", example="34.77")
     * )
     * )
     * )
     * ),
     * @OA\Response(response=400, description="Bad Request / Validasi Gagal"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'tahun' => 'required|integer'
            ]);

            $data = $this->service->getDashboard($request->tahun);

            return ApiResponse::success($data, 'Get data berhasil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Post(
     * path="/api/revenue",
     * operationId="storeOrUpdateRevenue",
     * tags={"Revenue / Pendapatan"},
     * summary="Store / Update Target & Realisasi Bulanan",
     * description="Menyimpan atau memperbarui data master target tahunan, bulanan, beserta nominal cicilan realisasi pendapatan per kategori code (Khusus Superadmin).",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"tahun","targets"},
     * @OA\Property(property="tahun", type="integer", example=1900),
     * @OA\Property(property="targets", type="array",
     * @OA\Items(type="object",
     * required={"category_code","target_tahunan","target_bulanan"},
     * @OA\Property(property="category_code", type="string", example="umum"),
     * @OA\Property(property="target_tahunan", type="number", example=2803200000),
     * @OA\Property(property="target_bulanan", type="number", example=233600000)
     * )
     * ),
     * @OA\Property(property="realisasi", type="array", nullable=true,
     * @OA\Items(type="object",
     * required={"bulan","categories"},
     * @OA\Property(property="bulan", type="integer", minimum=1, maximum=12, example=1),
     * @OA\Property(property="categories", type="array",
     * @OA\Items(type="object",
     * required={"category_code","amount"},
     * @OA\Property(property="category_code", type="string", example="umum"),
     * @OA\Property(property="amount", type="number", example=2000000000)
     * )
     * )
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Data target dan realisasi berhasil disimpan",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Data target dan realisasi berhasil disimpan"),
     * @OA\Property(property="data", type="null", example=null)
     * )
     * ),
     * @OA\Response(response=422, description="Validation Error (Unprocessable Entity)"),
     * @OA\Response(response=401, description="Unauthenticated / Token missing"),
     * @OA\Response(response=403, description="Forbidden (Bukan Superadmin)")
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'tahun' => 'required|integer',
                'targets' => 'required|array',
                'targets.*.category_code' => 'required|string|exists:sqlite_secondary.categories,code',
                'targets.*.target_tahunan' => 'required|numeric',
                'targets.*.target_bulanan' => 'required|numeric',
                
                'realisasi' => 'nullable|array',
                'realisasi.*.bulan' => 'required|integer|between:1,12',
                'realisasi.*.categories' => 'required|array',
                'realisasi.*.categories.*.category_code' => 'required|string|exists:sqlite_secondary.categories,code',
                'realisasi.*.categories.*.amount' => 'required|numeric',
            ]);

            $this->service->storeOrUpdate($request->all());

            return ApiResponse::success(null, 'Data target dan realisasi berhasil disimpan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error($e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     * path="/api/revenue/years",
     * operationId="getRevenueYears",
     * tags={"Revenue / Pendapatan"},
     * summary="Get List Tahun Terdaftar",
     * description="Mengambil semua data tahun unik yang sudah terdaftar di database untuk kebutuhan dropdown opsi form filter (Khusus Superadmin).",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Get list tahun berhasil",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Get list tahun berhasil"),
     * @OA\Property(property="data", type="array", @OA\Items(type="integer", example=1900))
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function years()
    {
        try {
            $data = $this->service->getYearList();
            return ApiResponse::success($data, 'Get list tahun berhasil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    /**
     * @OA\Get(
     * path="/api/revenue/detail",
     * operationId="getRevenueDetailByYear",
     * tags={"Revenue / Pendapatan"},
     * summary="Get Raw Data Form Input by Year",
     * description="Mengambil skema data target dan nominal realisasi asli (mentah) per tahun untuk kebutuhan binding default values ke dalam Form Edit (Khusus Superadmin).",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="tahun",
     * in="query",
     * description="Tahun data yang mau di-load ke form",
     * required=true,
     * @OA\Schema(type="integer", example=1900)
     * ),
     * @OA\Response(
     * response=200,
     * description="Get detail data tahun berhasil",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Get detail data tahun berhasil"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="tahun", type="integer", example=1900),
     * @OA\Property(property="targets", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="category_code", type="string", example="umum"),
     * @OA\Property(property="category_name", type="string", example="UMUM"),
     * @OA\Property(property="target_tahunan", type="number", example=2803200000),
     * @OA\Property(property="target_bulanan", type="number", example=233600000)
     * )
     * ),
     * @OA\Property(property="realisasi", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="bulan", type="integer", example=1),
     * @OA\Property(property="bulan_name", type="string", example="Januari"),
     * @OA\Property(property="categories", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="category_code", type="string", example="umum"),
     * @OA\Property(property="category_name", type="string", example="UMUM"),
     * @OA\Property(property="amount", type="number", example=2000000000)
     * )
     * )
     * )
     * )
     * )
     * )
     * ),
     * @OA\Response(response=400, description="Validasi tahun salah"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function showByYear(Request $request)
    {
        try {
            $request->validate([
                'tahun' => 'required|integer'
            ]);

            $data = $this->service->getByYear($request->tahun);

            return ApiResponse::success($data, 'Get detail data tahun berhasil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }
}