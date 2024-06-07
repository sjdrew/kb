/* CREATES DATABASE in V1.20 format */
/* OSQL EXample: */
/* osal -U sa -P pass -Q 'drop database KB' */
/* osql -U sa -P pass -Q 'Create Database KB' */
/* osql -U sa -P pass -i -d KB install.sql  */

/* When testing ... */
/* use master  */
/* ; /* go */ */
/* drop database KB  */
/* ; /* go */ */
/* Create Database KB */
/* ; /* go */ */
/* use KB */


declare @DBNAME varchar(256)
set @DBNAME = DB_NAME()

IF  EXISTS (SELECT * FROM sys.server_principals WHERE name = N'KBApp')
	DROP LOGIN [KBApp];

exec sp_addlogin KBApp, kb$Zz01$02;
exec sp_adduser KBApp, KBApp, db_owner;
exec sp_addrolemember N'db_owner', N'KBApp';


/****** Object:  Table [dbo].[Settings]    Script Date: 7/12/2005 2:55:51 PM ******/
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[Settings]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[Settings]
; /* go */


/****** Object:  Table [dbo].[Areas]    Script Date: 7/12/2005 2:55:51 PM ******/
CREATE TABLE [dbo].[Areas] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Name] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL 
) ON [PRIMARY]
; /* go */

ALTER TABLE [dbo].[Areas] WITH NOCHECK ADD 
	CONSTRAINT [PK_Areas] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */


/****** Object:  Table [dbo].[ArticleAttachments]    Script Date: 7/12/2005 2:55:52 PM ******/
CREATE TABLE [dbo].[ArticleAttachments] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ArticleID] [int] NOT NULL ,
	[Size] [int] NULL ,
	[DocType] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Filename] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Attachment] [image] NULL,
	[AsContent] [int] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
; /* go */

ALTER TABLE [dbo].[ArticleAttachments] WITH NOCHECK ADD 
	CONSTRAINT [PK_Attachments] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */
CREATE  INDEX [IX_ArticleID] ON [dbo].[ArticleAttachments]([ArticleID]) ON [PRIMARY]
; /* go */


/****** Object:  Table [dbo].[Articles]    Script Date: 7/12/2005 2:55:52 PM ******/
CREATE TABLE [dbo].[Articles] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[GroupID] [int] NULL ,
	[Contact1] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Contact2] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ReviewBy] [datetime] NULL ,
	[LastReviewed] [datetime] NULL ,
	[LastReviewedBy] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Expires] [datetime] NULL ,
	[Hits] [int] NULL ,
	[LastHitBy] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ViewableBy] [int] NULL ,
	[Title] [varchar] (150) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Area] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Product] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Type] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Content] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ContentLastModified] [datetime] NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
; /* go */

ALTER TABLE [dbo].[Articles] WITH NOCHECK ADD 
	CONSTRAINT [PK_Articles] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */

 CREATE  UNIQUE  INDEX [IX_Articles] ON [dbo].[Articles]([ID]) ON [PRIMARY]
; /* go */

 CREATE  INDEX [IX_GroupID] ON [dbo].[Articles]([GroupID]) ON [PRIMARY]
; /* go */

 CREATE  INDEX [IX_Type] ON [dbo].[Articles]([Type]) ON [PRIMARY]
; /* go */


/****** Object:  Table [dbo].[ArchiveArticles]  Date: 8/12/2005 ******/
CREATE TABLE [dbo].[ArchiveArticles] (
	[ID] [decimal] (8,4) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[GroupID] [int] NULL ,
	[Contact1] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Contact2] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ReviewBy] [datetime] NULL ,
	[LastReviewed] [datetime] NULL ,
	[LastReviewedBy] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Expires] [datetime] NULL ,
	[Hits] [int] NULL ,
	[LastHitBy] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ViewableBy] [int] NULL ,
	[Title] [varchar] (150) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Area] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Product] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Type] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Content] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ContentLastModified] [datetime] NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
; /* go */
 CREATE  UNIQUE  INDEX [IX_ArchiveArticles] ON [dbo].[ArchiveArticles]([ID]) ON [PRIMARY]
; /* go */

ALTER TABLE [dbo].[ArchiveArticles] WITH NOCHECK ADD 
	CONSTRAINT [PK_ArchiveArticles] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */

CREATE TABLE [dbo].[ArchiveArticleAttachments] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ArchiveArticleID] [decimal] (8,4) NOT NULL ,
	[Size] [int] NULL ,
	[DocType] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Filename] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Attachment] [image] NULL, 
	[AsContent] [int] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
; /* go */
 CREATE INDEX [IX_ArchiveArticleID] ON [dbo].[ArchiveArticleAttachments]([ArchiveArticleID]) ON [PRIMARY]
; /* go */

ALTER TABLE [dbo].[ArchiveArticleAttachments] WITH NOCHECK ADD 
	CONSTRAINT [PK_ArchiveAttachments] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */

CREATE TABLE [dbo].[AuditTrail] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ArticleID] [int] NOT NULL ,
	[Trail] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL 
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[AuditTrail] WITH NOCHECK ADD 
	CONSTRAINT [PK_AuditTrail] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */
 CREATE INDEX [IX_AuditTrail] ON [dbo].[AuditTrail]([ArticleID]) ON [PRIMARY]
; /* go */

CREATE TABLE [dbo].[FieldDetails] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[TableName] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[ColumnName] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[FieldName] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[Required] [varchar] (3) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[HelpText] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[RWGroups] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[RGroups] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Type] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[FieldValues] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[RadioGroup] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[HTMLSize] [int] NULL ,
	[MaxLength] [int] NULL ,
	[Style] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[TagParams] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Query] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[QFieldText] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[QFieldValue] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
; /* go */

ALTER TABLE [dbo].[FieldDetails] WITH NOCHECK ADD 
	CONSTRAINT [PK_FieldDetails] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */

 CREATE INDEX [IX_FieldName] ON [dbo].[FieldDetails]([FieldName]) ON [PRIMARY]
; /* go */



CREATE TABLE [dbo].[Groups] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[GroupID] [int] NOT NULL 
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[Groups] WITH NOCHECK ADD 
	CONSTRAINT [PK_Groups] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */
CREATE  UNIQUE  INDEX [IX_GroupID] ON [dbo].[Groups]([GroupID]) ON [PRIMARY]
; /* go */

CREATE TABLE [dbo].[Hits] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ArticleID] [int] NULL 
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[Hits] WITH NOCHECK ADD 
	CONSTRAINT [PK_Hits] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */
CREATE INDEX [IX_CREATEDBY] ON [dbo].[Hits]([CREATEDBY]) ON [PRIMARY]
; /* go */
CREATE INDEX [IX_ArticleID] ON [dbo].[Hits]([ArticleID]) ON [PRIMARY]
; /* go */

CREATE TABLE [dbo].[Messages] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[GroupID] [int] NULL ,
	[Subject] [varchar] (200) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Author] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[DisplayUntil] [datetime] NULL ,
	[Message] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[StartTime] [datetime] NULL ,
	[EndTime] [datetime] NULL ,
	[ServiceName] [varchar] (60) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ServiceType] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Type] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[TicketNumber] [varchar] (15) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Escalated] [varchar] (3) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Prompter] [varchar] (3) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[Messages] WITH NOCHECK ADD 
	CONSTRAINT [PK_Messages] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */
 CREATE INDEX [IX_DisplayUntil] ON [dbo].[Messages]([DisplayUntil]) ON [PRIMARY]
; /* go */
 CREATE INDEX [IX_Type] ON [dbo].[Messages]([Type]) ON [PRIMARY]
; /* go */

CREATE TABLE [dbo].[Related] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[IDA] [int] NOT NULL ,
	[IDB] [int] NOT NULL 
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[Related] WITH NOCHECK ADD 
	CONSTRAINT [PK_Related] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */


CREATE TABLE [dbo].[Searches] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Search] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Matches] [int] NULL ,
	[SearchType] [varchar] (10) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[Searches] WITH NOCHECK ADD 
	CONSTRAINT [PK_Searches] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */
 CREATE INDEX [IX_Searches] ON [dbo].[Searches]([CREATED]) ON [PRIMARY]
; /* go */

CREATE TABLE [dbo].[Settings] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[AppName] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[PrivMode] [varchar] (20) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[AuthenticationMode] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[HomePageConfig] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[SMTPServer] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[NotifyEmail] [varchar] (128) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[FullTextBackground] [varchar] (1) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[SearchHistoryDays] [int] NULL ,
	[HitsHistoryDays] [int] NULL ,
	[LastModifyLock] [int] NULL ,
	[DontLogAdmin] [varchar] (1) COLLATE SQL_Latin1_General_CP1_CI_AS NULL, 
	[FiltersOnHomePage] [int] NULL ,
	[AllowCreateBulletins] [int] NULL ,
	[ArticleVersions] [int] NULL,
	[DBVersion] [decimal](4,2) default '1.20',
	[DBLastUpdate] [datetime] NULL,
	[AppVersion] [decimal](4,2) default '1.20'
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[Settings] WITH NOCHECK ADD 
	CONSTRAINT [PK_Settings] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */


CREATE TABLE [dbo].[Types] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Name] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL 
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[Types] WITH NOCHECK ADD 
	CONSTRAINT [PK_Types] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */


CREATE TABLE [dbo].[users] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LASTMODIFIED] [datetime] NULL ,
	[LASTMODIFIEDBY] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[STATUS] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Username] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[Password] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Groups] [varchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Email] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LastLogin] [datetime] NULL ,
	[FirstName] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[LastName] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Phone] [varchar] (30) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Priv] [int] NULL ,
	[Pagination] [int] not NULL DEFAULT 50 ,
	[NotesOrder] [varchar] (10) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL DEFAULT 'asc' ,
	[SearchMode] [varchar] (20) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Previews] [varchar] (3) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[NotifyUpdated] [varchar] (5) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[NotifyNew] [varchar] (5) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[NotifySubmitted] [varchar] (5) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
; /* go */
ALTER TABLE [dbo].[users] WITH NOCHECK ADD 
	CONSTRAINT [PK_users] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
; /* go */
CREATE  INDEX [IX_users] ON [dbo].[users]([ID]) ON [PRIMARY]
; /* go */
CREATE  UNIQUE  INDEX [IX_Username] ON [dbo].[users]([Username]) ON [PRIMARY]
; /* go */


/* 
 * Enable Full Text Catalogs, first remove if already there.
 */



exec sp_tableoption N'Articles', 'text in row', 'ON'
exec sp_tableoption N'ArticleAttachments', 'text in row', 'ON'
 
if (select DATABASEPROPERTY(DB_NAME(), N'IsFullTextEnabled')) <> 1 
exec sp_fulltext_database N'enable' 
;

if exists (select * from dbo.sysfulltextcatalogs where name = DB_NAME())
BEGIN
	exec sp_fulltext_catalog @DBNAME, N'drop'
END
;

if not exists (select * from dbo.sysfulltextcatalogs where name = DB_NAME())
BEGIN
	exec sp_fulltext_catalog @DBNAME, N'create'
END
;

exec sp_fulltext_table N'[dbo].[ArticleAttachments]', N'create', @DBNAME, N'PK_Attachments';
exec sp_fulltext_column N'[dbo].[ArticleAttachments]', N'Filename', N'add', 1033  ;
exec sp_fulltext_column N'[dbo].[ArticleAttachments]', N'Attachment', N'add', 1033, N'DocType' ;
exec sp_fulltext_table N'[dbo].[ArticleAttachments]', N'activate'  ;
exec sp_fulltext_table N'[dbo].[Articles]', N'create', @DBNAME, N'IX_Articles'; 
exec sp_fulltext_column N'[dbo].[Articles]', N'Title', N'add', 1033  ;
exec sp_fulltext_column N'[dbo].[Articles]', N'Product', N'add', 1033  ;
exec sp_fulltext_column N'[dbo].[Articles]', N'Type', N'add', 1033  ;
exec sp_fulltext_column N'[dbo].[Articles]', N'Content', N'add', 1033 ;
exec sp_fulltext_table N'[dbo].[Articles]', N'activate';
Exec sp_fulltext_table 'Articles', 'start_change_tracking';
EXEC sp_fulltext_table 'Articles', 'start_background_updateindex';
Exec sp_fulltext_table 'ArticleAttachments', 'start_change_tracking';
EXEC sp_fulltext_table 'ArticleAttachments', 'start_background_updateindex';


/* End Fulltext */



INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','10/18/2004','SDrew','users','Username','Username','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,40,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','Password','Password','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','LastLogin','LastLogin','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','10/21/2004','SDrew','users','FirstName','FirstName','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','LastName','LastName','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','Phone','Phone','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','11/09/2004','sdrew','users','Pagination','Pagination','No',null,'Submitter;Administrators','Everyone','DropList','10,15,20,25,30,50,100',null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'users','NotesOrder','NotesOrder','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','ArticleID','ArticleID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','Name','Name','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Attachments','Content','Content','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,32,32,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','11/08/2004','sdrew','Articles','ID','ID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Articles','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','10/18/2004','SDrew','Articles','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin',null,null,'Articles','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','10/18/2004','SDrew','Articles','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','11/07/2004','sdrew','Articles','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','DropList','Pending Review,Active,Obsolete',null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','11/08/2004','sdrew','Articles','Title','Title','Yes','A Short descriptive title of the article','Submitters;Administrators','Everyone','TextBox',null,null,90,150,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','11/08/2004','sdrew','Articles','Product','Product','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,35,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/03/2004','Admin','01/12/2005','sdrew','Articles','Content','Content','No',null,'Submitter;Administrators','Everyone','TextArea',null,null,70,35,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/04/2004','Admin','10/18/2004','SDrew','Articles','Area','Area','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,35,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/04/2004','Admin','04/07/2005','sdrew','Articles','Type','Type','No',null,'Submitter;Administrators','Everyone','DropList',null,null,35,100,'field','style=''width:175px''','select distinct Name from Types where STATUS=''Active'' order by Name','Name','Name')
INSERT INTO FieldDetails VALUES ('10/04/2004','Admin','10/04/2004','Admin','Articles','ReviewBy','ReviewBy','No ',null,'Submitter;Administrators','Everyone','Date',null,null,11,11,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/04/2004','Admin','11/08/2004','sdrew','Articles','Expires','Expires','No',null,'Submitter;Administrators','Everyone','Date',null,null,11,11,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/04/2004','Admin',null,null,'Articles','Hits','Hits','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/08/2004','Admin',null,null,'Articles','LastHitBy','LastHitBy','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/10/2004','Admin','10/23/2004','SDrew','users','Priv','Priv','No ',null,'Submitter;Administrators','Everyone','DropList','Guest;1,Support;2,Editor;4,Admin;8',null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/10/2004','Admin','11/08/2004','sdrew','Articles','ViewableBy','ViewableBy','No',null,'Submitter;Administrators','Everyone','DropList','Public;1,Support;2,Editors;4,Administrators;8',null,10,10,'field','style=''width:125px''',null,null,null)
INSERT INTO FieldDetails VALUES ('10/10/2004','Admin',null,null,'Articles','LastReviewed','LastReviewed','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/10/2004','Admin',null,null,'Articles','LastReviewedBy','LastReviewedBy','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin','11/08/2004','sdrew','Articles','STATUS','STATUS_Search','No',null,'Submitter;Administrators','Everyone','DropList',',Pending Review,Active,Obsolete',null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin',null,null,'ArticleAttachments','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin',null,null,'ArticleAttachments','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin',null,null,'ArticleAttachments','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin',null,null,'ArticleAttachments','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin',null,null,'ArticleAttachments','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin',null,null,'ArticleAttachments','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/13/2004','Admin',null,null,'ArticleAttachments','ArticleID','ArticleID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/14/2004','Admin',null,null,'ArticleAttachments','Size','Size','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/14/2004','Admin',null,null,'ArticleAttachments','DocType','DocType','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/14/2004','Admin',null,null,'ArticleAttachments','Attachment','Attachment','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,16,16,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/15/2004','Admin',null,null,'ArticleAttachments','Filename','Filename','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'users','Email','Email','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Areas','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Areas','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Areas','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Areas','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Areas','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew','10/18/2004','SDrew','Areas','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','DropList','Active,Inactive',null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Areas','Name','Name','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Types','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Types','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Types','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Types','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Types','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew','10/18/2004','SDrew','Types','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','DropList','Active,Inactive',null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/18/2004','SDrew',null,null,'Types','Name','Name','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Groups','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Groups','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Groups','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Groups','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Groups','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew','10/21/2004','SDrew','Groups','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','DropList','Active,Inactive',null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Groups','Name','Name','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Groups','GroupID','GroupID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew','10/21/2004','SDrew','Groups','Name','GroupSelect','No ',null,'Submitter;Administrators','Everyone','DropList',null,null,50,50,'field','style=''width:260px''','select * from Groups where STATUS=''Active'' order by Name','Name','GroupID')
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','STATUS','STATUS','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','AppName','AppName','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew','10/22/2004','SDrew','Settings','PrivMode','PrivMode','No ',null,'Submitter;Administrators','Everyone','DropList','Simple,Group',null,20,20,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew','10/22/2004','SDrew','Settings','AuthenticationMode','AuthenticationMode','No ',null,'Submitter;Administrators','Everyone','DropList','Windows NT Authentication;NT,Local Account;Local',null,30,30,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'Settings','HomePageConfig','HomePageConfig','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew','12/10/2004','sdrew','Settings','SMTPServer','SMTPServer','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew',null,null,'users','Groups','Groups','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,255,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew','11/01/2004','SDrew','Articles','GroupID','GroupID','Yes',null,'Submitter;Administrators','Everyone','DropList',null,null,10,10,'field','style=''width:200px''','select * from Groups where STATUS = ''Active'' order by Name','Name','GroupID')
INSERT INTO FieldDetails VALUES ('10/22/2004','SDrew','10/31/2004','SDrew','Articles','ViewableBy','ViewableByG','Yes',null,'Submitter;Administrators','Everyone','DropList','Public;1,Group Members;2,Editors;4,Administrators;8',null,10,10,'field','style=''width:175px''',null,null,null)
INSERT INTO FieldDetails VALUES ('10/31/2004','SDrew','11/08/2004','sdrew','Articles','Contact1','Contact1','No','Enter a contact name','Submitter;Administrators','Everyone','TextBox',null,null,25,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('10/31/2004','SDrew','11/08/2004','sdrew','Articles','Contact2','Contact2','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,25,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/04/2004','SDrew',null,null,'Related','ID','ID','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/04/2004','SDrew',null,null,'Related','CREATED','CREATED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/04/2004','SDrew',null,null,'Related','CREATEDBY','CREATEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/04/2004','SDrew',null,null,'Related','LASTMODIFIED','LASTMODIFIED','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/04/2004','SDrew',null,null,'Related','LASTMODIFIEDBY','LASTMODIFIEDBY','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/04/2004','SDrew',null,null,'Related','IDA','IDA','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/04/2004','SDrew',null,null,'Related','IDB','IDB','No ',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/05/2004','SDrew',null,null,'Settings','NotifyEmail','NotifyEmail','No ','Person to notify of any application detected errors','Submitter;Administrators','Everyone','TextBox',null,null,50,128,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/05/2004','SDrew',null,null,'Settings','FullTextBackground','FullTextBackground','No ',null,'Submitter;Administrators','Everyone','CheckBox','1',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/08/2004','sdrew','11/08/2004','sdrew','Articles','Type','Type_S','No','The Type of Article','Submitter;Administrators','Everyone','DropList',null,null,35,100,'field','style=''width:130px''','select distinct Name from Types where STATUS=''Active'' order by Name','Name','Name')
INSERT INTO FieldDetails VALUES ('11/08/2004','sdrew',null,null,'Articles','Product','Product_S','No ','','Submitter;Administrators','Everyone','TextBox','','',30,100,'field','','','','')
INSERT INTO FieldDetails VALUES ('11/09/2004','sdrew','11/09/2004','sdrew','users','SearchMode','SearchMode','No',null,'Submitter;Administrators','Everyone','DropList','English Query,Strict',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/09/2004','sdrew','11/09/2004','sdrew','users','Previews','Previews','No','Display preview of article in search results','Submitter;Administrators','Everyone','DropList','Yes,No',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew',null,null,'Messages','ID','ID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew',null,null,'Messages','CREATED','CREATED','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew',null,null,'Messages','CREATEDBY','CREATEDBY','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew',null,null,'Messages','LASTMODIFIED','LASTMODIFIED','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew',null,null,'Messages','LASTMODIFIEDBY','LASTMODIFIEDBY','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew','11/15/2004','sdrew','Messages','STATUS','STATUS','No',null,'Submitter;Administrators','Everyone','DropList','Visible,Hidden',null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew',null,null,'Messages','GroupID','GroupID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew','11/15/2004','sdrew','Messages','Subject','Subject','Yes',null,'Submitter;Administrators','Everyone','TextBox',null,null,70,200,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew',null,null,'Messages','Author','Author','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,100,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew','11/15/2004','sdrew','Messages','DisplayUntil','DisplayUntil','No',null,'Submitter;Administrators','Everyone','Date',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/15/2004','sdrew','11/15/2004','sdrew','Messages','Message','Message','No',null,'Submitter;Administrators','Everyone','TextArea',null,null,75,16,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/23/2004','sdrew',null,null,'Settings','SearchHistoryDays','SearchHistoryDays','No','Number of Days of Search history kept','Submitter;Administrators','Everyone','TextBox',null,null,4,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/23/2004','sdrew',null,null,'Settings','HitsHistoryDays','HitsHistoryDays','No','Number of Days of Article Hits history kept','Submitter;Administrators','Everyone','TextBox',null,null,4,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/25/2004','sdrew',null,null,'Settings','LastModifyLock','LastModifyLock','No','If set any modifications to articles will not affect their last modified date.','Submitter;Administrators','Everyone','CheckBox','1',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Articles','ContentLastModified','ContentLastModified','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Messages','StartTime','StartTime','No',null,'Submitter;Administrators','Everyone','Date',null,null,16,16,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Messages','EndTime','EndTime','No',null,'Submitter;Administrators','Everyone','Date',null,null,16,16,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Messages','ServiceName','ServiceName','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,30,60,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Messages','ServiceType','ServiceType','No',null,'Submitter;Administrators','Everyone','DropList','Application,Database,Network,Power,Server',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Messages','Type','Type','Yes',null,'Submitter;Administrators','Everyone','DropList','Advisory,Information,MUI-Open,MUI-Closed',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Messages','TicketNumber','TicketNumber','No','Enter only 1 Ticket number','Submitter;Administrators','Everyone','TextBox',null,null,20,15,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('11/30/2004','sdrew',null,null,'Messages','Escalated','Escalated','No','If Escalated set to Yes (ie TAM notified)','Submitter;Administrators','Everyone','DropList','No,Yes',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('12/01/2004','sdrew',null,null,'Messages','Prompter','Prompter','No','Indicates that the Phone prompter was active during the Start/End time','Submitter;Administrators','Everyone','DropList','No,Yes',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('12/10/2004','sdrew',null,null,'Settings','DontLogAdmin','DontLogAdmin','No','If checked when an Admin performs a Search or hits an article, it will not be logged','Submitter;Administrators','Everyone','CheckBox','1',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Searches','ID','ID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Searches','CREATED','CREATED','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Searches','CREATEDBY','CREATEDBY','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Searches','Search','Search','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,255,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Searches','Matches','Matches','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Searches','SearchType','SearchType','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Hits','ID','ID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Hits','CREATED','CREATED','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Hits','CREATEDBY','CREATEDBY','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('02/03/2005','sdrew',null,null,'Hits','ArticleID','ArticleID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew','05/12/2005','sdrew','users','NotifyUpdated','NotifyUpdated','No','If chedked you will be notified when any article is modified for a group that you are a member of','Submitter;Administrators','Everyone','CheckBox','Yes',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'users','NotifyNew','NotifyNew','No','If checked when a new article is submitted for one of your groups you will receive an email notification.','Submitter;Administrators','Everyone','CheckBox','Yes',null,null,null,null,null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'users','NotifySubmitted','NotifySubmitted','No','If chedked when any article that you had submitted or last reviewed is updated, you will receive an email notification.','Submitter;Administrators','Everyone','CheckBox','Yes',null,null,null,null,null,null,null,null)

INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'AuditTrail','ID','ID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'AuditTrail','CREATED','CREATED','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'AuditTrail','CREATEDBY','CREATEDBY','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'AuditTrail','MODIFIED','MODIFIED','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,8,8,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'AuditTrail','LASTMODIFIED','LASTMODIFIED',null,'No','Submitter;Administrators','Everyone','TextBox',null,null,50,50,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'AuditTrail','ArticleID','ArticleID','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,10,10,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'AuditTrail','Trail','Trail','No',null,'Submitter;Administrators','Everyone','TextBox',null,null,50,255,'field',null,null,null,null)

INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'Settings','FiltersOnHomePage','FiltersOnHomePage','No','If checked then Advanced Search filters are displayed on home page','Submitter;Administrators','Everyone','CheckBox','1',null,null,null,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'Settings','AllowCreateBulletins','AllowCreateBulletins','No','If checked then a Read user is allowed to create bulletins','Submitter;Administrators','Everyone','CheckBox','1',null,null,null,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'Settings','ArticleVersions','ArticleVersions','No','The Number of archived versions to retain','Submitter;Administrators','Everyone','TextBox',null,null,4,null,'field',null,null,null,null)

INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'Settings','DBVersion','DBVersion','No','Database Structure version','Submitter;Administrators','Everyone','TextBox',null,null,4,null,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'Settings','DBLastUpdate','DBLastUpdate','No','Date of Last structure update','Submitter;Administrators','Everyone','TextBox',null,null,4,null,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('05/12/2005','sdrew',null,null,'Settings','AppVersion','AppVersion','No','Application Version','Submitter;Administrators','Everyone','TextBox',null,null,4,null,'field',null,null,null,null)

INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LASTMODIFIED','Modified_Since','No','Select articles whos Modified date is equal to or greater than the date specified','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LASTMODIFIED','Modified_Before','No','Select articles whos Modified date is less than or equal to the date specified or if Blank then Todays date','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LastReviewed','Reviewed_Since','No','Select articles whos Last Reviewed date occurs on since the date specified','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)
INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LastReviewed','Reviewed_Before','No','Select articles whos Last Reviewed date occurs on or before the date specified or if Blank then Todays date','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)


INSERT INTO users (CREATED,CREATEDBY,STATUS,Username,[Password],Groups) 
	VALUES ('01/01/2005','Install','Active','Admin','Admin','1:A');


/* Default Types */
INSERT INTO Types VALUES (null,null,null,null,'Active','Procedure');
INSERT INTO Types VALUES (null,null,null,null,'Active','Error Messages');
INSERT INTO Types VALUES (null,null,null,null,'Active','Information');
INSERT INTO Types VALUES (null,null,null,null,'Active','Install');
INSERT INTO Types VALUES (null,null,null,null,'Active','Account Maintenance');
INSERT INTO Types VALUES (null,null,null,null,'Active','Resolution');
INSERT INTO Types VALUES (null,null,null,null,'Active','Security');
INSERT INTO Types VALUES (null,null,null,null,'Active','Escalations');
INSERT INTO Types VALUES (null,null,null,null,'Active','How To');

/* Default Groups */
INSERT INTO Groups VALUES ('10/22/2004','SDrew','10/22/2004','SDrew','Active','Administrators',1);
INSERT INTO Groups VALUES ('11/09/2004','sdrew',null,null,'Active','Public',10);

/* Default Settings */
INSERT INTO Settings (
     CREATED,
	 CREATEDBY,
	 AppName,
	 PrivMode,
	 AuthenticationMode,
	 HomePageConfig,
	 SMTPServer,
	 NotifyEmail,
	 FullTextBackground,
	 SearchHistoryDays,
	 HitsHistoryDays,
	 LastModifyLock,
	 DontLogAdmin, 
	 FiltersOnHomePage,
	 AllowCreateBulletins,
	 ArticleVersions,
	 DBVersion,
	 DBLastUpdate,
	 AppVersion)
     VALUES ('10/22/2004','SDrew','Service Desk Knowledge Base','Group','Local',null,'mail',
	         null,'1',100,400,0,0,0,1,5,'1.20',null,'1.20')
;

