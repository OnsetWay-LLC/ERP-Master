<?php

namespace App\Http\Controllers\Api\Database;

use App\Http\Controllers\Controller;
use App\Services\Database\DatabaseManagementService;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Illuminate\Http\Request;

class DatabaseManagementController extends Controller
{
    public function __construct(
        private readonly DatabaseManagementService $service
    ) {}

    public function backup(): JsonResponse
    {
        try {
            $backup = $this->service->createBackup();

            return response()->json([
                'status' => true,
                'message' => 'Database backup created successfully.',
                'data' => $backup,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function backups(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Database backups retrieved successfully.',
            'data' => $this->service->listBackups(),
        ]);
    }

    public function download(string $file)
    {
        try {
            $path = $this->service->getBackupPath($file);

            return response()->download($path);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function delete(string $file): JsonResponse
    {
        try {
            $this->service->deleteBackup($file);

            return response()->json([
                'status' => true,
                'message' => 'Database backup deleted successfully.',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
   public function restore(Request $request): JsonResponse
{
    $request->validate([
        'backup_file' => ['required', 'string'],
        'database_name' => ['nullable', 'string'],
    ]);

    try {
        $result = $this->service->restoreDatabase(
            $request->backup_file,
            $request->database_name
        );

        return response()->json([
            'status' => true,
            'message' => 'Database restored successfully.',
            'data' => $result,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 422);
    }
}
}