-- Updates from version 1.20 to 1.21
-- Inserts

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Modified_Since') 
INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LASTMODIFIED','Modified_Since','No','Select articles whos Modified date is equal to or greater than the date specified','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Modified_Before') 
INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LASTMODIFIED','Modified_Before','No','Select articles whos Modified date is less than or equal to the date specified or if Blank then Todays date','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Reviewed_Since') 
INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LastReviewed','Reviewed_Since','No','Select articles whos Last Reviewed date occurs on since the date specified','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Reviewed_Before') 
INSERT INTO FieldDetails VALUES ('09/27/2005','Admin',null,null,'Articles','LastReviewed','Reviewed_Before','No','Select articles whos Last Reviewed date occurs on or before the date specified or if Blank then Todays date','Submitter;Administrators','Everyone','Date',null,null,12,12,'field',null,null,null,null)
;

-- Updates
update Settings set DBVersion  = '1.21' where ID=1
update Settings set AppVersion = '1.21' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

