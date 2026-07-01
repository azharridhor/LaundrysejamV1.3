document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    window.toggleSidebar = function () {
        if (!sidebar) return;
        sidebar.classList.toggle('open');
    };

    const alerts = document.querySelectorAll('.alert-dismissible');
    if (alerts.length) {
        setTimeout(() => {
            alerts.forEach(a => {
                if (window.bootstrap && bootstrap.Alert) {
                    bootstrap.Alert.getOrCreateInstance(a).close();
                }
            });
        }, 4000);
    }
});
