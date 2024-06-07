-- Updates from version 1.29 to 1.30

-- Table Modifications

-- Add Custom1 table used for custom droplist
-- Add Custom1 varchar field to articles
-- Add Custom2 date fields to articles

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[Custom1]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 CREATE TABLE [dbo].[Custom1] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Name] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL 
) ON [PRIMARY]
;

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[Custom1]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 ALTER TABLE [dbo].[Custom1] WITH NOCHECK ADD 
	CONSTRAINT [PK_Custom1] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Articles' AND C.Name='Custom1') 
	Alter Table Articles Add [Custom1] [varchar] (50) default NULL
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='ArchiveArticles' AND C.Name='Custom1') 
	Alter Table ArchiveArticles Add [Custom1] [varchar] (50) default NULL
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Articles' AND C.Name='Custom2') 
	Alter Table Articles Add [Custom2] [datetime] default NULL
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='ArchiveArticles' AND C.Name='Custom2') 
	Alter Table ArchiveArticles Add [Custom2] [datetime]  default NULL
;



IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='users' AND C.Name='NotifyTechnicalReview') 
	Alter Table users Add [NotifyTechnicalReview] [varchar] (3) default NULL
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='users' AND C.Name='NotifyContentReview') 
	Alter Table users Add [NotifyContentReview] [varchar] (3) default NULL
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='users' AND C.Name='GroupID') 
	Alter Table users Add [GroupID] [int] default NULL
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='Custom1Label') 
	Alter Table Settings Add [Custom1Label] [varchar] (30) NULL 
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='Custom2Label') 
	Alter Table Settings Add [Custom2Label] [varchar] (30) NULL 
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='DefReviewPeriod') 
	Alter Table Settings Add [DefReviewPeriod] [int] NULL 
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='ReviewMode') 
	Alter Table Settings Add [ReviewMode] [varchar] (25) NULL 
;

-- Inserts

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Custom1') 
  INSERT INTO FieldDetails VALUES ('06/20/2006','sdrew',NULL,NULL,'Articles','Custom1','Custom1','No',null,'Submitter;Administrators','Everyone','DropList',null,null,50,100,'field','style=''width:175px''','select distinct Name from Custom1 where STATUS=''Active'' order by Name','Name','Name')

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Custom2') 
  INSERT INTO FieldDetails VALUES ('06/20/2006','sdrew',NULL,NULL,'Articles','Custom2','Custom2','No',null,'Submitter;Administrators','Everyone','Date',null,null,10,10,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Custom2_Since') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','Admin',null,null,'Articles','Custom2','Custom2_Since','No','Select articles based on the date range specified','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Custom2_Before') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','Admin',null,null,'Articles','Custom2','Custom2_Before','No','Select articles based on the date range specified','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)



if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'Custom1Label') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','Admin',null,null,'Settings','Custom1Label','Custom1Label','No ','Label Name for the Custom Droplist field used on Articles','Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'Custom2Label') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','Admin',null,null,'Settings','Custom2Label','Custom2Label','No ','Label Name for the Custom Date field used on Articles','Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'ReviewMode') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','Admin',null,null,'Settings','ReviewMode','ReviewMode','No ','Method for Article Reviews','Submitter;Administrators','Everyone','DropList','Technical and Content,Content Only',null,30,30,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'DefReviewPeriod') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','Admin',null,null,'Settings','DefReviewPeriod','DefReviewPeriod','No','Specify number of Months or blank to leave the Review By field empty by default','Submitter;Administrators','Everyone','TextBox','',null,6,6,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'users' and FieldName = 'NotifyTechnicalReview') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','sdrew',null,null,'users','NotifyTechnicalReview','NotifyTechnicalReview','No','If checked, you will receive Email notifications when an Article requires Technical Review','Submitter;Administrators','Everyone','CheckBox','Yes',null,null,null,null,null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'users' and FieldName = 'NotifyContentReview') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','sdrew',null,null,'users','NotifyContentReview','NotifyContentReview','No','If checked, you will receive Email notifications when an Article requires Content Review','Submitter;Administrators','Everyone','CheckBox','Yes',null,null,null,null,null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Custom1' and FieldName = 'ID') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','SDrew',null,null,'Custom1','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Custom1' and FieldName = 'CREATED') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','SDrew',null,null,'Custom1','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Custom1' and FieldName = 'CREATEDBY') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','SDrew',null,null,'Custom1','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Custom1' and FieldName = 'LASTMODIFIED') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','SDrew',null,null,'Custom1','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Custom1' and FieldName = 'LASTMODIFIEDBY') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','SDrew',null,null,'Custom1','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Custom1' and FieldName = 'STATUS') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','SDrew','10/18/2004','SDrew','Custom1','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','DropList','Active,Inactive',null,30,30,'field',null,null,null,null)
if NOT EXISTS (select ID from FieldDetails where TableName = 'Custom1' and FieldName = 'Name') 
	INSERT INTO FieldDetails VALUES ('06/20/2006','SDrew',null,null,'Custom1','Name','Name','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)


-- Updates

update FieldDetails set FieldValues = 'Compose,Pending Technical Review,Pending Content Review,Active,Obsolete'
     where FieldName = 'STATUS' and TableName = 'Articles'
update FieldDetails set FieldValues = ',Compose,Pending Technical Review,Pending Content Review,Active,Obsolete'
     where FieldName = 'STATUS_Search' and TableName = 'Articles'
	 
update Articles set STATUS = 'Pending Content Review' where STATUS = 'Pending Review'
update ArchiveArticles set STATUS = 'Pending Content Review' where STATUS = 'Pending Review'
;

update Settings set DBVersion  = '1.30' where ID=1
update Settings set AppVersion = '1.30' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1

;

