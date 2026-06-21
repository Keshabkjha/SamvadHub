<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Message;
use App\Models\Social;

class MessageController {
    /**
     * POST /api/message/send
     */
    public function send(Request $request): void {
        requireAuth();
        $userId = (int)$request->post('user_id', 0);
        $msg    = trim($request->post('msg', ''));
        $image  = $request->file('msg_img');
        
        if ($userId <= 0) {
            Response::json(['status' => false, 'error' => 'Invalid request.']);
        }
        if (empty($msg) && (!$image || $image['error'] === UPLOAD_ERR_NO_FILE)) {
            Response::json(['status' => false, 'error' => 'Please enter a message or select an image.']);
        }
        if (Social::checkBS($userId)) {
            Response::json(['status' => false, 'error' => 'You cannot message this user.']);
        }

        $msgImg = null;
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $upload = \App\Helpers\ImageHelper::handleUpload($image, 'messages', 800);
            if ($upload['status']) {
                $msgImg = $upload['filename'];
            } else {
                Response::json(['status' => false, 'error' => $upload['error']]);
            }
        }

        $status = Message::send($userId, $msg, $msgImg);
        Response::json(['status' => $status]);
    }

    /**
     * POST /api/message/get
     */
    public function get(Request $request): void {
        requireAuth();
        $chats    = Message::getAllConversations();
        $chatlist = '';

        foreach ($chats as $chat) {
            $ch_user = User::getById((int)$chat['user_id']);
            if (empty($ch_user) || empty($chat['messages'])) continue;

            $last_msg = $chat['messages'][0];
            $seen     = (int)$last_msg['read_status'] === 1 || 
                        (int)$last_msg['from_user_id'] === User::currentId();

            $chatlist .= '<div class="chat-item d-flex justify-content-between border-bottom chatlist_item" 
                data-bs-toggle="modal" data-bs-target="#chatbox" onclick="popchat(' . (int)$chat['user_id'] . ')">'
                . '<div class="d-flex align-items-center p-2 gap-2">'
                . '<img src="assets/images/profile/' . e($ch_user['profile_pic']) . '" alt="' . e($ch_user['first_name']) . '" height="40" width="40" class="rounded-circle border flex-shrink-0">'
                . '<div class="d-flex flex-column">'
                . '<span class="chat-name">' . e($ch_user['first_name']) . ' ' . e($ch_user['last_name']) . '</span>'
                . '<span class="chat-preview">' . e(mb_substr($last_msg['msg'], 0, 40)) . (strlen($last_msg['msg']) > 40 ? '…' : '') . '</span>'
                . '</div></div>'
                . '<div class="d-flex align-items-center pe-2">'
                . ($seen ? '' : '<div class="unread-dot"></div>')
                . '</div></div>';
        }

        $json = [
            'chatlist' => $chatlist,
            'newmsgcount' => Message::getUnreadCount(),
            'newnotifcount' => \App\Models\Notification::getUnreadCount()
        ];

        $chatter_id = (int)$request->post('chatter_id', 0);
        if ($chatter_id > 0) {
            $messages = Message::getMessages($chatter_id);
            Message::markAsRead($chatter_id);
            $is_blocked = (bool)Social::checkBS($chatter_id);
            $chatmsg    = '';

            foreach ($messages as $cm) {
                $is_own = (int)$cm['from_user_id'] === User::currentId();
                $cl1 = $is_own ? 'msg-bubble own' : 'msg-bubble other';
                
                $imgHtml = '';
                if (!empty($cm['msg_img'])) {
                    $imgHtml = '<div class="msg-media mt-1 mb-1">'
                             . '<img src="/assets/images/messages/' . e($cm['msg_img']) . '" '
                             . 'style="max-width: 100%; max-height: 200px; border-radius: 8px; cursor: pointer; display: block;" '
                             . 'onclick="window.open(this.src)">'
                             . '</div>';
                }
                
                $msgText = '';
                if (!empty($cm['msg'])) {
                    $msgText = '<span class="msg-text">' . e($cm['msg']) . '</span>';
                }

                $chatmsg .= '<div class="' . $cl1 . '">'
                    . $imgHtml
                    . $msgText
                    . '<span class="msg-time">' . e(gettime($cm['created_at'])) . '</span>'
                    . '</div>';
            }

            $json['blocked']          = $is_blocked;
            $json['chat']['msgs']     = $chatmsg;
            $json['chat']['userdata'] = User::getById($chatter_id);
        }

        Response::json($json);
    }
}
