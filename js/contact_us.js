/* ============================================
   CONTACT US PAGE - JAVASCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const successMessage = document.getElementById('successMessage');

    // Form validation and submission
    contactForm.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();

        // Check if form is valid
        if (!contactForm.checkValidity()) {
            contactForm.classList.add('was-validated');
            return;
        }

        // Get form data
        const formData = {
            fullName: document.getElementById('fullName').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            subject: document.getElementById('subject').value,
            message: document.getElementById('message').value,
            privacy: document.getElementById('privacy').checked
        };

        // Simulate form submission
        submitContactForm(formData);
    });

    // Reset form handler
    contactForm.addEventListener('reset', function() {
        contactForm.classList.remove('was-validated');
        successMessage.classList.add('d-none');
    });

    /**
     * Submit contact form
     * @param {Object} data - Form data
     */
    function submitContactForm(data) {
        // Show loading state
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sending...';

        // TODO: Replace with actual API call
        // Example: fetch('/api/contact', { method: 'POST', body: JSON.stringify(data) })
        
        // Simulate API call with setTimeout
        setTimeout(function() {
            console.log('Form Data:', data);

            // Show success message
            successMessage.classList.remove('d-none');
            
            // Reset form
            contactForm.reset();
            contactForm.classList.remove('was-validated');

            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            // Scroll to success message
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            // Hide success message after 5 seconds
            setTimeout(function() {
                successMessage.classList.add('d-none');
            }, 5000);

        }, 1500); // Simulate network delay
    }

    /**
     * Phone number formatting (optional enhancement)
     */
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
        
        if (value.length > 0) {
            if (value.length <= 3) {
                value = `(${value}`;
            } else if (value.length <= 6) {
                value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
            } else {
                value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
            }
        }
        
        e.target.value = value;
    });

    /**
     * Email validation enhancement
     */
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        const email = this.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    /**
     * Character counter for message textarea (optional)
     */
    const messageTextarea = document.getElementById('message');
    const maxLength = 1000;
    
    // Create character counter element
    const counterDiv = document.createElement('div');
    counterDiv.className = 'text-muted small mt-1 text-end';
    counterDiv.id = 'messageCounter';
    messageTextarea.parentNode.appendChild(counterDiv);
    
    // Update counter
    function updateCounter() {
        const remaining = maxLength - messageTextarea.value.length;
        counterDiv.textContent = `${messageTextarea.value.length} / ${maxLength} characters`;
        
        if (remaining < 100) {
            counterDiv.classList.remove('text-muted');
            counterDiv.classList.add('text-warning');
        } else {
            counterDiv.classList.remove('text-warning');
            counterDiv.classList.add('text-muted');
        }
    }
    
    messageTextarea.setAttribute('maxlength', maxLength);
    messageTextarea.addEventListener('input', updateCounter);
    updateCounter(); // Initial count

    console.log('Contact Us page initialized');
});