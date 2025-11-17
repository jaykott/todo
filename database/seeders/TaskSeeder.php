<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            ['name' => 'Complete project documentation', 'order' => 1, 'completed' => false, 'due_date' => now()->addDays(3)->format('Y-m-d')],
            ['name' => 'Review code changes', 'order' => 2, 'completed' => true, 'due_date' => now()->subDays(2)->format('Y-m-d')],
            ['name' => 'Update database schema', 'order' => 3, 'completed' => false, 'due_date' => now()->addDays(7)->format('Y-m-d')],
            ['name' => 'Write unit tests', 'order' => 4, 'completed' => false, 'due_date' => now()->addDays(5)->format('Y-m-d')],
            ['name' => 'Fix bug in authentication', 'order' => 5, 'completed' => true, 'due_date' => now()->subDays(1)->format('Y-m-d')],
            ['name' => 'Design new feature UI', 'order' => 6, 'completed' => false, 'due_date' => now()->addDays(10)->format('Y-m-d')],
            ['name' => 'Deploy to staging', 'order' => 7, 'completed' => false, 'due_date' => now()->addDays(2)->format('Y-m-d')],
            ['name' => 'Update dependencies', 'order' => 8, 'completed' => true, 'due_date' => now()->subDays(5)->format('Y-m-d')],
            ['name' => 'Optimize database queries', 'order' => 9, 'completed' => false, 'due_date' => now()->addDays(14)->format('Y-m-d')],
            ['name' => 'Prepare presentation slides', 'order' => 10, 'completed' => false, 'due_date' => now()->addDays(6)->format('Y-m-d')],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
