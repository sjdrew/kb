-- Updates from version 1.32 to 1.33

-- Table Modifications

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='RemedyARServer') 
	Alter Table Settings Add [RemedyARServer] [varchar] (30) NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='RemedyDBServer') 
	Alter Table Settings Add [RemedyDBServer] [varchar] (30) NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='HelpDeskTable') 
	Alter Table Settings Add [HelpDeskTable] [varchar] (8) NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='HDWorkLogTable') 
	Alter Table Settings Add [HDWorkLogTable] [varchar] (8) NULL 

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='RemedyFullTextBackground') 
	Alter Table Settings Add [RemedyFullTextBackground] [varchar] (1) NULL 


;

-- Updates


if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'Contact1_S') 
INSERT INTO FieldDetails VALUES ('11/09/2007','sdrew',null,null,'Articles','Contact1','Contact1_S','No ','','Submitter;Administrators','Everyone','TextBox','','',30,100,'field','','','','')

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'RemedyFullTextBackground') 
INSERT INTO FieldDetails VALUES ('11/09/2007','SDrew',null,null,'Settings','RemedyFullTextBackground','RemedyFullTextBackground','No ',null,'Submitter;Administrators','Everyone','CheckBox','1',null,null,null,null,null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'RemedyARServer') 
	INSERT INTO FieldDetails VALUES ('11/09/2007','Admin',null,null,'Settings','RemedyARServer','RemedyARServer','No ','Name of the Remedy AR Server','Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'RemedyDBServer') 
	INSERT INTO FieldDetails VALUES ('11/09/2007','Admin',null,null,'Settings','RemedyDBServer','RemedyDBServer','No ','Name of the Remedy Database Server','Submitter;Administrators','Everyone','TextBox',null,null,30,30,'field',null,null,null,null)

-- 
-- Replacing Contact1/2 as droplists from user table
--
delete from FieldDetails  where TableName = 'Articles' and FieldName = 'Contact1'
delete from FieldDetails  where TableName = 'Articles' and FieldName = 'Contact2'
;

INSERT INTO FieldDetails VALUES ('11/09/2007','sdrew','11/09/2007','sdrew','Articles',
'Contact1','Contact1','No',null,'Submitter;Administrators','Everyone',
'DropList',null,null,10,10,'field','style=''width:175px''',
'select Username,FirstName + '' '' + LastName as Name from [users] order by Name','Name','Username')

INSERT INTO FieldDetails VALUES ('11/09/2007','sdrew','11/09/2007','sdrew','Articles',
'Contact2','Contact2','No',null,'Submitter;Administrators','Everyone',
'DropList',null,null,10,10,'field','style=''width:175px''',
'select Username,FirstName + '' '' + LastName as Name from [users] order by Name','Name','Username')


update Settings set DBVersion  = '1.33' where ID=1
update Settings set AppVersion = '1.33' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1

;

