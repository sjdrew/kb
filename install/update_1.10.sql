-- 1.20 Update script

-- Table modifications, must be done first
IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='ArticleAttachments' AND C.Name='AsContent') 
	Alter Table ArticleAttachments Add  [AsContent] [int] NULL 
IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='ArchiveArticleAttachments' AND C.Name='AsContent') 
	Alter Table ArchiveArticleAttachments Add  [AsContent] [int] NULL 
IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DBVersion') 
	Alter Table Settings Add [DBVersion] [decimal](4,2) default '1.20' 
IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DBLastUpdate') 
	Alter Table Settings Add [DBLastUpdate] [datetime] NULL 
IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='AppVersion') 
	Alter Table Settings Add [AppVersion] [decimal](4,2) default '1.20' NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DisplayNewCount') 
	Alter Table Settings Add [DisplayNewCount] [int] default '10' NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DisplayNewSort') 
	Alter Table Settings Add [DisplayNewSort] [int] default '0' NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DisplayViewedCount') 
	Alter Table Settings Add [DisplayViewedCount] [int] default '10' NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DisplayViewedSort') 
	Alter Table Settings Add [DisplayViewedSort] [int] default '0' NULL 

;

-- Inserts
if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DBVersion') 
	INSERT INTO FieldDetails VALUES (GetDate(),'sdrew',null,null,'Settings','DBVersion','DBVersion','No','Database Structure version','Submitter;Administrators','Everyone','TextBox',null,null,4,null,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DBLastUpdate')
	INSERT INTO FieldDetails VALUES (GetDate(),'sdrew',null,null,'Settings','DBLastUpdate','DBLastUpdate','No','Date of Last structure update','Submitter;Administrators','Everyone','TextBox',null,null,4,null,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'AppVersion') 
	INSERT INTO FieldDetails VALUES (GetDate(),'sdrew',null,null,'Settings','AppVersion','AppVersion','No','Application Version','Submitter;Administrators','Everyone','TextBox',null,null,4,null,'field',null,null,null,null)


if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DisplayNewCount') 
	INSERT INTO FieldDetails VALUES ('06/09/2005','SDrew',null,null,'Settings','DisplayNewCount','DisplayNewCount','Yes',null,'Submitter;Administrators','Everyone','DropList','5,10,15,20,25,30,40,50',null,10,10,'field','',null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DisplayNewSort') 
	INSERT INTO FieldDetails VALUES ('06/09/2005','SDrew',null,null,'Settings','DisplayNewSort','DisplayNewSort','Yes',null,'Submitter;Administrators','Everyone','DropList','Age;1,Title;2',null,10,10,'field','',null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DisplayViewedCount') 
	INSERT INTO FieldDetails VALUES ('06/09/2005','SDrew',null,null,'Settings','DisplayViewedCount','DisplayViewedCount','Yes',null,'Submitter;Administrators','Everyone','DropList','5,10,15,20,25,30,40,50',null,10,10,'field','',null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DisplayViewedSort') 
	INSERT INTO FieldDetails VALUES ('06/09/2005','SDrew',null,null,'Settings','DisplayViewedSort','DisplayViewedSort','Yes',null,'Submitter;Administrators','Everyone','DropList','Hits;1,Title;2',null,10,10,'field','',null,null,null)

;

-- Updates
update Settings set DBVersion  = '1.20' where ID=1
update Settings set AppVersion = '1.20' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;
