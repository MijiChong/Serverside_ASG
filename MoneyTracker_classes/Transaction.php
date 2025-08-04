<?php
// =============================================================================
// MoneyTracker_classes/Transaction.php - Transaction Management Class
// =============================================================================

class Transaction {
    private $conn;
    private $table = 'transactions';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Modified: Added $cashbook_id parameter
    public function addTransaction($user_id, $cashbook_id, $category_id, $transaction_type, $amount, $description, $transaction_date) {
        try {
            $query = "INSERT INTO " . $this->table . " (firebase_uid, cashbook_id, category_id, transaction_type, amount, description, transaction_date) VALUES (:user_id, :cashbook_id, :category_id, :transaction_type, :amount, :description, :transaction_date)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':cashbook_id', $cashbook_id); // NEW
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':transaction_type', $transaction_type);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':transaction_date', $transaction_date);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            throw new Exception("Error adding transaction: " . $e->getMessage());
        }
    }

    // Modified: Added $cashbook_id parameter
    public function getTransactions($user_id, $cashbook_id, $limit = 50, $offset = 0) {
        try {
            $query = "SELECT 
                                t.transaction_id,
                                t.firebase_uid,
                                t.category_id,
                                c.category_name,
                                c.color,
                                c.icon,
                                t.transaction_type,
                                t.amount,
                                t.description,
                                t.transaction_date,
                                t.created_at
                             FROM " . $this->table . " t
                             JOIN categories c ON t.category_id = c.category_id
                             WHERE t.firebase_uid = :user_id AND t.cashbook_id = :cashbook_id
                             ORDER BY t.transaction_date DESC, t.created_at DESC
                             LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':cashbook_id', $cashbook_id); // NEW
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching transactions: " . $e->getMessage());
        }
    }

    // Modified: Added $cashbook_id parameter
    public function getUserBalance($user_id, $cashbook_id) {
        try {
            $query = "SELECT 
                                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as total_income,
                                SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expense,
                                SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE -amount END) as current_balance
                             FROM " . $this->table . " 
                             WHERE firebase_uid = :user_id AND cashbook_id = :cashbook_id"; // NEW: Filter by cashbook_id
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':cashbook_id', $cashbook_id); // NEW
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total_income' => $result['total_income'] ?? 0,
                'total_expense' => $result['total_expense'] ?? 0,
                'current_balance' => $result['current_balance'] ?? 0
            ];
        } catch (Exception $e) {
            throw new Exception("Error fetching user balance: " . $e->getMessage());
        }
    }

    // Modified: Added $cashbook_id parameter
    public function deleteTransaction($transaction_id, $user_id, $cashbook_id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE transaction_id = :transaction_id AND firebase_uid = :user_id AND cashbook_id = :cashbook_id"; // NEW: Filter by cashbook_id
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':transaction_id', $transaction_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':cashbook_id', $cashbook_id); // NEW
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error deleting transaction: " . $e->getMessage());
        }
    }

    // Modified: Added $cashbook_id parameter
    public function updateTransaction($transaction_id, $user_id, $cashbook_id, $category_id, $transaction_type, $amount, $description, $transaction_date) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET category_id = :category_id, 
                          transaction_type = :transaction_type, 
                          amount = :amount, 
                          description = :description, 
                          transaction_date = :transaction_date
                      WHERE transaction_id = :transaction_id AND firebase_uid = :user_id AND cashbook_id = :cashbook_id"; // NEW: Filter by cashbook_id

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':transaction_type', $transaction_type);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':transaction_date', $transaction_date);
            $stmt->bindParam(':transaction_id', $transaction_id);
            $stmt->bindParam(':user_id', $user_id); 
            $stmt->bindParam(':cashbook_id', $cashbook_id); // NEW
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error updating transaction: " . $e->getMessage());
        }
    }

    // Modified: Added $cashbook_id parameter
    public function getMonthlyReport($user_id, $cashbook_id, $year, $month) {
        try {
            $query = "SELECT 
                                c.category_name,
                                c.color,
                                SUM(CASE WHEN t.transaction_type = 'income' THEN t.amount ELSE 0 END) as income,
                                SUM(CASE WHEN t.transaction_type = 'expense' THEN t.amount ELSE 0 END) as expense,
                                COUNT(t.transaction_id) as transaction_count
                             FROM transactions t
                             JOIN categories c ON t.category_id = c.category_id
                             WHERE t.firebase_uid = :user_id 
                             AND t.cashbook_id = :cashbook_id -- NEW: Filter by cashbook_id
                             AND YEAR(t.transaction_date) = :year 
                             AND MONTH(t.transaction_date) = :month
                             GROUP BY c.category_id, c.category_name, c.color
                             ORDER BY (
                                SUM(CASE WHEN t.transaction_type = 'income' THEN t.amount ELSE 0 END) +
                                SUM(CASE WHEN t.transaction_type = 'expense' THEN t.amount ELSE 0 END)
                             ) DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':cashbook_id', $cashbook_id); // NEW
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':month', $month);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching monthly report: " . $e->getMessage());
        }
    }
}
?>