document.addEventListener('DOMContentLoaded', function() {
    // Get all department tabs
    const deptTabs = document.querySelectorAll('.dept-tab');
    const departmentSections = document.querySelectorAll('.department-section');

    // Add click event listeners to all department tabs
    deptTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            deptTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get the department name from data attribute
            const department = this.getAttribute('data-department');
            
            // Hide all department sections
            departmentSections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Show the selected department section
            const selectedSection = document.getElementById(`${department}-section`);
            if (selectedSection) {
                selectedSection.style.display = 'block';
            }
        });
    });

    // Initialize by showing the first department (DIT)
    const firstTab = document.querySelector('.dept-tab[data-department="dit"]');
    if (firstTab) {
        firstTab.click();
    }
}); 