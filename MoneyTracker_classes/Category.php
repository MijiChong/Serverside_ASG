<?php
// =============================================================================
// MoneyTracker_classes/Category.php - Category Management Class
// =============================================================================

class Category {
    private $conn;
    private $table = 'categories';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Fetches all active categories for a specific user and cashbook.
     * @param string $user_id The Firebase UID of the user.
     * @param int $cashbook_id The ID of the current cashbook.
     * @return array An array of category data.
     * @throws Exception If there's a database error.
     */
    public function getCategories($user_id, $cashbook_id) {
        try {
            $query = "SELECT category_id, category_name, category_type, color, icon, is_active 
                      FROM " . $this->table . " 
                      WHERE firebase_uid = :user_id AND cashbook_id = :cashbook_id AND is_active = 1 
                      ORDER BY category_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching categories: " . $e->getMessage());
        }
    }

    /**
     * Fetches active categories for a specific user and cashbook, filtered by type.
     * @param string $user_id The Firebase UID of the user.
     * @param int $cashbook_id The ID of the current cashbook.
     * @param string $type The category type ('income', 'expense').
     * @return array An array of category data.
     * @throws Exception If there's a database error.
     */
    public function getCategoriesByType($user_id, $cashbook_id, $type) {
        try {
            $query = "SELECT category_id, category_name, category_type, color, icon, is_active 
                      FROM " . $this->table . " 
                      WHERE firebase_uid = :user_id AND cashbook_id = :cashbook_id AND (category_type = :type OR category_type = 'both') AND is_active = 1 
                      ORDER BY category_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching categories by type: " . $e->getMessage());
        }
    }

    /**
     * Adds a new category for a specific user and cashbook.
     * If an inactive category with the same name exists, it will be reactivated instead.
     * @param string $user_id The Firebase UID of the user.
     * @param int $cashbook_id The ID of the current cashbook.
     * @param string $category_name The name of the category.
     * @param string $category_type The type of the category ('income', 'expense', 'both').
     * @param string $color Hex color code for UI.
     * @param string $icon FontAwesome icon class.
     * @return int|false The ID of the new/reactivated category on success, false on failure.
     * @throws Exception If there's a database error (e.g., duplicate name for an active category).
     */
    public function addCategory($user_id, $cashbook_id, $category_name, $category_type = 'both', $color = '#3498db', $icon = 'default') {
        try {
            // First, check if an inactive category with the same name exists
            $check_query = "SELECT category_id FROM " . $this->table . " 
                            WHERE firebase_uid = :user_id AND cashbook_id = :cashbook_id 
                            AND category_name = :category_name AND is_active = 0";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $check_stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
            $check_stmt->bindParam(':category_name', $category_name);
            $check_stmt->execute();
            $inactive_category = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($inactive_category) {
                // If an inactive category exists, reactivate it and update its properties
                $update_query = "UPDATE " . $this->table . " 
                                 SET category_type = :category_type, 
                                     color = :color, 
                                     icon = :icon, 
                                     is_active = 1 
                                 WHERE category_id = :category_id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(':category_type', $category_type);
                $update_stmt->bindParam(':color', $color);
                $update_stmt->bindParam(':icon', $icon);
                $update_stmt->bindParam(':category_id', $inactive_category['category_id'], PDO::PARAM_INT);
                
                if ($update_stmt->execute()) {
                    return $inactive_category['category_id'];
                }
                return false;
            } else {
                // No inactive category found, proceed with inserting a new one
                $query = "INSERT INTO " . $this->table . " (firebase_uid, cashbook_id, category_name, category_type, color, icon) 
                          VALUES (:user_id, :cashbook_id, :category_name, :category_type, :color, :icon)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
                $stmt->bindParam(':category_name', $category_name);
                $stmt->bindParam(':category_type', $category_type);
                $stmt->bindParam(':color', $color);
                $stmt->bindParam(':icon', $icon);
                
                if ($stmt->execute()) {
                    return $this->conn->lastInsertId();
                }
                return false;
            }
        } catch (PDOException $e) {
            // Check for duplicate entry error (SQLSTATE 23000) for unique_category_per_user_cashbook
            // This will now only trigger if an *active* category with the same name exists.
            if ($e->getCode() == '23000') {
                throw new Exception("Category name '" . htmlspecialchars($category_name) . "' already exists for this cashbook. Please choose a different name.");
            }
            throw new Exception("Error adding category: " . $e->getMessage());
        }
    }

    /**
     * Updates an existing category for a specific user and cashbook.
     * @param int $category_id The ID of the category to update.
     * @param string $user_id The Firebase UID of the user.
     * @param int $cashbook_id The ID of the current cashbook.
     * @param string $category_name The new name of the category.
     * @param string $category_type The new type of the category.
     * @param string $color The new hex color code.
     * @param string $icon The new FontAwesome icon class.
     * @return bool True on success, false on failure.
     * @throws Exception If there's a database error.
     */
    public function updateCategory($category_id, $user_id, $cashbook_id, $category_name, $category_type, $color, $icon) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET category_name = :category_name, 
                          category_type = :category_type, 
                          color = :color, 
                          icon = :icon 
                      WHERE category_id = :category_id AND firebase_uid = :user_id AND cashbook_id = :cashbook_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
            $stmt->bindParam(':category_name', $category_name);
            $stmt->bindParam(':category_type', $category_type);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':icon', $icon);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Check for duplicate entry error (SQLSTATE 23000) for unique_category_per_user_cashbook
            if ($e->getCode() == '23000') {
                throw new Exception("Category name '" . htmlspecialchars($category_name) . "' already exists for this cashbook. Please choose a different name.");
            }
            throw new Exception("Error updating category: " . $e->getMessage());
        }
    }

    /**
     * Deletes a category. If the category has no associated transactions, it performs a hard delete.
     * Otherwise, it performs a soft delete by setting its is_active flag to 0.
     * @param int $category_id The ID of the category to delete.
     * @param string $user_id The Firebase UID of the user.
     * @param int $cashbook_id The ID of the current cashbook.
     * @return bool True on success, false on failure.
     * @throws Exception If there's a database error.
     */
    public function deleteCategory($category_id, $user_id, $cashbook_id) {
        try {
            if ($this->hasTransactions($category_id, $user_id, $cashbook_id)) {
                // If transactions exist, perform a soft delete
                $query = "UPDATE " . $this->table . " SET is_active = 0 
                          WHERE category_id = :category_id AND firebase_uid = :user_id AND cashbook_id = :cashbook_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
                return $stmt->execute();
            } else {
                // No transactions exist, perform a hard delete
                $query = "DELETE FROM " . $this->table . " 
                          WHERE category_id = :category_id AND firebase_uid = :user_id AND cashbook_id = :cashbook_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
                return $stmt->execute();
            }
        } catch (Exception $e) {
            throw new Exception("Error deleting category: " . $e->getMessage());
        }
    }

    /**
     * Gets statistics for categories for a specific user and cashbook.
     * @param string $user_id The Firebase UID of the user.
     * @param int $cashbook_id The ID of the current cashbook.
     * @return array An array of category statistics.
     * @throws Exception If there's a database error.
     */
    public function getCategoryStats($user_id, $cashbook_id) {
        try {
            $query = "SELECT 
                                c.category_id,
                                c.category_name,
                                c.color,
                                c.icon,
                                COUNT(t.transaction_id) as transaction_count,
                                SUM(CASE WHEN t.transaction_type = 'income' THEN t.amount ELSE 0 END) as total_income,
                                SUM(CASE WHEN t.transaction_type = 'expense' THEN t.amount ELSE 0 END) as total_expense
                            FROM " . $this->table . " c
                            LEFT JOIN transactions t ON c.category_id = t.category_id AND t.cashbook_id = c.cashbook_id
                            WHERE c.firebase_uid = :user_id AND c.cashbook_id = :cashbook_id AND c.is_active = 1
                            GROUP BY c.category_id, c.category_name, c.color, c.icon
                            ORDER BY c.category_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching category stats: " . $e->getMessage());
        }
    }

    /**
     * Adds a predefined set of default categories for a specific user and cashbook.
     * This is typically called when a new cashbook is created.
     * @param string $user_id The Firebase UID of the user.
     * @param int $cashbook_id The ID of the cashbook to add categories to.
     * @return bool True if all default categories were added successfully, false otherwise.
     */
    public function addDefaultCategories($user_id, $cashbook_id) {
        $default_categories = [
            ['name' => 'Food', 'type' => 'expense', 'color' => '#FF5733', 'icon' => 'utensils'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => '#337AFF', 'icon' => 'bus'],
            ['name' => 'Rent', 'type' => 'expense', 'color' => '#8A2BE2', 'icon' => 'home'],
            ['name' => 'Utilities', 'type' => 'expense', 'color' => '#00CED1', 'icon' => 'lightbulb'],
            ['name' => 'Salary', 'type' => 'income', 'color' => '#28A745', 'icon' => 'dollar-sign'],
            ['name' => 'Savings', 'type' => 'both', 'color' => '#FFC300', 'icon' => 'piggy-bank'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#FF33A8', 'icon' => 'film'],
            ['name' => 'Education', 'type' => 'expense', 'color' => '#4CAF50', 'icon' => 'graduation-cap'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#9C27B0', 'icon' => 'shopping-bag'],
            ['name' => 'Health', 'type' => 'expense', 'color' => '#DC3545', 'icon' => 'heartbeat'],
        ];

        $all_added = true;
        foreach ($default_categories as $cat_data) {
            try {
                // Call the existing addCategory method
                $result = $this->addCategory(
                    $user_id,
                    $cashbook_id,
                    $cat_data['name'],
                    $cat_data['type'],
                    $cat_data['color'],
                    $cat_data['icon']
                );
                if (!$result) {
                    $all_added = false;
                    // Log or handle specific failure for a category if needed
                }
            } catch (Exception $e) {
                // Catch exceptions (e.g., duplicate name if logic changes)
                error_log("Failed to add default category '{$cat_data['name']}' for user $user_id, cashbook $cashbook_id: " . $e->getMessage());
                $all_added = false;
            }
        }
        return $all_added;
    }

    /**
     * Checks if a category has any associated transactions.
     * @param int $category_id The ID of the category to check.
     * @param string $user_id The Firebase UID of the user owning the category.
     * @param int $cashbook_id The ID of the cashbook the category belongs to.
     * @return bool True if transactions exist, false otherwise.
     */
    public function hasTransactions($category_id, $user_id, $cashbook_id) {
        $query = "SELECT COUNT(*) FROM transactions WHERE category_id = :category_id AND firebase_uid = :user_id AND cashbook_id = :cashbook_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':cashbook_id', $cashbook_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
