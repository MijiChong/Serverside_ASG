let selectedGradient = null;
        let uploadedImage = null;

        // Open avatar modal
        function openAvatarModal() {
            const modal = new bootstrap.Modal(document.getElementById('avatarModal'));
            modal.show();
        }

        // Handle file upload
        document.getElementById("avatarUpload").addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    showNotification("File size must be less than 5MB", "error");
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = (event) => {
                    uploadedImage = event.target.result;
                    selectedGradient = null;
                    
                    // Clear gradient selections
                    document.querySelectorAll('.gradient-option').forEach(option => {
                        option.classList.remove('active');
                    });
                    
                    // Update upload area to show preview
                    const uploadArea = document.querySelector('.upload-area');
                    uploadArea.innerHTML = `
                        <img src="${uploadedImage}" alt="Preview" class="upload-preview">
                        <p class="upload-text">Image selected</p>
                        <p class="upload-hint">Click to change</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });

        // Select gradient
        function selectGradient(groupNum) {
            selectedGradient = groupNum;
            uploadedImage = null;
            
            // Clear upload preview
            const uploadArea = document.querySelector('.upload-area');
            uploadArea.innerHTML = `
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text">Click to upload or drag and drop</p>
                <p class="upload-hint">PNG, JPG, GIF up to 5MB</p>
            `;
            
            // Update gradient selection
            document.querySelectorAll('.gradient-option').forEach(option => {
                option.classList.remove('active');
            });
            document.querySelector(`[data-gradient="${groupNum}"]`).classList.add('active');
        }

        // Apply avatar selection
        function applyAvatarSelection() {
            const avatar = document.getElementById("avatarPreview");
            
            if (uploadedImage) {
                // Apply uploaded image
                avatar.style.backgroundImage = `url(${uploadedImage})`;
                avatar.style.backgroundSize = "cover";
                avatar.style.backgroundPosition = "center";
                avatar.querySelector(".avatar-text").style.display = "none";
                
                // Remove all gradient classes
                for (let i = 1; i <= 5; i++) {
                    avatar.classList.remove(`gradient-group-${i}`);
                }
            } else if (selectedGradient) {
                // Apply selected gradient
                avatar.style.backgroundImage = "";
                avatar.querySelector(".avatar-text").style.display = "flex";
                
                // Remove all gradient classes and add selected one
                for (let i = 1; i <= 5; i++) {
                    avatar.classList.remove(`gradient-group-${i}`);
                }
                avatar.classList.add(`gradient-group-${selectedGradient}`);
            }
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
            modal.hide();
            
            showNotification("Avatar updated successfully!", "success");
        }

        // Reset form function
        function resetForm() {
            document.getElementById("profileForm").reset();
            showNotification("Form reset successfully!", "info");
        }

        // Make functions available globally
        window.openAvatarModal = openAvatarModal;
        window.selectGradient = selectGradient;
        window.applyAvatarSelection = applyAvatarSelection;
        window.resetForm = resetForm;