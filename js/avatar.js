document.addEventListener("DOMContentLoaded", () => {
  // Attach event listener to the avatar button
  const chooseAvatarBtn = document.getElementById("chooseAvatarBtn");
  if (chooseAvatarBtn) {
    chooseAvatarBtn.addEventListener("click", openAvatarModal);
  }

  // Show the avatar modal
  window.openAvatarModal = function () {
    const modal = document.getElementById("avatarModal");
    if (modal) {
      modal.style.display = "block";
    }
  };

  // Gradient selection logic
  window.selectGradient = function (id) {
    document.querySelectorAll('.gradient-option').forEach(el => {
      el.classList.remove('selected');
    });

    const selected = document.querySelector(`.gradient-option[data-gradient='${id}']`);
    if (selected) {
      selected.classList.add('selected');
      const selectedGradient = document.getElementById('selectedGradient');
      if (selectedGradient) {
        selectedGradient.value = id; // Save selected gradient ID
      }
    }
  };

  // Apply avatar gradient preview
  window.applyAvatarSelection = function () {
    const selected = document.querySelector('.gradient-option.selected');
    const gradientId = selected ? selected.dataset.gradient : 1;

    const avatar = document.getElementById('avatarPreview');
    if (avatar) {
      avatar.className = `profile-avatar gradient-group-${gradientId}`;
    }

    // Hide modal
    const modal = document.getElementById("avatarModal");
    if (modal) {
      modal.style.display = "none";
    }
  };

  // Reset form (optional)
  window.resetForm = function () {
    const form = document.getElementById("profileForm");
    if (form) {
      form.reset();
      alert("Form reset");
    }
  };
});
