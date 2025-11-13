<?php
    require_once __DIR__ . '/dbh.inc.php';
    class AddTask extends Dbh {
        private $task_creator_id;
        private $task_item;

        public function __construct($task_creator_id, $task_item) {
            parent::__construct();
            $this->task_creator_id = $task_creator_id;
            $this->task_item = $task_item;
        }

        public function addTask() {
            $task_creator_id = $this->task_creator_id;
            $task = $this->task_item;
            $conn = $this->connect();

            $stmt = $conn->prepare("INSERT INTO tasks (creator_id, task_item) VALUES (:creator_id, :task_item)");
            $stmt->bindParam(':creator_id', $task_creator_id, PDO::PARAM_INT);
            $stmt->bindParam(':task_item', $task, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        }
    }