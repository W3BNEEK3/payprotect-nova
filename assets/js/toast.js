(function () {
    function showToast(type, title, message, timeoutMs) {
        var root = document.getElementById('toast-root');
        if (!root) return;

        var toast = document.createElement('div');
        toast.className = 'toast ' + (type || '');
        toast.innerHTML =
            '<div class="toast-title">' + (title || '') + '</div>' +
            '<p class="toast-message">' + (message || '') + '</p>';

        root.appendChild(toast);

        var timeout = timeoutMs || 4500;
        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-6px)';
            setTimeout(function () {
                if (toast.parentNode) root.removeChild(toast);
            }, 200);
        }, timeout);
    }

    window.NTToast = { show: showToast };
})();