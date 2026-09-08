<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BackupApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BackupApiController extends Controller
{
    public function __construct(
        protected BackupApiService $api
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->api->listBackups());
    }

    public function show(string $filename): JsonResponse
    {
        return response()->json($this->api->getBackup($filename));
    }

    public function store(): JsonResponse
    {
        $backup = $this->api->createBackup();

        return response()->json($backup, 201);
    }

    public function destroy(string $filename): JsonResponse
    {
        $this->api->deleteBackup($filename);

        return response()->json(null, 204);
    }

    public function download(string $filename)
    {
        return $this->api->downloadBackup($filename);
    }

    public function restore(Request $request, string $filename): JsonResponse
    {
        $result = $this->api->restoreBackup($filename, $request->input('target_database'));

        return response()->json($result);
    }

    public function templates(): JsonResponse
    {
        return response()->json($this->api->getTemplates());
    }

    public function schedules(): JsonResponse
    {
        return response()->json($this->api->getSchedules());
    }

    public function health(): JsonResponse
    {
        return response()->json($this->api->getHealth());
    }
}
