// Add event listeners for filter form changes
document.addEventListener('DOMContentLoaded', function() {
    // Get all filter inputs
    const filterInputs = document.querySelectorAll('#filterForm select, #filterForm input[type="text"]');
    
    // Add change event listener to each filter input
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Submit the form when any filter changes
            document.getElementById('filterForm').submit();
        });
    });

    // Add debounced search
    const searchInput = document.getElementById('search');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
});

// Task sharing functionality
function showShareModal(taskId) {
    // Implementation for task sharing modal
}

// Task editing functionality
function editTask(taskId) {
    window.location.href = `edit_task.php?id=${taskId}`;
}

// Mark task as complete
function markComplete(taskId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'taskOperations.php';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = document.querySelector('input[name="csrf_token"]').value;
    
    const taskIdInput = document.createElement('input');
    taskIdInput.type = 'hidden';
    taskIdInput.name = 'task_id';
    taskIdInput.value = taskId;
    
    const operationInput = document.createElement('input');
    operationInput.type = 'hidden';
    operationInput.name = 'operation';
    operationInput.value = 'Mark as Complete';
    
    form.appendChild(csrfInput);
    form.appendChild(taskIdInput);
    form.appendChild(operationInput);
    document.body.appendChild(form);
    form.submit();
}

// Delete task confirmation
function confirmDelete(taskId) {
    if (confirm('Are you sure you want to delete this task?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'taskOperations.php';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = document.querySelector('input[name="csrf_token"]').value;
        
        const taskIdInput = document.createElement('input');
        taskIdInput.type = 'hidden';
        taskIdInput.name = 'task_id';
        taskIdInput.value = taskId;
        
        const operationInput = document.createElement('input');
        operationInput.type = 'hidden';
        operationInput.name = 'operation';
        operationInput.value = 'Delete';
        
        form.appendChild(csrfInput);
        form.appendChild(taskIdInput);
        form.appendChild(operationInput);
        document.body.appendChild(form);
        form.submit();
    }
}