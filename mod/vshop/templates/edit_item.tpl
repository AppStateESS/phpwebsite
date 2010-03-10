{START_FORM}
<fieldset><legend><strong>{INFO_LABEL}</strong></legend>
<p><strong>{DEPT_ID_LABEL}</strong><br />
{DEPT_ID}</p>
<p><strong>{TITLE_LABEL}</strong><br />
{TITLE}</p>
<p><strong>{DESCRIPTION_LABEL}</strong><br />
{DESCRIPTION}</p>
<p>{FILE_MANAGER}</p>
<p>{PRICE} <strong>{PRICE_LABEL}</strong><br />
<!-- BEGIN stock -->{STOCK} <strong>{STOCK_LABEL}</strong><br />
<!-- END stock --> <!-- BEGIN weight -->{WEIGHT} <strong>{WEIGHT_LABEL}</strong><br />
<!-- END weight --> <!-- BEGIN shipping -->{SHIPPING} <strong>{SHIPPING_LABEL}</strong><br />
<!-- END shipping --> <!-- BEGIN taxable -->{TAXABLE} <strong>{TAXABLE_LABEL}</strong><br />
<!-- END taxable --></p>
</fieldset>
<fieldset><legend><strong>{ATTRIBUTES_LABEL}</strong></legend>
<div align="right">{ADD_ATTRIBUTE_LINK}</div>
<table width="99%" cellpadding="4">
    <tr>
        <th>{ATTRIBUTE_SET_LABEL}</th>
        <th>{ATTRIBUTE_VALUE_LABEL}</th>
        <th colspan="2">{ATTRIBUTE_PRICE_LABEL}</th>
        <th colspan="2">{ATTRIBUTE_WEIGHT_LABEL}</th>
        <th>&nbsp;</th>
    </tr>
    <!-- BEGIN attributes -->
    <tr{TOGGLE}>
        <td>{ATTRIBUTE_SET}</td>
        <td>{ATTRIBUTE_VALUE}</td>
        <td>{ATTRIBUTE_PRICE_PREFIX}</td>
        <td>{ATTRIBUTE_PRICE_MOD}</td>
        <td>{ATTRIBUTE_WEIGHT_PREFIX}</td>
        <td>{ATTRIBUTE_WEIGHT_MOD}</td>
        <td>{ATTRIBUTE_LINKS}</td>
    </tr>
    <!-- END attributes -->
</table>
<!-- BEGIN none -->
<p>{NONE}</p>
<!-- END none --></fieldset>
{SUBMIT} {END_FORM}
