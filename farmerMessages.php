<?php
include "inc/header.php";

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Farmer');
$userEmail = $_SESSION['user_email'] ?? '';

// Get selected conversation from query parameter
$selectedConversationId = (int) ($_GET['conversation_id'] ?? 0);
$selectedCustomerId = (int) ($_GET['customer_id'] ?? 0);

// Handle sending a new message
$messageError = '';
$messageSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $conversationId = (int) ($_POST['conversation_id'] ?? 0);
    $messageText = trim($_POST['message_text'] ?? '');
    
    if (empty($messageText)) {
        $messageError = 'Message cannot be empty.';
    } elseif ($messageText && strlen($messageText) <= 5000) {
        if ($db->query("INSERT INTO messages (conversation_id, sender_id, sender_type, message_text, created_at) VALUES ($conversationId, $userId, 'farmer', '" . $db->real_escape_string($messageText) . "', NOW())")) {
            // Update conversation updated_at
            $db->query("UPDATE conversations SET updated_at = NOW() WHERE conversation_id = $conversationId");
            $messageSuccess = 'Message sent successfully!';
            $_POST['message_text'] = '';
        } else {
            $messageError = 'Failed to send message. Please try again.';
        }
    } else {
        $messageError = 'Message is too long (max 5000 characters).';
    }
}

// Get all conversations where this farmer is involved
$conversationsSql = "SELECT c.conversation_id, c.customer_id, c.subject, c.updated_at, u.user_name AS customer_name, u.user_email AS customer_email, 
                    (SELECT COUNT(*) FROM messages WHERE conversation_id = c.conversation_id AND is_read = 0 AND sender_id != $userId) AS unread_count,
                    (SELECT message_text FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1) AS last_message,
                    (SELECT created_at FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1) AS last_message_date
                    FROM conversations c
                    LEFT JOIN users u ON u.user_id = c.customer_id
                    WHERE c.farmer_id = $userId
                    ORDER BY c.updated_at DESC";
$conversationsResult = $db->query($conversationsSql);

// Get messages for selected conversation
$messages = [];
$currentConversation = null;
if ($selectedConversationId > 0) {
    $convCheck = $db->query("SELECT c.*, u.user_name AS customer_name, u.user_email AS customer_email FROM conversations c LEFT JOIN users u ON u.user_id = c.customer_id WHERE c.conversation_id = $selectedConversationId AND c.farmer_id = $userId LIMIT 1");
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

// Get basic stats
$totalConversations = $db->query("SELECT COUNT(*) as count FROM conversations WHERE farmer_id = $userId")->fetch_assoc()['count'];
$unreadCount = $db->query("SELECT COUNT(*) as count FROM messages WHERE conversation_id IN (SELECT conversation_id FROM conversations WHERE farmer_id = $userId) AND is_read = 0 AND sender_id != $userId")->fetch_assoc()['count'];

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
                    <i class="fas fa-comments me-2" style="color: #9ef7b8;"></i>Customer Messages
                </h1>
                <p style="color: #9ef7b8;">Communicate with customers about their inquiries</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div style="background: #0d261a; border: 1px solid rgba(158, 247, 184, 0.1); border-radius: 8px; padding: 15px; text-align: center;">
                    <div style="color: #9ef7b8; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px;">Total Conversations</div>
                    <div style="color: #e9fff4; font-size: 2rem; font-weight: 700;"><?php echo (int) $totalConversations; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div style="background: #0d261a; border: 1px solid rgba(158, 247, 184, 0.1); border-radius: 8px; padding: 15px; text-align: center;">
                    <div style="color: #9ef7b8; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px;">Unread Messages</div>
                    <div style="color: #e9fff4; font-size: 2rem; font-weight: 700;"><?php echo (int) $unreadCount; ?></div>
                </div>
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
                        <i class="fas fa-inbox me-2" style="color: #9ef7b8;"></i>Customer Conversations
                    </h5>

                    <!-- Conversations -->
                    <div style="max-height: 550px; overflow-y: auto;">
                        <?php if ($conversationsResult && $conversationsResult->num_rows > 0): while ($conv = $conversationsResult->fetch_assoc()): ?>
                            <div class="conversation-item <?php echo $selectedConversationId === (int) $conv['conversation_id'] ? 'active' : ''; ?>" 
                                 onclick="window.location.href='?conversation_id=<?php echo (int) $conv['conversation_id']; ?>'">
                                <h6><?php echo htmlspecialchars($conv['customer_name'] ?: 'Unknown Customer'); ?></h6>
                                <p><?php echo htmlspecialchars(substr($conv['last_message'] ?? 'No messages yet', 0, 50)); ?></p>
                                <small style="color: #9ef7b8; display: block; margin-top: 4px;">
                                    <?php echo $conv['last_message_date'] ? htmlspecialchars(date('M j, Y g:i A', strtotime($conv['last_message_date']))) : 'No messages'; ?>
                                </small>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="badge bg-info" style="margin-top: 4px;"><?php echo (int) $conv['unread_count']; ?> new</span>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; else: ?>
                            <p style="color: #9ef7b8; text-align: center; padding: 20px;">No conversations yet. Customers will reach out here!</p>
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
                                <i class="fas fa-user me-2" style="color: #9ef7b8;"></i><?php echo htmlspecialchars($currentConversation['customer_name'] ?: 'Unknown'); ?>
                            </h6>
                            <small style="color: #9ef7b8;"><?php echo htmlspecialchars($currentConversation['customer_email'] ?: ''); ?></small>
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
                                    <p>No messages yet. Respond to start the conversation!</p>
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
                            <p>Select a conversation from the left to view and respond to messages</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>
