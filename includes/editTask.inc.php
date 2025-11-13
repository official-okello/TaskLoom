<?php
    include_once "dbh.inc.php";

    class EditTask extends Dbh {
        private $task_id;
        private $task_item;

        public function __construct($task_id, $task_item) {
            parent::__construct();
            $this->task_id = $task_id;
            $this->task_item = $task_item;
        }

        public function editTask() {
            $task_id = $this->task_id;
            $task_item = $this->task_item;
            $conn = $this->connect();

            $stmt = $conn->prepare("UPDATE tasks SET task_item = :task_item WHERE id = :task_id");
            $stmt->bindParam(':task_item', $task_item);
            $stmt->bindParam(':task_id', $task_id);
            $stmt->execute();
            return true;
        }
    }