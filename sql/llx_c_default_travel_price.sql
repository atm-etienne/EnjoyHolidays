CREATE TABLE llx_c_default_travel_price(
    rowid       integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    fk_country  int not null,
    amount      int null,
    position    int NULL,
    use_default varchar(255) DEFAULT '1' NULL,
    active      int NOT NULL
) ENGINE=innodb;
