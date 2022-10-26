USE [SILKROAD_R_ACCOUNT]
GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_BUY_GAME_LIST_X]    Script Date: 10/19/2022 3:52:46 AM ******/
DROP PROCEDURE [dbo].[WEB_ITEM_BUY_GAME_LIST_X]
GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_BUY_GAME_LIST_X]    Script Date: 10/19/2022 3:52:46 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/*********************************************************************
2022-10-19 PAGINATION
*********************************************************************/
CREATE PROC [dbo].[WEB_ITEM_BUY_GAME_LIST_X]
	@i_page_num		int,
	@i_page_size	int,
	@i_silk_type	int,
	@i_ShopType1	int,
	@i_ShopType2	int,	
	@i_open			varchar(10),
	@i_search_word	varchar(50)
AS
BEGIN
	SET NOCOUNT ON
	
	DECLARE @PageNumber AS INT
	DECLARE @RowsOfPage AS INT
	SET @PageNumber=@i_page_num	
	SET @RowsOfPage=@i_page_size

	SELECT
		service, package_code, silk_type, silk_price, discount_rate, package_id, package_name, us_explain, us_use_method, us_use_restriction, tr_explain, tr_use_method, tr_use_restriction, eg_explain, eg_use_method, eg_use_restriction, es_explain, es_use_method, es_use_restriction, de_explain, de_use_method, de_use_restriction, shop_name_us, sub_name_us, item_order, is_best, is_new, is_list, active, vip_level, month_limit
	FROM
		VW_WEB_MALL_LIST WITH(NOLOCK)
	WHERE
		silk_type = @i_silk_type
		AND (( @i_ShopType1 = 0 AND @i_ShopType2 = 0 ) OR ( ref_no = @i_ShopType1 AND sub_no = @i_ShopType2 ) )
		AND ref_no <= 100
		AND	service = 1
		AND active <> 0
		AND ( @i_open = '' OR active = @i_open )
		AND	package_name LIKE '%'+ @i_search_word +'%'
	ORDER BY package_id ASC, item_order ASC
	OFFSET (@PageNumber-1)*@RowsOfPage ROWS
	FETCH NEXT @RowsOfPage ROWS ONLY

END

GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_BUY_X]    Script Date: 10/19/2022 3:57:57 AM ******/
DROP PROCEDURE [dbo].[WEB_ITEM_BUY_X]
GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_BUY_X]    Script Date: 10/19/2022 3:57:57 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/******************************************************************************  
2022-10-19 ADDED PAY WITH GIFT SILKS FIRST
2022-10-20 SILK ITEMS CAN BE PAYED WITH PREMIUM SILK
2022-10-22 SILK PURCHASE METHOD IMPROVED
		   (silk_gift->silk_own->silk_gift_premium->silk_own_premium)
2022-10-26 FIX PURCHASE TIME FLOWS
******************************************************************************/ 
ALTER PROC [dbo].[WEB_ITEM_BUY_X]
	@i_cp_jid				INT,
	@i_silk_type			TINYINT,
	@i_silk_offset			INT,
	@i_shard_id				INT,
	@i_shard_name			VARCHAR(50),
	@i_package_id			INT,
	@i_section				INT,
	@i_message				VARCHAR(128),
	@i_reg_ip				VARCHAR(50),
	@i_invoice_id			VARCHAR(32),
	@i_cp_invoice_id		VARCHAR(32)
AS
BEGIN
	SET NOCOUNT ON

	DECLARE @v_sp_result INT	
	DECLARE @o_result INT

	SET @i_silk_type = ISNULL(@i_silk_type,-1)
	IF ( @i_silk_type != 0 AND @i_silk_type != 3 )
	BEGIN
		SELECT -1 AS 'RetVal'
		SET @o_result = -1
		GOTO ErrorHandler
	END

	SET @i_cp_jid		 = ISNULL(@i_cp_jid,0)
	SET @i_silk_offset	 = ISNULL(@i_silk_offset,0)
	SET @i_shard_id		 = ISNULL(@i_shard_id,0)
	SET @i_shard_name	 = ISNULL(@i_shard_name,'')
	SET @i_package_id	 = ISNULL(@i_package_id,0)
	SET @i_section		 = ISNULL(@i_section,0)	
	SET @i_message		 = ISNULL(@i_message,'')
	SET @i_reg_ip		 = ISNULL(@i_reg_ip,'')
	SET @i_invoice_id	 = ISNULL(@i_invoice_id,'')
	SET @i_cp_invoice_id = ISNULL(@i_cp_invoice_id,0)

	IF	( @i_cp_jid = 0 OR @i_silk_offset = 0 OR @i_shard_id = 0 OR @i_package_id = 0 OR @i_section = 0 OR len(@i_shard_name) = 0 OR len(@i_reg_ip) = 0 ) 
	BEGIN
		SELECT -2 AS 'RetVal'
		SET @o_result = -2
		GOTO ErrorHandler
	END

SET XACT_ABORT ON

BEGIN TRANSACTION
	DECLARE @v_package_code VARCHAR(128)
	DECLARE @v_name_code	VARCHAR(128)
	DECLARE @v_package_name VARCHAR(64)
	DECLARE @v_month_limit	INT
	
	DECLARE	@v_offset_silk_own int
	DECLARE	@v_offset_silk_own_premium int

	IF (@i_silk_type = 0)
	BEGIN
		SET @v_offset_silk_own = @i_silk_offset
		SET @v_offset_silk_own_premium = 0
	END
	ELSE		
	BEGIN
		SET @v_offset_silk_own = 0
		SET @v_offset_silk_own_premium = @i_silk_offset
	END

	SELECT 
		@v_package_code = TB1.package_code, 
		@v_name_code = TB1.name_code, 
		@v_month_limit = TB1.month_limit, 
		@v_package_name = TB2.package_name
	FROM
		WEB_PACKAGE_ITEM TB1, WEB_PACKAGE_ITEM_LANG TB2
	WHERE
		TB1.package_id = TB2.package_id AND 
		TB1.package_id = @i_package_id

	IF ( @@error != 0 OR @@rowcount != 1 )
	BEGIN
		SELECT -31 AS 'RetVal'
		SET @o_result = -31
		GOTO ErrorHandler
	END	

	IF ( @v_month_limit > 0 )
	BEGIN
		DECLARE @v_db_cnt INT
		DECLARE @CMonthFirstTime DATETIME
		SET @CMonthFirstTime = DATEADD(DAY, -DATEPART(DAY,GETDATE()),CONVERT(CHAR(10),GETDATE(),21))+1

		SELECT @v_db_cnt = COUNT('') FROM WEB_ITEM_GIVE_LIST 
		WHERE 
			cp_jid = @i_cp_jid AND 
			item_code_package = @v_package_code AND
			reg_date >= @CMonthFirstTime

		IF ( @v_db_cnt >= @v_month_limit )
		BEGIN
			SELECT -39 AS 'RetVal'
			SET @o_result = -39
			GOTO ErrorHandler
		END
	END

	DECLARE @v_identity BIGINT

	IF @v_package_code != 'PACKAGE_ITEM_MALL_INSTANTACCESS' AND @v_package_code != 'PACKAGE_ITEM_PRE_MALL_INSTANTACCESS'
	BEGIN
		INSERT INTO dbo.WEB_ITEM_GIVE_LIST
		(
			cp_jid, shard_id, character_id, character_lv, 
			item_code_package, item_name_package, name_code_package, section, 
			silk_own, silk_own_premium, silk_gift, silk_gift_premium, silk_point, 
			[message], reg_ip, reg_date, recieve_date, invoice_id, cp_invoice_id
		)
		VALUES
		(
			@i_cp_jid, @i_shard_id, null, null, 
			@v_package_code, @v_package_name, @v_name_code, @i_section, 
			@v_offset_silk_own, @v_offset_silk_own_premium, 0, 0, 0, 
			@i_message, @i_reg_ip, getdate(), null, @i_invoice_id,@i_cp_invoice_id
		)
		IF ( @@error != 0 OR @@rowcount = 0 )
		BEGIN
			SELECT -32 AS 'RetVal'
			SET @o_result = -32
			GOTO ErrorHandler
		END
	END ELSE
	BEGIN
		INSERT INTO dbo.WEB_ITEM_GIVE_LIST
		(
			cp_jid, shard_id, character_id, character_lv, 
			item_code_package, item_name_package, name_code_package, section, 
			silk_own, silk_own_premium, silk_gift, silk_gift_premium, silk_point, 
			[message], reg_ip, reg_date, recieve_date, invoice_id, cp_invoice_id
		)
		VALUES
		(
			@i_cp_jid, 0, null, null, 
			@v_package_code, @v_package_name, @v_name_code, @i_section, 
			@v_offset_silk_own, @v_offset_silk_own_premium, 0, 0, 0, 
			@i_message, @i_reg_ip, getdate(), getdate(), @i_invoice_id,@i_cp_invoice_id
		)
		IF ( @@error != 0 OR @@rowcount = 0 )
		BEGIN
			SELECT -32 AS 'RetVal'
			SET @o_result = -32
			GOTO ErrorHandler
		END

		INSERT INTO _GameInstantTicket(JID, RegistrationDate) VALUES(@i_cp_jid, GETDATE())
		
		IF ( @@error != 0 OR @@rowcount = 0 )
		BEGIN
			SELECT -32 AS 'RetVal'
			SET @o_result = -32
			GOTO ErrorHandler
		END
	END

	SET @v_identity = @@identity

	IF ( @v_package_code != 'PACKAGE_ITEM_MALL_INSTANTACCESS' and @v_package_code != 'PACKAGE_ITEM_PRE_MALL_INSTANTACCESS' )
	BEGIN
		INSERT INTO dbo.WEB_ITEM_GIVE_NOTICE ( ref_idx, cp_jid ) VALUES ( @v_identity, @i_cp_jid  )
		IF ( @@error != 0 OR @@rowcount = 0 ) BEGIN
			SELECT -33
			SET @o_result = -33
			GOTO ErrorHandler
		END	

		INSERT INTO WEB_ITEM_GIVE_LIST_DETAIL ( ref_idx, item_code, item_name, item_quantity, ref_rent )
		SELECT @v_identity, item_code, item_name_eng, item_quantity, ref_rent 
		FROM dbo.WEB_PACKAGE_ITEM_DETAIL 
		WHERE package_id = @i_package_id
		IF ( @@error != 0 OR @@rowcount = 0 )
		BEGIN
			SELECT -34 AS 'RetVal'
			SET @o_result = -34
			GOTO ErrorHandler
		END	
	END

	DECLARE @silk_own INT
	DECLARE @silk_gift INT
	DECLARE @silk_own_premium INT
	DECLARE @silk_gift_premium INT
	DECLARE @silk_price INT
	DECLARE @remainder INT = 0
	DECLARE @remainder_silk_own INT = 0
	DECLARE @remainder_silk_gift INT = 0
	DECLARE @remainder_silk_own_premium INT = 0
	DECLARE @remainder_silk_gift_premium INT = 0

	SELECT @silk_price = silk_price FROM WEB_PACKAGE_ITEM WITH (NOLOCK) WHERE package_id = @i_package_id

	SELECT @silk_own = silk_own, @silk_gift = silk_gift,
		   @silk_own_premium = silk_own_premium, @silk_gift_premium = silk_gift_premium
	FROM SK_Silk WITH (NOLOCK) WHERE JID = @i_cp_jid

	IF ( @i_silk_type = 0 )
	BEGIN
		IF (@silk_own+@silk_gift+@silk_own_premium+@silk_gift_premium >= @silk_price)
		BEGIN
			IF (@silk_gift > 0)
			BEGIN
				SET @remainder_silk_gift = @silk_gift-@silk_price

				IF (@remainder_silk_gift < 0)
				BEGIN
					SELECT @silk_gift = 0
				END ELSE
				BEGIN
					SELECT @silk_gift = @remainder_silk_gift
				END
			END ELSE
			IF (@silk_own > 0 OR @remainder_silk_gift < 0)
			BEGIN
				SET @remainder_silk_own = @silk_own-@silk_price

				IF (@remainder_silk_gift < 0)
				BEGIN
					IF (@silk_own+@remainder_silk_gift < 0)
					BEGIN
						SELECT @remainder_silk_own = @silk_own+@remainder_silk_gift, @silk_own = 0
					END ELSE
					BEGIN
						SELECT @silk_own = @silk_own+@remainder_silk_gift, @remainder_silk_own = 0
					END
				END ELSE
				IF (@remainder_silk_own < 0)
				BEGIN
					SELECT @silk_own = 0
				END ELSE
				BEGIN
					SELECT @silk_own = @remainder_silk_own
				END
			END ELSE
			IF (@silk_gift_premium > 0 OR @remainder_silk_own < 0)
			BEGIN
				SET @remainder_silk_gift_premium = @silk_gift_premium-@silk_price

				IF (@remainder_silk_own < 0)
				BEGIN
					IF (@silk_gift_premium+@remainder_silk_own < 0)
					BEGIN
						SELECT @remainder_silk_gift_premium = @silk_gift_premium+@remainder_silk_own, @silk_gift_premium = 0
					END ELSE
					BEGIN
						SELECT @silk_gift_premium = @silk_gift_premium+@remainder_silk_own, @remainder_silk_gift_premium = 0
					END
				END ELSE
				IF (@remainder_silk_gift_premium < 0)
				BEGIN
					SELECT @silk_gift_premium = 0
				END ELSE
				BEGIN
					SELECT @silk_gift_premium = @remainder_silk_gift_premium
				END
			END ELSE
			IF (@silk_own_premium > 0 OR @remainder_silk_gift_premium < 0)
			BEGIN
				SET @remainder_silk_own_premium = @silk_own_premium-@silk_price

				IF (@remainder_silk_gift_premium < 0)
				BEGIN
					IF (@silk_own_premium+@remainder_silk_gift_premium < 0)
					BEGIN
						SELECT -35 AS 'RetVal'
						SET @o_result = -35
						GOTO ErrorHandler
					END ELSE
					BEGIN
						SELECT @silk_own_premium = @silk_own_premium+@remainder_silk_gift_premium
					END
				END ELSE
				IF (@remainder_silk_own_premium < 0)
				BEGIN
					SELECT -36 AS 'RetVal'
					SET @o_result = -36
					GOTO ErrorHandler
				END ELSE
				BEGIN
					SELECT @silk_own_premium = @silk_own_premium-@silk_price
				END
			END ELSE
			BEGIN
				SELECT -40 AS 'RetVal'
				SET @o_result = -40
				GOTO ErrorHandler
			END

			UPDATE SK_Silk SET
				silk_own = @silk_own, silk_gift = @silk_gift,
				silk_own_premium = @silk_own_premium, silk_gift_premium = @silk_gift_premium
			WHERE JID = @i_cp_jid
			IF ( @@ERROR != 0 OR @@ROWCOUNT = 0 )
			BEGIN
				SELECT -37 AS 'RetVal'
				SET @o_result = -37
				GOTO ErrorHandler
			END

		END ELSE
		BEGIN
			SELECT -44 AS 'RetVal'
			SET @o_result = -44
			GOTO ErrorHandler
		END
	END

	IF ( @i_silk_type = 3 )
	BEGIN
		IF ((@silk_own_premium+@silk_gift_premium) >= @silk_price)
		BEGIN
			IF (@silk_gift_premium > 0)
			BEGIN
				SET @remainder_silk_gift_premium = @silk_gift_premium-@silk_price
				
				IF (@remainder_silk_gift_premium < 0)
				BEGIN
					SELECT @silk_gift_premium = 0
				END ELSE
				BEGIN
					SELECT @silk_gift_premium = @remainder_silk_gift_premium
				END
			END ELSE
			IF (@silk_own_premium > 0 OR @remainder_silk_gift_premium < 0)
			BEGIN
				SET @remainder_silk_own_premium = @silk_own_premium-@silk_price

				IF (@remainder_silk_gift_premium < 0)
				BEGIN
					IF (@silk_own_premium+@remainder_silk_gift_premium < 0)
					BEGIN
						SELECT -35 AS 'RetVal'
						SET @o_result = -35
						GOTO ErrorHandler
					END ELSE
					BEGIN
						SELECT @silk_own_premium = @silk_own_premium+@remainder_silk_gift_premium
					END
				END ELSE
				IF (@remainder_silk_own_premium < 0)
				BEGIN
					SELECT -36 AS 'RetVal'
					SET @o_result = -36
					GOTO ErrorHandler
				END ELSE
				BEGIN
					SELECT @silk_own_premium = @silk_own_premium-@silk_price
				END
			END ELSE
			BEGIN
				SELECT -41 AS 'RetVal'
				SET @o_result = -41
				GOTO ErrorHandler
			END

			UPDATE SK_Silk SET
				silk_own = @silk_own, silk_gift = @silk_gift,
				silk_own_premium = @silk_own_premium, silk_gift_premium = @silk_gift_premium
			WHERE JID = @i_cp_jid
			IF ( @@ERROR != 0 OR @@ROWCOUNT = 0 )
			BEGIN
				SELECT -39 AS 'RetVal'
				SET @o_result = -39
				GOTO ErrorHandler
			END

		END ELSE
		BEGIN
			SELECT -43 AS 'RetVal'
			SET @o_result = -43
			GOTO ErrorHandler
		END
	END

COMMIT TRANSACTION		 

SELECT 1 AS 'RetVal'
RETURN

END

ErrorHandler:
	IF ( @@trancount > 0 )
	BEGIN
		ROLLBACK TRANSACTION
	END

	INSERT INTO WEB_ABOUT_SILK_ERROR_LOG
	(
		location, error_no, cp_jid, silk_type, silk_reason, reg_date, reg_ip, 
		param1, param2, param3, param4, param5, param6, param7, param8, param9
	)
	VALUES
	(
		21, @o_result, @i_cp_jid, @i_silk_type, 5, getdate(), @i_reg_ip, 
		@i_silk_offset, @v_offset_silk_own, 0, 0, 0, 
		@i_shard_id, @i_shard_name, @i_package_id, @i_section
	)

RETURN @o_result

GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_HISTORY_X]    Script Date: 10/19/2022 3:56:14 AM ******/
DROP PROCEDURE [dbo].[WEB_ITEM_HISTORY_X]
GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_HISTORY_X]    Script Date: 10/19/2022 3:56:14 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/*********************************************************************
2022-10-19 PAGINATION APPLIED
*********************************************************************/
CREATE PROC [dbo].[WEB_ITEM_HISTORY_X]
	@i_page_num		int,
	@i_page_size	int,
	@i_year			int,
	@i_month		int,
	@i_cp_jid		int
AS
BEGIN
	SET NOCOUNT ON

	DECLARE @PageNumber AS INT
	DECLARE @RowsOfPage AS INT
	SET @PageNumber=@i_page_num	
	SET @RowsOfPage=@i_page_size

	IF (@i_year = 0 AND @i_month = 0)
	BEGIN
		SELECT [character_id], [item_name_package] ,[silk_own] ,[silk_own_premium] ,[silk_gift] ,[silk_gift_premium] ,[silk_point] ,[reg_date]
		FROM WEB_ITEM_GIVE_LIST WITH(NOLOCK)
		WHERE cp_jid = @i_cp_jid
		ORDER BY reg_date DESC
		OFFSET (@PageNumber-1)*@RowsOfPage ROWS
		FETCH NEXT @RowsOfPage ROWS ONLY
	END ELSE
	BEGIN
		SELECT [character_id], [item_name_package] ,[silk_own] ,[silk_own_premium] ,[silk_gift] ,[silk_gift_premium] ,[silk_point] ,[reg_date]
		FROM WEB_ITEM_GIVE_LIST WITH(NOLOCK)
		WHERE cp_jid = @i_cp_jid
			AND DATEPART(YEAR, [reg_date]) = @i_year
			AND DATEPART(MONTH, [reg_date]) = @i_month
		ORDER BY reg_date DESC
		OFFSET (@PageNumber-1)*@RowsOfPage ROWS
		FETCH NEXT @RowsOfPage ROWS ONLY
	END
END

GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_RESERVED_X]    Script Date: 10/19/2022 3:53:10 AM ******/
DROP PROCEDURE [dbo].[WEB_ITEM_RESERVED_X]
GO

/****** Object:  StoredProcedure [dbo].[WEB_ITEM_RESERVED_X]    Script Date: 10/19/2022 3:53:10 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/******************************************************************************  
2022-10-19 MAXIMUM RESERVE ITEMS SET TO 20
******************************************************************************/ 
CREATE PROC [dbo].[WEB_ITEM_RESERVED_X]
	@i_UserJID			INT,
	@i_Package_ID		INT
AS
BEGIN
	SET NOCOUNT ON

	IF EXISTS ( SELECT '' FROM WEB_PACKAGE_ITEM WHERE package_id = @i_Package_ID AND vip_level > 0 )
	BEGIN
		SELECT -3 AS RetVal
		RETURN
	END

	DECLARE @Reserved_Item INT	
	SELECT TOP 1 @Reserved_Item = UserJID FROM WEB_ITEM_RESERVED WHERE UserJID = @i_UserJID AND package_id = @i_Package_ID
	
	IF (@Reserved_Item is null)
	BEGIN

		DECLARE @v_item_count int
		SET @v_item_count = 0
		SELECT @v_item_count = COUNT('') FROM WEB_ITEM_RESERVED WITH(NOLOCK)
		WHERE UserJID = @i_UserJID
		IF @v_item_count > 20
		BEGIN
			SELECT -2 AS RetVal
			RETURN
		END
		
		INSERT INTO WEB_ITEM_RESERVED VALUES(@i_UserJID, @i_Package_ID)
		SELECT 0 AS RetVal
		RETURN

	END ELSE
	BEGIN
		SELECT -1 AS RetVal
		RETURN
	END
END

GO

