-- ============================================================
-- SamvadHub Database Migration
-- Run this script AFTER the original SamvadHub.sql
-- Version: 2.0 (Elite Upgrade)
-- ============================================================

USE `SamvadHub`;

-- ─── 1. Add bio, website, and is_admin to users table ────────────────────────
ALTER TABLE `users` 
    ADD COLUMN `bio`      TEXT          DEFAULT NULL AFTER `profile_pic`,
    ADD COLUMN `website`  VARCHAR(255)  DEFAULT NULL AFTER `bio`,
    ADD COLUMN `is_admin` TINYINT(1)    NOT NULL DEFAULT 0 AFTER `website`,
    ADD COLUMN `is_private` TINYINT(1)  NOT NULL DEFAULT 0 AFTER `is_admin`;

-- ─── 2. Update ac_status comment for new status 3 (deleted) ─────────────────
-- 0=not verified, 1=active, 2=blocked by admin, 3=deleted (soft)
ALTER TABLE `users` 
    MODIFY COLUMN `ac_status` int(11) NOT NULL DEFAULT 0
    COMMENT '0=not verified,1=active,2=blocked,3=deleted';

-- ─── 3. Create bookmarks table ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `bookmarks` (
    `id`         int(11)     NOT NULL AUTO_INCREMENT,
    `user_id`    int(11)     NOT NULL,
    `post_id`    int(11)     NOT NULL,
    `created_at` timestamp   NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_bookmark` (`user_id`, `post_id`),
    KEY `idx_bookmarks_user` (`user_id`),
    KEY `idx_bookmarks_post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── 4. Create reports table ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reports` (
    `id`               int(11)      NOT NULL AUTO_INCREMENT,
    `reporter_id`      int(11)      NOT NULL,
    `reported_user_id` int(11)      DEFAULT NULL,
    `post_id`          int(11)      DEFAULT NULL,
    `reason`           varchar(500) NOT NULL DEFAULT 'No reason provided',
    `status`           tinyint(1)   NOT NULL DEFAULT 0 COMMENT '0=pending,1=reviewed,2=dismissed',
    `created_at`       timestamp    NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_reports_reporter` (`reporter_id`),
    KEY `idx_reports_user`     (`reported_user_id`),
    KEY `idx_reports_post`     (`post_id`),
    KEY `idx_reports_status`   (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── 5. Add performance indexes on foreign key columns ───────────────────────

-- follow_list indexes
ALTER TABLE `follow_list`
    ADD INDEX `idx_follow_follower` (`follower_id`),
    ADD INDEX `idx_follow_user`     (`user_id`);

-- block_list indexes
ALTER TABLE `block_list`
    ADD INDEX `idx_block_user`         (`user_id`),
    ADD INDEX `idx_block_blocked_user`  (`blocked_user_id`);

-- likes indexes
ALTER TABLE `likes`
    ADD INDEX `idx_likes_post`   (`post_id`),
    ADD INDEX `idx_likes_user`   (`user_id`);

-- comments indexes
ALTER TABLE `comments`
    ADD INDEX `idx_comments_post`   (`post_id`),
    ADD INDEX `idx_comments_user`   (`user_id`),
    ADD INDEX `idx_comments_created` (`created_at`);

-- messages indexes
ALTER TABLE `messages`
    ADD INDEX `idx_messages_to`   (`to_user_id`),
    ADD INDEX `idx_messages_from` (`from_user_id`),
    ADD INDEX `idx_messages_read` (`read_status`);

-- notifications indexes
ALTER TABLE `notifications`
    ADD INDEX `idx_notif_to`      (`to_user_id`),
    ADD INDEX `idx_notif_read`    (`read_status`),
    ADD INDEX `idx_notif_created` (`created_at`);

-- posts indexes
ALTER TABLE `posts`
    ADD INDEX `idx_posts_user`    (`user_id`),
    ADD INDEX `idx_posts_created` (`created_at`);

-- users indexes
ALTER TABLE `users`
    ADD INDEX `idx_users_email`    (`email`),
    ADD INDEX `idx_users_username` (`username`),
    ADD INDEX `idx_users_status`   (`ac_status`);

-- ─── 6. Re-hash existing MD5 passwords to bcrypt ─────────────────────────────
-- IMPORTANT: After running this, all existing users will need to reset their password
-- OR you can run the PHP migration script (see migration_passwords.php)
-- DO NOT run this blindly — notify users first.
-- UPDATE `users` SET `password` = 'NEEDS_RESET' WHERE LENGTH(`password`) = 32;

-- ─── 7. Update notifications post_id column type ─────────────────────────────
-- Fix: post_id was TEXT, should be INT
ALTER TABLE `notifications` 
    MODIFY COLUMN `post_id` int(11) NOT NULL DEFAULT 0;

-- ─── Done ─────────────────────────────────────────────────────────────────────
SELECT 'Migration completed successfully!' AS status;
