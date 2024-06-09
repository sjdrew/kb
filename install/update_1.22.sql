-- Updates from version 1.22 to 1.23

-- Table Modifications

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DefaultSearchMode') 
	Alter Table Settings Add [DefaultSearchMode] [varchar] (20) default 'English Query' NULL 

;

CREATE  INDEX [IX_Product] ON [dbo].[Articles]([Product]) ON [PRIMARY]
;

-- Inserts

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DefaultSearchMode') 
	INSERT INTO FieldDetails VALUES ('10/24/2005','SDrew',null,null,'Settings','DefaultSearchMode','DefaultSearchMode','Yes',null,'Submitter;Administrators','Everyone','DropList','English Query,Strict',null,10,10,'field','',null,null,null)

-- Updates


update Settings set DBVersion  = '1.23' where ID=1
update Settings set AppVersion = '1.23' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

