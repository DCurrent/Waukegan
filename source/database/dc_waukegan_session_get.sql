USE [EHSINFO]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Stored procedure that gets session data 
-- by its ID. Called by read() function.
CREATE PROCEDURE [dbo].[dc_waukegan_session_get]
	
	-- Parameters
	@id				varchar(40) = NULL	-- Primary key.

AS	
BEGIN	
	SET NOCOUNT ON;	
		SELECT session_data 
			FROM dbo.tbl_session
			WHERE 
				session_id = @id
	
END
