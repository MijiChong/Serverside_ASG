import { doc, getDoc } from 'https://www.gstatic.com/firebasejs/10.7.0/firebase-firestore.js';

const uid = typeof FIREBASE_UID !== 'undefined' ? FIREBASE_UID : null;
if (!uid) console.error("FIREBASE_UID is missing!");

// ENHANCED FLOATING LABEL HANDLING - Special textarea support
function updateFloatingLabels() {
    const floatingContainers = document.querySelectorAll('.form-floating');
    
    floatingContainers.forEach(container => {
        const input = container.querySelector('.form-control, .form-select');
        
        if (input) {
            // Check if input has value or is a date input with value
            const hasValue = input.value && input.value.trim() !== '';
            const isDateWithValue = input.type === 'date' && input.value;
            const isTextareaWithValue = input.tagName.toLowerCase() === 'textarea' && input.value && input.value.trim() !== '';
            
            if (hasValue || isDateWithValue || isTextareaWithValue) {
                container.classList.add('has-value');
            } else {
                container.classList.remove('has-value');
            }
            
            // Special handling for textarea
            if (input.tagName.toLowerCase() === 'textarea') {
                const label = container.querySelector('label');
                if (label) {
                    if (input.value && input.value.trim() !== '') {
                        label.style.setProperty('transform', 'scale(0.85) translateY(-0.75rem) translateX(0.15rem)', 'important');
                        label.style.setProperty('opacity', '0.65', 'important');
                        label.style.setProperty('color', 'var(--primary-color)', 'important');
                        label.style.setProperty('font-weight', '600', 'important');
                        label.style.setProperty('padding', '0.25rem 0.75rem', 'important');
                        label.style.setProperty('height', 'auto', 'important');
                    } else if (!input.matches(':focus')) {
                        label.style.removeProperty('transform');
                        label.style.removeProperty('opacity');
                        label.style.removeProperty('color');
                        label.style.removeProperty('font-weight');
                        label.style.removeProperty('padding');
                        label.style.removeProperty('height');
                    }
                }
            }
        }
    });
}

function initializeFloatingLabels() {
    const floatingContainers = document.querySelectorAll('.form-floating');
    
    floatingContainers.forEach(container => {
        const input = container.querySelector('.form-control, .form-select');
        
        if (input) {
            // Set placeholder to ensure :not(:placeholder-shown) works
            if (!input.getAttribute('placeholder')) {
                input.setAttribute('placeholder', ' ');
            }
            
            // Event listeners for floating label behavior
            input.addEventListener('input', function() {
                updateFloatingLabels();
            });
            
            input.addEventListener('change', function() {
                updateFloatingLabels();
            });
            
            input.addEventListener('focus', function() {
                container.classList.add('has-focus');
                updateFloatingLabels();
            });
            
            input.addEventListener('blur', function() {
                container.classList.remove('has-focus');
                setTimeout(() => updateFloatingLabels(), 100);
            });
            
            // Special handling for date inputs
            if (input.type === 'date') {
                input.addEventListener('blur', function() {
                    setTimeout(updateFloatingLabels, 50);
                });
            }
            
            // Enhanced handling for textarea elements
            if (input.tagName.toLowerCase() === 'textarea') {
                input.addEventListener('input', function() {
                    setTimeout(() => updateFloatingLabels(), 50);
                });
                
                input.addEventListener('keyup', function() {
                    setTimeout(() => updateFloatingLabels(), 50);
                });
                
                input.addEventListener('paste', function() {
                    setTimeout(() => updateFloatingLabels(), 100);
                });
                
                // Force update on focus for textarea
                input.addEventListener('focus', function() {
                    const label = container.querySelector('label');
                    if (label) {
                        label.style.setProperty('transform', 'scale(0.85) translateY(-0.75rem) translateX(0.15rem)', 'important');
                        label.style.setProperty('opacity', '0.65', 'important');
                        label.style.setProperty('color', 'var(--primary-color)', 'important');
                        label.style.setProperty('font-weight', '600', 'important');
                        label.style.setProperty('padding', '0.25rem 0.75rem', 'important');
                        label.style.setProperty('height', 'auto', 'important');
                    }
                });
            }
        }
    });
    
    // Initial update with longer delay for textarea
    setTimeout(updateFloatingLabels, 200);
}

// Enhanced notification system matching dashboard design
function showNotification(message, type = "info") {
    const notification = document.createElement('div');
    const alertClass = type === 'success' ? 'alert-success' : type === 'danger' ? 'alert-danger' : 'alert-info';
    
    notification.className = `alert ${alertClass} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        min-width: 320px;
        max-width: 400px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        animation: slideInRight 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    `;
    
    const iconMap = {
        success: 'fa-check-circle',
        danger: 'fa-exclamation-triangle', 
        info: 'fa-info-circle'
    };
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${iconMap[type]} me-2 flex-shrink-0"></i>
            <span class="flex-grow-1">${message}</span>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-in forwards';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function getSelectedGradient() {
    // First try to get from selected gradient option
    const selected = document.querySelector(".gradient-option.selected");
    if (selected) {
        return parseInt(selected.dataset.gradient);
    }
    
    // Fallback to hidden input
    const selectedGradient = document.getElementById('selectedGradient');
    if (selectedGradient && selectedGradient.value) {
        return parseInt(selectedGradient.value);
    }
    
    // Default fallback
    return 1;
}

function showLoading(show = true) {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        if (show) {
            overlay.style.display = 'flex';
            // Add fade in animation
            overlay.style.animation = 'fadeIn 0.3s ease-out';
        } else {
            overlay.style.animation = 'fadeOut 0.3s ease-in';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }
    }
}

// Function to get Firestore user data
async function getFirestoreUserData() {
    try {
        if (!window.firestore) {
            throw new Error("Firestore not initialized");
        }
        
        const userDoc = await getDoc(doc(window.firestore, "users", uid));
        
        if (userDoc.exists()) {
            const userData = userDoc.data();
            return {
                username: userData.username || userData.displayName || '',
                email: userData.email || ''
            };
        } else {
            console.warn("No Firestore user document found");
            return { username: '', email: '' };
        }
    } catch (error) {
        console.error("Error fetching Firestore data:", error);
        return { username: '', email: '' };
    }
}

async function loadUserData() {
    showLoading(true);
    
    try {
        // First get Firestore data
        const firestoreData = await getFirestoreUserData();
        console.log("Firestore data:", firestoreData);

        // Sync Firestore data to MySQL first if we have it
        if (firestoreData.username || firestoreData.email) {
            await syncFirestoreToMySQL(firestoreData.username, firestoreData.email);
        }

        // Then load complete profile from MySQL (which now includes synced Firestore data)
        const mysqlResponse = await fetch("profile_load.php");
        const profileData = await mysqlResponse.json();
        
        console.log("MySQL profile data:", profileData);

        if (profileData.error) {
            console.warn("Error loading profile:", profileData.error);
            // Use Firestore data as fallback
            document.getElementById("username").value = firestoreData.username || "";
            document.getElementById("email").value = firestoreData.email || "";
        } else {
            // Use MySQL data (which includes synced Firestore data)
            document.getElementById("username").value = profileData.username || firestoreData.username || "";
            document.getElementById("email").value = profileData.email || firestoreData.email || "";
            
            // Fill other profile fields
            document.getElementById("firstName").value = profileData.first_name || "";
            document.getElementById("lastName").value = profileData.last_name || "";
            document.getElementById("dob").value = profileData.dob || "";
            document.getElementById("phone").value = profileData.phone || "";
            document.getElementById("address").value = profileData.address || "";

            // Handle avatar gradient
            const avatarGradient = profileData.avatar_gradient || 1;
            const avatar = document.getElementById("avatarPreview");
            if (avatar) {
                avatar.className = `profile-avatar gradient-group-${avatarGradient}`;
            }

            // Update hidden input
            const selectedGradient = document.getElementById('selectedGradient');
            if (selectedGradient) {
                selectedGradient.value = avatarGradient;
            }

            // Visually select the corresponding gradient
            document.querySelectorAll(".gradient-option").forEach(el => {
                el.classList.remove("selected");
                if (parseInt(el.dataset.gradient) === parseInt(avatarGradient)) {
                    el.classList.add("selected");
                }
            });
        }
        
        // Update avatar text with first letter of username or email
        const avatarText = document.querySelector('.avatar-text');
        if (avatarText) {
            const usernameField = document.getElementById("username").value;
            const emailField = document.getElementById("email").value;
            const firstLetter = (usernameField || emailField || 'U').charAt(0).toUpperCase();
            avatarText.textContent = firstLetter;
            
            // Also update preview avatar text
            const previewAvatarText = document.querySelector('#previewAvatar .avatar-text');
            if (previewAvatarText) {
                previewAvatarText.textContent = firstLetter;
            }
        }
        
        // CRITICAL: Update floating labels after all data is populated
        setTimeout(() => {
            updateFloatingLabels();
            console.log('Floating labels updated after data load');
        }, 150);
        
    } catch (error) {
        console.error("Error loading profile:", error);
        showNotification("Failed to load profile data. Please try refreshing the page.", "danger");
    } finally {
        showLoading(false);
    }
}

// Function to sync Firestore data to MySQL
async function syncFirestoreToMySQL(username, email) {
    try {
        const response = await fetch("sync_firestore_data.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                uid: uid,
                username: username,
                email: email
            })
        });
        
        const result = await response.json();
        if (result.status !== 'success') {
            console.warn("Failed to sync Firestore data to MySQL:", result.message);
        } else {
            console.log("Successfully synced Firestore data to MySQL");
        }
    } catch (error) {
        console.error("Error syncing Firestore data:", error);
    }
}

// Wait for Firestore to be initialized before loading data
function waitForFirestore() {
    if (window.firestore) {
        loadUserData();
    } else {
        setTimeout(waitForFirestore, 100);
    }
}

// Start loading when page is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize floating label event listeners FIRST
    initializeFloatingLabels();
    
    // Add loading animations to form elements
    const formElements = document.querySelectorAll('.form-floating');
    formElements.forEach((element, index) => {
        element.style.animation = `fadeIn 0.6s ease-out ${index * 0.1}s both`;
    });
    
    // Initialize profile data loading
    waitForFirestore();
});

// Enhanced form submission handler
document.getElementById("profileForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const selectedGradient = getSelectedGradient();
    console.log("Selected gradient for saving:", selectedGradient);

    // Form validation
    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const phone = document.getElementById("phone").value.trim();
    
    // Basic validation
    if (phone && !/^[\+]?[0-9\-\s\(\)]+$/.test(phone)) {
        showNotification("Please enter a valid phone number.", "danger");
        return;
    }

    const profileData = {
        uid: uid,
        firstName: firstName,
        lastName: lastName,
        dob: document.getElementById("dob").value,
        phone: phone,
        address: document.getElementById("address").value.trim(),
        avatarGradient: selectedGradient
    };

    console.log("Profile data being sent:", profileData);

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Enhanced loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving Changes...';
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');

    try {
        const response = await fetch("profile_save.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(profileData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log("Save result:", result);

        if (result.status === 'success') {
            showNotification("Profile updated successfully! Your changes have been saved.", "success");
            
            // Add success animation to the form
            const formCard = document.querySelector('.module-card');
            if (formCard) {
                formCard.style.animation = 'pulse 0.6s ease-in-out';
                setTimeout(() => {
                    formCard.style.animation = '';
                }, 600);
            }
        } else {
            showNotification(result.message || "Failed to save profile. Please try again.", "danger");
        }
    } catch (err) {
        console.error("Save error:", err);
        showNotification("Error saving profile. Please check your connection and try again.", "danger");
    } finally {
        // Reset button state
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        }, 500);
    }
});

// Enhanced reset form function
function resetForm() {
    if (confirm("Are you sure you want to reset all changes? This will reload your original profile data.")) {
        const resetBtn = document.querySelector('.reset-btn');
        if (resetBtn) {
            const originalText = resetBtn.innerHTML;
            resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting...';
            resetBtn.disabled = true;
            resetBtn.classList.add('loading');
            
            // Animate form reset
            const formCard = document.querySelector('.module-card');
            if (formCard) {
                formCard.style.opacity = '0.7';
                formCard.style.transform = 'scale(0.98)';
            }
            
            setTimeout(() => {
                loadUserData();
                resetBtn.innerHTML = originalText;
                resetBtn.disabled = false;
                resetBtn.classList.remove('loading');
                
                if (formCard) {
                    formCard.style.opacity = '';
                    formCard.style.transform = '';
                }
                
                showNotification("Profile data has been reset to original values.", "info");
                
                // Update floating labels after reset
                setTimeout(() => {
                    updateFloatingLabels();
                }, 300);
            }, 1000);
        } else {
            loadUserData();
            // Update floating labels after direct reset
            setTimeout(updateFloatingLabels, 200);
        }
    }
}

// Make resetForm available globally
window.resetForm = resetForm;

// Enhanced input handling with floating label support
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.form-control');
    
    inputs.forEach(input => {
        // Add focus animations
        input.addEventListener('focus', function() {
            this.closest('.form-floating').style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.closest('.form-floating').style.transform = '';
            
            // Update floating labels on blur
            setTimeout(updateFloatingLabels, 100);
        });
        
        // Update floating labels on input with debounce
        let inputTimeout;
        input.addEventListener('input', function() {
            clearTimeout(inputTimeout);
            inputTimeout = setTimeout(() => {
                updateFloatingLabels();
            }, 50);
        });
        
        // Real-time validation for phone
        if (input.id === 'phone') {
            input.addEventListener('input', function() {
                const value = this.value.trim();
                const isValid = !value || /^[\+]?[0-9\-\s\(\)]+$/.test(value);
                
                if (value && !isValid) {
                    this.style.borderColor = '#ef4444';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(239, 68, 68, 0.15)';
                } else {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }
            });
        }
    });
    
    // Handle form changes that might affect floating labels
    const form = document.getElementById('profileForm');
    if (form) {
        form.addEventListener('reset', () => {
            setTimeout(updateFloatingLabels, 100);
        });
    }
});

// Observer to handle dynamic content changes
const observer = new MutationObserver((mutations) => {
    let shouldUpdate = false;
    
    mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && 
            (mutation.attributeName === 'value' || mutation.attributeName === 'class')) {
            shouldUpdate = true;
        }
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && node.classList && node.classList.contains('form-floating')) {
                    shouldUpdate = true;
                }
            });
        }
    });
    
    if (shouldUpdate) {
        setTimeout(updateFloatingLabels, 50);
    }
});

// Start observing
observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['value', 'class']
});

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.02);
        }
    }
    
    /* Enhanced floating label debugging */
    .form-floating.debug-labels > label {
        background: rgba(255, 0, 0, 0.1);
        border: 1px solid red;
    }
    
    .form-floating.has-value.debug-labels > label {
        background: rgba(0, 255, 0, 0.1);
        border: 1px solid green;
    }
`;
document.head.appendChild(style);

// Export functions for global use and debugging
window.updateFloatingLabels = updateFloatingLabels;
window.initializeFloatingLabels = initializeFloatingLabels;

// Debug function (remove in production)
window.debugFloatingLabels = () => {
    document.querySelectorAll('.form-floating').forEach(el => {
        el.classList.toggle('debug-labels');
    });
    updateFloatingLabels();
    console.log('Debug mode toggled for floating labels');
};