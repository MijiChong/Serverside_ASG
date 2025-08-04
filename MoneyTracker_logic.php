<?php
// =============================================================================
// MoneyTracker_logic.php
// =============================================================================
session_start();

require 'mysql.php';

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    //echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$uid = $_SESSION['uid'];

require_once 'MoneyTracker_classes/User.php';
require_once 'MoneyTracker_classes/Category.php';
require_once 'MoneyTracker_classes/Transaction.php';
require_once 'MoneyTracker_classes/Cashbook.php';

$db = $pdo;
$category = new Category($db);
$transaction = new Transaction($db);
$cashbook = new Cashbook($db);

$user_id = $uid;

$error = '';
$success = '';

// --- Cashbook Management Logic ---
$user_cashbooks = $cashbook->getCashbooksByUserId($user_id);
$current_cashbook_id = null;

// If no cashbooks exist for the user, create a default one
if (empty($user_cashbooks)) {
    try {
        $default_cashbook_id = $cashbook->addCashbook($user_id, 'Default Cashbook', 'Your primary cashbook.');
        $_SESSION['current_cashbook_id'] = $default_cashbook_id;
        // Refresh cashbooks list
        $user_cashbooks = $cashbook->getCashbooksByUserId($user_id);
        
        // Add default categories to the newly created default cashbook
        $category->addDefaultCategories($user_id, $default_cashbook_id);

        $success = 'Default cashbook created for you with essential categories!';
    } catch (Exception $e) {
        $error = 'Failed to create default cashbook: ' . $e->getMessage();
    }
}

// Determine current cashbook from GET, Session, or first available
if (isset($_GET['cashbook_id']) && in_array($_GET['cashbook_id'], array_column($user_cashbooks, 'cashbook_id'))) {
    $current_cashbook_id = (int)$_GET['cashbook_id'];
    $_SESSION['current_cashbook_id'] = $current_cashbook_id;
} elseif (isset($_SESSION['current_cashbook_id']) && in_array($_SESSION['current_cashbook_id'], array_column($user_cashbooks, 'cashbook_id'))) {
    $current_cashbook_id = (int)$_SESSION['current_cashbook_id'];
} else {
    // Fallback to the first cashbook if session is not set or invalid
    if (!empty($user_cashbooks)) {
        $current_cashbook_id = $user_cashbooks[0]['cashbook_id'];
        $_SESSION['current_cashbook_id'] = $current_cashbook_id;
    }
}

// Get the name of the current cashbook for display
$current_cashbook_name = 'No Cashbook Selected';
if ($current_cashbook_id) {
    foreach ($user_cashbooks as $cb) {
        if ($cb['cashbook_id'] == $current_cashbook_id) {
            $current_cashbook_name = htmlspecialchars($cb['cashbook_name']);
            break;
        }
    }
}


// Default filter values for monthly report (now per cashbook)
$filter_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$filter_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure a cashbook is selected before processing transactions/categories
    // Allow adding new cashbook, renaming, and deleting without a current cashbook selected
    // as these operations might affect the current cashbook state or are independent.
    // The check for $current_cashbook_id is now more specific to transaction/category operations.

    if (isset($_POST['add_transaction'])) {
        if (!$current_cashbook_id) {
            $error = 'Please select a cashbook to add a transaction.';
        } else {
            try {
                $transaction->addTransaction(
                    $user_id,
                    $current_cashbook_id, // Pass cashbook_id
                    $_POST['category_id'],
                    $_POST['transaction_type'],
                    $_POST['amount'],
                    $_POST['description'],
                    $_POST['transaction_date']
                );
                $success = 'Transaction added successfully!';
            } catch (Exception $e) {
                $error = 'Failed to add transaction: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['add_category'])) {
        if (!$current_cashbook_id) {
            $error = 'Please select a cashbook to add a category.';
        } else {
            try {
                $category->addCategory(
                    $user_id,
                    $current_cashbook_id, // Pass cashbook_id
                    $_POST['category_name'],
                    $_POST['category_type'],
                    $_POST['color'] ?? '#3498db',
                    $_POST['icon'] ?? 'default'
                );
                $success = 'Category added successfully!';
            } catch (Exception $e) {
                $error = 'Failed to add category: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['update_category'])) {
        if (!$current_cashbook_id) {
            $error = 'Please select a cashbook to update a category.';
        } else {
            try {
                $category->updateCategory(
                    $_POST['category_id'],
                    $user_id,
                    $current_cashbook_id,
                    $_POST['category_name'],
                    $_POST['category_type'],
                    $_POST['color'] ?? '#3498db',
                    $_POST['icon'] ?? 'default'
                );
                $success = 'Category updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update category: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['add_new_cashbook'])) {
        try {
            $new_cashbook_id = $cashbook->addCashbook(
                $user_id,
                $_POST['new_cashbook_name'],
                $_POST['new_cashbook_description'] ?? null
            );
            $_SESSION['current_cashbook_id'] = $new_cashbook_id; // Set new cashbook as active
            
            // Add default categories to the newly created cashbook
            $category->addDefaultCategories($user_id, $new_cashbook_id);

            $success = 'Cashbook "' . htmlspecialchars($_POST['new_cashbook_name']) . '" created successfully with essential categories!';
        } catch (Exception $e) {
            $error = 'Failed to add cashbook: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['rename_cashbook'])) { // NEW: Handle cashbook renaming
        if (!$current_cashbook_id) {
            $error = 'Please select a cashbook to rename.';
        } else {
            try {
                $cashbook->renameCashbook(
                    $current_cashbook_id,
                    $user_id,
                    $_POST['new_cashbook_name_rename']
                );
                $success = 'Cashbook renamed successfully!';
                // Update session variable to reflect new name if needed, or just refresh
                // For simplicity, we'll just redirect and let the page reload the name.
            } catch (Exception $e) {
                $error = 'Failed to rename cashbook: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['delete_cashbook'])) { // NEW: Handle cashbook deletion
        $cashbook_id_to_delete = $_POST['cashbook_id_to_delete'];
        if (!$cashbook_id_to_delete) {
            $error = 'No cashbook selected for deletion.';
        } else {
            try {
                $cashbook->deleteCashbook($cashbook_id_to_delete, $user_id);
                $success = 'Cashbook deleted successfully!';
                // If the deleted cashbook was the currently selected one, clear the session
                if ($current_cashbook_id == $cashbook_id_to_delete) {
                    unset($_SESSION['current_cashbook_id']);
                }
            } catch (Exception $e) {
                $error = 'Failed to delete cashbook: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['delete_transaction'])) {
        if (!$current_cashbook_id) {
            $error = 'Please select a cashbook to delete a transaction.';
        } else {
            try {
                $transaction->deleteTransaction($_POST['transaction_id'], $user_id, $current_cashbook_id);
                $success = 'Transaction deleted successfully!';
            } catch (Exception $e) {
                $error = 'Failed to delete transaction: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['update_transaction'])) {
        if (!$current_cashbook_id) {
            $error = 'Please select a cashbook to update a transaction.';
        } else {
            try {
                $transaction->updateTransaction(
                    $_POST['transaction_id'],
                    $user_id,
                    $current_cashbook_id,
                    $_POST['category_id'],
                    $_POST['transaction_type'],
                    $_POST['amount'],
                    $_POST['description'],
                    $_POST['transaction_date']
                );
                $success = 'Transaction updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update transaction: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['delete_category'])) {
        if (!$current_cashbook_id) {
            $error = 'Please select a cashbook to delete a category.';
        } else {
            try {
                $category->deleteCategory($_POST['category_id'], $user_id, $current_cashbook_id);
                $success = 'Category deleted successfully!';
            } catch (Exception $e) {
                // The database trigger will provide a specific message if transactions exist
                $error = 'Failed to delete category: ' . $e->getMessage();
            }
        }
    }

    // Redirect to prevent form resubmission on refresh, preserving filter parameters and current cashbook
    $redirect_cashbook_id = $current_cashbook_id ?? '';
    // If cashbook was deleted, we might not have a current_cashbook_id, so redirect without it.
    if (isset($_POST['delete_cashbook']) && ($current_cashbook_id == $_POST['cashbook_id_to_delete'])) {
        $redirect_cashbook_id = ''; // Clear cashbook_id from URL if the current one was deleted
    }
    header("Location: MoneyTracker_design.php?cashbook_id=$redirect_cashbook_id&month=$filter_month&year=$filter_year&success=" . urlencode($success) . "&error=" . urlencode($error));
    exit();
}

// Check for success/error messages from GET parameters after redirect
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}


// Get data for display (ALL NOW FILTERED BY CURRENT CASHBOOK)
// Only fetch data if a cashbook is selected
if ($current_cashbook_id) {
    $balance_data = $transaction->getUserBalance($user_id, $current_cashbook_id);
    $balance = is_array($balance_data) ? $balance_data['current_balance'] : 0;
    $recent_transactions = $transaction->getTransactions($user_id, $current_cashbook_id, 10);
    // Fetch all categories for the current cashbook, including inactive ones if needed for re-activation logic
    // However, for display, we still only want active ones.
    // The `getCategories` method already filters by `is_active = 1`.
    $all_categories = $category->getCategories($user_id, $current_cashbook_id);
    
    // Add can_delete flag to each category based on whether it has transactions
    // This is now based on the `hasTransactions` method in the Category class
    foreach ($all_categories as &$cat) {
        $cat['can_delete'] = !$category->hasTransactions($cat['category_id'], $user_id, $current_cashbook_id);
        // Add transaction_count to the category array for UI disabling logic
        // We need to fetch this explicitly if getCategories doesn't include it.
        // For now, relying on hasTransactions. If you need the count, getCategoryStats is better.
        // For the purpose of disabling the delete button, hasTransactions is sufficient.
    }
    unset($cat); // Break the reference to the last element
    
    $monthly_report = $transaction->getMonthlyReport($user_id, $current_cashbook_id, $filter_year, $filter_month);
} else {
    // Initialize empty data if no cashbook is selected
    $balance_data = ['total_income' => 0, 'total_expense' => 0, 'current_balance' => 0];
    $balance = 0;
    $recent_transactions = [];
    $all_categories = [];
    $monthly_report = [];
}


// Prepare month and year options for dropdowns
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
$current_year_for_dropdown = date('Y');
$years = range($current_year_for_dropdown, $current_year_for_dropdown - 5);
?>
