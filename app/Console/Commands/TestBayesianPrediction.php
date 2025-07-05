<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProjectPredictionService;
use App\Models\Project;

class TestBayesianPrediction extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bayesian:test {--project-id= : Test with specific project ID}';

    /**
     * The console command description.
     */
    protected $description = 'Test the Bayesian prediction system for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔬 Testing Bayesian Prediction System...');

        // Test 1: Check Python script existence
        $this->info("\n1. Checking Python script...");
        $scriptPath = storage_path('scripts/bayesian_predictor.py');
        if (file_exists($scriptPath)) {
            $this->info("✅ Python script exists at: {$scriptPath}");
        } else {
            $this->error("❌ Python script NOT found at: {$scriptPath}");
            return 1;
        }

        // Test 2: Check Python executable
        $this->info("\n2. Checking Python executable...");
        $pythonExec = config('bayesian.python_executable', 'python3');
        $pythonVersion = shell_exec("{$pythonExec} --version 2>&1");
        if ($pythonVersion) {
            $this->info("✅ Python executable: {$pythonExec}");
            $this->info("   Version: " . trim($pythonVersion));
        } else {
            $this->error("❌ Python executable not found: {$pythonExec}");
            $this->info("💡 Try installing Python 3 or update BAYESIAN_PYTHON_EXECUTABLE in .env");
            return 1;
        }

        // Test 3: Check Python dependencies
        $this->info("\n3. Checking Python dependencies...");
        $this->testPythonDependency('pgmpy');
        $this->testPythonDependency('numpy');

        // Test 4: Test Python script with sample data
        $this->info("\n4. Testing Python script with sample data...");
        $testFeatures = [
            'task_progress' => 1,
            'team_collaboration' => 1,
            'faculty_approval' => 1,
            'timeline_adherence' => 1
        ];

        $command = escapeshellcmd($pythonExec) . ' ' .
            escapeshellarg($scriptPath) . ' ' .
            escapeshellarg(json_encode($testFeatures));

        $this->info("Command: {$command}");

        $output = shell_exec($command . ' 2>&1');
        $this->info("Raw output: " . ($output ?: 'No output'));

        if ($output) {
            $result = json_decode(trim($output), true);
            if ($result && isset($result['success']) && $result['success']) {
                $this->info("✅ Python script working! Probability: {$result['completion_probability']}");
            } else {
                $this->error("❌ Python script error: " . ($result['error'] ?? 'Unknown error'));
                $this->info("Full output: " . $output);
            }
        }

        // Test 5: Test with actual project if provided
        $projectId = $this->option('project-id');
        if ($projectId) {
            $this->info("\n5. Testing with project ID: {$projectId}");
            $project = Project::find($projectId);

            if (!$project) {
                $this->error("❌ Project not found: {$projectId}");
                return 1;
            }

            $service = app(ProjectPredictionService::class);

            try {
                $this->info("Project: {$project->title}");
                $this->info("Tasks: " . $project->tasks()->count());
                $this->info("Approved tasks: " . $project->tasks()->where('status', 'Approved')->count());

                $prediction = $service->predictCompletion($project);

                $this->info("✅ Prediction successful!");
                $this->info("Probability: {$prediction['probability']}");
                $this->info("Percentage: {$prediction['percentage']}%");
                $this->info("Risk Level: {$prediction['risk_level']}");

                if (isset($prediction['error'])) {
                    $this->warn("Warning: " . $prediction['error']);
                }

                if (isset($prediction['debug_info'])) {
                    $this->info("Debug info: " . json_encode($prediction['debug_info'], JSON_PRETTY_PRINT));
                }
            } catch (\Exception $e) {
                $this->error("❌ Prediction failed: " . $e->getMessage());
                $this->info("Check storage/logs/laravel.log for detailed error information");
            }
        }

        $this->info("\n🔍 Check storage/logs/laravel.log for detailed debugging information");
        $this->info("💡 Run with --project-id=X to test with a specific project");

        return 0;
    }

    private function testPythonDependency($package)
    {
        $pythonExec = config('bayesian.python_executable', 'python3');
        $output = shell_exec("{$pythonExec} -c 'import {$package}; print(\"{$package} installed\")' 2>&1");

        if (strpos($output, "{$package} installed") !== false) {
            $this->info("✅ {$package} is installed");
        } else {
            $this->error("❌ {$package} is NOT installed");
            $this->info("   Install with: pip3 install {$package}");
            $this->info("   Error: " . trim($output));
        }
    }
}
