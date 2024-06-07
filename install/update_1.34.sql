-- Updates from version 1.34 to 1.35

-- Table Modifications

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='KnownErrorTable') 
	Alter Table Settings Add [KnownErrorTable] [varchar] (8) NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='KnownErrorWorkLogTable') 
	Alter Table Settings Add [KnownErrorWorkLogTable] [varchar] (8) NULL 
;

-- Updates

update Settings set DBVersion  = '1.35' where ID=1
update Settings set AppVersion = '1.35' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1

;

