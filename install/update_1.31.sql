-- Updates from version 1.31 to 1.32

-- Table Modifications

-- Updates

update Settings set DBVersion  = '1.32' where ID=1
update Settings set AppVersion = '1.32' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1

;

