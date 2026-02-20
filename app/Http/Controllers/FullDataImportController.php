<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Jobs\ProcessFullDataImport;
use App\Services\ImportStatusService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\FullDataImportRequest;

class FullDataImportController extends Controller
{
    public function __construct(protected ImportStatusService $statusService) {}

    /**
     * Handle the incoming request.
     *
     * Validates the file, stores it temporarily, creates a status entry,
     * dispatches the import job, and returns immediately with 202 Accepted.
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

        $user = $request->user();
        $teamId = $user->current_team_id;
        $importId = Str::uuid()->toString();

        // Store file temporarily for the job to process
        $filePath = 'imports/'.$importId.'.json';
        Storage::disk('local')->put($filePath, $contents);

        // Create status entry
        $this->statusService->create($importId, $teamId, $user->id);

        // Dispatch the job
        ProcessFullDataImport::dispatch($importId, $teamId, $user->id, $filePath);

        return response()->json([
            'success' => true,
            'message' => 'Import job queued successfully',
            'import_id' => $importId,
        ], 202);
    }
}
