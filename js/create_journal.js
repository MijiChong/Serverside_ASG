//create journal

const textarea = document.getElementById('content');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

// Character counter (optional enhancement)
textarea.addEventListener('input', function() {
    const charCount = this.value.length;
    const maxChars = 5000;
            
    if (charCount > maxChars * 0.9) {
         this.classList.add('near-limit');
    } else {
            this.classList.remove('near-limit');
         }
});