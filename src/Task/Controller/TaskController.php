<?php

namespace App\Task\Controller;

use App\Task\Application\AddTaskDTO;
use App\Task\Application\AddTaskService;
use App\Task\Application\DeleteTaskDTO;
use App\Task\Application\DeleteTaskService;
use App\Task\Application\GetAllTasksDTO;
use App\Task\Application\GetAllTasksService;
use App\Task\Application\GetTaskDTO;
use App\Task\Application\GetTaskService;
use App\Task\Infrastructure\TaskRepository;

class TaskController
{
    private $userId;
    private $taskId;

    public function __construct(/*private*/ int $userId)
    {
        $this->userId = $userId;
    }
    
    public function getAllByUserId()
    {
        $taskRepository = new TaskRepository();

        $getAllTasksDTO = new GetAllTasksDTO($this->userId);

        $getAllTasksService = new GetAllTasksService($taskRepository);
        $serviceResponse = $getAllTasksService->execute($getAllTasksDTO);

        echo json_encode($serviceResponse);
    }

    public function getByUserId(int $taskId)
    {
        $taskRepository = new TaskRepository();

        $getTaskDTO = new GetTaskDTO($this->userId, $taskId);

        $getTaskService = new GetTaskService($taskRepository);
        $serviceResponse = $getTaskService->execute($getTaskDTO);

        echo json_encode($serviceResponse);
    }

    public function addByUserId()
    {
        $taskRepository = new TaskRepository();

        //is user authenticate?

        $data = (array) json_decode(file_get_contents("php://input"), true); 

        $addTaskParams = [];
        $addTaskParams['name'] = $data['name'];
        $addTaskParams['priority'] = $data['priority'];
        $addTaskParams['is_completed'] = $data['is_completed'];

        $addTaskRequest = new AddTaskDTO(
            $addTaskParams['name'],
            $addTaskParams['priority'],
            $addTaskParams['is_completed'],
            $this->userId
        );

        $addTaskService = new AddTaskService($taskRepository);
        $serviceResponse = $addTaskService->execute($addTaskRequest);

        print_r($serviceResponse);

        // $errors = $this->getValidationErrors($data);

        // if (!empty($errors)) {
        //     $this->respondUnprocessableEntity($errors);
        //     return;
        // }

        // $id = $this->gateway->createByUserId($this->userId, $data);
        

        // $this->respondCreated($id);
    }

    public function updateByUserId(int $id)
    {
        $data = (array) json_decode(file_get_contents("php://input"), true); 
    
        $errors = $this->getValidationErrors($data, false);

        if (!empty($errors)) {

            $this->respondUnprocessableEntity($errors);
            return;
        }
        
        // $rows = $this->gateway->updateByUserId($this->userId, $id, $data);
        // echo json_encode(["message" => "Task updated", "rows" => $rows]);
    }

    public function deleteByUserId(int $taskId)
    { 
        $taskRepository = new TaskRepository();

        $deleteTaskDTO = new DeleteTaskDTO($this->userId, $taskId);

        $deleteTaskService = new DeleteTaskService($taskRepository);
        $serviceResponse = $deleteTaskService->execute($deleteTaskDTO);

        echo json_encode($serviceResponse);

        echo json_encode(["message" => "Task deleted"]);
    }



    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    private function respondMethodNotAllowed(string $allowedMethods): void
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["message" => "Task with ID $id not found"]);
    }

    private function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode(["message" => "Task created", "id" => $id]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data["name"])) {

            $errors[] = "name is required";
        }

        if (!empty($data["priority"])) {

            if (filter_var($data["priority"], FILTER_VALIDATE_INT) === false) {

                $errors[] = "priority must be an integer";
            }
        }

        return $errors;
    }
}
