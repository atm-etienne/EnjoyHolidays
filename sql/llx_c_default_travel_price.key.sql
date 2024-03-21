ALTER TABLE llx_c_default_travel_price ADD CONSTRAINT llx_c_default_travel_price_fk_country FOREIGN KEY (fk_country) REFERENCES llx_c_country (rowid);
