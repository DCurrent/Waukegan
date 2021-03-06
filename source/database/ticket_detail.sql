USE [ehs_ticket]
GO
/****** Object:  StoredProcedure [dbo].[ticket_detail]    Script Date: 3/14/2021 10:38:16 PM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- Create date: 2015-06-17
-- Description:	Get single ticket detail.
-- =============================================

ALTER PROCEDURE [dbo].[ticket_detail]
	
	-- filter
	@id					int				= NULL,
	@account			varchar(10)		= NULL,
	
	-- sorting
	@sort_field			tinyint 		= NULL,
	@sort_order			bit				= NULL
	
AS	
	SET NOCOUNT ON;
	
	-- Set defaults.
		-- Filters.	
		IF		@id IS NULL 
				OR @id < 1 SET @id = -1		
			
		IF		@account IS NULL SET @account = CURRENT_USER	
		
		-- Sorting field.	
		IF		@sort_field IS NULL 
				OR @sort_field = 0 
				OR @sort_field > 4 SET @sort_field = 3
		
		-- Sorting order.	
		IF		@sort_order IS NULL SET @sort_order = 1	
	
	-- We'll use this below for getting navigation ID's without rerunning the same SELECT query.
	DECLARE @row_current int = 0
	
	-- Set up table var so we can reuse results.		
	DECLARE @tempMain TABLE
	(
		row			int,
		id			int, 
		label		varchar(255), 
		details		varchar(max), 
		status		tinyint, 
		eta			datetime2, 
		log_create	datetime2, 
		log_update	datetime2, 
		account		varchar(10), 
		attachment	varchar(255)
	)		
		
	-- Populate main table var. This is the primary query. Order
	-- and query details go here.
	INSERT INTO @tempMain (row, id, label, details, status, eta, log_create, log_update, account, attachment)
	(SELECT ROW_NUMBER() OVER(ORDER BY 
								-- Sort order options here. CASE lists are ugly, but we'd like to avoid
								-- dynamic SQL for maintainability.
								CASE WHEN @sort_field = 1 AND @sort_order = 0	THEN label	END ASC,
								CASE WHEN @sort_field = 1 AND @sort_order = 1	THEN label	END DESC,
								CASE WHEN @sort_field = 2 AND @sort_order = 0	THEN status	END ASC,
								CASE WHEN @sort_field = 2 AND @sort_order = 1	THEN status	END DESC,
								CASE WHEN @sort_field = 3 AND @sort_order = 0	THEN log_create	END ASC,
								CASE WHEN @sort_field = 3 AND @sort_order = 1	THEN log_create	END DESC,
								CASE WHEN @sort_field = 4 AND @sort_order = 0	THEN log_update	END ASC,
								CASE WHEN @sort_field = 4 AND @sort_order = 1	THEN log_update	END DESC) 
		AS _row_number,
			_main.id, 
			_main.label, 
			_main.details, 
			_main.status,
			_main.eta, 
			_main.log_create, 
			_main.log_update,
			_main.account,
			_main.attachment
	FROM dbo.tbl_ticket _main
	WHERE (record_deleted IS NULL OR record_deleted = 0) 
									AND Exists (SELECT 1
													FROM tbl_ticket_party As _sub
													WHERE _main.id = _sub.fk_id
														AND _sub.account IN(@account)))
	
	
	
	-- Main detail	
	SELECT	
		* 
	FROM 
		@tempMain _data	
	WHERE
		id = @id
	 
	
	-- Sub table (journal)
	SELECT 
		id, 
		label, 
		details, 
		log_update,
		log_update_by 
	FROM 
		tbl_ticket_journal 
	WHERE 
		fk_id = @id AND (record_deleted IS NULL OR record_deleted = 0) 
	ORDER BY 
		log_create
	
	-- Sub table (party)	
	SELECT 
		id, 
		account 
	FROM 
		tbl_ticket_party 
	WHERE 
		fk_id = @id AND (record_deleted IS NULL OR record_deleted = 0)
	
	-- Navigation
		
		-- Output a recordset we need for control code.

		DECLARE @nav_first			int
		DECLARE @nav_previous		int
		DECLARE @nav_next			int
		DECLARE @nav_last			int

		SELECT @row_current = (SELECT row FROM @tempMain WHERE id = @id)
		
		SELECT @nav_first = (SELECT TOP 1 id FROM @tempMain)	
		SELECT @nav_last = (SELECT TOP 1 id FROM @tempMain ORDER BY row DESC)
		SELECT @nav_next = (SELECT TOP 1 id FROM @tempMain WHERE row > @row_current)
		SELECT @nav_previous = (SELECT TOP 1 id FROM @tempMain WHERE row < @row_current ORDER BY row DESC)

		SELECT @row_current AS row_current, 
		@nav_first AS nav_first,
		@nav_last AS nav_last,
		@nav_next AS nav_next,
		@nav_previous AS nav_previous