USE [EHSINFO]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[dc_waukegan_session_set]
	
	-- Parameters
	@session_id		varchar(26)		= NULL,	-- Primary key.
	@data			varchar(max)	= NULL,	-- PHP session variables.
	@source_file	varchar(2048)	= NULL, -- **File name of PHP script generating session data.
	@client_ip		varchar(50)		= NULL	-- **Client address (if available).

AS	
	 
BEGIN	
	SET NOCOUNT ON;	
		MERGE INTO dbo.tbl_session AS target_table
		USING 
				(SELECT @session_id AS session_id) AS _search
			ON 
				target_table.session_id = _search.session_id
			
			WHEN MATCHED THEN
				UPDATE SET
					session_data	= @data,
					last_update		= GETDATE(),
					source		= @source_file,
					ip		= @client_ip
			
			WHEN NOT MATCHED THEN
				INSERT (session_id, 
						session_data, 
						last_update, 
						source, 
						ip)
						
				VALUES (_search.session_id, 
						@data, 
						GETDATE(), 
						@source_file, 
						@client_ip);
END
