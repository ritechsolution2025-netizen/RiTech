<?php
/**
 * PHP Email Form
 * Library for sending emails via PHP
 */

class PHP_Email_Form {
    public $to = '';
    public $from_name = '';
    public $from_email = '';
    public $subject = '';
    public $ajax = false;
    public $smtp = array();
    
    private $message = '';
    private $headers = '';
    private $error = '';
    
    public function __construct() {
        // Initialize headers
        $this->headers = "MIME-Version: 1.0\r\n";
        $this->headers .= "Content-type: text/html; charset=UTF-8\r\n";
    }
    
    /**
     * Add message to email body
     */
    public function add_message($value, $label = '', $min_length = 0) {
        if (strlen($value) < $min_length && $min_length > 0) {
            $this->error = "$label must be at least $min_length characters long.";
            return false;
        }
        
        if (!empty($label)) {
            $this->message .= "<p><strong>$label:</strong> " . htmlspecialchars($value) . "</p>";
        } else {
            $this->message .= "<p>" . htmlspecialchars($value) . "</p>";
        }
        
        return true;
    }
    
    /**
     * Send the email
     */
    public function send() {
        // Validate required fields
        if (empty($this->to)) {
            $this->error = 'Recipient email address is not set.';
            return $this->ajax ? $this->ajax_response() : false;
        }
        
        if (empty($this->from_email)) {
            $this->error = 'Sender email address is not set.';
            return $this->ajax ? $this->ajax_response() : false;
        }
        
        if (empty($this->subject)) {
            $this->error = 'Email subject is required.';
            return $this->ajax ? $this->ajax_response() : false;
        }
        
        if (empty($this->message)) {
            $this->error = 'Email message is empty.';
            return $this->ajax ? $this->ajax_response() : false;
        }
        
        // Build complete email
        $email_body = $this->build_email();
        
        // Set additional headers
        $headers = $this->headers;
        $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Check if SMTP is configured
        if (!empty($this->smtp) && isset($this->smtp['host'])) {
            return $this->send_smtp($email_body, $headers);
        } else {
            return $this->send_mail($email_body, $headers);
        }
    }
    
    /**
     * Send email using PHP's mail() function
     */
    private function send_mail($email_body, $headers) {
        if (mail($this->to, $this->subject, $email_body, $headers)) {
            $this->error = '';
            return $this->ajax ? $this->ajax_response(true) : true;
        } else {
            $this->error = 'Failed to send email. Please try again later.';
            return $this->ajax ? $this->ajax_response() : false;
        }
    }
    
    /**
     * Send email via SMTP (basic implementation)
     */
    private function send_smtp($email_body, $headers) {
        // This is a basic SMTP implementation
        // For production, consider using PHPMailer or SwiftMailer
        $this->error = 'SMTP configuration not fully implemented. Using mail() instead.';
        return $this->send_mail($email_body, $headers);
    }
    
    /**
     * Build the complete email HTML
     */
    private function build_email() {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($this->subject) . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .email-container { background: #f9f9f9; padding: 30px; border-radius: 5px; }
                .header { background: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; margin: -30px -30px 20px -30px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
                strong { color: #007bff; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h2>' . htmlspecialchars($this->subject) . '</h2>
                    <p>From: ' . htmlspecialchars($this->from_name) . ' &lt;' . htmlspecialchars($this->from_email) . '&gt;</p>
                </div>
                <div class="content">' . $this->message . '</div>
                <div class="footer">
                    <p>This email was sent from your website contact form on ' . date('F j, Y, g:i a') . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Return AJAX response
     */
    private function ajax_response($success = false) {
        if ($success) {
            echo 'OK';
        } else {
            echo $this->error;
        }
        return $success;
    }
}
?>