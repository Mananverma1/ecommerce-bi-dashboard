<?php
// src/auth-handler.php

// Ensure an isolated tracking session exists securely before processing states
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = DatabaseConnection::getMySQL();

    // ── REGISTER WORKFLOW ──────────────────────────────────────────────────
    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        // Strict server-side input parameter check
        if (empty($name) || !$email || strlen($password) < 8) {
            header("Location: ../public/register.php?error=invalid_inputs");
            exit;
        }

        // Generate strong one-way cryptographic hash values
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Prepared statements ensure SQL Injection is structurally impossible
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $hashedPassword]);
            
            header("Location: ../public/login.php?registration=success");
            exit;
        } catch (PDOException $e) {
            // Check if the email address is already registered to avoid a database collision error
            if ($e->getCode() == 23000) {
                header("Location: ../public/register.php?error=email_exists");
            } else {
                header("Location: ../public/register.php?error=system_fault");
            }
            exit;
        }
    }

    // ── LOGIN WORKFLOW ─────────────────────────────────────────────────────
    if ($action === 'login') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            header("Location: ../public/login.php?error=empty_fields");
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Safely evaluate hashed passwords to confirm identity
        if ($user && password_verify($password, $user['password'])) {
            // Store specific tracking identifiers securely inside the server session array
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Route users based on their access permissions
            if ($user['role'] === 'admin') {
                header("Location: ../public/admin/dashboard.php");
            } else {
                header("Location: ../public/index.php");
            }
            exit;
        } else {
            // Keep error messages intentionally generic to prevent user enumeration attacks
            header("Location: ../public/login.php?error=invalid_credentials");
            exit;
        }
    }
}