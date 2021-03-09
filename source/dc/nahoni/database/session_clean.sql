-- session_clean
-- 2012-02-21
-- Caskey, Damon V.
-- https://github.com/DCurrent/Nahoni

-- Destroy all expired session data.

SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[session_clean]
	
	-- Parameters
	@life_max	int = 1440	-- Maximum lifetime of a session in seconds.

AS	
BEGIN
	
	SET NOCOUNT ON;	 
	
		DELETE FROM dbo.tbl_session WHERE (DATEDIFF(SECOND, last_update, GETDATE()) > @life_max)
					
END

GO


