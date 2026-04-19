document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar Toggle
    const btn = document.getElementById('sidebarCollapse');
    if(btn) {
        btn.onclick = () => {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.querySelector('.navbar-custom').classList.toggle('expanded');
            document.getElementById('content').classList.toggle('expanded');
        }
    }

    // 2. Horloge Temps Reel
    setInterval(() => {
        const now = new Date();
        const time = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true 
        });
        const el = document.getElementById('nav-clock');
        if(el) el.innerText = time;
    }, 1000);
});