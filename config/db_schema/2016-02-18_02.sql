ALTER TABLE home_accounts ADD aIcon VARCHAR(20) AFTER aName;

UPDATE home_accounts SET aIcon="money";
