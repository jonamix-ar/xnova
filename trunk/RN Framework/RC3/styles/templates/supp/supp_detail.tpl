<br>
<div id="content">
<table>
	<tr>
		<td colspan="4" class="c" width="50%"><center>{supp_header}</center></td>
	</tr>
	<tr>
		<td class="c" width="10%"><center>{ticket_id}</center></td>
		<td class="c" width="50%"><center>{subject}</center></td>
		<td class="c" width="15%"><center>{status}</center></td>
		<td class="c" width="25%"><center>{ticket_posted}</center></td>
	</tr>
	{tickets}
</table>


<table>
	<tr>
		<td class="c"><center>{text}</center></td>
	</tr>
	<tr>
		<td class="b"><center>{text_view}</center></td>
	</tr>
</table>

		<table>
	<tr>
		<td class="c" width="50%"><center>{answer_new}</center></td>
	</tr>
		<tr>
		<form action="support.php?ticket={id}&sendenantwort=1" method="POST">
			<td class="b" colspan="2">
			<input type="hidden" name="senden_antwort_id" value="{id}">
				{eintrag}
			</form>
		</td>
	</tr>
</table>
</div>