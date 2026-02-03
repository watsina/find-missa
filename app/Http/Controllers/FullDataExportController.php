<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DataExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FullDataExportController extends Controller
{
    public function __construct(protected DataExportService $exportService) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $teamId = $request->user()->current_team_id;
        $teamName = $request->user()->currentTeam->name ?? 'export';

        $data = $this->exportService->export($teamId);

        $filename = sprintf(
            '%s-full-export-%s.json',
            str_replace(' ', '-', strtolower($teamName)),
            now()->format('Y-m-d-His')
        );

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
