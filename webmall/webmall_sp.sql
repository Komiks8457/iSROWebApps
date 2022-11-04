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
2022-11-03 ADDED item_quantity column
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
		service, package_code, silk_type, silk_price, discount_rate, package_id, package_name, us_explain, us_use_method, us_use_restriction, tr_explain, tr_use_method, tr_use_restriction, eg_explain, eg_use_method, eg_use_restriction, es_explain, es_use_method, es_use_restriction, de_explain, de_use_method, de_use_restriction, shop_name_us, sub_name_us, item_order, is_best, is_new, is_list, active, vip_level, month_limit, item_quantity
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
2022-10-26 FIX PURCHASE FLOWS
2022-10-29 FIX PURCHASE FLOWS 2 - ADDED @ispaid indicator
2022-11-01 FIX PURCHASE PREMIUM SILK ERROR
2022-11-04 Removed the silk deduction method
******************************************************************************/ 
CREATE PROC [dbo].[WEB_ITEM_BUY_X]
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

/****** Object:  View [dbo].[VW_WEB_MALL_LIST]    Script Date: 11/3/2022 5:24:55 AM ******/
DROP VIEW [dbo].[VW_WEB_MALL_LIST]
GO

/****** Object:  View [dbo].[VW_WEB_MALL_LIST]    Script Date: 11/3/2022 5:24:55 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/******************************************************************************  
2022-11-03 added item_quantity
******************************************************************************/ 
CREATE VIEW [dbo].[VW_WEB_MALL_LIST]
AS
	SELECT 
		TB1.service, TB1.package_code, TB1.name_code, TB1.silk_type, TB1.silk_price, TB1.silk_price_grow, TB1.silk_price_item, TB1.discount_rate, TB1.discount_rate_grow, TB1.discount_rate_item, TB1.origin_server, TB1.grow_server, TB1.item_server, TB1.vip_level, TB1.month_limit,
		TB2.*,
		TB3.shop_name_us, TB4.sub_name_us, TB3.shop_order, TB4.sub_order,
		TB3.shop_name_tr, TB4.sub_name_tr, 
		TB3.shop_name_eg, TB4.sub_name_eg, 
		TB3.shop_name_es, TB4.sub_name_es, 
		TB3.shop_name_de, TB4.sub_name_de, 
		TB4.ref_no, TB4.sub_no,
		TB5.item_order, TB5.is_best, TB5.is_new, TB5.is_list, TB5.active, TB6.item_quantity, reg_date 
	FROM 
		WEB_PACKAGE_ITEM TB1, 
		WEB_PACKAGE_ITEM_LANG TB2,
		WEB_MALL_CATEGORY TB3,
		WEB_MALL_CATEGORY_SUB TB4,
		WEB_PACKAGE_ITEM_MALL TB5,
		WEB_PACKAGE_ITEM_DETAIL TB6
	WHERE 
		TB1.package_id = TB2.package_id AND
		( 
			( TB1.shop_no = TB3.shop_no AND TB1.shop_no_sub = TB4.sub_no ) OR
			( TB1.event_no = TB3.shop_no AND TB1.event_no_sub = TB4.sub_no )
		) AND
		TB3.shop_no = TB4.ref_no AND
		TB1.package_id = TB5.package_id AND
		TB1.package_id = TB6.package_id
GO

USE [GB_JoymaxPortal]
GO

/****** Object:  StoredProcedure [dbo].[X_DirectPaymentBeginCPTXByPS]    Script Date: 11/4/2022 7:33:41 PM ******/
DROP PROCEDURE [dbo].[X_DirectPaymentBeginCPTXByPS]
GO

/****** Object:  StoredProcedure [dbo].[X_DirectPaymentBeginCPTXByPS]    Script Date: 11/4/2022 7:33:41 PM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/*********************************************************************************************************
2022-11-04 Since its so fucking complicated to work on sp with output in ADOdb5 library we need to modify
**********************************************************************************************************/
CREATE PROCEDURE [dbo].[X_DirectPaymentBeginCPTXByPS]
	 @PTInvoiceID		varchar(32)
	,@CPJCIInvoiceID	varchar(32)
	,@ServiceCode		smallint
	,@Price				int
	,@SilkType			tinyint
	,@JID				int
	,@UserIP			binary(4)
	,@CPItemCode		varchar(128)
	,@CPItemName		varchar(64)
	,@CPItemCount		smallint
	,@ServerName		varchar(32) = NULL
	,@CharName			varchar(64) = NULL
	,@CharID			int = NULL
AS
	SET NOCOUNT ON

	Declare @StatusCode smallint
	Declare @JCUSN int = 0
	DECLARE @ErrorMessage	varchar(50)  
	DECLARE @CurrentDate	datetime
	DECLARE @CPItemID		int
	SET @CurrentDate = GETDATE()
	
	select @JCUSN = ChannelId from MU_JCPlanet with(nolock) Where JID = @JID
	
	if @JCUSN is null
	begin
		SET @JCUSN = 0	
	end


	-- 매월 1일 10분간 아이템 구매제한
	DECLARE @MonthFirstTime DATETIME
	SET @MonthFirstTime = DATEADD(DAY, -DATEPART(DAY,GETDATE()),CONVERT(CHAR(10),GETDATE(),21))+1
	IF @CurrentDate >= @MonthFirstTime AND @CurrentDate <= DATEADD(MINUTE,10,@MonthFirstTime) BEGIN
		SELECT -655431 as 'ReturnCode'
		RETURN -655431
	END
	--

	--필수 입력 데이터가 입력되지 않은 경우
	IF @PTInvoiceID IS NULL OR @ServiceCode IS NULL OR @CPJCIInvoiceID IS NULL OR @JID IS NULL OR @UserIP IS NULL OR @Price IS NULL 
		OR @SilkType IS NULL OR @CPItemCount IS NULL OR @CPItemName IS NULL OR @CPItemCode IS NULL BEGIN
		SELECT -655432 'ReturnCode'
		RETURN -655432
	END

	--결제 금액이나 수량이 1 미만인경우
	IF @Price <= 0 OR @CPItemCount <= 0 BEGIN
		SELECT -655433
		RETURN -655433
	END

	--미존재 서비스 코드
	IF NOT EXISTS(SELECT '' FROM dbo.M_Service WITH(NOLOCK) WHERE ServiceCode = @ServiceCode AND EndDate > GETDATE() ) BEGIN
		SELECT -131088 'ReturnCode'
		RETURN -131088
	END

	--존재 하지 않는 캐쉬 타입일 경우
	IF NOT EXISTS(SELECT '' FROM dbo.M_SilkType WITH(NOLOCK) WHERE SilkType = @SilkType AND Usable = 'Y') BEGIN
		SELECT -393259 'ReturnCode'
		RETURN -393259
	END

	--없는 유저일경우
	IF NOT EXISTS(SELECT '' FROM dbo.MU_User WITH(NOLOCK) WHERE JID = @JID) BEGIN
		SELECT -131076 'ReturnCode'
		RETURN -131076
	END

	if @JCUSN = 0
	begin
		--블럭된 유저일 경우
		IF EXISTS(SELECT '' FROM dbo.MUH_Blocked WITH(NOLOCK) WHERE JID = @JID AND StartDate <= @CurrentDate AND EndDate > @CurrentDate) BEGIN
			SELECT -131078 'ReturnCode'
			RETURN -131078
		END
	
		--IP블럭 검사
		IF EXISTS(SELECT '' FROM dbo.K_BlockedIP WITH(NOLOCK) WHERE UserIP = @UserIP AND ServiceCode IN (1, @ServiceCode) AND StartDate <= @CurrentDate AND @CurrentDate < EndDate) BEGIN
			SELECT -131080 'ReturnCode'
			RETURN -131080
		END
	end

	--주문번호가 존재하는 경우
	IF EXISTS(SELECT '' FROM dbo.APH_CPItemSaleDetails WITH(NOLOCK) WHERE (ServiceCode = @ServiceCode AND CPJCIInvoiceID = @CPJCIInvoiceID) OR (PTInvoiceID = @PTInvoiceID)) BEGIN
		SELECT -327709 'ReturnCode'
		RETURN -327709
	END

	if @JCUSN = 0
	begin
		--잔량검사
		IF (@Price * @CPItemCount) > (
						SELECT
							ISNULL(SUM(a.RemainedSilk), 0)
						FROM dbo.APH_ChangedSilk AS a WITH(NOLOCK)
							INNER JOIN dbo.M_SilkType AS b WITH(NOLOCK) On a.SilkType=b.SilkType
						WHERE a.AvailableStatus = 'Y'
						  AND a.AvailableDate > @CurrentDate
						  AND a.JID = @JID
						  AND b.SilkSort >= (SELECT c.SilkSort FROM dbo.M_SilkType AS c WITH(NOLOCK) WHERE c.SilkType = @SilkType)
						  AND b.Usable = 'Y'
					) BEGIN
			SELECT -327714 as 'ReturnCode'
			RETURN -327714
		END
	end

	--국가명 얻어옴
	DECLARE @CountryCode char(2)
	DECLARE @ReturnValue int
	
	EXECUTE @ReturnValue = dbo.A_SearchCountryWithIPBinary @UserIP, @CountryCode OUTPUT
	
	IF @ReturnValue<>0 BEGIN
		SELECT -655434 as 'ReturnCode'
		RETURN -655434
	END

	if @JCUSN = 0
	begin	
		--중국이나 베트남 등 결제 서비스 이용 불가 지역일 경우
		IF EXISTS(SELECT '' FROM dbo.K_BlockedService WITH(NOLOCK) WHERE ServiceCode IN (1, @ServiceCode) AND CountryCode = @CountryCode) BEGIN
			SELECT -131084 as 'ReturnCode'
			RETURN -131084
		END
	end
			
	BEGIN TRAN
		BEGIN TRY
			--입력된 아이템에 해당하는 키 값을 구함
			SELECT @CPItemID = CPItemID 
			FROM dbo.M_CPItem WITH(NOLOCK)
			WHERE ServiceCode = @ServiceCode
			  AND CPItemCode = @CPItemCode
			  AND CPItemName = @CPItemName
			  AND Price = @Price
				
			IF @CPItemID IS NULL
			BEGIN
				INSERT INTO dbo.M_CPItem(ServiceCode, CPItemCode, CPItemName, Price)
				VALUES(@ServiceCode, @CPItemCode, @CPItemName, @Price)
				
				IF @@ROWCOUNT <> 1
				BEGIN  
					SET @ErrorMessage = 'Insert Into M_CPItem'  
					GOTO WriteErrorLog  
				END
				
				SET @CPItemID = SCOPE_IDENTITY()
			END
			
			--select top 100 * from APH_CPItemSaleDetails
			
			INSERT INTO dbo.APH_CPItemSaleDetails(PTInvoiceID, CPJCIInvoiceID, ServiceCode, CPItemCount, Price, SilkType, JCISCode, JID, UserIP, CountryCode, CPPaymentDate, CPItemID, ServerName, CharName, CharID)
			SELECT @PTInvoiceID,@CPJCIInvoiceID,@ServiceCode,@CPItemCount,@Price,@SilkType,7000,@JID,@UserIP,@CountryCode,@CurrentDate,@CPItemID,@ServerName,@CharName,@CharID
			
			IF @@ROWCOUNT <> 1
			BEGIN  
				SET @ErrorMessage = 'Insert Into APH_CPItemSaleDetails'  
				GOTO WriteErrorLog  
			END

			SET @StatusCode = 7000
		END TRY
		BEGIN CATCH
			IF @@TRANCOUNT > 0
				ROLLBACK TRAN

			INSERT INTO dbo.AH_Error(ErrorDate, ErrorNumber, ErrorSeverity, ErrorState, ErrorProcedure, ErrorLine, ErrorMessage, ErrorParam)
			SELECT GETDATE()
				  ,ERROR_NUMBER() AS ErrorNumber
				  ,ERROR_SEVERITY() AS ErrorSeverity
				  ,ERROR_STATE() AS ErrorState
				  ,ERROR_PROCEDURE() AS ErrorProcedure
				  ,ERROR_LINE() AS ErrorLine
				  ,ERROR_MESSAGE() AS ErrorMessage
				  ,'PTInvoiceID:' + ISNULL(@PTInvoiceID, '') + ', '
				  +'CPJCIInvoiceID:' + ISNULL(@CPJCIInvoiceID, '') + ', '
				  +'ServiceCode:' + ISNULL(CONVERT(varchar, @ServiceCode), '') + ', '
				  +'JID:' + ISNULL(CONVERT(varchar, @JID), '') + ', '
				  +'UserIP:' + dbo.F_MakeIPBinaryToIPString(ISNULL(@UserIP, 0x00000000)) + ', '
				  +'Price:' + ISNULL(CONVERT(varchar, @Price), '') + ', '
				  +'SilkType:' + ISNULL(CONVERT(varchar, @SilkType), '') + ', '
				  +'CPItemCount:' + ISNULL(CONVERT(varchar, @CPItemCount), '') + ', '
				  +'CPItemName:' + ISNULL(@CPItemName, '') + ', '
				  +'CPItemID:' + ISNULL(CONVERT(varchar, @CPItemID), '') + ', '
				  +'CPItemCode:' + ISNULL(@CPItemCode, '') + ', '
				  +'ServerName:' + ISNULL(@ServerName, '') + ', '
				  +'CharName:' + ISNULL(@CharName, '') + ', '
				  +'CharID:' + ISNULL(CONVERT(varchar, @CharID), '') AS ErrorParam
			RETURN ERROR_NUMBER() * -1
		END CATCH

	IF @@TRANCOUNT > 0
		COMMIT TRAN

	SELECT @StatusCode as 'ReturnCode'
	RETURN 0

WriteErrorLog:

	IF @@TRANCOUNT > 0
		ROLLBACK TRAN

	INSERT INTO dbo.AH_Error(ErrorDate, ErrorNumber, ErrorSeverity, ErrorState, ErrorProcedure, ErrorLine, ErrorMessage, ErrorParam)
	SELECT GETDATE() AS ErrorDate
		  ,65544 AS ErrorNumber
		  ,0 AS ErrorSeverity
		  ,0 AS ErrorState
		  ,'B_DirectPaymentBeginCPTXByPS' AS ErrorProcedure
		  ,0 AS ErrorLine
		  ,@ErrorMessage + ' 문에 적용된 행이 없습니다.' AS ErrorMessage
		  ,'PTInvoiceID:' + ISNULL(@PTInvoiceID, '') + ', '
		  +'CPJCIInvoiceID:' + ISNULL(@CPJCIInvoiceID, '') + ', '
		  +'ServiceCode:' + ISNULL(CONVERT(VarChar,@ServiceCode), '') + ', '
		  +'JID:' + ISNULL(CONVERT(VarChar,@JID), '') + ', '
		  +'UserIP:' + dbo.F_MakeIPBinaryToIPString(ISNULL(@UserIP,0x00000000)) + ', '
		  +'Price:' + ISNULL(CONVERT(VarChar,@Price), '') + ', '
		  +'SilkType:' + ISNULL(CONVERT(VarChar,@SilkType), '') + ', '
		  +'CPItemCount:' + ISNULL(CONVERT(VarChar,@CPItemCount), '') + ', '
		  +'CPItemName:' + ISNULL(@CPItemName, '') + ', '
		  +'CPItemID:' + ISNULL(CONVERT(VarChar,@CPItemID), '') + ', '
		  +'CPItemCode:' + ISNULL(@CPItemCode, '') + ', '
		  +'ServerName:' + ISNULL(@ServerName, '') + ', '
		  +'CharName:' + ISNULL(@CharName, '') + ', '
		  +'CharID:' + ISNULL(CONVERT(VarChar,@CharID), '') AS ErrorParam
	SELECT -65544 as 'ReturnCode'
	RETURN -65544
GO

/****** Object:  StoredProcedure [dbo].[X_DirectPaymentCompletedCPTXByPS]    Script Date: 11/4/2022 7:34:42 PM ******/
DROP PROCEDURE [dbo].[X_DirectPaymentCompletedCPTXByPS]
GO

/****** Object:  StoredProcedure [dbo].[X_DirectPaymentCompletedCPTXByPS]    Script Date: 11/4/2022 7:34:42 PM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/********************************************************************************************************
2022-11-04 Since its so fucking complicated to work on sp with output in ADOdb5 library we need to modify
*********************************************************************************************************/
CREATE Procedure [dbo].[X_DirectPaymentCompletedCPTXByPS]
	 @PTInvoiceID VarChar(32)
AS
	SET NOCOUNT ON

	DECLARE @StatusCode		SMALLINT = 0
	DECLARE @ErrorMessage	VARCHAR(50)
	DECLARE @CPPaymentDate	DATETIME
	DECLARE @SilkType		TINYINT
	DECLARE @JID			INT
	DECLARE @SellingPrice	INT
	DECLARE @RowCount		INT
	DECLARE @ChangedSilk	TABLE
	(
		 CSID			INT
		,RemainedSilk	INT
		,ChangedSilk	INT
	)
	
	Declare @JCUSN int = 0

	
	if @JCUSN is null
	begin
		SET @JCUSN = 0	
	end
	
	--결제 정보를 알아옴
	SELECT
		 @CPPaymentDate = CPPaymentDate
		,@SilkType = SilkType
		,@SellingPrice=Price*CPItemCount
		,@JID=JID
	FROM dbo.APH_CPItemSaleDetails 
	WHERE PTInvoiceID=@PTInvoiceID
	
	select @JCUSN = ChannelId from MU_JCPlanet with(nolock) Where JID = @JID

	--미존재 주문번호
	IF @CPPaymentDate IS NULL BEGIN
		SELECT -327699 as 'ReturnCode'
		RETURN -327699
	END


	--차감 처리 시작
	BEGIN TRY
		BEGIN TRAN
	if @JCUSN = 0
	begin
		--변경해야 할 APH_ChangedSilk와 변경된 값을 구함
		;WITH RemainedSilk(RowNum, CSID, RemainedSilk)
		AS
		(
			SELECT
				 ROW_NUMBER() OVER (ORDER BY b.SilkSort, a.AvailableDate, a.RemainedSilk) AS RowNum
				,a.CSID
				,a.RemainedSilk
			FROM dbo.APH_ChangedSilk AS a
				INNER JOIN dbo.M_SilkType AS b
					ON a.SilkType=b.SilkType
			WHERE 
				a.AvailableStatus='Y' AND a.AvailableDate >= @CPPaymentDate
				AND a.JID=@JID
				AND b.SilkSort >= (SELECT c.SilkSort FROM dbo.M_SilkType AS c WHERE c.SilkType=@SilkType)
				AND b.Usable = 'Y'
		)
		INSERT INTO @ChangedSilk(CSID, RemainedSilk, ChangedSilk)
		SELECT
			 a.CSID
			,CASE
				WHEN SUM(b.RemainedSilk)-@SellingPrice <=0 Then 0
				ELSE SUM(b.RemainedSilk)-@SellingPrice
			END AS RemainedSilk
			,CASE WHEN SUM(b.RemainedSilk)-@SellingPrice <=0 Then 0 Else SUM(b.RemainedSilk)-@SellingPrice End - a.RemainedSilk AS ChangedSilk
		FROM
			RemainedSilk AS a
			Inner Join RemainedSilk AS b ON a.RowNum >= b.RowNum
		GROUP BY a.CSID,a.RemainedSilk
		HAVING SUM(b.RemainedSilk)-@SellingPrice < a.RemainedSilk
		
		SET @RowCount=@@RowCount


		--돈 부족
		IF @RowCount <= 0 OR (SELECT @SellingPrice + SUM(ChangedSilk) FROM @ChangedSilk) <> 0
		BEGIN
			IF @@TRANCOUNT > 0
				ROLLBACK TRAN
			
			UPDATE dbo.APH_CPItemSaleDetails 
			SET JCISCode = 9000
			WHERE PTInvoiceID=@PTInvoiceID
			
			SET @StatusCode = 9000
			
			IF @@ROWCOUNT <> 1
			BEGIN
				SET @ErrorMessage = 'Update APH_CPItemSaleDetails'
				GOTO WriteErrorLog
			END
			
			SELECT -327714 as 'ReturnCode'
			RETURN -327714
		END
		
		UPDATE a SET AvailableStatus = 'N' FROM dbo.APH_ChangedSilk AS a Inner Join @ChangedSilk AS b ON a.CSID = b.CSID Where a.AvailableStatus = 'Y'
		
		IF @@ROWCOUNT <> @RowCount
		BEGIN
			SET @ErrorMessage = 'Update APH_ChangedSilk'
			GOTO WriteErrorLog
		END
		
		
		INSERT INTO dbo.APH_ChangedSilk(InvoiceID, PTInvoiceID, ManagerGiftID, JID, RemainedSilk, ChangedSilk, SilkType
										, SellingTypeID, ChangeDate, AvailableDate, AvailableStatus)
		SELECT
			 a.InvoiceID
			,@PTInvoiceID AS PTInvoiceID
			,a.ManagerGiftID AS ManagerGiftID
			,a.JID
			,b.RemainedSilk
			,b.ChangedSilk
			,a.SilkType
			,2 AS SellingTypeID
			,@CPPaymentDate AS ChangeDate
			,a.AvailableDate
			,'Y' AS AvailableStatus
		FROM
			dbo.APH_ChangedSilk AS a 
			Inner Join @ChangedSilk AS b ON a.CSID=b.CSID
		ORDER BY a.SilkType
		
		IF @@ROWCOUNT <> @RowCount
		BEGIN
			SET @ErrorMessage = 'Insert APH_ChangedSilk'
			GOTO WriteErrorLog
		END

		--실크가 없는 행은 다시 검색되지 않도록 마감 처리
		UPDATE dbo.APH_ChangedSilk
		SET AvailableStatus = 'N'
		WHERE PTInvoiceID = @PTInvoiceID AND RemainedSilk <= 0
	end		
		UPDATE dbo.APH_CPItemSaleDetails 
		SET JCISCode = 10000
		WHERE PTInvoiceID=@PTInvoiceID
		
		IF @@ROWCOUNT <> 1
		BEGIN
			SET @ErrorMessage = 'Update APH_CPItemSaleDetails'
			GOTO WriteErrorLog
		END
		
		
		SET @StatusCode = 10000
		
		IF @@TRANCOUNT > 0
			COMMIT TRAN
	END TRY
	BEGIN CATCH
		IF @@TRANCOUNT > 0
			ROLLBACK TRAN

		--차감 실패
		UPDATE dbo.APH_CPItemSaleDetails 
		SET JCISCode=9000
		WHERE PTInvoiceID=@PTInvoiceID
		
		IF @@RowCount <> 1
		BEGIN
			SET @ErrorMessage = 'Update APH_CPItemSaleDetails'
			GOTO WriteErrorLog
		END
		
		INSERT INTO dbo.AH_Error(ErrorDate, ErrorNumber, ErrorSeverity, ErrorState, ErrorProcedure, ErrorLine, ErrorMessage, ErrorParam)
		SELECT
			 GetDate() AS ErrorDate
			,ERROR_NUMBER() AS ErrorNumber
			,ERROR_SEVERITY() AS ErrorSeverity
			,ERROR_STATE() AS ErrorState
			,ERROR_PROCEDURE() AS ErrorProcedure
			,ERROR_LINE() AS ErrorLine
			,ERROR_MESSAGE() AS ErrorMessage
			,'PTInvoiceID:' + IsNull(@PTInvoiceID, '') AS ErrorParam
		RETURN ERROR_NUMBER() * -1
	END CATCH
	
	SELECT @StatusCode as 'ReturnCode'
	RETURN 0

WriteErrorLog:

	IF @@TRANCOUNT > 0
		ROLLBACK TRAN

	INSERT INTO dbo.AH_Error(ErrorDate, ErrorNumber, ErrorSeverity, ErrorState, ErrorProcedure, ErrorLine, ErrorMessage, ErrorParam)
	SELECT
		 GETDATE() AS ErrorDate
		,65544 AS ErrorNumber
		,0 AS ErrorSeverity
		,0 AS ErrorState
		,'B_DirectPaymentCompletedCPTXByPS' AS ErrorProcedure
		,0 AS ErrorLine
		,@ErrorMessage + ' 문에 적용된 행이 없습니다.' AS ErrorMessage
		,'PTInvoiceID:' + IsNull(@PTInvoiceID, '') AS ErrorParam
	SELECT -65544 as 'StatusCode'
	RETURN -65544 
GO

/****** Object:  StoredProcedure [dbo].[X_GetJCash]    Script Date: 11/4/2022 7:35:33 PM ******/
DROP PROCEDURE [dbo].[X_GetJCash]
GO

/****** Object:  StoredProcedure [dbo].[X_GetJCash]    Script Date: 11/4/2022 7:35:33 PM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

/*********************************************************************************************************
2022-11-04 Since its so fucking complicated to work on sp with output in ADOdb5 library we need to modify
**********************************************************************************************************/
CREATE Procedure [dbo].[X_GetJCash]
	 @JID			Int
As
	Set NoCount On

	declare @PremiumSilk	Int
	declare @Silk			Int
	--declare @VipLevel		Int
	declare @UsageMonth		Int
	declare @Usage3Month	Int

	If @JID Is Null Begin
		Select -65543  as 'StatusCode'
		Return -65543
	End

	If Not Exists(Select '' From dbo.MU_User Where JID=@JID) Begin
		Select -131076  as 'StatusCode'
		Return -131076
	End

	Declare @CurrentDate DateTime
	Set @CurrentDate=GetDate()

	Select
		 @PremiumSilk	= IsNull(Sum(Case When b.SilkGroupType In (3, 4) Then a.RemainedSilk Else 0 End), 0)
		,@Silk			= IsNull(Sum(Case When b.SilkGroupType In (0, 1) Then a.RemainedSilk Else 0 End), 0)
	From dbo.APH_ChangedSilk AS a
		INNER JOIN dbo.M_SilkType AS b ON a.SilkType = b.SilkType
	Where
		    a.AvailableStatus='Y'
		And a.AvailableDate > @CurrentDate
		And a.JID=@JID

	If @PremiumSilk < 0 Or @Silk < 0 Begin
		Select -393258  as 'StatusCode'
		Return -393258
	End
/*
	-- VIP 유저 관련
	---------------------------------------------------------
	SELECT @VipLevel = MAX(VipLv) FROM
	(
		SELECT ISNULL(MAX(VipLv),0) AS VipLv FROM MU_Vip1 WITH(NOLOCK) WHERE JID = @JID
		UNION ALL
		SELECT ISNULL(MAX(VipLv),0) AS VipLv FROM MU_Vip3 WITH(NOLOCK) WHERE JID = @JID
	) TbVip
	
		
	Select @VipLevel = ISNULL(VipLv,0)From MU_VIP_Info with(nolock) Where JID = @JID
	if @VipLevel is null
		Set @VipLevel = 0
*/
	DECLARE @CMonthFirstTime DateTime
	SET @CMonthFirstTime = DATEADD(DAY, -DATEPART(DAY,@CurrentDate),CONVERT(CHAR(10),@CurrentDate,21))+1
	-- 현재월 프리미엄실크 아이템 구매금액
	SELECT @UsageMonth = ISNULL(SUM(Price),0) FROM APH_CPItemSaleDetails WITH(NOLOCK) 
	WHERE 
		JID = @JID AND JCISCode = 10000 AND
		CPPaymentDate >= @CMonthFirstTime AND
		SilkType = 3 AND 
		ServiceCode IN ( 3, 11, 13, 14 )
	-- 현재월 포함 최근 3개월 프리미엄실크 아이템 구매금액
	SELECT @Usage3Month = ISNULL(SUM(Price),0) FROM APH_CPItemSaleDetails WITH(NOLOCK) 
	WHERE 
		JID = @JID AND JCISCode = 10000 AND
		CPPaymentDate >= DATEADD(MONTH,-2,@CMonthFirstTime) AND
		SilkType = 3 AND 
		ServiceCode IN ( 3, 11, 13, 14 )
	---------------------------------------------------------
	-- VIP 유저 관련 끝 2015-11-30
	
	SELECT @PremiumSilk as 'PremiumSilk', @Silk as 'Silk', @UsageMonth as 'UsageMonth', @Usage3Month as 'Usage3Month'
	Return 0
GO
