-- Updates from version 1.24 to 1.25

-- Table Modifications

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='MaxUploadSize') 
	Alter Table Settings Add [MaxUploadSize] [int] NULL 
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Articles' AND C.Name='MustRead') 
	Alter Table Articles Add [MustRead] [varchar] (3) default 'No'
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='ArchiveArticles' AND C.Name='MustRead') 
	Alter Table ArchiveArticles Add [MustRead] [varchar] (3) default 'No'
;

IF NOT EXISTS (select null from sysindexes where [name] = 'IX_MustRead')
	CREATE  INDEX [IX_MustRead] ON [dbo].[Articles]([MustRead]) ON [PRIMARY]
;

-- Inserts

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'MaxUploadSize') 
	INSERT INTO FieldDetails VALUES ('11/21/2005','Admin',null,null,'Settings','MaxUploadSize','MaxUploadSize','No','Specify size in MegaBytes','Submitter;Administrators','Everyone','TextBox','',null,6,6,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'MustRead') 
	INSERT INTO FieldDetails VALUES ('11/21/2005','Admin',null,null,'Articles','MustRead','MustRead','No ','If set to Yes then this Article is dislayed on Home Page until user has read it','Submitter;Administrators','Everyone','DropList','No,Yes',null,3,3,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'MustRead_Search') 
	INSERT INTO FieldDetails VALUES ('11/21/2005','Admin',null,null,'Articles','MustRead','MustRead_Search','No ','Find Articles based on Setting of Must Read flag.','Submitter;Administrators','Everyone','DropList',',No,Yes',null,3,3,'field',null,null,null,null)


-- Updates

update Settings set DBVersion  = '1.25' where ID=1
update Settings set AppVersion = '1.25' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

