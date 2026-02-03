<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Services\DataImportService;
use App\Http\Requests\FullDataImportRequest;

class FullDataImportController extends Controller
{
    public function __construct(protected DataImportService $importService) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(FullDataImportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $contents = file_get_contents($file->getRealPath());

        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'message' => 'Invalid JSON file: '.json_last_error_msg(),
            ], 422);
        }

        // Validate the structure
        if (! isset($data['version']) || ! isset($data['tables'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid export file structure. Missing required keys: version, tables.',
            ], 422);
        }

        $teamId = $request->user()->current_team_id;

        $result = $this->importService->import($data, $teamId);

        if (! $result->isSuccessful()) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed',
                'errors' => $result->getErrors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Import completed successfully',
            'data' => $result->toArray(),
        ]);
    }
}
