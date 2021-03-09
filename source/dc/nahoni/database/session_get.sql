-- session_get
-- 2012-02-21
-- Caskey, Damon V.
-- https://github.com/DCurrent/Nahoni

-- Get a recordset of session data by ID.

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[session_get]
	
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

GO


