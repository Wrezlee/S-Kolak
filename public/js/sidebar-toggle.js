// Sidebar collapse/expand toggle - dipakai di semua halaman admin/operator/verifikator
function toggleSidebar() {
    var sidebar = document.getElementById("appSidebar");
    if (!sidebar) return;
    var collapsed = sidebar.classList.toggle("sidebar-collapsed");
    try {
        localStorage.setItem("skolak_sidebar_collapsed", collapsed ? "1" : "0");
    } catch (e) {
        // localStorage tidak tersedia (mis. mode private) - abaikan, cukup toggle visual saja
    }
}
