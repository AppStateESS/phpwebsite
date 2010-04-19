{LABEL}<br />
<table border="0" cellpadding="1" cellspacing="0" width="100%" style="font-size: 1em;">
    <tr>
        <th align="left">{ID_LABEL}</th>
        <th align="left">{NAME_LABEL}</th>
        <th align="left">{QTY_LABEL}</th>
        <th align="right" style="text-align: right">{PRICE_LABEL}</th>
        <th align="right" style="text-align: right">{SUBTOTAL_LABEL}</th>
    </tr>
<!-- BEGIN items -->
    <tr>
        <td align="left">{ID}</td>
        <td align="left">{NAME}</td>
        <td align="left">{QTY}</td>
        <td align="right">{PRICE}</td>
        <td align="right">{SUBTOTAL}</td>
    </tr>
<!-- END items -->
    <tr>
        <th align="left" colspan="4">{TOTAL_LABEL}</th>
        <th align="right" style="text-align: right">{TOTAL}</th>
    </tr>
<!-- BEGIN taxes -->
    <tr>
        <td align="left" colspan="4">{TAX_LABEL}</td>
        <td align="right">{TAX}</td>
    </tr>
<!-- END taxes -->
    <tr>
        <td align="left" colspan="4">{SHIPPING_LABEL}</td>
        <td align="right">{SHIPPING}</td>
    </tr>
    <tr>
        <th align="left" colspan="4">{FINAL_LABEL}</th>
        <th align="right" style="text-align: right">{FINAL}</th>
    </tr>
</table><br />
{SUBMIT}
{END_FORM}
