-- Caskey, Damon V.
-- 2012-02-21
-- https://github.com/DCurrent/Nahoni
--
-- Execute to build the table and stored
-- procedures for use by Nahoni PHP scripts.
--
-- If you are wondering why there's no convenient 
-- parameter to use different names for tables and 
-- procedures it's because that requires dynmaic 
-- SQL strings and I'm not a big fan of those. IMO 
-- dynamic SQL strings make a mess of the code and 
-- cause more problems than they solve. If you do 
-- need different names, just modify them below 
-- before executing.
--
-- If you have any other questions, feel free to
-- contact me: dc@caskeys.com

SET ANSI_NULLS ON
SET QUOTED_IDENTIFIER ON

-- Build table to store session data.
--
-- Fields marked ** are not part of the
-- default PHP session handling and are
-- added to aid with debugging.
CREATE TABLE dbo.tbl_dc_nahoni_session(
	session_id		varchar(128)	NOT NULL,	-- PHP generates 40 character session IDs by default, can accept a user generated ID up to 128 characters.
	session_data	varchar(max)	NULL,		-- The session data. PHP concatenates all session data into a single string, and breaks it back down into an array on retrieval.
	last_update		datetime2(7)	NULL,		-- Populated with current time whenever the session data is modified.
	source_file		varchar(max)	NULL,		-- **Source file. Not used by default PHP session handling, we add this to aid in debugging.
	client_ip		varchar(15)		NULL,		-- **Client IP address, if available.  
 CONSTRAINT PK_tbl_dc_nahoni_sessions PRIMARY KEY CLUSTERED 
(
	session_id ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

-- Stored procedure that cleans all table entries
-- over specified age. Called by gc() function.
CREATE PROCEDURE dbo.dc_nahoni_session_clean
	
	-- Parameters
	@life_max	int = 1440	-- Maximum lifetime of a session in seconds.

AS	
BEGIN
	SET NOCOUNT ON;	 
		DELETE FROM dbo.tbl_dc_nahoni_session WHERE (DATEDIFF(SECOND, last_update, GETDATE()) > @life_max)

END
GO

-- Stored procedure that destroys a single 
-- session by its ID. Called by destroy() function.
CREATE PROCEDURE dbo.dc_nahoni_session_destroy

	-- Parameters
	@id			varchar(40) = NULL	-- Primary key.

AS	
BEGIN	
	SET NOCOUNT ON;	
		DELETE FROM dbo.tbl_dc_nahoni_session WHERE session_id = @id					
	
END
GO

-- Stored procedure that gets session data 
-- by its ID. Called by read() function.
CREATE PROCEDURE dbo.dc_nahoni_session_get
	
	-- Parameters
	@id				varchar(40) = NULL	-- Primary key.

AS	
BEGIN	
	SET NOCOUNT ON;	
		SELECT session_data 
			FROM dbo.tbl_dc_nahoni_session
			WHERE 
				session_id = @id
	
END
GO

CREATE PROCEDURE dbo.dc_nahoni_session_set
	
	-- Parameters
	@session_id		varchar(26)		= NULL,	-- Primary key.
	@data			varchar(max)	= NULL,	-- PHP session variables.
	@source_file	varchar(2048)	= NULL, -- **File name of PHP script generating session data.
	@client_ip		varchar(50)		= NULL	-- **Client address (if available).

AS	
	 
BEGIN	
	SET NOCOUNT ON;	
		MERGE INTO dbo.tbl_dc_nahoni_session AS target_table
		USING 
				(SELECT @session_id AS session_id) AS _search
			ON 
				target_table.session_id = _search.session_id
			
			WHEN MATCHED THEN
				UPDATE SET
					session_data	= @data,
					last_update		= GETDATE(),
					source_file		= @source_file,
					client_ip		= @client_ip
			
			WHEN NOT MATCHED THEN
				INSERT (session_id, 
						session_data, 
						last_update, 
						source_file, 
						client_ip)
						
				VALUES (_search.session_id, 
						@data, 
						GETDATE(), 
						@source_file, 
						@client_ip);
END
GO
