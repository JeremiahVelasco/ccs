<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Task;

class FileController extends Controller
{
    public function viewTaskFile($taskId)
    {
        $task = Task::findOrFail($taskId);

        if (!$task->file_path || !Storage::disk('public')->exists($task->file_path)) {
            abort(404, 'File not found');
        }

        $filePath = Storage::disk('public')->path($task->file_path);
        $fileName = basename($task->file_path);
        $mimeType = mime_content_type($filePath);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ]);
    }
}
