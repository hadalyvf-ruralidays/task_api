<?php

namespace App\Task\Controller;

use App\Task\Application\AddTaskDTO;
use App\Task\Application\AddTaskService;
use App\Task\Infrastructure\TaskRepository;

class TaskController
{
    private $gateway;
    private $userId;

    public function __construct(/*private*/ TaskRepository $gateway,
                                /*private*/ int $userId)
    {
        $this->gateway = $gateway;
        $this->userId = $userId;
    }

    public function getAllByUserId()
    {
        echo json_encode($this->gateway->getAllByUserId($this->userId));
    }

    public function addByUserId()
    {
        $taskRepository = new TaskRepository();

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
        exit;

        $errors = $this->getValidationErrors($data);

        if (!empty($errors)) {
            $this->respondUnprocessableEntity($errors);
            return;
        }

        $id = $this->gateway->createByUserId($this->userId, $data);

        $this->respondCreated($id);
    }

    public function updateByUserId(int $id)
    {
        $data = (array) json_decode(file_get_contents("php://input"), true); 
    
        $errors = $this->getValidationErrors($data, false);

        if (!empty($errors)) {

            $this->respondUnprocessableEntity($errors);
            return;
        }
        
        $rows = $this->gateway->updateByUserId($this->userId, $id, $data);
        echo json_encode(["message" => "Task updated", "rows" => $rows]);
    }

    public function deleteByUserId(int $id)
    {
        $rows = $this->gateway->deleteByUserId($this->userId, $id);
        echo json_encode(["message" => "Task deleted", "rows" => $rows]);
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {
            if ($method == "GET") {

                $this->getAllByUserId();

            } elseif ($method == "POST") {

                $this->addByUserId();
                
            } else {

                $this->respondMethodNotAllowed("GET, POST");
            }
        } else {

            $task = $this->gateway->getByUserId($this->userId, $id);

            if ($task === false) {

                $this->respondNotFound($id);
                return;
            }

            switch ($method) {
                case "GET":
                    echo json_encode($task);
                    break;

                case "PATCH":
                    $this->updateByUserId($id);
                    break;

                case "DELETE":
                    $this->deleteByUserId($id);
                    break;

                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
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
