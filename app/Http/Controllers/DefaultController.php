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
use Illuminate\Support\Facades\Log;

class DefaultController extends Controller
{

    private $trackedLabels = [
        'task',
        'history',
        'doing',
        'done',
        'ready for test',
        'needs info',
        'need info',
        'needs infos',
        'need infos',
        'backlog',
        'sprint backlog'
    ];

    public function index(Request $request)
    {
        $postData = $request->post();

        $this->getUser($postData['user']);
        $this->getProject($postData['project']);
        $task = $this->getTask($postData['object_attributes']);
        $this->setAssign($postData['assignees'] ?? [], $task);
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

        $exists->fill($data);
        $exists->save();
        return $exists;

    }

    private function getTask(array $data)
    {
        $exists = Task::find($data['id']);

        if( $exists === null) {
            Task::create($data);
            $exists = Task::find($data['id']);

        }

        $exists->fill($data);
        $exists->save();
        return $exists;
    }

    private function setAssign($data, Task $task)
    {
        $users = collect($data)->transform(function($user) use($task) {
            return $this->getUser($user);
        });

        $task->users()->sync($users->pluck('id')->toArray(), true);

        $this->setAssignPastTrack($users, $task);


    }

    private function setAssignPastTrack($users, $task)
    {

        $users->each(function($user) use ($task) {
            $check = Track::where('task_id', $task->id)
                ->where('user_id', $user['id'])
                ->get();
            if ($check->count() === 0) {
                $past =  Update::where('task_id', $task->id)->get();
                collect($past->toArray())->each(function($entry) use ($user) {
                    $entry['user_id'] = $user['id'];
                    unset($entry['id']);
                    Track::create($entry);
                });
            }
        });
    }

    private function processChanges($data)
    {
        if (isset($data['changes']) && isset($data['changes']['labels'])) {
            $this->labelsProcess($data['changes']['labels'], $data['object_attributes']['id']);

        }

    }

    private function labelsProcess($changes, $taskId)
    {
        $prev = $changes['previous'];
        $current = $changes['current'];

        if (count($prev) !== 0) {
            $current = collect($prev)->filter(function($labelKeep) use ($current) {

                if (collect($current)->pluck('id')->search($labelKeep['id']) === false) {
                    return true;
                }

                return false;

            });
        }

        $this->setUpdates(collect($current), $taskId);
        $this->setTracks(collect($current), $taskId);

        $currentTagState = collect($current)->filter(function($currentLabel) {

            if (!in_array(strtolower($currentLabel['title']), $this->trackedLabels)) {
                return false;
            }

            if (strtolower($currentLabel['title']) === 'task') {
                return false;
            }

            return true;

        });

        Log::info('states', $currentTagState);
        if ($currentTagState->count() > 0) {
            $task = Task::findOrFail($taskId);
            $task->current_tag = $currentTagState->first()['name'];
            $task->save();

        }

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
            Task::find($taskId)->fill(['status' => $label['title']])->save();

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
