<?php

namespace App\Http\Controllers;

use App\Project;
use App\Task;
use App\Track;
use App\Update;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DefaultController extends Controller
{

    private $trackedLabels = [
        'doing',
        'done',
        'ready for test',
        'needs info',
        'need info',
        'needs infos',
        'need infos'
    ];

    private $taskLabel = 'task';

    private $hitoryLabel = 'history';

    public function index(Request $request)
    {
        $postData = $request->post();
        //dd($postData);
        $user = $this->getUser($postData['user']);
        $project = $this->getProject($postData['project']);
        $task = $this->getTask($postData['object_attributes']);

        $this->setAssign($postData['assignees'], $task);

        $this->processChanges($postData);

        return [
            'success' => true
        ];

    }

    protected function getUser(array $data)
    {
        $exists = User::where('username', $data['username'])->first();

        if ($exists === null) {
            $data['password'] = bcrypt('*01convenia*01');
            return User::create($data);
        }

        return $exists;
    }

    private function getProject(array $data)
    {

        $exists = Project::find($data['id']);

        if ($exists === null) {
            Project::create($data);
            $exists = Project::find($data['id']);
        }

        return $exists;

    }

    private function getTask(array $data)
    {
        $exists = Task::find($data['id']);

        if( $exists === null) {
            Task::create($data);
            $exists = Task::find($data['id']);

        }

        return $exists;
    }

    private function setAssign($data, Task $task)
    {
        $users = collect($data)->transform(function($user) use($task) {
            return $this->getUser($user);
        });

        $task->users()->sync($users->pluck('id')->toArray(), true);


    }

    private function processChanges($data)
    {
        if (isset($data['changes'])) {
            $this->labelsProcess($data['changes']['labels'], $data['object_attributes']['id']);

        }

    }

    private function labelsProcess($changes, $taskId)
    {
        $prev = $changes['previous'];
        $current = $changes['current'];

        $current = collect($prev)->filter(function($labelKeep) use ($current) {

            if (collect($current)->pluck('id')->search($labelKeep['id']) === false) {
                return true;
            }

            return false;

        });

        $this->setUpdates(collect($current), $taskId);
        $this->setTracks(collect($current), $taskId);

        collect($current)->each(function($label) {

        });
    }

    private function setUpdates(Collection $labels, $taskId)
    {
        $update = Update::where('task_id', $taskId)->orderBy('id', 'desc')->first();

        $labels->each(function($label) use($taskId, $update) {

            if (!in_array(strtolower($label['title']), $this->trackedLabels)) {
                return;
            }

            $spent = 0;

            if ($update !== null) {
                $spent = $update->created_at->diff(Carbon::now())->i;
            }

            $updateData = [
                'status' => $label['title'],
                'task_id' => $taskId,
                'spent' => $spent
            ];

            Update::create($updateData);


        });
    }

    private function setTracks(Collection $labels, $taskId)
    {
        $users = Task::find($taskId)->users()->get();

        collect($users->toArray())->transform(function($user) use($taskId, $labels) {

            $labels->each(function($label) use($taskId, $user) {

                if (!in_array(strtolower($label['title']), $this->trackedLabels)) {
                    return;
                }

                $spent = 0;
                $update = Track::where('task_id', $taskId)
                    ->where('user_id', $user['id'])
                    ->orderBy('id', 'desc')
                    ->first();

                if ($update !== null) {
                    $spent =  $update->created_at->diff(Carbon::now())->i;
                }

                $updateData = [
                    'status' => $label['title'],
                    'task_id' => $taskId,
                    'user_id' => $user['id'],
                    'spent' => $spent
                ];

                Track::create($updateData);

            });

        });

    }


}
