<?php
// =============================================================================
// MoneyTracker_classes/Cashbook.php - Cashbook Management Class
// =============================================================================

class Cashbook {
    private $conn;
    private $table = 'cashbooks';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Adds a new cashbook for a specific user.
     * @param string $user_id The Firebase UID of the user.
     * @param string $cashbook_name The name of the cashbook.
     * @param string|null $description An optional description for the cashbook.
     * @return int|false The ID of the new cashbook on success, false on failure.
     * @throws Exception If there's a database error (e.g., duplicate name).
     */
    public function addCashbook($user_id, $cashbook_name, $description = null) {
        try {
            $query = "INSERT INTO " . $this->table . " (firebase_uid, cashbook_name, description) VALUES (:firebase_uid, :cashbook_name, :description)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':firebase_uid', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':cashbook_name', $cashbook_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            // Check for duplicate entry error (SQLSTATE 23000) for unique_cashbook_name_per_user
            if ($e->getCode() == '23000') {
                throw new Exception("Cashbook name '" . htmlspecialchars($cashbook_name) . "' already exists. Please choose a different name.");
            }
            throw new Exception("Error adding cashbook: " . $e->getMessage());
        }
    }

    /**
     * Fetches all cashbooks for a specific user.
     * @param string $user_id The Firebase UID of the user.
     * @return array An array of cashbook data.
     * @throws Exception If there's a database error.
     */
    public function getCashbooksByUserId($user_id) {
        try {
            $query = "SELECT cashbook_id, cashbook_name, description FROM " . $this->table . " WHERE firebase_uid = :user_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching cashbooks: " . $e->getMessage());
        }
    }

    /**
     * Renames an existing cashbook for a specific user.
     * @param int $cashbook_id The ID of the cashbook to rename.
     * @param string $user_id The Firebase UID of the user.
     * @param string $new_cashbook_name The new name for the cashbook.
     * @return bool True on success, false on failure.
     * @throws Exception If there's a database error (e.g., duplicate name).
     */
    public function renameCashbook($cashbook_id, $user_id, $new_cashbook_name) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET cashbook_name = :new_cashbook_name 
                      WHERE cashbook_id = :cashbook_id AND firebase_uid = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':new_cashbook_name', $new_cashbook_name, PDO::PARAM_STR);
            $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Check for duplicate entry error (SQLSTATE 23000) for unique_cashbook_name_per_user
            if ($e->getCode() == '23000') {
                throw new Exception("Cashbook name '" . htmlspecialchars($new_cashbook_name) . "' already exists for your account. Please choose a different name.");
            }
            throw new Exception("Error renaming cashbook: " . $e->getMessage());
        }
    }

    /**
     * Deletes a cashbook and all its associated transactions and categories.
     * Due to CASCADE DELETE constraints in the database, related records in
     * 'transactions' and 'categories' tables will be automatically deleted.
     * @param int $cashbook_id The ID of the cashbook to delete.
     * @param string $user_id The Firebase UID of the user.
     * @return bool True on success, false on failure.
     * @throws Exception If there's a database error.
     */
    public function deleteCashbook($cashbook_id, $user_id) {
        try {
            $query = "DELETE FROM " . $this->table . " 
                      WHERE cashbook_id = :cashbook_id AND firebase_uid = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cashbook_id', $cashbook_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error deleting cashbook: " . $e->getMessage());
        }
    }
}
?>
