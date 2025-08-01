document.addEventListener("DOMContentLoaded", () => {
  // Attach event listener to the avatar button
  const chooseAvatarBtn = document.getElementById("chooseAvatarBtn");
  if (chooseAvatarBtn) {
    chooseAvatarBtn.addEventListener("click", openAvatarModal);
  }

  // Show the avatar modal - using Bootstrap modal
  window.openAvatarModal = function () {
    const modal = new bootstrap.Modal(document.getElementById('avatarModal'));
    modal.show();
  };

  // Gradient selection logic
  window.selectGradient = function (id) {
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
    }
  };

  // Apply avatar gradient preview and close modal
  window.applyAvatarSelection = function () {
    const selected = document.querySelector('.gradient-option.selected');
    const gradientId = selected ? selected.dataset.gradient : 1;

    // Update avatar preview
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
  };

  // Initialize gradient selection on page load
  const initializeGradientSelection = () => {
    const selectedGradient = document.getElementById('selectedGradient');
    const currentGradient = selectedGradient ? selectedGradient.value : 1;
    
    // Mark the current gradient as selected
    document.querySelectorAll('.gradient-option').forEach(el => {
      el.classList.remove('selected');
      if (parseInt(el.dataset.gradient) === parseInt(currentGradient)) {
        el.classList.add('selected');
      }
    });
  };

  // Call initialization
  setTimeout(initializeGradientSelection, 100);

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

  // Add click event listeners to gradient options
  document.querySelectorAll('.gradient-option').forEach(option => {
    option.addEventListener('click', function() {
      const gradientId = this.dataset.gradient;
      selectGradient(gradientId);
    });
  });
});