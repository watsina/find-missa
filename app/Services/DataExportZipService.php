<?php

namespace App\Services;

use ZipArchive;
use App\Models\Team;
use App\Models\Diocese;
use Illuminate\Support\Str;
use App\Exports\ParishesExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataExportZipService
{
    /**
     * Export all parishes as a zip file of Excel spreadsheets, one per diocese.
     */
    public function exportParishesAsZip(Team $team): BinaryFileResponse|JsonResponse
    {
        $folderName = $team->name;
        $exportDir = storage_path("exports/{$folderName}");
        $zipPath = "{$exportDir}/parishes-export.zip";

        if (! is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $zip = new ZipArchive;
        $openResult = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            Log::error("Failed to create zip file: {$zipPath}", ['error_code' => $openResult]);

            return response()->json([
                'message' => 'Failed to create export archive',
            ], 500);
        }

        try {
            foreach (Diocese::with(['parishes.churches', 'parishes.subParishes'])->get() as $diocese) {
                $fileName = ucwords(Str::kebab($diocese->name)." {$diocese->type} parishes and sub parishes.xlsx");
                $filePath = "{$folderName}/".$fileName;

                if (! Excel::store(new ParishesExport($diocese), $filePath, 'exports')) {
                    Log::warning("Failed to export diocese: {$diocese->name}");

                    continue;
                }

                $fullPath = storage_path("exports/{$filePath}");
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $fileName);
                }
            }
        } finally {
            $zip->close();
        }

        if (! file_exists($zipPath)) {
            return response()->json([
                'message' => 'Export failed - no files were created',
            ], 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(false);
    }
}
