-- session_destroy
-- 2012-02-21
-- Caskey, Damon V.
-- https://github.com/DCurrent/Nahoni

-- Destroy session data by ID.

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[session_destroy]
	
	-- Parameters
	@id				varchar(40) = NULL	-- Primary key.

AS	
BEGIN
	
	SET NOCOUNT ON;	 
	
		DELETE FROM dbo.tbl_session WHERE session_id = @id
					
END

GO


