USE [EHSINFO]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Stored procedure that destroys a single 
-- session by its ID. Called by destroy() function.
CREATE PROCEDURE [dbo].[dc_waukegan_session_destroy]

	-- Parameters
	@id			varchar(40) = NULL	-- Primary key.

AS	
BEGIN	
	SET NOCOUNT ON;	
		DELETE FROM dbo.tbl_session WHERE session_id = @id					
	
END
