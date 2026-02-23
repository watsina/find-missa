<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ImportStatusService;

class ImportStatusController extends Controller
{
    public function __construct(protected ImportStatusService $statusService) {}

    /**
     * Get the status of an import job.
     */
    public function __invoke(Request $request, string $importId): JsonResponse
    {
        $status = $this->statusService->getStatus($importId);

        if (! $status) {
            return response()->json([
                'message' => 'Import not found',
            ], 404);
        }

        // Security: Verify the import belongs to the requesting user's team
        $user = $request->user();
        if ($status['team_id'] !== $user->current_team_id) {
            return response()->json([
                'message' => 'Import not found',
            ], 404);
        }

        return response()->json($status);
    }
}
