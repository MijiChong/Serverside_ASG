document.addEventListener("DOMContentLoaded", () => {
  console.log("Avatar.js loaded");

  // Show the avatar modal - using Bootstrap modal
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

    // Show feedback
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

  // Helper function to show temporary messages
  function showTemporaryMessage(message, type = "info") {
    const alertClass = type === 'info' ? 'alert-info' : type === 'success' ? 'alert-success' : 'alert-warning';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = `
      top: 20px;
      right: 20px;
      z-index: 10001;
      min-width: 300px;
      animation: slideInRight 0.3s ease-out;
    `;
    alert.innerHTML = `
      <i class="fas fa-info-circle me-2"></i>${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
      if (alert.parentNode) {
        alert.remove();
      }
    }, 4000);
  }

  // Reset form function
  window.resetForm = function () {
    if (confirm("Are you sure you want to reset all changes?")) {
      // Reload the page data instead of just resetting the form
      if (typeof loadUserData === 'function') {
        loadUserData();
      } else {
        location.reload();
      }
    }
  };

  console.log("Avatar.js initialization complete");
});