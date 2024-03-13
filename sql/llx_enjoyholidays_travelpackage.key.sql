-- Copyright (C) 2024 SuperAdmin
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_enjoyholidays_travelpackage ADD INDEX idx_enjoyholidays_travelpackage_rowid (rowid);
ALTER TABLE llx_enjoyholidays_travelpackage ADD INDEX idx_enjoyholidays_travelpackage_ref (ref);
ALTER TABLE llx_enjoyholidays_travelpackage ADD CONSTRAINT llx_enjoyholidays_travelpackage_destinationCountry FOREIGN KEY (destinationCountry) REFERENCES llx_c_country(rowid);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_enjoyholidays_travelpackage ADD UNIQUE INDEX uk_enjoyholidays_travelpackage_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_enjoyholidays_travelpackage ADD CONSTRAINT llx_enjoyholidays_travelpackage_fk_field FOREIGN KEY (fk_field) REFERENCES llx_enjoyholidays_myotherobject(rowid);

