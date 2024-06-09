-- Updates from version 1.30 to 1.31

-- Table Modifications

-- Updates

update Settings set DBVersion  = '1.31' where ID=1
update Settings set AppVersion = '1.31' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1

;

