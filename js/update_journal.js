// Auto-resize textarea
const textarea = document.getElementById('content');
if (textarea) {
     textarea.addEventListener('input', function() {
           this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
     });

    // Initial resize
     textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
}