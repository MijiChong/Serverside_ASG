<?php
// =============================================================================
// MoneyTracker_design.php - Handles HTML structure and display for the dashboard
// This file includes dashboard_logic.php to get necessary data
// =============================================================================
$current_page = 'transactions';
require_once 'MoneyTracker_logic.php'; // Include the PHP logic file
require_once 'setting_loader.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTrackDiary - Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/MoneyTracker_style.css" rel="stylesheet"> <!-- Link to external custom CSS -->

    <!-- Navigation CSS -->
    <link href="navigation/navbar.css" rel="stylesheet">
    <link href="css/global-setting.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            /* Apply user's selected gradient */
            <?php echo getGradientCSS(); ?>
        }
    </style>
</head>
<body <?php echo getBodyClass(); ?>>
    <!-- Navigation Bar -->
    <?php include 'navigation/navbar.php'; ?>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!$current_cashbook_id): ?>
            <div class="alert alert-warning text-center" role="alert">
                You currently have no active cashbook. Please <a href="#" data-bs-toggle="modal" data-bs-target="#addCashbookModal">create a new cashbook</a> to start tracking expenses.
            </div>
        <?php endif; ?>

        <!-- NEW LOCATION FOR CASHBOOK SELECTOR -->
        <div class="row mb-4">
            <div class="col-md-8 offset-md-2">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-center">
                    <label for="cashbook_selector" class="form-label me-md-2 mb-2 mb-md-0 text-nowrap">Current Cashbook:</label>
                    <form method="GET" class="d-flex align-items-center flex-grow-1 w-100 w-md-auto">
                        <select name="cashbook_id" id="cashbook_selector" class="form-select me-2" onchange="this.form.submit()">
                            <?php if (!empty($user_cashbooks)): ?>
                                <?php foreach ($user_cashbooks as $cb): ?>
                                    <option value="<?php echo htmlspecialchars($cb['cashbook_id']); ?>"
                                        <?php echo ($current_cashbook_id == $cb['cashbook_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cb['cashbook_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">No Cashbooks</option>
                            <?php endif; ?>
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addCashbookModal">
                            <i class="fas fa-plus"></i> Add New
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#renameCashbookModal" <?php echo (!$current_cashbook_id || count($user_cashbooks) === 0) ? 'disabled' : ''; ?> data-current-cashbook-name="<?php echo htmlspecialchars($current_cashbook_name); ?>">
                            <i class="fas fa-edit"></i> Rename
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteCashbookModal" <?php echo ($current_cashbook_id && count($user_cashbooks) > 1) ? '' : 'disabled'; ?>>
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card balance-card">
                    <div class="card-body text-center">
                        <h3>Current Balance</h3>
                        <h2>RM <?php echo number_format((float)$balance, 2); ?></h2>
                    </div>
                </div>
            </div>
            
            <?php if (is_array($balance_data)): ?>
            <div class="col-md-4 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h5>Total Income</h5>
                        <h3 class="text-success">RM <?php echo number_format((float)$balance_data['total_income'], 2); ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h5>Total Expenses</h5>
                        <h3 class="text-danger">RM <?php echo number_format((float)$balance_data['total_expense'], 2); ?></h3>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <button type="button" class="btn btn-gradient w-100 py-3" data-bs-toggle="modal" data-bs-target="#addTransactionModal" <?php echo !$current_cashbook_id ? 'disabled' : ''; ?>>
                    <i class="fas fa-plus-circle me-2"></i> Add New Transaction
                </button>
            </div>
            <div class="col-md-6 mb-3">
                <button type="button" class="btn btn-gradient w-100 py-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal" <?php echo !$current_cashbook_id ? 'disabled' : ''; ?>>
                    <i class="fas fa-folder-plus me-2"></i> Add New Category
                </button>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-8">
                <!-- Recent Transactions Section -->
                <div class="card stat-card mb-4">
                    <div class="card-header bg-primary text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h5 class="mb-0">Recent Transactions (<?php echo $current_cashbook_name; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_transactions)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($recent_transactions as $transaction): ?>
                                    <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center transaction-card <?php echo $transaction['transaction_type']; ?>">
                                        <div class="mb-2 mb-md-0">
                                            <strong><?php echo htmlspecialchars($transaction['description'] ?? ''); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($transaction['transaction_date']); ?></small>
                                            <span class="category-badge ms-md-2" style="background-color: <?php echo htmlspecialchars($transaction['color']); ?>;">
                                                <i class="fas fa-<?php echo htmlspecialchars($transaction['icon']); ?>"></i> <?php echo htmlspecialchars($transaction['category_name']); ?>
                                            </span>
                                        </div>
                                        <div class="text-end d-flex align-items-center">
                                            <span class="fs-5 me-2 <?php echo ($transaction['transaction_type'] == 'income' ? 'text-success' : 'text-danger'); ?>">
                                                <?php echo ($transaction['transaction_type'] == 'income' ? '+' : '-') . number_format($transaction['amount'], 2); ?>
                                            </span>
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-info btn-sm me-2" 
                                                data-bs-toggle="modal" data-bs-target="#editTransactionModal"
                                                data-id="<?php echo htmlspecialchars($transaction['transaction_id']); ?>"
                                                data-type="<?php echo htmlspecialchars($transaction['transaction_type']); ?>"
                                                data-category-id="<?php echo htmlspecialchars($transaction['category_id']); ?>"
                                                data-amount="<?php echo htmlspecialchars($transaction['amount']); ?>"
                                                data-description="<?php echo htmlspecialchars($transaction['description'] ?? ''); ?>"
                                                data-date="<?php echo htmlspecialchars($transaction['transaction_date']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="transaction_id" value="<?php echo $transaction['transaction_id']; ?>">
                                                <button type="submit" name="delete_transaction" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this transaction?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-center text-muted py-3">No recent transactions to display for <?php echo $current_cashbook_name; ?>. Add one using the button above!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Monthly Report / Category Stats Section -->
                <div class="card stat-card mb-4">
                    <div class="card-header bg-info text-white" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h5 class="mb-0">Monthly Summary (<?php echo $current_cashbook_name; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <!-- Month and Year Filters -->
                        <form method="GET" class="mb-3" id="monthlyReportFilterForm">
                            <input type="hidden" name="cashbook_id" value="<?php echo htmlspecialchars($current_cashbook_id ?? ''); ?>">
                            <div class="row g-2">
                                <div class="col-6">
                                    <select name="month" id="filter_month" class="form-select form-select-sm">
                                        <?php foreach ($months as $num => $name): ?>
                                            <option value="<?php echo $num; ?>" <?php echo ($filter_month == $num) ? 'selected' : ''; ?>>
                                                <?php echo $name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select name="year" id="filter_year" class="form-select form-select-sm">
                                        <?php foreach ($years as $yr): ?>
                                            <option value="<?php echo $yr; ?>" <?php echo ($filter_year == $yr) ? 'selected' : ''; ?>>
                                                <?php echo $yr; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Apply Filter</button>
                                </div>
                            </div>
                        </form>

                        <?php if (!empty($monthly_report)): ?>
                            <ul class="list-group list-group-flush">
                                <?php
                                $total_month_income = 0;
                                $total_month_expense = 0;
                                foreach ($monthly_report as $report):
                                    $total_month_income += $report['income'];
                                    $total_month_expense += $report['expense'];
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="category-badge" style="background-color: <?php echo htmlspecialchars($report['color']); ?>;">
                                                <?php echo htmlspecialchars($report['category_name']); ?>
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($report['income'] > 0): ?>
                                                <small class="text-success">Inc: RM <?php echo number_format($report['income'], 2); ?></small><br>
                                            <?php endif; ?>
                                            <?php if ($report['expense'] > 0): ?>
                                                <small class="text-danger">Exp: RM <?php echo number_format($report['expense'], 2); ?></small>
                                            <?php endif; ?> 
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total Income:</span> <span class="text-success">RM <?php echo number_format($total_month_income, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total Expense:</span> <span class="text-danger">RM <?php echo number_format($total_month_expense, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold mt-2">
                                <span>Net Balance:</span> <span class="<?php echo ($total_month_income - $total_month_expense >= 0 ? 'text-success' : 'text-danger'); ?>">RM <?php echo number_format($total_month_income - $total_month_expense, 2); ?></span>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted py-3">No monthly data available for <?php echo $months[$filter_month] . ' ' . $filter_year; ?> in <?php echo $current_cashbook_name; ?>.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- All Categories List -->
                <div class="card stat-card">
                    <div class="card-header bg-warning text-dark" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h5 class="mb-0">My Categories (<?php echo $current_cashbook_name; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($all_categories)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($all_categories as $cat): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-start flex-grow-1 me-2">
                                            <span class="category-badge me-2 flex-shrink-0" style="background-color: <?php echo htmlspecialchars($cat['color']); ?>;">
                                                <i class="fas fa-<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                            </span>
                                            <div class="flex-grow-1">
                                                <span class="d-block"><?php echo htmlspecialchars($cat['category_name']); ?></span>
                                                <small class="text-muted d-block">(<?php echo ucfirst($cat['category_type']); ?>)</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center flex-shrink-0">
                                            <!-- Edit Category Button -->
                                            <button type="button" class="btn btn-outline-info btn-sm me-2" 
                                                data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                data-id="<?php echo htmlspecialchars($cat['category_id']); ?>"
                                                data-name="<?php echo htmlspecialchars($cat['category_name']); ?>"
                                                data-type="<?php echo htmlspecialchars($cat['category_type']); ?>"
                                                data-color="<?php echo htmlspecialchars($cat['color']); ?>"
                                                data-icon="<?php echo htmlspecialchars($cat['icon']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                                <button type="submit" name="delete_category" class="btn btn-outline-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to delete this category?');"
                                                    <?php echo (isset($cat['can_delete']) && !$cat['can_delete']) ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-center text-muted py-3">No categories added yet for <?php echo $current_cashbook_name; ?>. Add one using the button above!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Transaction Modal -->
        <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTransactionModalLabel">Add New Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="cashbook_id" value="<?php echo htmlspecialchars($current_cashbook_id ?? ''); ?>">
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Type</label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="" disabled selected>Select a Category</option> <!-- Default placeholder -->
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['category_id']); ?>"
                                            data-type="<?php echo htmlspecialchars($cat['category_type']); ?>"
                                            style="background-color: <?php echo htmlspecialchars($cat['color']); ?>; color: white;">
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="amount" name="amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description">
                            </div>
                            <div class="mb-3">
                                <label for="transaction_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="transaction_date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_transaction" class="btn btn-gradient">Add Transaction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Transaction Modal -->
        <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" id="edit_transaction_id" name="transaction_id">
                            <input type="hidden" name="cashbook_id" value="<?php echo htmlspecialchars($current_cashbook_id ?? ''); ?>">
                            <div class="mb-3">
                                <label for="edit_transaction_type" class="form-label">Type</label>
                                <select class="form-select" id="edit_transaction_type" name="transaction_type" required>
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_category_id" class="form-label">Category</label>
                                <select class="form-select" id="edit_category_id" name="category_id" required>
                                    <option value="" disabled selected>Select a Category</option> <!-- Default placeholder -->
                                    <?php foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['category_id']); ?>"
                                            data-type="<?php echo htmlspecialchars($cat['category_type']); ?>"
                                            style="background-color: <?php echo htmlspecialchars($cat['color']); ?>; color: white;">
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="edit_amount" name="amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="edit_description" name="description">
                            </div>
                            <div class="mb-3">
                                <label for="edit_transaction_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="edit_transaction_date" name="transaction_date" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update_transaction" class="btn btn-gradient">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Category Modal -->
        <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="cashbook_id" value="<?php echo htmlspecialchars($current_cashbook_id ?? ''); ?>">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="category_name" name="category_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="category_type" class="form-label">Category Type</label>
                                <select class="form-select" id="category_type" name="category_type" required>
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="color" class="form-label">Color (Hex Code)</label>
                                <input type="color" class="form-control form-control-color" id="color" name="color" value="#3498db">
                            </div>
                            <div class="mb-3">
                                <label for="icon" class="form-label">Icon (FontAwesome class, e.g., 'utensils', 'plane')</label>
                                <input type="text" class="form-control" id="icon" name="icon" value="default" placeholder="e.g., dollar-sign, shopping-cart">
                                <small class="form-text text-muted">Find icons at <a href="https://fontawesome.com/v5/icons" target="_blank">Font Awesome v5</a></small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_category" class="btn btn-gradient">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" id="edit_category_id" name="category_id">
                            <input type="hidden" name="cashbook_id" value="<?php echo htmlspecialchars($current_cashbook_id ?? ''); ?>">
                            <div class="mb-3">
                                <label for="edit_category_name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_category_type" class="form-label">Category Type</label>
                                <select class="form-select" id="edit_category_type" name="category_type" required>
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                    <option value="both">Both</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_category_color" class="form-label">Color (Hex Code)</label>
                                <input type="color" class="form-control form-control-color" id="edit_category_color" name="color">
                            </div>
                            <div class="mb-3">
                                <label for="edit_category_icon" class="form-label">Icon (FontAwesome class, e.g., 'utensils', 'plane')</label>
                                <input type="text" class="form-control" id="edit_category_icon" name="icon" placeholder="e.g., dollar-sign, shopping-cart">
                                <small class="form-text text-muted">Find icons at <a href="https://fontawesome.com/v5/icons" target="_blank">Font Awesome v5</a></small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="update_category" class="btn btn-gradient">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Rename Cashbook Modal (NEW) -->
        <div class="modal fade" id="renameCashbookModal" tabindex="-1" aria-labelledby="renameCashbookModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="renameCashbookModalLabel">Rename Cashbook</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="cashbook_id" value="<?php echo htmlspecialchars($current_cashbook_id ?? ''); ?>">
                            <div class="mb-3">
                                <label for="new_cashbook_name_rename" class="form-label">New Cashbook Name</label>
                                <input type="text" class="form-control" id="new_cashbook_name_rename" name="new_cashbook_name_rename" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="rename_cashbook" class="btn btn-gradient">Rename Cashbook</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Cashbook Modal (NEW) -->
        <div class="modal fade" id="deleteCashbookModal" tabindex="-1" aria-labelledby="deleteCashbookModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteCashbookModalLabel">Delete Cashbook</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="cashbook_id_to_delete" value="<?php echo htmlspecialchars($current_cashbook_id ?? ''); ?>">
                            <p>Are you sure you want to delete the cashbook "<strong><span id="cashbook_name_to_delete"></span></strong>"?</p>
                            <p class="text-danger">This action cannot be undone and will permanently delete all associated transactions and categories within this cashbook.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="delete_cashbook" class="btn btn-danger">Delete Cashbook</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add New Cashbook Modal -->
        <div class="modal fade" id="addCashbookModal" tabindex="-1" aria-labelledby="addCashbookModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCashbookModalLabel">Create New Cashbook</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="new_cashbook_name" class="form-label">Cashbook Name</label>
                                <input type="text" class="form-control" id="new_cashbook_name" name="new_cashbook_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_cashbook_description" class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="new_cashbook_description" name="new_cashbook_description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_new_cashbook" class="btn btn-gradient">Create Cashbook</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div> <!-- End of container -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/MoneyTracker_script.js"></script> <!-- Link to external custom JavaScript -->
</body>
</html>
