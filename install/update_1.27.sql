-- Updates from version 1.27 to 1.28

-- Table Modifications

IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[ArticleNotes]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[ArticleNotes](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[CREATED] [datetime] NULL,
	[CREATEDBY] [varchar](50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[LASTMODIFIED] [datetime] NULL,
	[LASTMODIFIEDBY] [varchar](50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[ArticleID] [int] NOT NULL,
	[NoteType] [varchar](50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[Notes] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL,	
 CONSTRAINT [PK_ArticleNotes] PRIMARY KEY CLUSTERED 
( [ID] ASC ) ON [PRIMARY] 
)
ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
CREATE INDEX [IX_ArticleNotesID] ON [dbo].[ArticleNotes]([ArticleID]) ON [PRIMARY]
END
;

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Articles' AND C.Name='Keywords') 
	Alter Table Articles Add [Keywords] [varchar] (255) default NULL
;

exec sp_fulltext_column N'[dbo].[Articles]', N'Keywords', N'add', 1033  
;


IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[Activity]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[Activity](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[CREATED] [datetime] not NULL,
	[CREATEDBY] [varchar](50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[ItemID] [int] NULL,
	[Tbl] [varchar](30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
	[Activity] [varchar](30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL,
 CONSTRAINT [PK_ActivityID] PRIMARY KEY CLUSTERED 
( [ID] ASC ) ON [PRIMARY] 
)
ON [PRIMARY]
CREATE INDEX [IX_ActivityDate] ON [dbo].[Activity]([CREATED]) ON [PRIMARY]
CREATE INDEX [IX_ActivityItemID] ON [dbo].[Activity]([ItemID]) ON [PRIMARY]
END
;


-- Inserts

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Keywords') 
	INSERT INTO FieldDetails 
	   VALUES ('03/03/2006','sdrew',null,null,'Articles','Keywords','Keywords','No','Optional Keywords to be added to this article.',
	   'Submitter;Administrators','Everyone',
	           'TextArea',null,null,40,3,null,null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Keyword_Search') 
	INSERT INTO FieldDetails 
	   VALUES ('03/03/2006','sdrew',null,null,'Articles','Keywords','Keyword_Search','No','Search for Articles with Keyword',
	   'Submitter;Administrators','Everyone',
	           'TextBox',null,null,30,3,null,null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'ArticleNotes' and FieldName = 'Notes') 
	INSERT INTO FieldDetails 
	   VALUES ('03/03/2006','sdrew',null,null,'ArticleNotes','Notes','Notes','No','','Submitter;Administrators','Everyone',
	           'TextArea',null,null,null,null,null,null,null,null,null)
			   
if NOT EXISTS (select ID from FieldDetails where TableName = 'ArticleNotes' and FieldName = 'ArticleID') 
	INSERT INTO FieldDetails 
		VALUES('03/03/2006','sdrew',null,null,'ArticleNotes','ArticleID','ArticleID','No','','Submitter;Administrators','Everyone',
				'TextBox',null,null,null,null,null,null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'ArticleNotes' and FieldName = 'NoteType') 
	INSERT INTO FieldDetails 
	VALUES('03/03/2006','sdrew',null,null,'ArticleNotes','NoteType','NoteType','Yes',null,'Submitter;Administrators','Everyone',
				'DropList','Comment,Action Required,Action Completed',null,null,null,null,null,null,null,null)	

if NOT EXISTS (select ID from FieldDetails where TableName = 'Searches' and FieldName = 'StartDate') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Searches','CREATED','StartDate','No','Starting Date','Submitter;Administrators','Everyone',
	      'Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Searches' and FieldName = 'EndDate') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Searches','CREATED','EndDate','No','Ending Date','Submitter;Administrators','Everyone',
	      'Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Searches' and FieldName = 'Account') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Searches','CREATEDBY','Account','No','Account Name of Person performing the search','Submitter;Administrators','Everyone',
	      'TextBox',null,null,20,50,'field',null,null,null,null)


if NOT EXISTS (select ID from FieldDetails where TableName = 'Hits' and FieldName = 'StartDate') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Hits','CREATED','StartDate','No','Starting Date',
	  'Submitter;Administrators','Everyone',
	      'Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Hits' and FieldName = 'EndDate') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Hits','CREATED','EndDate','No','Ending Date',
	  'Submitter;Administrators','Everyone',
	      'Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Hits' and FieldName = 'Account') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Hits','CREATEDBY','Account','No',
	  'Account Name of Person who read the Article','Submitter;Administrators','Everyone',
	      'TextBox',null,null,20,50,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Title_Search') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Articles','Title','Title_Search','No',
	  'Search for Articles with this word or phrase in the title','Submitter;Administrators','Everyone',
	      'TextBox',null,null,30,50,'field',null,null,null,null)


if NOT EXISTS (select ID from FieldDetails where TableName = 'Activity' and FieldName = 'StartDate') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Activity','CREATED','StartDate','No','Starting Date','Submitter;Administrators','Everyone',
	      'Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Activity' and FieldName = 'EndDate') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Activity','CREATED','EndDate','No','Ending Date','Submitter;Administrators','Everyone',
	      'Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Activity' and FieldName = 'Account') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Activity','CREATEDBY','Account','No','Account Name of Person performing the action','Submitter;Administrators','Everyone',
	      'TextBox',null,null,20,50,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Activity' and FieldName = 'ItemID') 
	INSERT INTO FieldDetails 
	  VALUES ('03/03/2006','sdrew',null,null,'Activity','ItemID','ItemID','No','Item ID','Submitter;Administrators','Everyone',
	      'TextBox',null,null,20,50,'field',null,null,null,null)




-- Updates
update Settings set DBVersion  = '1.28' where ID=1
update Settings set AppVersion = '1.28' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

