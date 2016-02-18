ALTER TABLE home_inputs ADD iAccount INT(10) UNSIGNED NOT NULL AFTER iId;

ALTER TABLE home_inputs ADD INDEX index_account_id (iAccount);

ALTER TABLE home_inputs ADD FOREIGN KEY (iAccount)
    REFERENCES home_accounts(aId)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
    
ALTER TABLE home_accounts DROP COLUMN aTable;
