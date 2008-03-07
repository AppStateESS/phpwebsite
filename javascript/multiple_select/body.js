<style>
.select-box {
    width : 300px;
    height : 150px;
    border : 1px solid black;
    overflow : auto;
    padding : 3px;
}
</style>
    <select id="{id}-select">
    <!-- BEGIN option-list --><option id="{id}-option-{value}" value="{value}">{pick}</option>
<!-- END option-list -->
    </select>
    <input id="{id}-button" type="button" onclick="add_value('{id}', '{select_name}')" value="{add}" />
    <div id="{id}-select-box" class="select-box">{default_matches}</div>
