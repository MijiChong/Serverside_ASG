// =============================================================================
// js/MoneyTracker_script.js - Custom JavaScript for the Cashbook App dashboard
// =============================================================================

// Function to manage category dropdowns (Add and Edit modals)
function manageCategoryDropdown(selectElementId, transactionTypeSelectId) {
    const categorySelect = document.getElementById(selectElementId);
    const transactionTypeSelect = document.getElementById(transactionTypeSelectId);
    // Capture initial options from the PHP-rendered HTML, excluding the placeholder
    let initialOptions = Array.from(categorySelect.querySelectorAll('option:not([value=""])')); 

    // Create a "No matching categories" option element
    const noCategoriesOption = document.createElement('option');
    noCategoriesOption.value = "";
    noCategoriesOption.disabled = true;
    noCategoriesOption.textContent = "No matching categories available.";
    noCategoriesOption.classList.add('no-categories-option'); // Add a class for easy targeting

    function filterCategories() {
        const selectedTransactionType = transactionTypeSelect.value;
        let hasVisibleOptions = false;
        
        // Clear existing options and re-add the default "Select a Category" placeholder
        categorySelect.innerHTML = '<option value="" disabled selected>Select a Category</option>';

        initialOptions.forEach(option => {
            const categoryType = option.dataset.type;
            if (categoryType === 'both' || categoryType === selectedTransactionType) {
                categorySelect.appendChild(option.cloneNode(true)); // Append a clone
                hasVisibleOptions = true;
            }
        });

        // If no categories are visible after filtering, add the "No matching categories" option
        if (!hasVisibleOptions) {
            categorySelect.appendChild(noCategoriesOption);
            // Ensure the "No matching categories" option is selected if no others are present
            noCategoriesOption.selected = true;
        } else {
            // If there are visible options, ensure the "Select a Category" placeholder remains selected
            // unless a specific category was already selected (e.g., for edit modal)
            if (categorySelect.value === "") { // Only re-select placeholder if nothing else is selected
                categorySelect.querySelector('option[value=""][disabled]').selected = true;
            }
        }
    }

    // Initial filter on page load
    filterCategories();

    // Add event listener for transaction type change
    transactionTypeSelect.addEventListener('change', filterCategories);
}


document.addEventListener('DOMContentLoaded', function() {
    // Initialize category filtering for Add Transaction Modal
    manageCategoryDropdown('category_id', 'transaction_type');

    // JavaScript to restrict amount input to numbers and a single decimal point for ADD modal
    const addAmountInput = document.getElementById('amount');
    addAmountInput.addEventListener('keypress', function(e) {
        const charCode = (e.which) ? e.which : e.keyCode;

        // Allow digits (0-9)
        if (charCode >= 48 && charCode <= 57) {
            return true;
        }

        // Allow only one decimal point
        if (charCode === 46) { // ASCII for '.'
            if (this.value.indexOf('.') === -1) {
                return true;
            }
        }

        // Prevent all other characters
        e.preventDefault();
        return false;
    });

    // Additionally, handle paste events to clean input for ADD modal
    addAmountInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasteData = e.clipboardData.getData('text');
        let cleanedValue = '';
        let hasDecimal = this.value.indexOf('.') !== -1; // Check if current value already has a decimal

        for (let i = 0; i < pasteData.length; i++) {
            const char = pasteData[i];
            if (char >= '0' && char <= '9') {
                cleanedValue += char;
            } else if (char === '.' && !hasDecimal) {
                cleanedValue += char;
                hasDecimal = true;
            }
        }

        // Insert cleaned value at cursor position
        const start = this.selectionStart;
        const end = this.selectionEnd;
        const currentValue = this.value;
        this.value = currentValue.substring(0, start) + cleanedValue + currentValue.substring(end);

        // Manually set cursor position after paste
        this.selectionStart = this.selectionEnd = start + cleanedValue.length;
    });


    // --- Edit Transaction Modal Logic ---
    const editTransactionModal = document.getElementById('editTransactionModal');
    editTransactionModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const transactionId = button.getAttribute('data-id');
        const transactionType = button.getAttribute('data-type');
        const categoryId = button.getAttribute('data-category-id');
        const amount = button.getAttribute('data-amount');
        const description = button.getAttribute('data-description');
        const date = button.getAttribute('data-date');

        // Populate the modal fields
        const modal = this;
        modal.querySelector('#edit_transaction_id').value = transactionId;
        modal.querySelector('#edit_transaction_type').value = transactionType;
        modal.querySelector('#edit_amount').value = amount;
        modal.querySelector('#edit_description').value = description;
        modal.querySelector('#edit_transaction_date').value = date;

        // Initialize category filtering for Edit Transaction Modal
        // This needs to be done *after* the modal content is populated,
        // and the initialOptions should be captured from the currently
        // rendered options in the edit modal's select element.
        const editCategorySelect = modal.querySelector('#edit_category_id');
        const editTransactionTypeSelect = modal.querySelector('#edit_transaction_type');
        let editInitialOptions = Array.from(editCategorySelect.querySelectorAll('option:not([value=""])'));

        // Create a "No matching categories" option element for edit modal
        const editNoCategoriesOption = document.createElement('option');
        editNoCategoriesOption.value = "";
        editNoCategoriesOption.disabled = true;
        editNoCategoriesOption.textContent = "No matching categories available.";
        editNoCategoriesOption.classList.add('no-categories-option');

        function filterEditCategories() {
            const selectedEditTransactionType = editTransactionTypeSelect.value;
            let hasVisibleEditOptions = false;

            // Clear existing options except the default "Select a Category"
            editCategorySelect.innerHTML = '<option value="" disabled>Select a Category</option>'; // Don't pre-select here

            editInitialOptions.forEach(option => {
                const categoryType = option.dataset.type;
                if (categoryType === 'both' || categoryType === selectedEditTransactionType) {
                    editCategorySelect.appendChild(option.cloneNode(true)); // Append a clone
                    hasVisibleEditOptions = true;
                }
            });

            // If no categories are visible after filtering, add the "No matching categories" option
            if (!hasVisibleEditOptions) {
                editCategorySelect.appendChild(editNoCategoriesOption);
                editNoCategoriesOption.selected = true; // Select it if no others
            } else {
                // Re-select the original category if it's still visible
                let foundOriginalCategory = false;
                Array.from(editCategorySelect.options).forEach(option => {
                    if (option.value === categoryId && option.style.display !== 'none') {
                        option.selected = true;
                        foundOriginalCategory = true;
                    }
                });

                // If original category is not found or hidden, and there are other options,
                // ensure "Select a Category" is selected, or the first valid if needed.
                if (!foundOriginalCategory) {
                     if (editCategorySelect.value === "") { // Only re-select placeholder if nothing else is selected
                        editCategorySelect.querySelector('option[value=""][disabled]').selected = true;
                    }
                }
            }
        }

        // Initial filter and selection when modal is shown
        filterEditCategories();

        // Add change listener to edit_transaction_type to re-filter categories
        editTransactionTypeSelect.addEventListener('change', filterEditCategories);

        // JavaScript to restrict amount input to numbers and a single decimal point for EDIT modal
        const editAmountInput = modal.querySelector('#edit_amount');
        editAmountInput.addEventListener('keypress', function(e) {
            const charCode = (e.which) ? e.which : e.keyCode;

            // Allow digits (0-9)
            if (charCode >= 48 && charCode <= 57) {
                return true;
            }

            // Allow only one decimal point
            if (charCode === 46) { // ASCII for '.'
                if (this.value.indexOf('.') === -1) {
                    return true;
                }
            }

            // Prevent all other characters
            e.preventDefault();
            return false;
        });

        // Additionally, handle paste events to clean input for EDIT modal
        editAmountInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text');
            let cleanedValue = '';
            let hasDecimal = this.value.indexOf('.') !== -1; // Check if current value already has a decimal

            for (let i = 0; i < pasteData.length; i++) {
                const char = pasteData[i];
                if (char >= '0' && char <= '9') {
                    cleanedValue += char;
                } else if (char === '.' && !hasDecimal) {
                    cleanedValue += char;
                    hasDecimal = true;
                }
            }

            // Insert cleaned value at cursor position
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const currentValue = this.value;
            this.value = currentValue.substring(0, start) + cleanedValue + currentValue.substring(end);

            // Manually set cursor position after paste
            this.selectionStart = this.selectionEnd = start + cleanedValue.length;
        });
    });

    // --- Edit Category Modal Logic ---
    const editCategoryModal = document.getElementById('editCategoryModal');
    editCategoryModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const categoryId = button.getAttribute('data-id');
        const categoryName = button.getAttribute('data-name');
        const categoryType = button.getAttribute('data-type');
        const color = button.getAttribute('data-color');
        const icon = button.getAttribute('data-icon');

        // Populate the modal fields
        const modal = this;
        modal.querySelector('#edit_category_id').value = categoryId;
        modal.querySelector('#edit_category_name').value = categoryName;
        
        // For select elements, iterate options and set 'selected'
        const editCategoryTypeSelect = modal.querySelector('#edit_category_type');
        Array.from(editCategoryTypeSelect.options).forEach(option => {
            if (option.value === categoryType) {
                option.selected = true;
            } else {
                option.selected = false; // Ensure others are not selected
            }
        });

        modal.querySelector('#edit_category_color').value = color;
        modal.querySelector('#edit_category_icon').value = icon;
    });

    // --- Rename Cashbook Modal Logic (NEW) ---
    const renameCashbookModal = document.getElementById('renameCashbookModal');
    renameCashbookModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        // Get the current cashbook name from the data attribute on the button
        const currentCashbookName = button.getAttribute('data-current-cashbook-name');
        
        const modal = this;
        // Populate the input field with the current cashbook name
        modal.querySelector('#new_cashbook_name_rename').value = currentCashbookName;
    });

    // --- Delete Cashbook Modal Logic (NEW) ---
    const deleteCashbookModal = document.getElementById('deleteCashbookModal');
    deleteCashbookModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        // Get the current cashbook name from the selected option in the cashbook selector
        const cashbookSelector = document.getElementById('cashbook_selector');
        const currentCashbookName = cashbookSelector.options[cashbookSelector.selectedIndex].textContent;
        
        const modal = this;
        // Populate the span with the cashbook name for confirmation
        modal.querySelector('#cashbook_name_to_delete').textContent = currentCashbookName;
    });
});
