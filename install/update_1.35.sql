-- Updates from version 1.34 to 1.35

-- Table Modifications

EXEC sp_fulltext_column       -- Drop your column from full text search here
@tabname =  'Articles' , 
@colname =  'Content' , 
@action =  'drop' ;

alter table Articles alter column [Content] NVARCHAR(MAX) COLLATE Latin1_General_100_CI_AS_KS_SC_UTF8;

EXEC sp_fulltext_column       -- add back
@tabname =  'Articles' , 
@colname =  'Content' , 
@action =  'add' ;