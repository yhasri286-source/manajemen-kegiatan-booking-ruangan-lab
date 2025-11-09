// DOM Manipulation and Event Handling
document.addEventListener('DOMContentLoaded', function() {
    // Initialize pagination
    initPagination();
    
    // Initialize search functionality
    initSearch();
    
    // Form validation
    initFormValidation();
    
    // Time validation for booking form
    initTimeValidation();
});

// Pagination functionality
function initPagination() {
    const table = document.getElementById('bookingsTable');
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    const rowsPerPage = 10;
    const pageCount = Math.ceil(rows.length / rowsPerPage);
    const pagination = document.getElementById('pagination');
    
    if (pageCount <= 1) return;
    
    let currentPage = 1;
    
    function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
        
        updatePaginationButtons();
    }
    
    function updatePaginationButtons() {
        pagination.innerHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            const prevBtn = createPaginationButton('←', () => {
                currentPage--;
                showPage(currentPage);
            });
            pagination.appendChild(prevBtn);
        }
        
        // Page buttons
        for (let i = 1; i <= pageCount; i++) {
            const btn = createPaginationButton(i, () => {
                currentPage = i;
                showPage(currentPage);
            });
            if (i === currentPage) {
                btn.classList.add('active');
            }
            pagination.appendChild(btn);
        }
        
        // Next button
        if (currentPage < pageCount) {
            const nextBtn = createPaginationButton('→', () => {
                currentPage++;
                showPage(currentPage);
            });
            pagination.appendChild(nextBtn);
        }
    }
    
    function createPaginationButton(text, clickHandler) {
        const btn = document.createElement('button');
        btn.textContent = text;
        btn.addEventListener('click', clickHandler);
        return btn;
    }
    
    showPage(1);
}

// Search functionality
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const table = document.getElementById('bookingsTable');
    const rows = table.querySelectorAll('tbody tr');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    showFieldError(field, 'Field ini wajib diisi.');
                } else {
                    clearFieldError(field);
                }
            });
            
            if (!valid) {
                e.preventDefault();
                showFlashMessage('Harap isi semua field yang wajib.', 'error');
            }
        });
    });
}

// Time validation for booking form
function initTimeValidation() {
    const bookingForm = document.getElementById('bookingForm');
    if (!bookingForm) return;
    
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    const date = document.getElementById('date');
    
    function validateTimes() {
        if (startTime.value && endTime.value) {
            if (startTime.value >= endTime.value) {
                showFieldError(startTime, 'Waktu mulai harus sebelum waktu selesai.');
                showFieldError(endTime, 'Waktu selesai harus setelah waktu mulai.');
                return false;
            } else {
                clearFieldError(startTime);
                clearFieldError(endTime);
            }
        }
        
        if (date.value) {
            const selectedDate = new Date(date.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                showFieldError(date, 'Tidak dapat memilih tanggal yang sudah lewat.');
                return false;
            } else {
                clearFieldError(date);
            }
        }
        
        return true;
    }
    
    startTime.addEventListener('change', validateTimes);
    endTime.addEventListener('change', validateTimes);
    date.addEventListener('change', validateTimes);
    
    bookingForm.addEventListener('submit', function(e) {
        if (!validateTimes()) {
            e.preventDefault();
            showFlashMessage('Harap periksa input waktu dan tanggal.', 'error');
        }
    });
}

// Utility functions
function showFieldError(field, message) {
    clearFieldError(field);
    field.style.borderColor = '#e74c3c';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.style.borderColor = '';
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function showFlashMessage(message, type) {
    // Create flash message element
    const flashDiv = document.createElement('div');
    flashDiv.className = `flash flash-${type}`;
    flashDiv.textContent = message;
    
    // Find or create flash messages container
    let flashContainer = document.querySelector('.flash-messages');
    if (!flashContainer) {
        flashContainer = document.createElement('div');
        flashContainer.className = 'flash-messages';
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(flashContainer, container.firstChild);
        }
    }
    
    flashContainer.appendChild(flashDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        flashDiv.remove();
    }, 5000);
}

// Export functionality (for future implementation)
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Clean and escape data
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    // Download CSV file
    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename + '.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}