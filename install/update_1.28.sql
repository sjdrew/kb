-- Updates from version 1.28 to 1.29

-- Table Modifications

-- Inserts

-- Updates

update Settings set DBVersion  = '1.29' where ID=1
update Settings set AppVersion = '1.29' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

