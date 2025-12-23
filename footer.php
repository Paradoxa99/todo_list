</div> <!-- End main container -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// AJAX function to change task status without reload
function changeStatus(taskId, newStatus) {
    fetch('ajax_change_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'task_id=' + taskId + '&status=' + encodeURIComponent(newStatus)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to update the display
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thay đổi trạng thái.');
    });
}
</script>
</body>
</html>
