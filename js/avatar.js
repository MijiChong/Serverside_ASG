document.addEventListener("DOMContentLoaded", () => {
    console.log("Avatar.js loaded");

    // Show the avatar modal
    window.openAvatarModal = function () {
        console.log("Opening avatar modal");
        const modal = new bootstrap.Modal(document.getElementById('avatarModal'));
        
        // Initialize current selection when modal opens
        const currentGradient = document.getElementById('selectedGradient')?.value || 1;
        selectGradient(parseInt(currentGradient));
        
        modal.show();
    };

    // Gradient selection logic
    window.selectGradient = function (id) {
        console.log("Selecting gradient:", id);
        
        // Remove selected class from all options
        document.querySelectorAll('.gradient-option').forEach(el => {
            el.classList.remove('selected');
        });

        // Add selected class to clicked option
        const selected = document.querySelector(`.gradient-option[data-gradient='${id}']`);
        if (selected) {
            selected.classList.add('selected');
            
            // Update hidden input
            const selectedGradient = document.getElementById('selectedGradient');
            if (selectedGradient) {
                selectedGradient.value = id;
            }

            // Update preview avatar in modal
            const previewAvatar = document.getElementById('previewAvatar');
            if (previewAvatar) {
                previewAvatar.className = `preview-avatar gradient-group-${id}`;
                
                // Update preview letter (first letter of username or email)
                const username = document.getElementById('username')?.value || '';
                const email = document.getElementById('email')?.value || '';
                const letter = (username || email || 'U').charAt(0).toUpperCase();
                const previewText = previewAvatar.querySelector('.avatar-text');
                if (previewText) {
                    previewText.textContent = letter;
                }
            }
        }
    };

    // Apply avatar gradient and close modal
    window.applyAvatarSelection = function () {
        const selected = document.querySelector('.gradient-option.selected');
        const gradientId = selected ? selected.dataset.gradient : 1;

        console.log("Applying gradient:", gradientId);

        // Update main avatar preview
        const avatar = document.getElementById('avatarPreview');
        if (avatar) {
            avatar.className = `profile-avatar gradient-group-${gradientId}`;
        }

        // Update hidden input to ensure it's saved
        const selectedGradient = document.getElementById('selectedGradient');
        if (selectedGradient) {
            selectedGradient.value = gradientId;
        }

        // Close modal using Bootstrap
        const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
        if (modal) {
            modal.hide();
        }

        // Show feedback with matching dashboard style
        showTemporaryMessage("Avatar updated! Don't forget to save your changes.", "info");
    };

    // Initialize gradient selection on page load
    const initializeGradientSelection = () => {
        const selectedGradient = document.getElementById('selectedGradient');
        const currentGradient = selectedGradient ? selectedGradient.value : 1;
        
        console.log("Initializing gradient selection:", currentGradient);
        
        // Mark the current gradient as selected
        document.querySelectorAll('.gradient-option').forEach(el => {
            el.classList.remove('selected');
            if (parseInt(el.dataset.gradient) === parseInt(currentGradient)) {
                el.classList.add('selected');
            }
        });

        // Update main avatar
        const avatar = document.getElementById('avatarPreview');
        if (avatar) {
            avatar.className = `profile-avatar gradient-group-${currentGradient}`;
        }
    };

    // Add click event listeners to gradient options
    document.querySelectorAll('.gradient-option').forEach(option => {
        option.addEventListener('click', function() {
            const gradientId = this.dataset.gradient;
            selectGradient(parseInt(gradientId));
        });
    });

    // Initialize after a short delay to ensure DOM is ready
    setTimeout(initializeGradientSelection, 200);

    // Helper function to show temporary messages - matching dashboard notifications
    function showTemporaryMessage(message, type = "info") {
        const alertClass = type === 'info' ? 'alert-info' : type === 'success' ? 'alert-success' : 'alert-warning';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alert.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 10001;
            min-width: 300px;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            animation: slideInRight 0.3s ease-out;
        `;
        
        const iconMap = {
            info: 'fa-info-circle',
            success: 'fa-check-circle', 
            warning: 'fa-exclamation-triangle',
            danger: 'fa-times-circle'
        };
        
        alert.innerHTML = `
            <i class="fas ${iconMap[type] || iconMap.info} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(alert);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.classList.add('fade-out');
                setTimeout(() => alert.remove(), 300);
            }
        }, 4000);
    }

    // Reset form function
    window.resetForm = function () {
        if (confirm("Are you sure you want to reset all changes? This will reload your original profile data.")) {
            // Show loading state
            const resetBtn = document.querySelector('.reset-btn');
            if (resetBtn) {
                const originalText = resetBtn.innerHTML;
                resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting...';
                resetBtn.disabled = true;
                
                // Restore original text after reset
                setTimeout(() => {
                    resetBtn.innerHTML = originalText;
                    resetBtn.disabled = false;
                }, 1000);
            }
            
            // Reload the page data instead of just resetting the form
            if (typeof loadUserData === 'function') {
                loadUserData();
            } else {
                location.reload();
            }
        }
    };

    // Add smooth animations for gradient selections
    document.querySelectorAll('.gradient-option').forEach(option => {
        option.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.05)';
        });
        
        option.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = '';
            }
        });
    });

    // Enhance modal animations
    const avatarModal = document.getElementById('avatarModal');
    if (avatarModal) {
        avatarModal.addEventListener('shown.bs.modal', function () {
            // Add stagger animation to gradient options
            const options = this.querySelectorAll('.gradient-option');
            options.forEach((option, index) => {
                option.style.animation = `fadeIn 0.5s ease-out ${index * 0.1}s both`;
            });
        });
        
        avatarModal.addEventListener('hidden.bs.modal', function () {
            // Reset animations
            const options = this.querySelectorAll('.gradient-option');
            options.forEach(option => {
                option.style.animation = '';
            });
        });
    }

    console.log("Avatar.js initialization complete");
});