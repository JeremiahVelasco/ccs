<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Task;

class FileController extends Controller
{
    public function viewTaskFile($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);

            if (!$task->file_path) {
                abort(404, 'No file associated with this task');
            }

            if (!Storage::disk('public')->exists($task->file_path)) {
                abort(404, 'File not found on server');
            }

            $filePath = Storage::disk('public')->path($task->file_path);

            if (!file_exists($filePath)) {
                abort(404, 'File does not exist');
            }

            $fileName = basename($task->file_path);

            // Get MIME type safely
            $mimeType = 'application/octet-stream'; // default
            if (function_exists('mime_content_type')) {
                $detectedMime = mime_content_type($filePath);
                if ($detectedMime) {
                    $mimeType = $detectedMime;
                }
            } else {
                // Fallback MIME type detection
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'txt' => 'text/plain',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                ];
                $mimeType = $mimeTypes[$extension] ?? $mimeType;
            }

            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Task not found');
        } catch (\Exception $e) {
            Log::error('File viewing error: ' . $e->getMessage());
            abort(500, 'Error viewing file: ' . $e->getMessage());
        }
    }
}
