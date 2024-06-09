-- Updates from version 1.25 to 1.26

-- Table Modifications

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[MessageHits]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[MessageHits] (
	[ID] [int] IDENTITY (1, 1) NOT NULL ,
	[CREATED] [datetime] NULL ,
	[CREATEDBY] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[MessageID] [int] NULL 
) ON [PRIMARY]
END
;

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[MessageHits]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
ALTER TABLE [dbo].[MessageHits] WITH NOCHECK ADD 
	CONSTRAINT [MessageHits] PRIMARY KEY  CLUSTERED 
	(
		[ID]
	)  ON [PRIMARY] 
CREATE INDEX [IX_BHITSCREATEDBY] ON [dbo].[MessageHits]([CREATEDBY]) ON [PRIMARY]
CREATE INDEX [IX_BULLETINID] ON [dbo].[MessageHits]([ArticleID]) ON [PRIMARY]
END
;

-- Inserts


-- Updates

update Settings set DBVersion  = '1.26' where ID=1
update Settings set AppVersion = '1.26' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

