ALTER TABLE wbb1_1_board
ADD countGuthaben tinyint( 1 ) UNSIGNED NOT NULL DEFAULT '1',
ADD postAddGuthaben int( 10 ) NOT NULL DEFAULT '0',
ADD threadAddGuthaben int( 10 ) NOT NULL DEFAULT '0';