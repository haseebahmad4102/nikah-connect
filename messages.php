<?php
$pageTitle = 'Messages';
require_once 'includes/db.php';
requireLogin();

// Guardians view chats read-only from their panel
if(isParent()) { header('Location: '.SITE_URL.'/guardian.php'); exit; }

$user = currentUser();
$withId = (int)($_GET['with'] ?? 0);

// Get conversations (users I've exchanged messages with, and accepted interests)
$conversations = db()->fetchAll(
    "SELECT DISTINCT u.id, u.full_name, u.gender,
        (SELECT m2.message FROM messages m2 WHERE (m2.sender_id=u.id AND m2.receiver_id=?) OR (m2.sender_id=? AND m2.receiver_id=u.id) ORDER BY m2.created_at DESC LIMIT 1) as last_message,
        (SELECT m3.created_at FROM messages m3 WHERE (m3.sender_id=u.id AND m3.receiver_id=?) OR (m3.sender_id=? AND m3.receiver_id=u.id) ORDER BY m3.created_at DESC LIMIT 1) as last_time,
        (SELECT COUNT(*) FROM messages m4 WHERE m4.sender_id=u.id AND m4.receiver_id=? AND m4.is_read=0) as unread_count
     FROM users u
     WHERE u.id IN (
        SELECT CASE WHEN sender_id=? THEN receiver_id ELSE sender_id END FROM interests
        WHERE (sender_id=? OR receiver_id=?) AND status IN ('pending','success')
     )
     ORDER BY last_time DESC",
    [$user['id'],$user['id'],$user['id'],$user['id'],$user['id'],$user['id'],$user['id'],$user['id']]
);

// Current conversation
$chatWith = null;
$chatMessages = [];
if($withId) {
    // Check they have accepted interest
    $hasAccess = db()->fetchOne(
        "SELECT id FROM interests WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) AND status IN ('pending','success')",
        [$user['id'], $withId, $withId, $user['id']]
    );
    if($hasAccess) {
        $chatWith = db()->fetchOne("SELECT * FROM users u LEFT JOIN profiles p ON u.id=p.user_id WHERE u.id=?", [$withId]);
        // Mark as read
        db()->execute("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0", [$withId, $user['id']]);
        $chatMessages = db()->fetchAll(
            "SELECT m.*, u.full_name FROM messages m JOIN users u ON m.sender_id=u.id WHERE (m.sender_id=? AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=?) ORDER BY m.created_at ASC",
            [$user['id'],$withId,$withId,$user['id']]
        );
    }
}

// Send message
if($_SERVER['REQUEST_METHOD']==='POST' && $withId && $chatWith) {
    $msg = trim($_POST['message'] ?? '');
    if($msg && strlen($msg) <= 500) {
        // Basic halal filter — block inappropriate words
        $bannedWords = ['dating','boyfriend','girlfriend','love you','flirt','sexy','hot'];
        $isFlag = false;
        foreach($bannedWords as $bw) {
            if(stripos($msg, $bw) !== false) { $isFlag = true; break; }
        }
        db()->insert(
            "INSERT INTO messages (sender_id, receiver_id, message, is_flagged, wali_can_view) VALUES (?,?,?,?,1)",
            [$user['id'], $withId, $msg, $isFlag ? 1 : 0]
        );
        addNotification($withId, 'message', 'New message from '.htmlspecialchars($user['full_name']), SITE_URL.'/messages.php?with='.$user['id']);
        header('Location: '.SITE_URL.'/messages.php?with='.$withId);
        exit;
    }
}

require_once 'includes/header.php';
?>

<div style="max-width:1100px;margin:0 auto;padding:20px">
    <div class="chat-layout" style="height:calc(100vh - 110px)">
        <!-- Chat List -->
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <h3 style="font-size:16px;font-weight:600"><i class="fas fa-comments" style="color:var(--primary)"></i> Messages</h3>
                <p style="font-size:12px;color:var(--text-light);margin-top:2px">All chats visible to your Wali</p>
            </div>
            <?php if(empty($conversations)): ?>
            <div style="padding:24px;text-align:center;color:var(--text-light);font-size:13px">
                <i class="fas fa-comments" style="font-size:32px;display:block;margin-bottom:10px;color:var(--border)"></i>
                No conversations yet.<br>Accept an interest to start chatting.
            </div>
            <?php else: ?>
            <?php foreach($conversations as $conv): ?>
            <a href="messages.php?with=<?=$conv['id']?>" class="chat-list-item <?= $withId==$conv['id']?'active':'' ?>" style="text-decoration:none;color:inherit">
                <div class="chat-list-avatar"><?= $conv['gender']==='male'?'👳':'🧕' ?></div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div class="chat-list-name"><?=htmlspecialchars($conv['full_name'])?></div>
                        <?php if($conv['unread_count']>0): ?>
                        <span style="background:var(--primary);color:white;font-size:10px;font-weight:700;padding:2px 6px;border-radius:10px"><?=$conv['unread_count']?></span>
                        <?php endif; ?>
                    </div>
                    <div class="chat-list-preview"><?= htmlspecialchars($conv['last_message']??'No messages yet') ?></div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Chat Main -->
        <div class="chat-main">
            <?php if($chatWith): ?>
            <div class="chat-header">
                <div class="chat-list-avatar"><?= $chatWith['gender']==='male'?'👳':'🧕' ?></div>
                <div>
                    <div style="font-weight:600;font-size:15px"><?=htmlspecialchars($chatWith['full_name'])?></div>
                    <div style="font-size:12px;color:var(--text-light)"><?=htmlspecialchars($chatWith['city']??'')?></div>
                </div>
                <div style="margin-left:auto;display:flex;gap:8px">
                    <a href="view-profile.php?id=<?=$withId?>" class="btn btn-outline btn-sm"><i class="fas fa-user"></i> Profile</a>
                </div>
            </div>

            <div class="chat-notice">
                <i class="fas fa-shield-alt"></i>
                <strong>Halal Communication Zone</strong> — All messages are monitored and visible to your Guardian (Wali). Please maintain Islamic adab (etiquette) at all times.
            </div>

            <div class="chat-messages" id="chatMessages">
                <?php if(empty($chatMessages)): ?>
                <div style="text-align:center;color:var(--text-light);padding:40px 20px;font-size:13px">
                    <div style="font-family:'Amiri',serif;font-size:22px;color:var(--primary);margin-bottom:8px">بِسْمِ اللَّهِ</div>
                    Start your conversation with Islamic greetings.<br>
                    Remember: maintain modesty and Islamic adab.
                </div>
                <?php else: ?>
                <?php foreach($chatMessages as $msg): ?>
                <?php $isSent = $msg['sender_id'] == $user['id']; ?>
                <div class="message-bubble <?= $isSent?'sent':'received' ?>">
                    <?php if($msg['is_flagged']): ?>
                    <div style="font-size:11px;color:#e74c3c;margin-bottom:2px"><i class="fas fa-exclamation-triangle"></i> Message flagged for review</div>
                    <?php endif; ?>
                    <div class="bubble-content"><?=nl2br(htmlspecialchars($msg['message']))?></div>
                    <div class="bubble-time"><?=timeAgo($msg['created_at'])?><?= $isSent && $msg['is_read'] ? ' · Read' : '' ?></div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="chat-input-area">
                <form method="POST" style="display:flex;gap:10px;flex:1;align-items:flex-end" id="msgForm">
                    <textarea name="message" id="msgInput" placeholder="As-salamu alaykum! Type your message (max 500 chars)..." maxlength="500" rows="1" style="flex:1;border-radius:22px;padding:11px 18px;resize:none;font-size:14px;border:1.5px solid var(--border);outline:none;font-family:inherit" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('msgForm').submit();}"></textarea>
                    <button type="submit" class="btn btn-primary" style="border-radius:50%;width:44px;height:44px;padding:0;flex-shrink:0;justify-content:center">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;color:var(--text-light);gap:14px;padding:40px">
                <div style="font-size:52px">💬</div>
                <h3 style="color:var(--text-dark)">Select a Conversation</h3>
                <p style="font-size:14px;text-align:center;max-width:280px">
                    Choose a match from the left to start a halal conversation.
                    Only accepted connections can message each other.
                </p>
                <div style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.2);border-radius:10px;padding:14px;text-align:center;max-width:300px">
                    <div style="font-family:'Amiri',serif;font-size:18px;color:var(--primary);margin-bottom:4px">وَعَاشِرُوهُنَّ بِالْمَعْرُوفِ</div>
                    <div style="font-size:11px;color:var(--text-light)">"And live with them in kindness" — Quran 4:19</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom
const chatMessages = document.getElementById('chatMessages');
if(chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;

// Auto-resize textarea
const msgInput = document.getElementById('msgInput');
if(msgInput) {
    msgInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
