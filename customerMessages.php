<?php
include "inc/header.php";

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Customer');
$userEmail = $_SESSION['user_email'] ?? '';

// Get selected conversation from query parameter
$selectedConversationId = (int) ($_GET['conversation_id'] ?? 0);
$selectedFarmerId = (int) ($_GET['farmer_id'] ?? 0);

// Handle sending a new message
$messageError = '';
$messageSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $conversationId = (int) ($_POST['conversation_id'] ?? 0);
    $messageText = trim($_POST['message_text'] ?? '');
    
    if (empty($messageText)) {
        $messageError = 'Message cannot be empty.';
    } elseif ($messageText && strlen($messageText) <= 5000) {
        if ($db->query("INSERT INTO messages (conversation_id, sender_id, sender_type, message_text, created_at) VALUES ($conversationId, $userId, 'customer', '" . $db->real_escape_string($messageText) . "', NOW())")) {
            $messageSuccess = 'Message sent successfully!';
            $_POST['message_text'] = '';
        } else {
            $messageError = 'Failed to send message. Please try again.';
        }
    } else {
        $messageError = 'Message is too long (max 5000 characters).';
    }
}

// Start a new conversation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_conversation'])) {
    $farmerId = (int) ($_POST['farmer_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $firstMessage = trim($_POST['first_message'] ?? '');
    
    if ($farmerId > 0 && !empty($firstMessage)) {
        // Check if conversation already exists
        $existingConv = $db->query("SELECT conversation_id FROM conversations WHERE customer_id = $userId AND farmer_id = $farmerId LIMIT 1");
        if ($existingConv && $existingConv->num_rows > 0) {
            $conv = $existingConv->fetch_assoc();
            $selectedConversationId = $conv['conversation_id'];
            // Add message to existing conversation
            $db->query("INSERT INTO messages (conversation_id, sender_id, sender_type, message_text, created_at) VALUES ($selectedConversationId, $userId, 'customer', '" . $db->real_escape_string($firstMessage) . "', NOW())");
        } else {
            // Create new conversation
            $escapedSubject = $db->real_escape_string($subject ?: 'Product Inquiry');
            if ($db->query("INSERT INTO conversations (customer_id, farmer_id, subject, created_at, updated_at) VALUES ($userId, $farmerId, '$escapedSubject', NOW(), NOW())")) {
                $selectedConversationId = $db->insert_id;
                // Add first message
                $db->query("INSERT INTO messages (conversation_id, sender_id, sender_type, message_text, created_at) VALUES ($selectedConversationId, $userId, 'customer', '" . $db->real_escape_string($firstMessage) . "', NOW())");
                $messageSuccess = 'Conversation started!';
            } else {
                $messageError = 'Failed to start conversation.';
            }
        }
    } elseif (empty($firstMessage)) {
        $messageError = 'Please enter a message to start the conversation.';
    } else {
        $messageError = 'Please select a farmer.';
    }
}

// Get all conversations for this customer
$conversationsSql = "SELECT c.conversation_id, c.farmer_id, c.subject, c.updated_at, u.user_name AS farmer_name, u.user_email AS farmer_email, 
                    (SELECT COUNT(*) FROM messages WHERE conversation_id = c.conversation_id AND is_read = 0 AND sender_id != $userId) AS unread_count,
                    (SELECT message_text FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1) AS last_message,
                    (SELECT created_at FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1) AS last_message_date
                    FROM conversations c
                    LEFT JOIN users u ON u.user_id = c.farmer_id
                    WHERE c.customer_id = $userId
                    ORDER BY c.updated_at DESC";
$conversationsResult = $db->query($conversationsSql);

// Get messages for selected conversation
$messages = [];
$currentConversation = null;
if ($selectedConversationId > 0) {
    $convCheck = $db->query("SELECT c.*, u.user_name AS farmer_name, u.user_email AS farmer_email FROM conversations c LEFT JOIN users u ON u.user_id = c.farmer_id WHERE c.conversation_id = $selectedConversationId AND c.customer_id = $userId LIMIT 1");
    if ($convCheck && $convCheck->num_rows > 0) {
        $currentConversation = $convCheck->fetch_assoc();
        
        // Mark messages as read
        $db->query("UPDATE messages SET is_read = 1, read_at = NOW() WHERE conversation_id = $selectedConversationId AND sender_id != $userId AND is_read = 0");
        
        // Get all messages
        $messagesSql = "SELECT m.*, u.user_name AS sender_name FROM messages m LEFT JOIN users u ON u.user_id = m.sender_id WHERE m.conversation_id = $selectedConversationId ORDER BY m.created_at ASC";
        $messagesResult = $db->query($messagesSql);
        while ($msg = $messagesResult->fetch_assoc()) {
            $messages[] = $msg;
        }
    }
}

// Get list of farmers for starting new conversation
$farmersSql = "SELECT DISTINCT u.user_id, u.user_name, u.user_email FROM users u WHERE u.role = 2 AND u.status = 1 ORDER BY u.user_name ASC";
$farmersResult = $db->query($farmersSql);

?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap');
    
    .messaging-container {
        display: flex;
        height: calc(100vh - 200px);
        background: #06130d;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .conversations-list {
        flex: 0 0 300px;
        background: #0d261a;
        border-right: 1px solid rgba(158, 247, 184, 0.1);
        overflow-y: auto;
        padding: 15px;
    }
    
    .conversation-item {
        padding: 12px;
        margin-bottom: 8px;
        background: rgba(15, 138, 69, 0.1);
        border: 1px solid rgba(158, 247, 184, 0.15);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .conversation-item:hover,
    .conversation-item.active {
        background: rgba(15, 138, 69, 0.25);
        border-color: rgba(158, 247, 184, 0.3);
    }
    
    .conversation-item h6 {
        margin: 0 0 4px 0;
        color: #e9fff4;
        font-weight: 600;
    }
    
    .conversation-item p {
        margin: 0;
        font-size: 0.85rem;
        color: #9ef7b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .conversation-item .badge {
        display: inline-block;
        background: #10b881;
        color: white;
        border-radius: 10px;
        padding: 2px 6px;
        font-size: 0.75rem;
        margin-top: 4px;
    }
    
    .messages-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #06130d;
    }
    
    .messages-header {
        padding: 15px 20px;
        background: #0d261a;
        border-bottom: 1px solid rgba(158, 247, 184, 0.1);
        color: #e9fff4;
    }
    
    .messages-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .message {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .message.sent {
        justify-content: flex-end;
    }
    
    .message.received {
        justify-content: flex-start;
    }
    
    .message-content {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 8px;
        word-wrap: break-word;
    }
    
    .message.sent .message-content {
        background: #0f8a45;
        color: #ffffff;
    }
    
    .message.received .message-content {
        background: rgba(158, 247, 184, 0.15);
        color: #e9fff4;
        border: 1px solid rgba(158, 247, 184, 0.2);
    }
    
    .message-time {
        font-size: 0.75rem;
        color: #9ef7b8;
        margin-top: 4px;
    }
    
    .message-input-area {
        padding: 15px 20px;
        background: #0d261a;
        border-top: 1px solid rgba(158, 247, 184, 0.1);
    }
    
    .no-conversation {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        flex-direction: column;
        text-align: center;
        color: #9ef7b8;
    }
    
    .no-conversation i {
        font-size: 3rem;
        opacity: 0.5;
        margin-bottom: 20px;
    }
    
    .new-conversation-form {
        background: #0d261a;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.06);
        color: #e9fff4;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff;
        border-color: #0f8a45;
        box-shadow: none;
    }
    
    .form-label {
        color: #e9fff4;
    }
</style>

<div class="container-fluid" style="background: #06130d; min-height: 100vh; padding: 30px 0;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 style="color: #e9fff4; font-family: 'Space Grotesk', sans-serif;">
                    <i class="fas fa-comments me-2" style="color: #9ef7b8;"></i>Messages
                </h1>
                <p style="color: #9ef7b8;">Chat with farmers about products and inquiries</p>
            </div>
        </div>

        <?php if ($messageError): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger text-white mb-3">
                <i class="fas fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($messageError); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($messageSuccess): ?>
            <div class="alert alert-success bg-success bg-opacity-10 border border-success text-white mb-3">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($messageSuccess); ?>
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <!-- Conversations List -->
            <div class="col-lg-4">
                <div style="background: #0d261a; border-radius: 12px; padding: 20px; border: 1px solid rgba(158, 247, 184, 0.1);">
                    <h5 style="color: #e9fff4; margin-bottom: 15px;">
                        <i class="fas fa-inbox me-2" style="color: #9ef7b8;"></i>Conversations
                    </h5>
                    
                    <!-- New Conversation Form -->
                    <div class="new-conversation-form">
                        <h6 style="color: #e9fff4; margin-bottom: 12px;">Start New Conversation</h6>
                        <form method="POST">
                            <div class="mb-2">
                                <label class="form-label" for="farmer_id">Select Farmer</label>
                                <select class="form-select form-select-sm" id="farmer_id" name="farmer_id" required>
                                    <option value="">Choose a farmer...</option>
                                    <?php if ($farmersResult && $farmersResult->num_rows > 0): while ($farmer = $farmersResult->fetch_assoc()): ?>
                                        <option value="<?php echo (int) $farmer['user_id']; ?>"><?php echo htmlspecialchars($farmer['user_name']); ?></option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="subject">Subject (Optional)</label>
                                <input type="text" class="form-control form-control-sm" id="subject" name="subject" placeholder="e.g., Product Inquiry" maxlength="255">
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="first_message">Message</label>
                                <textarea class="form-control form-control-sm" id="first_message" name="first_message" rows="2" placeholder="Type your message..." maxlength="5000" required></textarea>
                            </div>
                            <button type="submit" name="start_conversation" class="btn btn-sm btn-success w-100">
                                <i class="fas fa-paper-plane me-1"></i>Send
                            </button>
                        </form>
                    </div>

                    <!-- Conversations -->
                    <div style="max-height: 500px; overflow-y: auto;">
                        <?php if ($conversationsResult && $conversationsResult->num_rows > 0): while ($conv = $conversationsResult->fetch_assoc()): ?>
                            <div class="conversation-item <?php echo $selectedConversationId === (int) $conv['conversation_id'] ? 'active' : ''; ?>" 
                                 onclick="window.location.href='?conversation_id=<?php echo (int) $conv['conversation_id']; ?>'">
                                <h6><?php echo htmlspecialchars($conv['farmer_name'] ?: 'Unknown Farmer'); ?></h6>
                                <p><?php echo htmlspecialchars(substr($conv['last_message'] ?? 'No messages yet', 0, 50)); ?></p>
                                <small style="color: #9ef7b8; display: block; margin-top: 4px;">
                                    <?php echo $conv['last_message_date'] ? htmlspecialchars(date('M j, Y g:i A', strtotime($conv['last_message_date']))) : 'No messages'; ?>
                                </small>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="badge bg-info" style="margin-top: 4px;"><?php echo (int) $conv['unread_count']; ?> new</span>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; else: ?>
                            <p style="color: #9ef7b8; text-align: center; padding: 20px;">No conversations yet. Start one above!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="col-lg-8">
                <?php if ($currentConversation): ?>
                    <div style="background: #0d261a; border-radius: 12px; border: 1px solid rgba(158, 247, 184, 0.1); height: 600px; display: flex; flex-direction: column;">
                        <!-- Header -->
                        <div style="padding: 15px 20px; background: #123523; border-bottom: 1px solid rgba(158, 247, 184, 0.1); color: #e9fff4; border-radius: 12px 12px 0 0;">
                            <h6 style="margin: 0; color: #e9fff4;">
                                <i class="fas fa-user me-2" style="color: #9ef7b8;"></i><?php echo htmlspecialchars($currentConversation['farmer_name'] ?: 'Unknown'); ?>
                            </h6>
                            <small style="color: #9ef7b8;"><?php echo htmlspecialchars($currentConversation['farmer_email'] ?: ''); ?></small>
                            <?php if ($currentConversation['subject']): ?>
                                <p style="margin: 4px 0 0 0; color: #b8d8c5; font-size: 0.9rem;"><?php echo htmlspecialchars($currentConversation['subject']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Messages -->
                        <div style="flex: 1; overflow-y: auto; padding: 20px; background: #06130d;">
                            <?php if (count($messages) > 0): ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message <?php echo (int) $msg['sender_id'] === $userId ? 'sent' : 'received'; ?>">
                                        <div>
                                            <div class="message-content">
                                                <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                            </div>
                                            <div class="message-time">
                                                <?php echo htmlspecialchars(date('M j, g:i A', strtotime($msg['created_at']))); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-conversation">
                                    <i class="fas fa-comment-dots"></i>
                                    <p>No messages yet. Start the conversation!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Input Area -->
                        <div style="padding: 15px 20px; background: #123523; border-top: 1px solid rgba(158, 247, 184, 0.1); border-radius: 0 0 12px 12px;">
                            <form method="POST">
                                <input type="hidden" name="conversation_id" value="<?php echo (int) $currentConversation['conversation_id']; ?>">
                                <div class="input-group">
                                    <textarea class="form-control" name="message_text" placeholder="Type a message..." rows="2" maxlength="5000" required style="background: rgba(255,255,255,0.06); color: #e9fff4; border: 1px solid rgba(255,255,255,0.1);"></textarea>
                                    <button type="submit" name="send_message" class="btn btn-success">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="background: #0d261a; border-radius: 12px; border: 1px solid rgba(158, 247, 184, 0.1); height: 600px; display: flex; align-items: center; justify-content: center;">
                        <div class="no-conversation">
                            <i class="fas fa-handshake"></i>
                            <h5 style="color: #e9fff4; margin-top: 15px;">No Conversation Selected</h5>
                            <p>Select a conversation from the left or create a new one to start messaging</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>
