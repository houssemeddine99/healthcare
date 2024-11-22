document.addEventListener('DOMContentLoaded', function() {
    const userTypeButtons = document.querySelectorAll('.user-type-toggle button');
    const userTypeInput = document.getElementById('userType');
    const apciField = document.querySelector('.patient-field');
    const loginForm = document.getElementById('loginForm'); 
    userTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            userTypeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const userType = this.dataset.type;
            userTypeInput.value = userType;
            
          
            if (userType === 'admin') {
                apciField.style.display = 'none';
            } else {
                apciField.style.display = 'block';
            }
        });
    });
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('auth/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                const errorDiv = document.querySelector('.error-message') || 
                    document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = data.message;
                
                if (!document.querySelector('.error-message')) {
                    loginForm.appendChild(errorDiv);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});