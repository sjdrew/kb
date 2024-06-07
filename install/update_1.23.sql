-- Updates from version 1.23 to 1.24

-- Table Modifications

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[ContentSections]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[ContentSections] (
	[SectionName] [varchar] (50) PRIMARY KEY ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Content] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
END
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='AllowCreateBulletinsW') 
	Alter Table Settings Add [AllowModifyArticles] [int] NULL 
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='AllowCreateBulletinsW') 
	Alter Table Settings Add [AllowCreateBulletinsW] [int] NULL 
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Articles' AND C.Name='Priority') 
	Alter Table Articles Add [Priority] [varchar] (10) default 'Low'
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='ArchiveArticles' AND C.Name='Priority') 
	Alter Table ArchiveArticles Add [Priority] [varchar] (10) default 'Low'
;


-- Inserts

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'AllowModifyArticles') 
	INSERT INTO FieldDetails VALUES ('11/09/2005','Admin',null,null,'Settings','AllowModifyArticles','AllowModifyArticles','No','If checked then a Write user is allowed to modify Articles, otherwise only permitted to create Articles.','Submitter;Administrators','Everyone','CheckBox','1',null,null,null,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'AllowCreateBulletinsW') 
	INSERT INTO FieldDetails VALUES ('11/09/2005','Admin',null,null,'Settings','AllowCreateBulletinsW','AllowCreateBulletinsW','No','If checked then a Write user is allowed to create bulletins','Submitter;Administrators','Everyone','CheckBox','1',null,null,null,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Priority') 
	INSERT INTO FieldDetails VALUES ('11/09/2005','Admin',null,null,'Articles','Priority','Priority','No ',null,'Submitter;Administrators','Everyone','DropList','Low,High',null,10,10,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Priority_Search') 
	INSERT INTO FieldDetails VALUES ('11/09/2005','Admin',null,null,'Articles','Priority','Priority_Search','No',null,'Submitter;Administrators','Everyone','DropList',',Low,High',null,10,10,'field',null,null,null,null)

-- Updates

update FieldDetails set FieldValues = '10;10,15;15,20;20,25;25,30;30,50;50,100;100,ALL;-1' 
     where FieldName = 'Pagination'

update Settings set DBVersion  = '1.24' where ID=1
update Settings set AppVersion = '1.24' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

