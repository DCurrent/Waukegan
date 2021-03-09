-- session_set
-- 2012-02-21
-- Caskey, Damon V.
-- https://github.com/DCurrent/Nahoni

-- Create or update session record.

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[session_set]
	
	-- Parameters
	@session_id		varchar(26)		= NULL,	-- Primary key.
	@data			varchar(max)	= NULL,	-- PHP session variables.
	@source			varchar(2048)	= NULL, -- PHP script generating session data.
	@host			varchar(50)		= NULL	-- Host device address (if available).

AS	
	
BEGIN
	
	SET NOCOUNT ON;	 
	
		MERGE INTO dbo.tbl_session
		USING 
				(SELECT @session_id AS session_id) AS _search
			ON 
				tbl_session.session_id = _search.session_id
			
			WHEN MATCHED THEN
				UPDATE SET
					session_data	= @data,
					last_update		= GETDATE(),
					source			= @source,
					host			= @host
			
			WHEN NOT MATCHED THEN
				INSERT (session_id, 
						session_data, 
						last_update, 
						source, 
						host)
						
				VALUES (_search.session_id, 
						@data, 
						GETDATE(), 
						@source, 
						@host);
					
END

GO


