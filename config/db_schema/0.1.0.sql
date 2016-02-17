CREATE TABLE IF NOT EXISTS home_groups (
    gId int(10) unsigned NOT NULL AUTO_INCREMENT,
    gName tinytext NOT NULL,
    gCreationDate timestamp DEFAULT CURRENT_TIMESTAMP,
    gModificationDate timestamp ON UPDATE CURRENT_TIMESTAMP,
    gSystem tinyint(1) NOT NULL,
    PRIMARY KEY (gId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS home_users (
    uId int(10) unsigned NOT NULL AUTO_INCREMENT,
    uLogin text NOT NULL,
    uPassword text NOT NULL,
    uDisplayName text NOT NULL,
    uEmail text,
    uLastLogin bigint(20) NOT NULL DEFAULT '0',
    uFirstLogin bigint(20) NOT NULL DEFAULT '0',
    uOptions text NOT NULL,
    uCreationDate timestamp DEFAULT CURRENT_TIMESTAMP,
    uModificationDate timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (uId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS home_accounts (
    aId int(10) unsigned NOT NULL AUTO_INCREMENT,
    aName tinytext NOT NULL,
    aGroup int(10) unsigned NOT NULL,
    aTable tinytext NOT NULL,
    aLog tinyint(1) NOT NULL DEFAULT '0',
    aLimit int(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (aId),
    INDEX index_group_id (aGroup),
    FOREIGN KEY (aGroup)
        REFERENCES home_groups(gId)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS home_members (
    mId int(10) unsigned NOT NULL AUTO_INCREMENT,
    mUserId int(10) unsigned NOT NULL,
    mGroupId int(10) unsigned NOT NULL,
    mAdmin tinyint(1) NOT NULL,
    PRIMARY KEY (mId),
    INDEX index_user_id (mUserId),
    INDEX index_group_id (mGroupId),
    FOREIGN KEY (mUserId)
        REFERENCES home_users(uId)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (mGroupId)
        REFERENCES home_groups(gId)
        ON DELETE CASCADE
        ON UPDATE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS home_inputs (
    iId int(10) unsigned NOT NULL AUTO_INCREMENT,
    iType tinytext NOT NULL,
    iUser int(10) unsigned,
    iAmount double NOT NULL,
    iNotes text NOT NULL,
    iDate date NOT NULL,
    iCreationDate timestamp DEFAULT CURRENT_TIMESTAMP,
    iModificationDate timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (iId),
    INDEX index_user_id (iUser),
    FOREIGN KEY (iUser)
        REFERENCES home_users(uId)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS home_tags (
    tId int(10) unsigned NOT NULL AUTO_INCREMENT,
    tName tinytext NOT NULL,
    tAccount int(10) unsigned NOT NULL,
    tIcon tinytext NOT NULL,
    PRIMARY KEY (tId),
    INDEX index_account_id (tAccount),
    FOREIGN KEY (tAccount)
        REFERENCES home_accounts(aId)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS home_transfers (
    tId int(10) unsigned NOT NULL AUTO_INCREMENT,
    tAmount double NOT NULL,
    tNotes text NOT NULL,
    tCreationDate timestamp DEFAULT CURRENT_TIMESTAMP,
    tModificationDate timestamp ON UPDATE CURRENT_TIMESTAMP,
    tSender int(10) unsigned NOT NULL,
    tReceiver int(10) unsigned NOT NULL,
    tAccount int(10) unsigned NOT NULL,
    tDate date NOT NULL,
    PRIMARY KEY (tId),
    INDEX index_account_id (tAccount),
    INDEX index_sender_id (tSender),
    INDEX index_receiver_id (tReceiver),
    FOREIGN KEY (tAccount)
        REFERENCES home_accounts(aId)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (tSender)
        REFERENCES home_users(uId)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (tReceiver)
        REFERENCES home_users(uId)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO home_users(uId, uLogin, uEmail, uDisplayName, uPassword, uOptions) VALUES (1, 'admin', '', 'Admin', '$1$2dzgl1Zu$QvAm6AIJTiIxGGLv64vV7.', '{}');
INSERT INTO home_groups(gId, gName, gSystem) VALUES (1, 'admin', 1);
INSERT INTO home_members(mId, mGroupId, mUserId, mAdmin) VALUES (1, 1, 1, 1);
