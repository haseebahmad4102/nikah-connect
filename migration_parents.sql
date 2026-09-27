-- NikahConnect Migration: Parent (Guardian) accounts + parent-based Rishta approval + visit scheduling
-- Run this ONCE in phpMyAdmin / MySQL CLI on an existing `nikahconnect` database.
-- For a fresh install, database.sql already contains everything below.

USE nikahconnect;

-- 1) Allow a new 'parent' role and link a parent account to the child (boy/girl) it guards
ALTER TABLE users
    MODIFY COLUMN role ENUM('user','wali','parent','admin') DEFAULT 'user';

ALTER TABLE users
    ADD COLUMN child_id INT NULL AFTER role,
    ADD COLUMN guardian_relation VARCHAR(50) NULL AFTER child_id;

-- 2) Interests become "Rishta proposals" approved by BOTH parents (never by the boy/girl themselves)
--    Keep 'accepted' in the enum only long enough to migrate old rows to 'success'.
ALTER TABLE interests
    MODIFY COLUMN status ENUM('pending','accepted','success','rejected','withdrawn') DEFAULT 'pending';

UPDATE interests SET status='success' WHERE status='accepted';

ALTER TABLE interests
    ADD COLUMN sender_parent_approved TINYINT(1) DEFAULT 0,
    ADD COLUMN receiver_parent_approved TINYINT(1) DEFAULT 0,
    ADD COLUMN sender_parent_approved_at TIMESTAMP NULL,
    ADD COLUMN receiver_parent_approved_at TIMESTAMP NULL,
    ADD COLUMN visit_status ENUM('none','proposed','confirmed') DEFAULT 'none',
    ADD COLUMN visit_date DATE NULL,
    ADD COLUMN visit_proposed_by INT NULL;
