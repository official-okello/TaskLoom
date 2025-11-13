<?php
require_once __DIR__ . '/bootstrap.php';

class TaskValidator {
    // Task item validation
    public static function validateTaskItem(string $taskItem): string {
        $taskItem = trim($taskItem);
        if (empty($taskItem)) {
            throw new ValidationException("Task description is required.");
        }
        if (mb_strlen($taskItem) > 1000) {
            throw new ValidationException("Task description cannot exceed 1000 characters.");
        }
        return htmlspecialchars($taskItem, ENT_QUOTES, 'UTF-8');
    }

    // Due date validation
    public static function validateDueDate(?string $dueDate): ?string {
        if (empty($dueDate)) {
            return null;
        }
        $timestamp = strtotime($dueDate);
        if ($timestamp === false) {
            throw new ValidationException("Invalid due date format.");
        }
        // Ensure due date is not in the past
        if ($timestamp < strtotime('today')) {
            throw new ValidationException("Due date cannot be in the past.");
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    // Category validation
    public static function validateCategory(?string $category): ?string {
        if (empty($category)) {
            return null;
        }
        $category = trim($category);
        if (mb_strlen($category) > 50) {
            throw new ValidationException("Category name cannot exceed 50 characters.");
        }
        return htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
    }

    // Tags validation
    public static function validateTags(?string $tags): ?string {
        if (empty($tags)) {
            return null;
        }
        $tags = trim($tags);
        if (mb_strlen($tags) > 255) {
            throw new ValidationException("Tags cannot exceed 255 characters.");
        }
        // Split tags, clean them, and rejoin
        $tagArray = array_map(function($tag) {
            return htmlspecialchars(trim($tag), ENT_QUOTES, 'UTF-8');
        }, explode(',', $tags));
        return implode(',', array_filter($tagArray));
    }

    // Priority validation
    public static function validatePriority(?string $priority): string {
        $validPriorities = ['low', 'medium', 'high'];
        $priority = strtolower(trim($priority ?? 'medium'));
        if (!in_array($priority, $validPriorities)) {
            throw new ValidationException("Invalid priority level.");
        }
        return $priority;
    }

    // Sort validation
    public static function validateSortField(string $field): string {
        $validFields = ['date_created', 'due_date', 'priority', 'status', 'category'];
        if (!in_array($field, $validFields)) {
            throw new ValidationException("Invalid sort field.");
        }
        return $field;
    }

    // Sort order validation
    public static function validateSortOrder(string $order): string {
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            throw new ValidationException("Invalid sort order.");
        }
        return $order;
    }

    // Status validation
    public static function validateStatus(string $status): string {
        // Accept legacy values but return canonical DB values 'open' or 'done'
        $status = strtolower(trim($status));
        $mapping = [
            'pending' => 'open',
            'open' => 'open',
            'todo' => 'open',
            'notdone' => 'open',
            'complete' => 'done',
            'completed' => 'done',
            'done' => 'done',
            'finished' => 'done'
        ];

        if (!isset($mapping[$status])) {
            throw new ValidationException("Invalid status.");
        }

        return $mapping[$status];
    }
}

// Custom validation exception
class ValidationException extends Exception {}