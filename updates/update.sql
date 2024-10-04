
EXEC sp_fulltext_column       -- Drop your column from full text search here
@tabname =  'Articles' , 
@colname =  'Content' , 
@action =  'drop' ;

alter table Articles alter column [Content] NVARCHAR(MAX) COLLATE LATIN1_GENERAL_100_CI_AS_SC_UTF8;

EXEC sp_fulltext_column       -- add back
@tabname =  'Articles' , 
@colname =  'Content' , 
@action =  'add' ;


-- Latin1_General_100_BIN2_UTF8;
-- Latin1_General_100_CI_AS_KS_SC_UTF8;
-- LATIN1_GENERAL_100_CI_AS_SC_UTF8.