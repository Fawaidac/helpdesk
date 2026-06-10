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
        $this->middleware('api.auth');
    }

    /**
     * @OA\Get(
     * path="/api/komplain",
     * operationId="getDataKomplain",
     * tags={"Data Komplain"},
     * summary="Get List Data Komplain (Paginated)",
     * description="Mengambil semua data komplain bulan berjalan dengan filter pencarian, kategori keywords, status pengerjaan, dan tim PDE.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Cari berdasarkan nama, pelapor, ruangan, permasalahan, atau nomor WA",
     * required=false,
     * @OA\Schema(type="string", example="printer")
     * ),
     * @OA\Parameter(
     * name="kategori",
     * in="query",
     * description="Filter kategori berdasarkan kecocokan keyword masalah (simrs / maintanance)",
     * required=false,
     * @OA\Schema(type="string", enum={"simrs", "maintanance"})
     * ),
     * @OA\Parameter(
     * name="is_done",
     * in="query",
     * description="Filter status pengerjaan (true: memiliki nomor_act, false: nomor_act kosong)",
     * required=false,
     * @OA\Schema(type="boolean", example=false)
     * ),
     * @OA\Parameter(
     * name="recent",
     * in="query",
     * description="Jika is_done=false dan recent=true, tampilkan komplain 1 jam terakhir saja",
     * required=false,
     * @OA\Schema(type="boolean", example=true)
     * ),
     * @OA\Parameter(
     * name="nomor_act",
     * in="query",
     * description="Filter berdasarkan nomor_act penanganan tertentu, atau string 'null' untuk yang belum ditangani",
     * required=false,
     * @OA\Schema(type="string", example="null")
     * ),
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Nomor halaman pagination",
     * required=false,
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Get data komplain success",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="get data komplain success"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="data", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="id", type="integer", example=12),
     * @OA\Property(property="nama", type="string", example="Roni"),
     * @OA\Property(property="nama_pelapor", type="string", example="Siti"),
     * @OA\Property(property="ruangan", type="string", example="Poli Jantung"),
     * @OA\Property(property="permasalahan", type="string", example="Printer macet tinta habis"),
     * @OA\Property(property="nomor_wa", type="string", example="08123456789"),
     * @OA\Property(property="nomor_act", type="string", nullable=true, example="085544332211"),
     * @OA\Property(property="tanggal", type="string", format="date", example="2026-06-10"),
     * @OA\Property(property="pde", type="object", nullable=true,
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="nama", type="string", example="Ahmad PDE"),
     * @OA\Property(property="alamat", type="string", example="Gedung IT Lantai 2")
     * )
     * )
     * ),
     * @OA\Property(property="total", type="integer", example=25)
     * )
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');
            $kategori = $request->query('kategori');
            $isDone = $request->query('is_done');
            $recent = $request->query('recent');
            $nomorAct = $request->query('nomor_act');

            if ($isDone !== null) {
                $isDone = filter_var($isDone, FILTER_VALIDATE_BOOLEAN);
            }
            if ($recent !== null) {
                $recent = filter_var($recent, FILTER_VALIDATE_BOOLEAN);
            }

            $data = $this->service->getAll(
                $search,
                $kategori,
                $isDone,
                $recent,
                $nomorAct
            );
            
            return ApiResponse::success($data, 'get data komplain success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/komplain/dashboard",
     * operationId="getKomplainDashboard",
     * tags={"Data Komplain"},
     * summary="Get Counter Statisitik Dashboard",
     * description="Mengambil rangkuman jumlah tiket open, done, pembagian kategori, beserta performa per tim PDE bulan berjalan.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="get data dashboard success",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="get data dashboard success"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="ticket_open", type="integer", example=5),
     * @OA\Property(property="ticket_done", type="integer", example=42),
     * @OA\Property(property="simrs_masuk", type="integer", example=20),
     * @OA\Property(property="simrs_done", type="integer", example=18),
     * @OA\Property(property="maintenance_masuk", type="integer", example=27),
     * @OA\Property(property="maintenance_done", type="integer", example=24),
     * @OA\Property(property="pde_performance", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="id", type="integer", nullable=true, example=1),
     * @OA\Property(property="nama", type="string", example="Ahmad PDE"),
     * @OA\Property(property="alamat", type="string", nullable=true, example="Jember"),
     * @OA\Property(property="telp", type="string", example="085544332211"),
     * @OA\Property(property="total", type="integer", example=15)
     * )
     * )
     * )
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function dashboard()
    {
        try {
            $data = $this->service->getDashboardCount();

            return ApiResponse::success($data, 'get data dashboard success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/pde",
     * operationId="getTeamPde",
     * tags={"Data Komplain"},
     * summary="Get Distinct Team PDE Data",
     * description="Mengambil daftar unik tim PDE yang sudah pernah menangani komplain (berdasarkan unique nomor_act).",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="get data team PDE success",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="get data team PDE success"),
     * @OA\Property(property="data", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="id", type="integer", nullable=true, example=1),
     * @OA\Property(property="nama", type="string", example="PDE Team 1"),
     * @OA\Property(property="alamat", type="string", nullable=true, example=null),
     * @OA\Property(property="telp", type="string", example="085544332211")
     * )
     * )
     * )
     * ),
     * @OA\Response(response=401, description="Unauthenticated"),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function getDataPde()
    {
        try {
            $data = $this->service->getDataTeamPde();

            return ApiResponse::success($data, 'get data team PDE success');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}