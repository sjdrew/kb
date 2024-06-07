-- Updates from version 1.21 to 1.22

-- Inserts

-- Updates
update Settings set DBVersion  = '1.22' where ID=1
update Settings set AppVersion = '1.22' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

