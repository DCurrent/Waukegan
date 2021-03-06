USE [EHSINFO]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Stored procedure that cleans all table entries
-- over specified age. Called by gc() function.
CREATE PROCEDURE [dbo].[dc_waukegan_session_clean]
	
	-- Parameters
	@life_max	int = 1440	-- Maximum lifetime of a session in seconds.

AS	
BEGIN
	SET NOCOUNT ON;	 
		DELETE FROM dbo.tbl_session WHERE (DATEDIFF(SECOND, last_update, GETDATE()) > @life_max)

END
