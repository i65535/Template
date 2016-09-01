<div class="search-wrap">
	<div class="search-content">
		<form action="javascript:searchData()" method="post" name="searchForm">
			<table class="search-tab">
				<tr>
					<th width="70">{:L('keyword')}:</th>
					<td><input class="common-text" placeholder="关键字" name="keyword" value="" id="" type="text"></td>
					<td>{:L('start_date')}:</td>
					<td>&nbsp;<input class="Wdate" name="start_date" type="text" onClick="WdatePicker()"></td>
					<td>{:L('end_date')}:</td>
					<td>&nbsp;<input class="Wdate" name="end_date" type="text" onClick="WdatePicker()"></td>
					<td><input class="btn btn-primary btn2" name="sub" value="{:L('button_search')}" type="submit"></td>
				</tr>
			</table>
		</form>
	</div>
</div>

<script language="JavaScript">
	/**
     * 搜索订单
     */
	function searchData()
	{
		listTable.filter['keyword'] = Utils.trim(document.forms['searchForm'].elements['keyword'].value);
		listTable.filter['start_date'] = Utils.trim(document.forms['searchForm'].elements['start_date'].value);
		listTable.filter['end_date'] = Utils.trim(document.forms['searchForm'].elements['end_date'].value);
		listTable.filter['page'] = 1;
		listTable.loadList();
	}
</script>

