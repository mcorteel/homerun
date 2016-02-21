CREATE TABLE IF NOT EXISTS home_lists (
    lId int(10) unsigned NOT NULL AUTO_INCREMENT,
    lTitle tinytext NOT NULL,
    lGroup int(10) unsigned NOT NULL,
    lIcon varchar(20) NOT NULL,
    lCreationDate timestamp DEFAULT CURRENT_TIMESTAMP,
    lModificationDate timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (lId),
    INDEX index_group_id (lGroup),
    FOREIGN KEY (lGroup)
        REFERENCES home_groups(gId)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS home_list_items (
    iId int(10) unsigned NOT NULL AUTO_INCREMENT,
    iList int(10) unsigned NOT NULL,
    iUser int(10) unsigned NOT NULL,
    iContent tinytext NOT NULL,
    iStatus int(1) NOT NULL,
    iOrder int(4) NOT NULL,
    iCreationDate timestamp DEFAULT CURRENT_TIMESTAMP,
    iModificationDate timestamp ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (iId),
    INDEX index_list_id (iList),
    FOREIGN KEY (iList)
        REFERENCES home_lists(lId)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX index_user_id (iUser),
    FOREIGN KEY (iUser)
        REFERENCES home_users(uId)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
