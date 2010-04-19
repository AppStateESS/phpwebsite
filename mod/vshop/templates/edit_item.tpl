{START_FORM}
<fieldset>
    <legend><strong>{INFO_LABEL}</strong></legend>
    <p><strong>{DEPT_ID_LABEL}</strong><br />{DEPT_ID}</p>
    <p><strong>{TITLE_LABEL}</strong><br />{TITLE}</p>
    <p><strong>{DESCRIPTION_LABEL}</strong><br />{DESCRIPTION}</p>
    <p>{FILE_MANAGER}</p>
    <p>
        {PRICE} <strong>{PRICE_LABEL}</strong><br />
        <!-- BEGIN stock -->{STOCK} <strong>{STOCK_LABEL}</strong><br /><!-- END stock -->
        <!-- BEGIN weight -->{WEIGHT} <strong>{WEIGHT_LABEL}</strong><br /><!-- END weight -->
        <!-- BEGIN shipping -->{SHIPPING} <strong>{SHIPPING_LABEL}</strong><br /><!-- END shipping -->
        <!-- BEGIN taxable -->{TAXABLE} <strong>{TAXABLE_LABEL}</strong><br /><!-- END taxable -->
    </p>
</fieldset>
{SUBMIT}
{END_FORM}
