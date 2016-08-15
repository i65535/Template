<div class="row" style="margin:0">
    <div style="float:left; width:700px; margin:5px 0;">
      <form action="javascript:searchData()" name="searchForm">
        <img src="/data/common/images/icon_search.gif" width="26" height="22" border="0" alt="SEARCH" />
        &nbsp;{$lang['keyword']}&nbsp;<input type="text" name="keyword" size="8">
        &nbsp;{$lang['start_date']}&nbsp;<input class="Wdate" name="start_date" type="text" onClick="WdatePicker()">
        &nbsp;{$lang['end_date']}&nbsp;<input class="Wdate" name="end_date" type="text" onClick="WdatePicker()">
        <input type="submit" value="{$lang['button_search']}" class="btn btn-default ectouch-mb5" />
      </form>
    </div>
    {if $action_link1}
	<div class="pull-right ectouch-mb5"><a href="{$action_link1['href']}" class="btn btn-primary ectouch-mb5">{$action_link1['text']}</a></div>
	{/if}
</div>
<script language="javascript" type="text/javascript" src="__PUBLIC__/js/My97DatePicker/WdatePicker.js"></script>
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
