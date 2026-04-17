    <!-- Vendor JS -->
    <script src="<?php echo BASE_URL; ?>assets/vendors/js/vendors.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/js/daterangepicker.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/js/apexcharts.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/vendors/js/circle-progress.min.js"></script>
    
    <!-- Theme JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/common-init.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/theme-customizer-init.min.js"></script>
    
    <script>
        // Global Table Search Logic
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('globalSearchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const value = this.value.toLowerCase();
                    const tables = document.querySelectorAll('table tbody');
                    
                    tables.forEach(tbody => {
                        const rows = tbody.querySelectorAll('tr');
                        rows.forEach(row => {
                            if (row.innerText.toLowerCase().indexOf(value) > -1) {
                                row.style.display = "";
                            } else {
                                // Don't hide "No records found" messages if they are the only row
                                if (!row.classList.contains('no-results-row')) {
                                    row.style.display = "none";
                                }
                            }
                        });
                    });
                });
            }
            console.log("Zenith Learn Platform Initialized with Global Search");
        });
    </script>
</body>
</html>
