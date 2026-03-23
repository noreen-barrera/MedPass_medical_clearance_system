// insert java script code
let lastScrollY = window.scrollY;
    const navbar = document.querySelector('.navbar');

    window.addEventListener('scroll', () => {
        if (window.scrollY > lastScrollY) {
            // Scrolling down - hide navbar
            navbar.style.top = '-100px';
        } else {
            // Scrolling up - show navbar
            navbar.style.top = '0';
        }
        lastScrollY = window.scrollY;
    });
    
//FOR MANAGE TEST RESULTS PART (makes file upload button unclickable if status is not set to "completed")
function toggleFileUpload(selectElement) {
    const fileInput = selectElement.parentElement.querySelector('input[type="file"]');

    if (selectElement.value === 'Pending') {
        fileInput.disabled = true;
        fileInput.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        fileInput.disabled = false;
        fileInput.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

//add patient
function validateForm() {
    const phone = document.querySelector('input[name="phone"]').value;
    const regex = /^\d{10,15}$/;
    if (!regex.test(phone)) {
        alert("Invalid phone number. Use 10-15 digits only.");
        return false;
    }
    return true;
}

//edit view patient info
document.addEventListener('DOMContentLoaded', function() {
    const editButton = document.getElementById('editButton');
    const cancelButton = document.getElementById('cancelButton');
    const saveButton = document.getElementById('saveButton');
    const viewProfile = document.getElementById('viewProfile');
    const editProfile = document.getElementById('editProfile');
    
    // Function to toggle edit mode
    function toggleEditMode(isEditing) {
        if (isEditing) {
            viewProfile.classList.add('hidden');
            editProfile.classList.remove('hidden');
            editButton.classList.add('hidden');
            cancelButton.classList.remove('hidden');
            saveButton.classList.remove('hidden');
            
            // Toggle test status and file upload fields
            document.querySelectorAll('[id^="viewStatus-"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="editStatus-"]').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('[id^="viewFile-"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="editFile-"]').forEach(el => el.classList.remove('hidden'));
        } else {
            editProfile.classList.add('hidden');
            viewProfile.classList.remove('hidden');
            editButton.classList.remove('hidden');
            cancelButton.classList.add('hidden');
            saveButton.classList.add('hidden');
            
            // Toggle test status and file upload fields back
            document.querySelectorAll('[id^="viewStatus-"]').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('[id^="editStatus-"]').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('[id^="viewFile-"]').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('[id^="editFile-"]').forEach(el => el.classList.add('hidden'));
        }
    }
    
    editButton.addEventListener('click', function() {
        toggleEditMode(true);
    });
    
    cancelButton.addEventListener('click', function() {
        toggleEditMode(false);
    });

});

